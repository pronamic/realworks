<?php namespace CustomPost\Reader\Json;

use CustomPost\ServiceProvider;
use CustomPost\Reader\ReaderServiceProvider;

class JsonReaderServiceProvider extends ServiceProvider
{
	public function register()
	{
		$this->entity->register(new ReaderServiceProvider($this->entity, $this->app));

		$this->registerReader();
	}

	protected function registerReader()
	{
		$this->entity->singleton('reader', function($entity)
		{
			return new JsonReader($entity['reader.root']);
		});
	}
}
