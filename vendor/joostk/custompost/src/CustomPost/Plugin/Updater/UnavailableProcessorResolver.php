<?php namespace CustomPost\Plugin\Updater;

use CustomPost\Entity;
use JoostK\Illuminate\Container\Container;

class UnavailableProcessorResolver
{
	protected $app;

	public function __construct(Container $app)
	{
		$this->app = $app;
	}

	public function resolve(Entity $entity)
	{
		$action = $entity['config']['unavailable'];

		if ($this->app->bound($key = "updater.unavailable.{$action}"))
		{
			return $this->app->make($key, $entity);
		}
		else
		{
			return $this->app->make('updater.unavailable.status', $entity)->setStatus($action);
		}
	}
}
