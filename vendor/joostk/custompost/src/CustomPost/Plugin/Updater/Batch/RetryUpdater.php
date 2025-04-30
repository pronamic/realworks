<?php namespace CustomPost\Plugin\Updater\Batch;

use CustomPost\Plugin\Updater\Sources\FilesystemSource;

class RetryUpdater extends Updater
{
	protected $log;

	public function retry($log)
	{
		$this->log = $log;

		return $this->update();
	}

	protected function applyUpdate()
	{
		return $this->updateSource($this->log->entity, array(
			'user' => $this->log->user,
		));
	}

	protected function resolveSource($entity, array $settings)
	{
		return new FilesystemSource($this->log->archive);
	}
}
