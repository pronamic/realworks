<?php namespace CustomPost\Plugin\Updater\Logger;

interface LoggerInterface
{
	public function log($message);

	public function info($message);

	public function warning($message);

	public function error($message);

	public function object($model, $state);

	public function changes(array $changes);

	public function archive($log);

	public function setArchive($archive);

	public function trackChanges($changed, $force = true);

	public function pop();

	public function close();

	public function getId();
}
