<?php namespace Realworks\Nieuwbouw\Project;

use CustomPost\ServiceProvider;
use CustomPost\Support\RangeField;

class AdditionalFieldsProvider extends ServiceProvider
{
	public function register()
	{
		$me = $this;

		$this->entity->resolving('fields.manager', function() use ($me)
		{
			$me->registerRangeField('inhoud');
			$me->registerRangeField('woonoppervlakte');
			$me->registerRangeField('perceeloppervlakte');
			$me->registerRangeField('koopAanneemsom');
			$me->registerRangeField('huurprijs');
		});
	}

	public function registerRangeField($field)
	{
		$this->entity->custom($field, function($project) use ($field)
		{
			return RangeField::range(
				$project->get("{$field}Van"),
				$project->get("{$field}TotEnMet")
			);
		});
	}
}
