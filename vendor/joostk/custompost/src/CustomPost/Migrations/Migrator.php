<?php namespace CustomPost\Migrations;

use ReflectionClass;
use ReflectionMethod;
use CustomPost\Plugin\Plugin;

class Migrator
{
	const RAN = '_cp_ran_migrations';

	const FILES = '_cp_file_versions';

	protected $ran;

	protected static $files;

	protected $identifier;

	public function __construct($identifier)
	{
		$this->identifier = $identifier;

		$this->determineRanMigrations();

		$this->determineFileVersions();
	}

	protected function determineRanMigrations()
	{
		$this->ran = get_option($this->ranOptionName(), array());
	}

	protected function determineFileVersions()
	{
		if (static::$files === null)
		{
			static::$files = get_option(static::FILES, array());
		}
	}

	protected function ranOptionName()
	{
		return static::RAN.'_'.$this->identifier;
	}

	public function run($migration)
	{
		$updated = false;
		$class = new ReflectionClass($migration);

		foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method)
		{
			$updated |= $this->ensure($migration, $method);
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

	protected function ensure($migration, ReflectionMethod $method)
	{
		return $this->pending($method) ? $this->execute($migration, $method) : false;
	}

	protected function execute($migration, ReflectionMethod $method)
	{
		if ($this->invoke($migration, $method) !== false)
		{
			$this->ran[] = $this->name($method);

			return true;
		}
		else
		{
			return false;
		}
	}

	protected function invoke($migration, ReflectionMethod $method)
	{
		return $method->invoke($migration);
	}

	protected function pending(ReflectionMethod $method)
	{
		return ! starts_with($method->name, '_') and ! in_array($this->name($method), $this->ran);
	}

	protected function name(ReflectionMethod $method)
	{
		return $method->class.'::'.$method->name;
	}
}
