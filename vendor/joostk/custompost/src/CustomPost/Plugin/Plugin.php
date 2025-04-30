<?php namespace CustomPost\Plugin;

use Closure;
use CustomPost\Entity;
use ReflectionFunction;
use JoostK\Wordpress\Support\ProviderContainer;

abstract class Plugin extends ProviderContainer
{
	const API = 'https://tussendoor.nl/get-the-request/wp-updates/';

	protected $path;

	protected $identifier;

	protected $api;

	protected $title;

	protected $entities = array();

	public function start()
	{
		$this->register(new PluginServiceProvider($this));
	}

	public function boot()
	{
		parent::boot();

		foreach ($this->entities as $entity)
		{
			$entity->boot();
		}
	}

	public function addEntity(Entity $entity)
	{
		$identifier = $entity->getIdentifier();

		$this->entities[$identifier] = $entity;
		$this->instance($identifier, $entity);

		return $this;
	}

	public function getIdentifier()
	{
		return $this->identifier;
	}

	public function getPluginPath()
	{
		return $this->path;
	}

	public function getTitle()
	{
		return $this->title;
	}

	public function getPluginApiUrl()
	{
		return static::API.$this->api;
	}

	public function entity($identifier)
	{
		return isset($this->entities[$identifier]) ? $this->entities[$identifier] : null;
	}

	public function entities()
	{
		return $this->entities;
	}

	public function disable($identifier)
	{
		$entity = $this->entity($identifier);

		if ($entity)
		{
			$this->disableChildren($entity);

			unset($this->entities[$identifier]);
		}
	}

	protected function disableChildren($entity)
	{
		foreach ($entity->children() as $child)
		{
			$this->disable($child->getIdentifier());
		}
	}

	public function adminUrl($hash)
	{
		return admin_url("admin.php?page={$this->getIdentifier()}#{$hash}");
	}

	public function filter($action, Closure $callback, $priority = 10)
	{
		$ref = new ReflectionFunction($callback);

		add_filter("{$this->identifier}: {$action}", $callback, $priority, $ref->getNumberOfParameters());
	}

	public function filtered($action)
	{
		$args = array_slice(func_get_args(), 1);

		return apply_filters_ref_array("{$this->identifier}: {$action}", $args);
	}

	public function listen($event, $callback)
	{
		return $this['events']->listen($event, $callback);
	}

	public function config($key, $value = null)
	{
		if (func_num_args() === 1)
		{
			return $this['config']->get($key);
		}
		else
		{
			return $this['config']->set($key, $value);
		}
	}

	public function listenAll($event, $callback)
	{
		foreach ($this->entities as $entity)
		{
			$entity['events']->listen($event, function() use ($entity, $callback)
			{
				$parameters = func_get_args();

				array_unshift($parameters, $entity);

				return call_user_func_array($callback, $parameters);
			});
		}
	}

	public function fireAll($event, $payload = array(), $halt = false)
	{
		foreach ($this->entities as $entity)
		{
			$entity['events']->fire($event, $payload, $halt);
		}
	}

	abstract public function getConfiguration();

	public function saveConfiguration()
	{
		$this['events']->fire('save.config');

		$default = $this->getConfiguration();

		$changed = array_diff_assoc_recursive($this['config']->get(), $default);

		return $this['config.storage']->save($changed);
	}

	public function __call($method, $parameters)
	{
		array_unshift($parameters, $method);

		return call_user_func_array(array($this, 'make'), $parameters);
	}
}
