<?php namespace CustomPost\Plugin\Updater;

use Carbon\Carbon;
use CustomPost\Entity;
use CustomPost\Database\Post;
use CustomPost\Fields\Types\StringT;
use CustomPost\Plugin\Updater\Logger\LoggerInterface;

abstract class UnavailableProcessor
{
	protected $entity;

	protected $logger;

	public function __construct(Entity $entity)
	{
		$this->entity = $entity;
	}

	public function setLogger(LoggerInterface $logger)
	{
		$this->logger = $logger;

		return $this;
	}

	public function process(array $models, $user)
	{
		if ( ! $this->checkTable() or empty($user)) return;

		list($query, $bindings) = $this->getQuery($models, $user);

		$deleted = $this->entity['db']->column($query, 0, $bindings);

		switch ($count = count($deleted))
		{
			case 0: break;
			case 1: $this->logger->info("Eén object niet langer beschikbaar."); break;
			default: $this->logger->info("{$count} objecten niet langer beschikbaar."); break;
		}

		foreach ($deleted as $id)
		{
			$model = $this->entity['repository']->find($id);

			if ($model and $model->exists()) $this->apply($model);
		}
	}

	protected function checkTable()
	{
		$table = $this->entity['database.table'];

		return $table->hasColumn('user');
	}

	protected function getQuery(array $models, $user)
	{
		$table = $this->entity['fields.manager']->getTable();
		$where = '';

		$updated = $this->getIds($models);

		if (count($updated) > 0)
		{
			$binding = $this->entity['fields.manager']->getPrimaryField() instanceof StringT ? '%s' : '%d';
			$pattern = implode(', ', array_fill(0, count($updated), $binding));

			$where .= "AND t.id NOT IN ({$pattern})";
		}

		// Add user to bindings array
		array_unshift($updated, $user);

		return array(
			"SELECT t.`{$table->getPrimaryKey()}`
			FROM `{$table->getName()}` t
			WHERE t.user = %s {$where}",

			$updated
		);
	}

	protected function getIds(array $models)
	{
		$ids = array();

		foreach ($models as $model)
		{
			$ids[] = $model->primary()->value();
		}

		return $ids;
	}

	public function apply(Post $model)
	{
		$this->perform($model);

		$this->markUnpublished($model);

		$this->fireEvent($model);
	}

	protected function markUnpublished(Post $model)
	{
		if ($model->exists())
		{
			if ($this->entity['fields.manager']->getField('unpublishedAt') !== null)
			{
				$model->unpublishedAt = Carbon::now();
			}

			$model->save();
		}
	}

	protected function fireEvent(Post $model)
	{
		if ($model->exists())
		{
			$model->fireEvent('unpublished');
		}
	}

	abstract public function perform(Post $model);
}
