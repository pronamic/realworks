<?php namespace JoostK\Wordpress\Support;

use JoostK\Illuminate\Container\Container;

class ServiceProvider
{
	protected $app;

	public function __construct(Container $app)
	{
		$this->app = $app;
	}

	public function register()
	{

	}

	public function boot()
	{

	}

	public function replaceWith($class)
	{
		return new $class($this->app);
	}
}
