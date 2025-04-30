<?php namespace JoostK\Wordpress\Config;

use JoostK\Wordpress\Support\ServiceProvider;

class ConfigServiceProvider extends ServiceProvider
{
	public function register()
	{
		$this->registerConfig();

		$this->registerStorage();
	}

	public function registerConfig()
	{
		$this->app->singleton('config', function($app)
		{
			$config = new Config($app->getConfiguration());

			return $config->replace($app['config.storage']->load() ?: array());
		});
	}

	public function registerStorage()
	{
		$this->app->singleton('config.storage', function($app)
		{
			return new ConfigStorage('config.php');
		});
	}
}
