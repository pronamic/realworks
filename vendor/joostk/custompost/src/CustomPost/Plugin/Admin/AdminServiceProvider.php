<?php namespace CustomPost\Plugin\Admin;

use CustomPost\Plugin\Logging\LoggingServiceProvider;
use JoostK\Wordpress\Admin\AdminServiceProvider as BaseServiceProvider;

class AdminServiceProvider extends BaseServiceProvider
{
	public function register()
	{
		parent::register();

		$this->registerDashboard();

		$this->registerNotices();

		$this->app->register(new LoggingServiceProvider($this->app));
	}

	protected function registerDashboard()
	{
		$this->app->singleton('admin.dashboard', function($app)
		{
			return new Dashboard($app);
		});
	}

	protected function registerNotices()
	{
		$this->app->singleton('admin.notices', function($app)
		{
			return new Notices\NoticeManager($app->getIdentifier());
		});
	}

	protected function registerAjaxRoutes()
	{
		$ajax = $this->app['ajax']->setNonceAction('custompost_nonce');

		require __DIR__.'/routes.php';
	}

	public function boot()
	{
		parent::boot();

		$this->app['admin.notices']->process();
	}
}
