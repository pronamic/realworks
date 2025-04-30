<?php namespace JoostK\Wordpress\Events;

use JoostK\Wordpress\Support\ServiceProvider;

class EventsServiceProvider extends ServiceProvider
{
	public function register()
	{
		$this->app->singleton('events', function($app)
		{
			return new Events($app);
		});
	}
}
