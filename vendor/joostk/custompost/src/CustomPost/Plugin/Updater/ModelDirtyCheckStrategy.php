<?php namespace CustomPost\Plugin\Updater;

use CustomPost\Database\ModelInterface;

class ModelDirtyCheckStrategy implements DirtyCheckStrategy {

	public function isDirty(ModelInterface $model)
	{
		return $model->isDirty();
	}

}
