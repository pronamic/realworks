<?php namespace CustomPost\Plugin\Updater\AutoDelete;

class AutoDeleteScheduler
{
	const EVENT = 'scheduler_autodelete_action';

	protected $prefix;

	public function __construct($prefix)
	{
		$this->prefix = $prefix.'_';
	}

	public function clear()
	{
		wp_clear_scheduled_hook($this->prefix.static::EVENT);
	}

	public function schedule($date)
	{
		wp_clear_scheduled_hook($this->prefix.static::EVENT);
		wp_schedule_single_event($date, $this->prefix.static::EVENT);
	}

	public function reschedule()
	{
		$this->schedule(time() + HOUR_IN_SECONDS);
	}

	public function scheduled()
	{
		return wp_next_scheduled($this->prefix.static::EVENT);
	}

	public function setup()
	{
		if ($this->scheduled() === false)
		{
			$this->reschedule();

			return true;
		}
		else
		{
			return false;
		}
	}
}
