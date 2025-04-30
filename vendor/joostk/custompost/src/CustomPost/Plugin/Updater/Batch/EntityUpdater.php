<?php namespace CustomPost\Plugin\Updater\Batch;

use Exception;
use CustomPost\Plugin\Updater\UpdateCancelledException;
use CustomPost\Plugin\Updater\EntityUpdater as BaseEntityUpdater;

class EntityUpdater extends BaseEntityUpdater
{
	protected $user;

	public function getId()
	{
		return $this->logger->getId();
	}

	public function setUser($user)
	{
		$this->user = $user;

		return $this;
	}

	public function processArchive($path)
	{
		$this->logger->setSource($this->entity->getIdentifier(), $this->user);

		try
		{
			parent::processArchive($path);
		}
		catch (UpdateCancelledException $exception)
		{
			$this->logger->info('Bijwerken geannuleerd.');
		}
		catch (Exception $exception)
		{
			$this->logger->error($exception->getMessage());
		}

		$this->logger->close();

		// Retrow the exception, refactor into finally clause for PHP 5.5
		if (isset($exception)) throw $exception;
	}

	protected function processData($data)
	{
		$models = $this->reader->read($data);
		$models = $this->skipModels($models);

		$this->logger->total(count($models));
		$this->logger->info('Bijwerken gestart.');

		foreach ($models as $model)
		{
			$this->processModel($model);
			$this->canceller->check();

			gc_collect_cycles();
		}

		if ($this->user !== '__uploaded__')
		{
			$this->unavailableProcessor->process($models, $this->user);
		}

		$this->logger->info('Bijwerken beëindigd.');
	}

	protected function processModel($model)
	{
		if ($this->user) $model->user = $this->user;

		if ($this->entity['fields.manager']->getField('unpublishedAt') !== null)
		{
			$model->unpublishedAt = null;
		}

		parent::processModel($model);
	}
}
