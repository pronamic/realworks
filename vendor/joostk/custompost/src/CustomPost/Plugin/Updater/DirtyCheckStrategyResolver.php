<?php namespace CustomPost\Plugin\Updater;

use CustomPost\Entity;
use JoostK\Illuminate\Container\Container;

class DirtyCheckStrategyResolver {

	protected $app;

	protected $force;

	public function __construct(Container $app, $force)
	{
		$this->app = $app;
		$this->force = $force;
	}

	public function resolve(Entity $entity)
	{
		// If force is enabled, always assume that the model is dirty
		if ($this->force)
		{
			return new ForceDirtyCheckStrategy;
		}

		if ($entity->bound('updater.dirtycheck'))
		{
			return $entity['updater.dirtycheck'];
		}
		else
		{
			return new ModelDirtyCheckStrategy;
		}
	}

}
