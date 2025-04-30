<?php namespace JoostK\Wordpress\Admin;

use JoostK\Wordpress\View\View;
use JoostK\Illuminate\Container\Container;

abstract class Page
{
	protected $app;

	protected $view;

	public function setApp(Container $app)
	{
		$this->app = $app;

		return $this;
	}

	public function setView(View $view)
	{
		$this->view = $view;

		return $this;
	}

	abstract public function registerMenu();

	public function register()
	{
		$page = $this->registerMenu();

		add_action("admin_print_scripts-{$page}", array($this, 'loadScripts'));
		add_action("admin_print_styles-{$page}", array($this, 'loadStyles'));
	}

	public function loadScripts()
	{
		$allowed = apply_filters('tsd_admin_page_allowed_scripts', array(
			'common',
			'thickbox',
			'heartbeat',
			'wp-auth-check',
			'admin-bar',
			'hoverIntent',

			'debug-bar',
			'debug-bar-codemirror',
			'debug-bar-console',

			'query-monitor',
		));

		foreach (wp_scripts()->registered as $script)
		{
			if ( ! in_array($script->handle, $allowed))
			{
				wp_dequeue_script($script->handle);
			}
		}
	}

	public function loadStyles()
	{
		// Override
	}
}
