<?php namespace CustomPost\Plugin\Admin;

use CustomPost\Plugin\Plugin;

class Addons
{
	protected $app;

	public function __construct(Plugin $app)
	{
		$this->app = $app;
	}

	public function get()
	{
		return $this->app->filtered('plugin.addons', $this->resolveAddons());
	}

	protected function resolveAddons()
	{
		return $this->app->bound('addons') ? $this->app['addons']->get() : array();
	}
}
