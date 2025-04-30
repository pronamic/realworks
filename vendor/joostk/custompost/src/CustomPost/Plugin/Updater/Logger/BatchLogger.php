<?php namespace CustomPost\Plugin\Updater\Logger;

class BatchLogger extends JsonLogger implements BatchLoggerInterface
{
	public function setStatus($status)
	{
		parent::setStatus($status);

		// This status update may be from an archive logger, so write immediately
		$this->write();
	}
}
