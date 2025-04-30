<?php namespace CustomPost\Plugin\Updater\Single;

use CustomPost\Plugin\Updater\UpdaterServiceProvider as BaseServiceProvider;

class UpdaterServiceProvider extends BaseServiceProvider
{
	public function register()
	{
		parent::register();

		$this->registerUpdater();

		$this->registerRetryUpdater();

		$this->registerEntity();
	}

	protected function registerUpdater()
	{
		$this->app['updater'] = function($app)
		{
			return new Updater($app, $app['updater.logger.batch'], $app['updater.canceller'], $app['updater.paths'], $app['plugin.licenser'], $app['config']['updater.executor']);
		};
	}

	protected function registerRetryUpdater()
	{
		$this->app['updater.retry'] = function($app)
		{
			return new RetryUpdater($app, $app['updater.logger.batch'], $app['updater.canceller'], $app['updater.paths'], $app['plugin.licenser'], $app['config']['updater.executor']);
		};
	}

	protected function registerEntity()
	{
		$me = $this;

		$this->app['updater.entity'] = function($app, $arguments) use ($me)
		{
			list($identifier, $logger) = $arguments;

			$entity = $app->make($identifier);
			$class = $app->make('updater.entity.class');

			// Leak logger into entity container, so that other
			// update services may use the same logger instance.
			$entity['updater.logger'] = $logger;

			$dirtyCheckStrategy = $app['updater.dirtycheck.resolver']->resolve($entity);

			$updater = new $class($entity, $entity['reader'], $logger, $app['updater.extractor'], $app['updater.canceller'], $dirtyCheckStrategy);

			$processor = $app['updater.unavailable.resolver']->resolve($entity);

			return $updater->setUnavailableProcessor($processor);
		};
	}
}
