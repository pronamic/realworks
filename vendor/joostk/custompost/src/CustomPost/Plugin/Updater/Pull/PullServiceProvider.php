<?php namespace CustomPost\Plugin\Updater\Pull;

use JoostK\Wordpress\Support\ServiceProvider;

class PullServiceProvider extends ServiceProvider
{
	public function register()
	{
		$this->registerScheduler();

		$this->registerScheduleInterval();

		$this->registerCronUpdater();
	}

	protected function registerScheduler()
	{
		$this->app['updater.scheduler'] = function($app)
		{
			$scheduler = new Scheduler($app['updater.scheduler.interval'], $app['config']['updater.attempts']);

			return $scheduler->setPrefix($app->getIdentifier());
		};
	}

	protected function registerScheduleInterval()
	{
		$this->app['updater.scheduler.interval'] = function($app)
		{
			return with(new ScheduleIntervalResolver)->fromConfig($app['config']);
		};
	}

	protected function registerCronUpdater()
	{
		$this->app['updater.cron'] = function($app)
		{
			return new CronUpdater($app['updater'], $app['updater.scheduler'], $app['events']);
		};
	}

	public function boot()
	{
		$this->scheduleWordpressCronjob();

		$this->registerWordpressCronjob();

		$this->extendDashboardData();

		$this->validateSchedulerTime();
	}

	protected function scheduleWordpressCronjob()
	{
		if (count($this->app['config']['updater.sources']) and $this->app['plugin.licenser']->licensed())
		{
			$this->app['updater.scheduler']->setup();
		}
		else
		{
			$this->app['updater.scheduler']->clear();
		}
	}

	protected function registerWordpressCronjob()
	{
		$app = $this->app;

		add_action($app->getIdentifier().'_'.Scheduler::EVENT, function() use ($app)
		{
			$app['updater.cron']->update();
		});
	}

	protected function extendDashboardData()
	{
		$me = $this;

		$this->app->filter('admin.dashboard.data', function($data) use ($me)
		{
			$data['updater']['scheduled'] = $me->scheduledUpdateData();

			return $data;
		});
	}

	public function scheduledUpdateData()
	{
		$time = $this->app['updater.scheduler']->scheduled();

		return $time ? get_date_from_gmt(gmdate('Y-m-d H:i:s', $time), 'Y-m-d\TH:i:s') : false;
	}

	protected function validateSchedulerTime()
	{
		$app = $this->app;

		$this->app['events']->listen('save.config', function() use ($app)
		{
			// Try instantiating a scheduler instance, it will throw an exception for invalid times
			$app->make('updater.scheduler');
		});
	}
}
