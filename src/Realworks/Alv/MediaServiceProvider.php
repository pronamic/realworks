<?php namespace Realworks\Alv;

use CustomPost\ServiceProvider;

class MediaServiceProvider extends ServiceProvider
{
	public function register()
	{
		$this->registerReader();
	}

	protected function registerReader()
	{
		$this->entity['updater.media.reader'] = function($entity)
		{
			return new Media\Reader;
		};
	}
}
