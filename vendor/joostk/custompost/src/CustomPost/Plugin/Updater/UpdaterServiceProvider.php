<?php namespace CustomPost\Plugin\Updater;

use CustomPost\Entity;
use CustomPost\Database\WordpressDatabase;
use JoostK\Wordpress\Support\ServiceProvider;

class UpdaterServiceProvider extends ServiceProvider
{
	public function register()
	{
		$this->registerPathResolver();

		$this->registerUploadUpdater();

		$this->registerChildEntityUpdater();

		$this->registerCanceller();

		$this->registerBatchLogger();

		$this->registerUnavailableProcessors();

		$this->registerUnavailableProcessorResolver();

		$this->registerDirtyCheckStrategyResolver();

		$this->registerExtractor();

		$this->registerExtractorZipArchiveEntrySelector();
	}

	protected function registerPathResolver()
	{
		$this->app->singleton('updater.paths', function($app)
		{
			return new PathResolver($app->getIdentifier());
		});

		$this->app->alias('updater.paths', 'CustomPost\Plugin\Updater\PathResolver');
	}

	protected function registerUploadUpdater()
	{
		$this->app->singleton('updater.upload', function($app)
		{
			return new UploadUpdater($app, $app['updater.retry'], $app['updater.paths']);
		});
	}

	protected function registerChildEntityUpdater()
	{
		$me = $this;

		$this->app['updater.entity.child'] = function($app, $entity) use ($me)
		{
			// Create logger and leak into entity container, so that
			// other update services may use the same logger instance.
			$entity['updater.logger'] = $logger = $app->make(

				'updater.logger.archive', array($entity->getIdentifier(), null, $entity->parent()->make('updater.logger'))

			);

			$dirtyCheckStrategy = $app['updater.dirtycheck.resolver']->resolve($entity);

			$updater = new Batch\ChildEntityUpdater($entity, $entity['reader'], $logger, $app['updater.extractor'], $app['updater.canceller'], $dirtyCheckStrategy);

			$processor = $app['updater.unavailable.resolver']->resolve($entity);

			return $updater->setUnavailableProcessor($processor);
		};
	}

	protected function registerCanceller()
	{
		$this->app->singleton('updater.canceller', function($app)
		{
			$canceller = new Canceller(new WordpressDatabase($GLOBALS['wpdb']));

			return $canceller->setPrefix($app->getIdentifier());
		});
	}

	protected function registerBatchLogger()
	{
		$this->app['updater.logger.batch'] = function($app)
		{
			$logger = new Logger\BatchLogger($app['json.encoder']);

			$paths = $app['updater.paths'];

			return $logger->setPaths(
				$paths->getBasePath(),
				$paths->nextBatchLog()
			);
		};
	}

	protected function registerUnavailableProcessors()
	{
		$this->app['updater.unavailable.__delete__'] = function($app, Entity $entity)
		{
			return new UnavailableProcessors\DeleteModel($entity);
		};

		$this->app['updater.unavailable.__void__'] = function($app, Entity $entity)
		{
			return new UnavailableProcessors\VoidAction($entity);
		};

		$this->app['updater.unavailable.status'] = function($app, Entity $entity)
		{
			return new UnavailableProcessors\StatusUpdate($entity);
		};
	}

	public function registerUnavailableProcessorResolver()
	{
		$this->app->singleton('updater.unavailable.resolver', function($app)
		{
			return new UnavailableProcessorResolver($app);
		});
	}

	public function registerDirtyCheckStrategyResolver()
	{
		$this->app->singleton('updater.dirtycheck.resolver', function($app)
		{
			$force = $app['config']['updater.executor.force'] === true;

			return new DirtyCheckStrategyResolver($app, $force);
		});
	}

	protected function registerExtractor()
	{
		$this->app->singleton('updater.extractor', function($app)
		{
			return new ExtensionAwareExtractor($app);
		});
	}

	protected function registerExtractorZipArchiveEntrySelector()
	{
		$this->app->singleton('updater.extractor.zip-archive.entry-selector', function($app)
		{
			return new ZipArchiveEntryExtensionSelector('.xml');
		});
	}

	public function boot()
	{
		$this->app['migrator']->run(new Migrations\DeleteErrorLogs($this->app['updater.paths']));
	}
}
