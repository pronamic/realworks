<?php namespace CustomPost\Plugin\Updater\AutoDelete;

use Exception;
use Carbon\Carbon;
use CustomPost\Entity;
use CustomPost\Database\Post;
use CustomPost\Plugin\Updater\Logger\LoggerInterface;

class AutoDeleter
{
	protected $entity;

	protected $logger;

	public function __construct(Entity $entity, LoggerInterface $logger)
	{
		$this->entity = $entity;
		$this->logger = $logger;
	}

	public function deleteSince($days)
	{
		$unpublishedAt = Carbon::now()->subDays($days);

		$models = $this->findDeletions($unpublishedAt);

		if ( ! empty($models))
		{
			$this->deleteModels($models, $unpublishedAt);
		}
	}

	protected function findDeletions(Carbon $unpublishedAt)
	{
		/**
		 * 2021-02-26 - Pronamic, RvdS
		 * Carbon date provided in `where()` instead of date string.
		 *
		 * print_r( $this->entity->search()->where('unpublishedAt', '<=', $unpublishedAt->format('Y-m-d H:i:s'))->all()->go()->getQuery()->request);
		 * return iterator_to_array($this->entity->search()->where('unpublishedAt', '<=', $unpublishedAt->format('Y-m-d H:i:s'))->all());
		 */

		return iterator_to_array($this->entity->search()->where('unpublishedAt', '<=', $unpublishedAt)->all());
	}

	protected function deleteModels(array $models, Carbon $unpublishedAt)
	{
		$this->logger->info('Automatisch verwijderen van onbeschikbare objecten sinds '.$unpublishedAt->format('d-m-Y H:i').'.');

		foreach ($models as $model)
		{
			$this->deleteModel($model);
		}

		$this->logger->info("Automatisch verwijderen van objecten voltooid.");
	}

	protected function deleteModel(Post $model)
	{
		try
		{
			$this->logger->log("Object {$model->primary()} (WordPress #{$model->getPrimaryKey()}) is niet meer beschikbaar sinds ".$model->unpublishedAt->format('d-m-Y H:i')." en wordt verwijderd.");

			$model->delete();
		}
		catch (Exception $e)
		{
			error_log((string) $e);

			$this->logger->object($model, 'exception');
			$this->logger->error($e->getMessage());
		}
	}
}
