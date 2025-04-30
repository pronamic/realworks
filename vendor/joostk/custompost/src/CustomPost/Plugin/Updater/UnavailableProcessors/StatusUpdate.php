<?php namespace CustomPost\Plugin\Updater\UnavailableProcessors;

use CustomPost\Database\Post;
use CustomPost\Plugin\Updater\UnavailableProcessor;

class StatusUpdate extends UnavailableProcessor
{
	protected $status;

	public function setStatus($status)
	{
		$this->status = $status;

		return $this;
	}

	protected function getQuery(array $models, $user)
	{
		$table = $this->entity['fields.manager']->getTable();
		$posts = $this->entity['db']->getTableName('posts');

		list($query, $bindings) = parent::getQuery($models, $user);

		array_unshift($bindings, $this->status);

		$join = "JOIN `{$posts}` p ON p.ID = t.{$table->getPrimaryKey()}";

		$query = str_replace('WHERE ', "{$join} WHERE p.post_status <> %s AND ", $query);

		return array($query, $bindings);
	}

	public function perform(Post $model)
	{
		$this->logger->object($model, "status: {$this->status}");

		$model->update(array(
			'post_status' => $this->status,
		));
	}
}
