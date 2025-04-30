<?php namespace CustomPost\Plugin\Updater\UnavailableProcessors;

use CustomPost\Database\Post;
use CustomPost\Plugin\Updater\UnavailableProcessor;

class VoidAction extends UnavailableProcessor
{
	protected function getQuery(array $models, $user)
	{
		list($query, $bindings) = parent::getQuery($models, $user);

		if ($this->entity['fields.manager']->getTable()->hasColumn('unpublishedAt'))
		{
			$query = str_replace('WHERE ', 'WHERE t.unpublishedAt IS NULL AND ', $query);
		}

		return array($query, $bindings);
	}

	public function perform(Post $model)
	{
		$this->logger->object($model, 'void');
	}
}
