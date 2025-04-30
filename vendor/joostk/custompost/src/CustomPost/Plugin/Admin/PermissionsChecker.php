<?php namespace CustomPost\Plugin\Admin;

use CustomPost\Plugin\Plugin;

class PermissionsChecker
{
	protected $app;

	public function __construct(Plugin $app)
	{
		$this->app = $app;
	}

	public function check()
	{
		list($directories, $files) = array($this->directories(), $this->files());

		foreach ($directories as $directory => &$state) $state = $this->isWritable($directory);
		foreach ($files as $file => &$state) $state = $this->isWritable($file);

		$issues = count(array_filter($directories + $files)) !== count($directories) + count($files);

		return compact('directories', 'files', 'issues');
	}

	protected function directories()
	{
		$directories = array(
			$this->app['config.storage']->getBasePath(),
			$this->app['updater.paths']->getBasePath(),
		);

		foreach ($this->app->entities() as $entity)
		{
			$directories[] = $entity['config.storage']->getBasePath();
			$directories[] = $entity['search.fields.storage']->getBasePath();
		}

		return array_flip($directories);
	}

	protected function files()
	{
		$files = array(
			$this->app['config.storage']->getPath(),
		);

		foreach ($this->app->entities() as $entity)
		{
			$files[] = $entity['config.storage']->getPath();
			$files[] = $entity['search.fields.storage']->getPath();
		}

		return array_flip($files);
	}

	protected function isWritable($path)
	{
		$up = 5;

		while ( ! file_exists($path) and $up--)
		{
			$path = dirname($path);
		}

		return is_writable($path);
	}
}
