<?php namespace CustomPost\Plugin\Updater;

use Exception;
use RuntimeException;
use CustomPost\Entity;
use CustomPost\Reader\Reader;
use CustomPost\Database\Post;
use CustomPost\Database\Model;
use CustomPost\Plugin\Updater\Logger\LoggerInterface;

abstract class EntityUpdater
{
	protected $entity;

	protected $reader;

	protected $logger;

	protected $extractor;

	protected $canceller;

	protected $dirtyCheckStrategy;

	protected $unavailableProcessor;

	public function __construct(Entity $entity, Reader $reader, LoggerInterface $logger, ArchiveExtractorInterface $extractor, Canceller $canceller, DirtyCheckStrategy $dirtyCheckStrategy)
	{
		$this->entity = $entity;
		$this->reader = $reader;
		$this->logger = $logger;
		$this->extractor = $extractor;
		$this->canceller = $canceller;
		$this->dirtyCheckStrategy = $dirtyCheckStrategy;
	}

	public function setUnavailableProcessor(UnavailableProcessor $unavailableProcessor)
	{
		$this->unavailableProcessor = $unavailableProcessor->setLogger($this->logger);

		return $this;
	}

	public function processArchive($path)
	{
		$this->logger->setArchive($path);

		try
		{
			$data = $this->extractor->extract($path);

			if ($data)
			{
				$this->processData($data);

				$this->fireEntityEvents();
			}
			else
			{
				$this->logger->info('Het archief bevat geen data.');
			}
		}
		catch (Exception $e)
		{
			$this->fireEntityEvents();

			throw $e;
		}
	}

	public function unavailable(Post $model)
	{
		$this->unavailableProcessor->apply($model);
	}

	abstract protected function processData($data);

	protected function skipModels(array $models)
	{
		foreach ($models as $index => $model)
		{
			if ($this->entity->filtered('updater.skip', false, $model))
			{
				$this->logger->info("Object {$model->primary()} is genegeerd en wordt niet geïmporteerd.");

				$model->delete();

				unset($models[$index]);
			}
		}

		return $models;
	}

	protected function processModel($model)
	{
		if ($this->dirtyCheckStrategy->isDirty($model))
		{
			$state = $model->exists() ? 'updated' : 'added';
			$dirty = $state === 'updated' ? $model->getDirty() : null;

			try
			{
				$model->save();
			}
			catch (Exception $e)
			{
				error_log((string) $e);

				$this->logger->object($model, 'exception');
				$this->logger->error($e->getMessage());

				return;
			}

			$this->logger->object($model, $state);

			if ($dirty !== null) $this->logChangedFields($dirty);

			$this->fireModelEvents($model, 'updated');
		}
		else
		{
			$this->logger->object($model, 'unchanged');

			$this->fireModelEvents($model, 'unchanged');
		}
	}

	protected function fireModelEvents($model, $event)
	{
		try
		{
			$model->fireEvent($event);
		}
		catch (UpdateCancelledException $e)
		{
			throw $e;
		}
		catch (Exception $e)
		{
			error_log((string) $e);

			$this->logger->error($e->getMessage());
		}
	}

	protected function logChangedFields(array $dirty)
	{
		$changes = array();

		foreach (array_dot($dirty) as $field)
		{
			$changes[] = array(
				'field' => $field->name(),
				'label' => $field->label(),
				'diff' => $field->diff(),
			);
		}

		if ($changes) $this->logger->changes($changes);
	}

	protected function fireEntityEvents()
	{
		$this->entity['events']->fire('entity.updated', array($this->entity, $this->logger));
	}
}
