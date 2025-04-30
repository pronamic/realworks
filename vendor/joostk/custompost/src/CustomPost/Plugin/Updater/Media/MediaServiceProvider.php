<?php namespace CustomPost\Plugin\Updater\Media;

use CustomPost\Entity;
use CustomPost\Database\Post;
use CustomPost\Reader\AbstractReader;
use CustomPost\Database\WordpressDatabase;
use JoostK\Wordpress\Support\ServiceProvider;

class MediaServiceProvider extends ServiceProvider
{
	public function register()
	{
		$this->registerPath();

		$this->registerUpdater();

		$this->registerFetcher();

		$this->registerDeleter();

		$this->registerQueue();
	}

	protected function registerPath()
	{
		$this->app['updater.media.path'] = function($app, $entity)
		{
			return $app['updater.paths']->getBasePath() . $entity->getIdentifier();
		};
	}

	protected function registerUpdater()
	{
		$me = $this;

		$this->app['updater.media'] = function($app, $args) use ($me)
		{
			list($entity, $post) = $args;

			$fetcher = $me->getMediaFetcher($entity);
			$basePath = $app->make('updater.media.path', $entity);

			return new Updater($post, $fetcher, $entity['updater.logger'], $app['updater.canceller'], $app['updater.media.queue'], $basePath);
		};
	}

	protected function registerFetcher()
	{
		$this->app['updater.media.fetcher'] = function($app)
		{
			return new HttpFetcher($app['remote']);
		};
	}

	protected function registerDeleter()
	{
		$this->app['updater.media.deleter'] = function($app, $entity)
		{
			$basePath = $app->make('updater.media.path', $entity);

			return new Deleter($basePath);
		};
	}

	protected function registerQueue()
	{
		$this->app->singleton('updater.media.queue', function($app)
		{
			$db = new WordpressDatabase($GLOBALS['wpdb']);

			return new ProcessingQueue($db, $app->getIdentifier().'_media_queue');
		});
	}

	public function boot()
	{
		foreach ($this->app->entities() as $entity)
		{
			if ($entity['config']['media'])
			{
				$this->bootMediaUpdater($entity);
			}

			$this->bootMediaDeleter($entity);
		}

		$this->registerMediaPostprocessor();
	}

	protected function registerMediaPostprocessor()
	{
		$app = $this->app;

		add_action(Processor::EVENT, function() use ($app)
		{
			with(new Processor)->process($app['updater.media.queue']);
		});

		$this->app['updater.media.queue']->resume();
	}

	protected function bootMediaUpdater(Entity $entity)
	{
		list($me, $app) = array($this, $this->app);

		$entity->listen('reader.completed', function(Post $post, AbstractReader $reader, $data) use ($me, $app, $entity)
		{
			$post->listen('updated', function($post) use ($me, $app, $entity, $data)
			{
				$media = $me->getMediaReader($entity)->read($data);

				$updater = $app->make('updater.media', array($entity, $post));
				$updater->update($media);
			});
		});
	}

	public function getMediaFetcher(Entity $entity)
	{
		if ($entity->bound('updater.media.fetcher'))
		{
			return $entity['updater.media.fetcher'];
		}
		else
		{
			return $this->app['updater.media.fetcher'];
		}
	}

	public function getMediaReader(Entity $entity)
	{
		if ($entity->bound('updater.media.reader'))
		{
			return $entity['updater.media.reader'];
		}
		else
		{
			return $this->app['updater.media.reader'];
		}
	}

	protected function bootMediaDeleter(Entity $entity)
	{
		$app = $this->app;

		$entity->listen('post.delete', function(Post $post) use ($app, $entity)
		{
			$app->make('updater.media.deleter', $entity)->deleteMedia($post);
		});
	}
}
