<?php namespace CustomPost\Plugin\Updater\Pull;

use InvalidArgumentException;

class Scheduler
{
	const EVENT = 'scheduler_update_action';

	const ATTEMPTS = 'scheduler_update_attempts';

	const PREVIOUS_DATE = 'scheduler_previous_date';

	protected $interval;

	protected $attempts;

	protected $prefix;

	public function __construct(ScheduleInterval $interval, $attempts)
	{
		$this->interval = $interval;
		$this->attempts = $attempts;
	}

	public function setPrefix($prefix)
	{
		$this->prefix = $prefix.'_';

		return $this;
	}

	public function getMaxAttempts()
	{
		return $this->attempts;
	}

	public function clear()
	{
		delete_transient($this->prefix.static::ATTEMPTS);
		delete_transient($this->prefix.static::PREVIOUS_DATE);
		wp_clear_scheduled_hook($this->prefix.static::EVENT);
	}

	public function schedule($date)
	{
		wp_clear_scheduled_hook($this->prefix.static::EVENT);
		wp_schedule_single_event($date, $this->prefix.static::EVENT);
		set_transient($this->prefix.static::PREVIOUS_DATE, $date, 2 * DAY_IN_SECONDS);
	}

	public function delay($delay)
	{
		$this->schedule(time() + $delay * 60);
	}

	public function retry()
	{
		$attempts = (int) get_transient($this->prefix.static::ATTEMPTS);
		set_transient($this->prefix.static::ATTEMPTS, ++$attempts, 12 * HOUR_IN_SECONDS);

		if ($attempts < $this->attempts)
		{
			$this->delay(15 * $attempts);

			return true;
		}
		else
		{
			return false;
		}
	}

	public function reschedule()
	{
		// Reset retry count
		delete_transient($this->prefix.static::ATTEMPTS);

		$previousDate = $this->determinePreviousDate();
		$date = $this->interval->next($previousDate);

		if ($date !== null)
		{
			$this->schedule($date - get_option('gmt_offset') * HOUR_IN_SECONDS);
		}
		else
		{
			$this->clear();
		}
	}

	protected function determinePreviousDate()
	{
		$previousDate = (int) get_transient($this->prefix.static::PREVIOUS_DATE);

		if ($previousDate === 0)
		{
			return null;
		}

		return $previousDate + get_option('gmt_offset') * HOUR_IN_SECONDS;
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
