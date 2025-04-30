<?php namespace CustomPost\Reader;

use CustomPost\ServiceProvider;

class ReaderServiceProvider extends ServiceProvider
{
	public function register()
	{
		$this->registerRootReader();
	}

	protected function registerRootReader()
	{
		$this->entity->singleton('reader.root', function($entity)
		{
			$reader = new RootReader($entity['fields.manager'], $entity['repository'], $entity['config']['reader.root']);

			return $reader->setEvents($entity['events']);
		});
	}
}
