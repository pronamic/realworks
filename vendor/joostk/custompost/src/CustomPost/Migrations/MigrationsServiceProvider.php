<?php namespace CustomPost\Migrations;

use JoostK\Wordpress\Support\ServiceProvider;

class MigrationsServiceProvider extends ServiceProvider
{
	public function register()
	{
		$this->registerMigrator();
	}

	protected function registerMigrator()
	{
		$this->app->singleton('migrator', function($app)
		{
			return new Migrator($app->getIdentifier());
		});
	}
}
