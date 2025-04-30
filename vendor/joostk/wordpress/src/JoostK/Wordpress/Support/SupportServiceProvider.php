<?php namespace JoostK\Wordpress\Support;

class SupportServiceProvider extends ServiceProvider
{
	public function register()
	{
		$this->registerCoordinatesFetcher();
	}

	protected function registerCoordinatesFetcher()
	{
		$this->app->singleton('coordinates', function($app)
		{
			return new CoordinatesFetcher($app['remote'], $app['config']['maps.api-key']);
		});
	}
}
