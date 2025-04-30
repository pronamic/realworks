<?php namespace CustomPost\Plugin\Updater\Batch;

use CustomPost\Plugin\Updater\Logger\ArchiveLogger;
use CustomPost\Plugin\Updater\UpdaterServiceProvider as BaseServiceProvider;

class UpdaterServiceProvider extends BaseServiceProvider
{
	public function register()
	{
		parent::register();

		$this->registerUpdater();

		$this->registerRetryUpdater();

		$this->registerEntity();

		$this->registerArchiveLogger();
	}

	protected function registerUpdater()
	{
		$this->app['updater'] = function($app)
		{
			$updater = new Updater($app, $app['updater.logger.batch'], $app['updater.canceller'], $app['updater.paths'], $app['plugin.licenser'], $app['config']['updater.executor']);

			return $updater->setSources($app['config']['updater.sources']);
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
			list($identifier, $user, $batchLogger) = $arguments;

			$entity = $app->make($identifier);

			// Create logger and leak into entity container, so that
			// other update services may use the same logger instance.
			$entity['updater.logger'] = $logger = $app->make(

				'updater.logger.archive', array($identifier, $user, $batchLogger)

			);

			$dirtyCheckStrategy = $app['updater.dirtycheck.resolver']->resolve($entity);

			$updater = new EntityUpdater($entity, $entity['reader'], $logger, $app['updater.extractor'], $app['updater.canceller'], $dirtyCheckStrategy);

			$processor = $app['updater.unavailable.resolver']->resolve($entity);

			return $updater->setUnavailableProcessor($processor);
		};
	}

	protected function registerArchiveLogger()
	{
		$this->app['updater.logger.archive'] = function($app, array $arguments)
		{
			list($entity, $user, $batchLogger) = $arguments;

			$logger = new ArchiveLogger($app['json.encoder'], $batchLogger);

			$paths = $app['updater.paths'];

			return $logger->setPaths(
				$paths->getBasePath(),
				$paths->nextEntityLog($entity, $user)
			);
		};
	}
}
