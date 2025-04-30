<?php namespace CustomPost\Plugin\Admin;

use JoostK\Wordpress\Support\ServiceProvider;

class EntitiesServiceProvider extends ServiceProvider
{
	public function boot()
	{
		$this->enableUsedSources();
	}

	protected function enableUsedSources()
	{
		foreach ($this->app['config']['updater.sources'] as $source)
		{
			foreach ($source['entities'] as $entity)
			{
				$instance = $this->app->entity($entity);

				if ($instance) $instance->setEnabled(true);
			}
		}
	}
}
