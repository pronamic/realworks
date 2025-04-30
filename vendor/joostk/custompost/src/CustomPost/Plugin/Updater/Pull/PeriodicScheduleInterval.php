<?php namespace CustomPost\Plugin\Updater\Pull;

use InvalidArgumentException;

class PeriodicScheduleInterval implements ScheduleInterval
{
	protected $intervalInSeconds;

	public function __construct($period, $unit)
	{
		$this->setPeriod($period, $unit);
	}

	public function setPeriod($period, $unit)
	{
		if ( ! ctype_digit($period))
		{
			throw new InvalidArgumentException('Bijwerk interval moet ingegeven worden als geheel getal.');
		}

		switch ($unit)
		{
			case 'minute':
			case 'minutes':
				$this->intervalInSeconds = (int) $period * MINUTE_IN_SECONDS;
				break;
			case 'hour':
			case 'hours':
				$this->intervalInSeconds = ((int) $period) * HOUR_IN_SECONDS;
				break;
			default:
				throw new InvalidArgumentException('Bijwerk interval moet worden ingegeven in minuten ("minutes") of uren ("hours")');
		}

		return $this;
	}

	public function getIntervalInSeconds()
	{
		return $this->intervalInSeconds;
	}

	public function next($previousDate)
	{
		if ($previousDate === null)
		{
			return current_time('timestamp');
		}

		return $previousDate + $this->intervalInSeconds;
	}
}
