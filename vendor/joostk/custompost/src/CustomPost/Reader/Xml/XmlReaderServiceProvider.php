<?php namespace CustomPost\Reader\Xml;

use CustomPost\ServiceProvider;
use CustomPost\Reader\ReaderServiceProvider;

class XmlReaderServiceProvider extends ServiceProvider
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
			return new XmlReader($entity['reader.root']);
		});
	}
}
