<?php namespace JoostK\Wordpress\Remote;

use JoostK\Wordpress\Support\ServiceProvider;

class RemoteServiceProvider extends ServiceProvider
{
	public function register()
	{
		$this->app->singleton('remote', function($app)
		{
			return new WordpressRemote;
		});
	}
}
