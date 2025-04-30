<?php namespace Realworks\Vgm\Complexen\Type;

use CustomPost\ServiceProvider;
use CustomPost\Support\RangeField;

class AdditionalFieldsProvider extends ServiceProvider
{
	public function boot()
	{
		$me = $this;

		$this->entity->resolving('fields.manager', function() use ($me)
		{
			$me->registerRangeField('inhoud');
			$me->registerRangeField('woonoppervlakte');
			$me->registerRangeField('perceeloppervlakte');
			$me->registerRangeField('woonkameroppervlakte');
			$me->registerRangeField('koopAanneemsom');
			$me->registerRangeField('huurprijs');
		});
	}

	public function registerRangeField($field)
	{
		$this->entity->custom($field, function($type) use ($field)
		{
			return RangeField::range(
				$type->get("{$field}Van"),
				$type->get("{$field}TotEnMet")
			);
		});
	}
}
