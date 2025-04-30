<?php namespace CustomPost\Plugin\Updater\Logger;

class SilentLogger implements BatchLoggerInterface, ArchiveLoggerInterface
{
	protected $transcript = '';

	public function log($message)
	{
		$this->transcript .= __FUNCTION__ . ": {$message}\n";
	}

	public function info($message)
	{
		$this->transcript .= __FUNCTION__ . ": {$message}\n";
	}

	public function warning($message)
	{
		$this->transcript .= __FUNCTION__ . ": {$message}\n";
	}

	public function error($message)
	{
		$this->transcript .= __FUNCTION__ . ": {$message}\n";
	}

	public function pop()
	{
		return $this;
	}

	public function archive($log)
	{
		$this->transcript .= __FUNCTION__ . ": {$log}\n";
	}

	public function total($count)
	{
	}

	public function setArchive($archive)
	{
	}

	public function setSource($entity, $user)
	{
	}

	public function trackChanges($changed, $force = true)
	{
	}

	public function close()
	{
	}

	public function getId()
	{
		return '/dev/null';
	}

	public function object($model, $state)
	{
		$this->transcript .= "model: {$model->primary()} {$state}\n";
	}

	public function changes(array $changes)
	{
		$this->transcript .= 'changes: '.json_encode($changes)."\n";
	}

	public function getTranscript()
	{
		return $this->transcript;
	}

	public function clear()
	{
		$this->transcript = '';

		return $this;
	}
}
