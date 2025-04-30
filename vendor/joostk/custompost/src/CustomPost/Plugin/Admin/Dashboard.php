<?php namespace CustomPost\Plugin\Admin;

use CustomPost\Plugin\Plugin;

class Dashboard
{
	protected $app;

	public function __construct(Plugin $app)
	{
		$this->app = $app;
	}

	public function get()
	{
		return $this->app->filtered('admin.dashboard.data', array(
			'version' => $this->version(),
			'license' => $this->license(),
			'updater' => $this->updater(),
			'permissions' => $this->permissions(),
			'cache' => $this->cache(),
			'addons' => $this->addons(),
			'env' => $this->env(),
		));
	}

	protected function version()
	{
		$updater = $this->app['plugin.updater'];

		return array(
			'current' => $updater->getVersion(),
			'update' => $updater->getUpdate(),
		);
	}

	protected function license()
	{
		$licenser = $this->app['plugin.licenser'];

		return array(
			'code' => $licenser->license(),
			'status' => $licenser->status(),
			'valid' => $licenser->licensed(),
		);
	}

	protected function updater()
	{
		return array(
			'latest' => $this->latestUpdate(),
		);
	}

	protected function latestUpdate()
	{
		$latest = $this->app->make('CustomPost\\Plugin\\Logging\\LogDateFinderInterface')->latest();

		if ($latest)
		{
			$log = $this->app->make('CustomPost\\Plugin\\Logging\\FilesystemBatchLogFinder')->get($latest);

			if ($log) return $log->summary();
		}
	}

	protected function permissions()
	{
		return with(new PermissionsChecker($this->app))->check();
	}

	protected function cache()
	{
		return with(new CacheInfo($this->app))->get();
	}

	protected function addons()
	{
		return with(new Addons($this->app))->get();
	}

	protected function env()
	{
		return array(
			'php' => phpversion(),
		);
	}
}
