<?php namespace CustomPost\Plugin\Updater\Single;

use CustomPost\Plugin\Updater\Sources\FilesystemSource;

class RetryUpdater extends Updater
{
	protected $log;

	public function retry($log)
	{
		$this->log = $log;

		return $this->update();
	}

	protected function resolveSource($entity, array $settings)
	{
		return new FilesystemSource($this->log->archive);
	}
}
