<?php namespace JoostK\Wordpress\Support;

use JoostK\Illuminate\Container\Container;

abstract class Facade
{
	protected static $container;

	public static function setContainer(Container $container)
	{
		static::$container = $container;
	}

	public static function getContainer()
	{
		return static::$container;
	}

	protected static function getFacadeAccessor()
	{
		return null;
	}

	protected static function resolveInstance()
	{
		$accessor = static::getFacadeAccessor();

		return $accessor ? static::$container[$accessor] : static::$container;
	}

	public static function __callStatic($method, $args)
	{
		$instance = static::resolveInstance();

		switch (count($args))
		{
			case 0:
				return $instance->{$method}();
			case 1:
				return $instance->{$method}($args[0]);
			case 2:
				return $instance->{$method}($args[0], $args[1]);
			case 3:
				return $instance->{$method}($args[0], $args[1], $args[2]);
			case 4:
				return $instance->{$method}($args[0], $args[1], $args[2], $args[3]);
			case 5:
				return $instance->{$method}($args[0], $args[1], $args[2], $args[3], $args[4]);
			default:
				return call_user_func_array(array($instance, $method), $args);
		}
	}
}
