<?php namespace CustomPost\Providers;

use CustomPost\ServiceProvider;
use JoostK\Wordpress\Events\Events;

class EventsServiceProvider extends ServiceProvider
{
	public function register()
	{
		$this->entity->singleton('events', function($entity)
		{
			return new Events($entity, $entity['config']['post.type']);
		});
	}
}
