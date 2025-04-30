<?php namespace CustomPost\Database;

use ReflectionClass;
use ReflectionMethod;
use CustomPost\Entity;

class Migrator
{
	const RAN = '_cp_ran_migrations';

	const FILES = '_cp_file_versions';

	protected $ran;

	protected static $files;

	protected $entity;

	public function __construct(Entity $entity)
	{
		$this->entity = $entity;

		$this->determineRanMigrations();

		$this->determineFileVersions();
	}

	protected function determineRanMigrations()
	{
		$this->ran = get_option($this->ranOptionName(), array());

		if (empty($this->ran))
		{
			$this->ran = get_option(static::RAN, array());

			update_option($this->ranOptionName(), $this->ran);
		}
	}

	protected function ranOptionName()
	{
		return static::RAN.'_'.$this->entity->getIdentifier();
	}

	protected function determineFileVersions()
	{
		if (static::$files === null)
		{
			static::$files = get_option(static::FILES, array());
		}
	}

	public function run($updater)
	{
		$updated = false;
		$class = new ReflectionClass($updater);

		foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method)
		{
			$updated |= $this->ensure($updater, $method);
		}

		if ($updated) update_option($this->ranOptionName(), $this->ran);
	}

	public function watch($path, $callback)
	{
		$version = filemtime($path);

		if (array_get(static::$files, $path) !== $version)
		{
			$callback();

			static::$files[$path] = $version;

			update_option(static::FILES, static::$files);
		}
	}

	protected function ensure($updater, ReflectionMethod $method)
	{
		return $this->pending($method) ? $this->execute($updater, $method) : false;
	}

	protected function execute($updater, ReflectionMethod $method)
	{
		if ($method->invoke($updater, $this, $this->entity) !== false)
		{
			$this->ran[] = $this->name($method);

			return true;
		}
		else
		{
			return false;
		}
	}

	protected function pending(ReflectionMethod $method)
	{
		return ! starts_with($method->name, '_') and ! in_array($this->name($method), $this->ran);
	}

	protected function name(ReflectionMethod $method)
	{
		return $method->class.'::'.$method->name;
	}

	public function update($field)
	{
		list($table, $field) = $this->split($field);

		$table->updateColumn($field);
	}

	public function delete($field)
	{
		list($table, $field) = $this->split($field);

		$table->deleteColumn($field);
	}

	public function rename($field, $from)
	{
		list($table, $to) = $this->split($field);

		$table->renameColumn($from, $to);
	}

	protected function split($field)
	{
		$parts = explode('.', $field);

		$field = array_pop($parts);
		$parent = implode('.', $parts);

		return array($this->entity['fields.manager']->traverse($parent)->getTable(), $field);
	}
}
