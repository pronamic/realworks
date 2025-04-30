<?php namespace JoostK\Wordpress\View;

use JoostK\Wordpress\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
	public function register()
	{
		$this->app->singleton('view', function($app)
		{
			return new View;
		});
	}
}
