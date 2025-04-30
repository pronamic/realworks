<?php namespace CustomPost\Plugin\Updater\Batch;

use Exception;
use CustomPost\Entity;
use CustomPost\Plugin\Updater\UpdateCancelledException;

class ChildEntityUpdater extends EntityUpdater
{
	public function update($parent, $data)
	{
		$this->user = $parent->id();

		$this->logger->setSource($this->entity->getIdentifier(), null);
		$this->logger->parent()->archive($this->getId());

		try
		{
			$this->processData($data);
		}
		catch (UpdateCancelledException $exception)
		{
			$this->logger->info('Bijwerken geannuleerd.');
		}
		catch (Exception $exception)
		{
			$this->logger->error($exception->getMessage());
		}

		$this->fireEntityEvents();
		$this->logger->close();

		// Retrow the exception, refactor into finally clause for PHP 5.5
		if (isset($exception)) throw $exception;
	}

	protected function saveModel($model)
	{
		parent::saveModel($model);

		$model->update(array(
			'post_parent' => $this->user,
		));
	}
}
