<?php namespace CustomPost\Plugin\Updater\Batch;

use Closure;
use CustomPost\Plugin\Updater\EntityUpdater;
use CustomPost\Plugin\Updater\Updater as BaseUpdater;

class Updater extends BaseUpdater
{
	protected $sources;

	protected $predicate;

	public function setSources(array $sources)
	{
		$this->sources = $sources;

		return $this;
	}

	public function setPredicate(Closure $predicate)
	{
		$this->predicate = $predicate;

		return $this;
	}

	protected function applyUpdate()
	{
		$result = true;

		foreach ($this->sources as $settings)
		{
			foreach ($settings['entities'] as $entity)
			{
				if ($this->predicate and ! call_user_func($this->predicate, array('entity' => $entity, 'user' => $settings['user'])))
				{
					continue;
				}

				$result = $this->updateSource($entity, $settings) && $result;
			}
		}

		return $result;
	}

	protected function updateSource($entity, array $settings)
	{
		$user = $settings['user'] ?: 'handmatig geüpload';

		$this->logger->info("Bijwerken van {$entity}: {$user}.");

		$updater = $this->newEntityUpdater($entity, $settings['user']);
		$source = $this->resolveSource($entity, $settings);

		return $this->updateEntity($updater, $source);
	}

	protected function newEntityUpdater($entity, $user)
	{
		$updater = $this->container->make('updater.entity', array($entity, $user, $this->logger));

		return $updater->setUser($user);
	}

	protected function resolveSource($entity, array $settings)
	{
		$source = parent::resolveSource($entity, $settings);

		$source->setIdentifier($entity);
		$source->setCredentials($settings['user'], array_get($settings, 'password'));
		$source->setPath(
			$this->pathResolver->getBasePath().
			$this->pathResolver->getEntityPath($entity, $settings['user'])
		);

		return $source;
	}

	protected function processData(EntityUpdater $updater, $path)
	{
		$this->logger->pop()->archive($updater->getId());

		return parent::processData($updater, $path);
	}
}
