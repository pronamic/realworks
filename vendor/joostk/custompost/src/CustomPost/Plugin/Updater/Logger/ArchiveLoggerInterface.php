<?php namespace CustomPost\Plugin\Updater\Logger;

interface ArchiveLoggerInterface extends LoggerInterface
{
	public function setSource($entity, $user);

	public function total($count);
}
