<?php namespace CustomPost\Plugin\Updater\AutoDelete;

use CustomPost\Fields\Field;
use CustomPost\ServiceProvider;

class AutoDeleteServiceProvider extends ServiceProvider
{
	public function register()
	{
		$this->registerJob();

		$this->registerScheduler();
	}

	protected function registerJob()
	{
		$this->entity['updater.autodelete.job'] = function($entity)
		{
			$job = new AutoDeleteJob($entity['config']['auto_delete'], $entity['config']['unavailable']);

			return $job->setDeleterResolver(array($this, 'resolveDeleter'));
		};
	}

	protected function registerScheduler()
	{
		$this->entity['updater.autodelete.scheduler'] = function($entity)
		{
			return new AutoDeleteScheduler($entity->getIdentifier());
		};
	}

	public function resolveDeleter()
	{
		return new AutoDeleter($this->entity, $this->app['updater.logger.batch']);
	}

	public function boot()
	{
		$this->addUnpublishedField();

		$this->scheduleWordpressCronjob();

		$this->registerWordpressCronjob();
	}

	protected function addUnpublishedField()
	{
		$this->entity->filter('fields.augment', function(array $fields)
		{
			return array_merge($fields, array(
				Field::datetime('unpublishedAt'),
			));
		});
	}

	protected function scheduleWordpressCronjob()
	{
		if ($this->entity['updater.autodelete.job']->shouldRun())
		{
			$this->entity['updater.autodelete.scheduler']->setup();
		}
		else
		{
			$this->entity['updater.autodelete.scheduler']->clear();
		}
	}

	protected function registerWordpressCronjob()
	{
		$entity = $this->entity;

		add_action($entity->getIdentifier().'_'.AutoDeleteScheduler::EVENT, function() use ($entity)
		{
			$entity['updater.autodelete.job']->process();
			$entity['updater.autodelete.scheduler']->reschedule();
		});
	}
}
