<?php namespace Realworks\Admin;

use CustomPost\Plugin\Admin\EntitiesServiceProvider;
use CustomPost\Plugin\Admin\AdminServiceProvider as BaseServiceProvider;

class AdminServiceProvider extends BaseServiceProvider
{
	public function register()
	{
		parent::register();

		$this->registerAddons();

		$this->app->register(new EntitiesServiceProvider($this->app));
	}

	public function registerPages()
	{
		$this->registerPage(new Pages\MainPage);
	}

	protected function registerAddons()
	{
		$this->app->singleton('addons', function($app)
		{
			return new Addons;
		});
	}

	public function boot()
	{
		$this->showNotices();

		add_action('admin_enqueue_scripts', function()
		{
			wp_enqueue_style('realworks-admin', plugins_url('assets/css/admin.css', REALWORKS));
		});

		parent::boot();
	}

	protected function showNotices()
	{
		if ($this->app->bound('admin.notices')) // Temporary check because framework change is postponed
		{
			$this->app['admin.notices']->register(new Notices\Addons\AddonNotice);
		}
	}
}
