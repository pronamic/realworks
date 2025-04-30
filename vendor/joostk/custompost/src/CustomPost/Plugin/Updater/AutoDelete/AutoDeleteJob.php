<?php namespace CustomPost\Plugin\Updater\AutoDelete;

class AutoDeleteJob
{
	protected $deleterResolver;

	protected $config;

	protected $unavailableAction;

	public function __construct(array $config, $unavailableAction)
	{
		$this->config = $config;
		$this->unavailableAction = $unavailableAction;
	}

	public function setDeleterResolver($deleterResolver)
	{
		$this->deleterResolver = $deleterResolver;

		return $this;
	}

	public function process()
	{
		if ($this->shouldRun())
		{
			$this->resolveDeleter()->deleteSince($this->daysSinceDeletion());
		}
	}

	protected function resolveDeleter()
	{
		return call_user_func($this->deleterResolver);
	}

	public function shouldRun()
	{
		return $this->isNotImmediateDelete() and $this->isEnabled() and $this->daysSinceDeletion();
	}

	protected function isNotImmediateDelete()
	{
		return $this->unavailableAction !== '__delete__';
	}

	protected function isEnabled()
	{
		if ($this->maxDays() !== null)
		{
			return true;
		}

		return isset($this->config['enabled']) ? $this->config['enabled'] : false;
	}

	protected function daysSinceDeletion()
	{
		$maxDays = $this->maxDays();
		$days = (is_int($this->config['days']) and $this->config['days'] > 0) ? $this->config['days'] : null;

		if ($days === null)
		{
			return $maxDays;
		}
		else if ($maxDays === null)
		{
			return $days;
		}
		else
		{
			return min($days, $maxDays);
		}
	}

	protected function maxDays()
	{
		$maxDays = array_get($this->config, 'max_days', 0);

		return (is_int($maxDays) and $maxDays > 0) ? $maxDays : null;
	}
}
