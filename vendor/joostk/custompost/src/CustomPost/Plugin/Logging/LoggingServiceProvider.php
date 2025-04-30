<?php namespace CustomPost\Plugin\Logging;

use CustomPost\Plugin\Updater\PathResolver;
use JoostK\Wordpress\Support\ServiceProvider;

class LoggingServiceProvider extends ServiceProvider
{
	public function register()
	{
		$this->app->resolving('CustomPost\\Plugin\\Logging\\FilesystemArchiveLogFinder', function($finder, $app)
		{
			$finder->setBasePath($app['updater.paths']->getBasePath());
		});

		$this->app->resolving('CustomPost\\Plugin\\Logging\\FilesystemBatchLogFinder', function($finder, $app)
		{
			$finder->setBasePath($app['updater.paths']->getBasePath());
		});

		$this->app->bind('CustomPost\\Plugin\\Logging\\LogDateFinderInterface', function($app)
		{
			return with(new FilesystemDateLogFinder)->setBasePath($app['updater.paths']->getBasePath());
		});
	}

	public function boot()
	{
		Models\Log::setFinder($this->app->make('CustomPost\\Plugin\\Logging\\FilesystemArchiveLogFinder'));
	}
}
