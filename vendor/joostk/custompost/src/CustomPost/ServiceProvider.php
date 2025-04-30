<?php namespace CustomPost;

use JoostK\Illuminate\Container\Container;

abstract class ServiceProvider
{
	protected $entity;

	protected $app;

	public function __construct(Entity $entity, Container $app = null)
	{
		$this->entity = $entity;
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
		return new $class($this->entity, $this->app);
	}
}
