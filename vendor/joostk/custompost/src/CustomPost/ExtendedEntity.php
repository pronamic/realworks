<?php namespace CustomPost;

use Closure;
use ReflectionFunction;
use WP_Query as WordpressQuery;
use CustomPost\Support\Template;
use CustomPost\Formatter\Formatter;
use CustomPost\Search\SearchBuilder;
use CustomPost\Support\QueryCollection;

abstract class ExtendedEntity extends Entity
{
	public function form()
	{
		return $this['search.entity']->getForm();
	}

	public function getPostType()
	{
		return $this['config']['post.type'];
	}

	public function postType()
	{
		return $this->getPostType();
	}

	public function post()
	{
		return $this->getCurrentPost();
	}

	public function archive()
	{
		return get_post_type_archive_link($this->getPostType());
	}

	public function isArchive($name, array $query = array())
	{
		if (is_array($name)) list($name, $query) = array(null, $name);

		return $this['search.rewriter']->is($name, $query);
	}

	public function query($args = array())
	{
		return new QueryCollection($this, new WordpressQuery(wp_parse_args($args, array(
			'post_type' => $this->getPostType(),
		))));
	}

	public function search($query = array(), $args = array())
	{
		if (func_num_args() > 0)
		{
			_deprecated_function('::search($query)', '20151224', '::where(...)');

			return with(new SearchBuilder($this))->args($args)->applyQueryDeprecated($query);
		}
		else
		{
			return new SearchBuilder($this);
		}
	}

	public function count($query = array(), $args = array())
	{
		_deprecated_function('::count($query)', '20151224', '::where(...)->count()');

		return with(new SearchBuilder($this))->all()->arg('fields', 'ids')->args($args)->applyQueryDeprecated($query)->total();
	}

	public function template($name)
	{
		return new Template($this, $name);
	}

	public function macro($method, $callback)
	{
		return $this['fields.manager']->macro($method, $callback);
	}

	public function custom($name, $callback, $type = 'string')
	{
		$this->macro($name, function($object) use ($name, $callback, $type)
		{
			$result = call_user_func_array($callback, func_get_args());

			if ( ! $result instanceof Formatter)
			{
				$result = $object->custom($name, $result, $type);

				// Guess label from field name
				$result->getField()->label(null);
			}

			return $result;
		});
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

	public function find($id, $key = null)
	{
		return $this['repository']->find($id, $key);
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

	public function maps($type = 'all')
	{
		return $this['maps']->{$type}($GLOBALS['wp_query']);
	}

	public function saveConfiguration()
	{
		$default = $this->getConfiguration();

		$changed = array_diff_assoc_recursive($this['config']->get(), $default);

		$this['events']->fire('config.save');

		return $this['config.storage']->save($changed);
	}

	public function saveSearchFields()
	{
		$fields = $this['search.entity']->getFields();

		$serialized = $this['search.fields.serializer']->serialize($fields);

		return $this['search.fields.storage']->save($serialized);
	}

	public function updateSearchFields($fields)
	{
		$updated = $this['search.fields.deserializer']->deserialize($fields);

		$this['search.entity']->reload($updated);

		$this['search.bindingupdater']->update();

		$this->saveSearchFields();
	}

	public function watchFieldsUpdates($dir)
	{
		$entity = $this;

		$entity['migrator']->watch("{$dir}/fields.php", function() use ($entity)
		{
			$entity['fields.cache']->flush();
		});
	}

	public function get($field)
	{
		return $this['fields.manager']->traverse($field);
	}

	public function __call($method, $parameters)
	{
		if (method_exists('CustomPost\Search\SearchBuilder', $method))
		{
			return call_user_func_array(array(new SearchBuilder($this), $method), $parameters);
		}

		$field = array_shift($parameters);

		return call_user_func_array(array($this->get($field), $method), $parameters);
	}
}
