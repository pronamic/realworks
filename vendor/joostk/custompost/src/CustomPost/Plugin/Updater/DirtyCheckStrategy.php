<?php namespace CustomPost\Plugin\Updater;

use CustomPost\Database\ModelInterface;

interface DirtyCheckStrategy {

	public function isDirty(ModelInterface $model);

}
