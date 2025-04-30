<?php namespace CustomPost\Plugin\Updater\Single;

use CustomPost\Plugin\Updater\Updater as BaseUpdater;

class Updater extends BaseUpdater
{
	protected $entity;

	public function setEntity($entity)
	{
		$this->entity = $entity;
	}

	public function applyUpdate()
	{
		$updater = $this->newEntityUpdater($this->entity);
		$source = $this->resolveSource($this->entity, array());

		return $this->updateEntity($updater, $source);
	}

	protected function newEntityUpdater($entity)
	{
		return $this->container->make('updater.entity', array($entity, $this->logger));
	}

	protected function resolveSource($entity, array $settings)
	{
		$source = parent::resolveSource($entity, $settings);

		$source->setPath(
			$this->pathResolver->getBasePath().
			$this->pathResolver->nextDailyFile('.xml')
		);

		return $source;
	}
}
