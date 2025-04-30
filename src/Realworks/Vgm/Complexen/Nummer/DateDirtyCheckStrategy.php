<?php namespace Realworks\Vgm\Complexen\Nummer;

use CustomPost\Database\ModelInterface;
use CustomPost\Plugin\Updater\DirtyCheckStrategy;

class DateDirtyCheckStrategy implements DirtyCheckStrategy {

    public function isDirty(ModelInterface $model)
    {
        return $model->datumWijziging->isDirty();
    }

}
