<?php namespace CustomPost\Plugin\Updater\UnavailableProcessors;

use CustomPost\Database\Post;
use CustomPost\Plugin\Updater\UnavailableProcessor;

class DeleteModel extends UnavailableProcessor
{
	public function perform(Post $model)
	{
		$this->logger->info("Object {$model->primary()} (WordPress #{$model->getPrimaryKey()}) is verwijderd.");

		$model->delete();
	}
}
