<?php namespace CustomPost\Plugin\Updater;

use CustomPost\Database\ModelInterface;

class ForceDirtyCheckStrategy implements DirtyCheckStrategy {

	public function isDirty(ModelInterface $model)
	{
		return true;
	}

}
