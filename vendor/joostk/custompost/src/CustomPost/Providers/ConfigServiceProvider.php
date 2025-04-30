<?php namespace CustomPost\Providers;

use CustomPost\ServiceProvider;
use JoostK\Wordpress\Config\Config;
use JoostK\Wordpress\Config\ConfigStorage;

class ConfigServiceProvider extends ServiceProvider
{
	public function register()
	{
		$this->registerConfig();

		$this->registerStorage();
	}

	public function registerConfig()
	{
		$this->entity->singleton('config', function($entity)
		{
			$config = new Config($entity->getConfiguration());

			// Bind early since `config.storage` is dependent on `config`
			$entity->instance('config', $config);

			return $config->replace($entity['config.storage']->load() ?: array());
		});
	}

	public function registerStorage()
	{
		$this->entity->singleton('config.storage', function($entity)
		{
			return new ConfigStorage($entity['config']['paths.storage'] . '/config.php');
		});
	}
}
