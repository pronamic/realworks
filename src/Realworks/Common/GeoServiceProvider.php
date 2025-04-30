<?php namespace Realworks\Common;

use CustomPost\Fields\Field;
use CustomPost\Plugin\Geo\GeoServiceProvider as BaseServiceProvider;

class GeoServiceProvider extends BaseServiceProvider
{
	public function additionalFields()
	{
		return array(
			Field::string('provincie'),
		);
	}

	public function assignGeoData($model, array $data)
	{
		$model->provincie = array_get($data, 'address.administrative_area_level_1');
	}

	protected function requiresUpdate($model)
	{
		return $model->adres->isDirty() or $model->plaats->isDirty() or $model->provincie->isEmpty();
	}

	protected function address($model)
	{
		return "{$model->adres} {$model->plaats} Nederland";
	}
}
