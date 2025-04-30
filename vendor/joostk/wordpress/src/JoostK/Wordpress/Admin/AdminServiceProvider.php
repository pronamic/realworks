<?php namespace JoostK\Wordpress\Admin;

use JoostK\Wordpress\Support\ServiceProvider;

class AdminServiceProvider extends ServiceProvider
{
	public function register()
	{
		$this->app->singleton('ajax', function($app)
		{
			return new Ajax($app);
		});
	}

	public function boot()
	{
		add_action('admin_init', array($this, 'registerDeferred'));
		add_action('admin_menu', array($this, 'registerPages'));
	}

	public function registerDeferred()
	{
		$this->registerAjaxRoutes();
	}

	protected function registerAjaxRoutes()
	{

	}

	public function registerPages()
	{

	}

	protected function registerPage(Page $page)
	{
		$page->setApp($this->app)->setView($this->app['view'])->register();
	}
}
