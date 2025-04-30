<?php namespace Realworks\Nieuwbouw\Type;

use CustomPost\Database\ModelInterface;
use CustomPost\Plugin\Updater\DirtyCheckStrategy;

class DateDirtyCheckStrategy implements DirtyCheckStrategy {

    public function isDirty(ModelInterface $model)
    {
        return $model->datumWijziging->isDirty();
    }

}
