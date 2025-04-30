<?php namespace CustomPost\Providers;

use CustomPost\ServiceProvider;

class MacrosServiceProvider extends ServiceProvider
{
	public function register()
	{
		$this->entity->resolving('fields.manager', function($manager, $entity)
		{
			foreach ($entity->getMacros() as $macro => $callback)
			{
				$manager->macroUnless($macro, $callback);
			}
		});
	}
}
