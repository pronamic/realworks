<?php namespace CustomPost\Plugin\Updater\Pull;

use InvalidArgumentException;

class DailyScheduleInterval implements ScheduleInterval
{
	protected $hour;

	protected $minute;

	public function __construct($time)
	{
		$this->setTime($time);
	}

	public function setTime($time)
	{
		if ( ! preg_match('~^([01]?\d|2[0-3]):([0-5]\d)$~', $time, $matches))
		{
			throw new InvalidArgumentException("Bijwerktijd moet ingegeven worden als 'uu:mm'.");
		}

		$this->hour = (int) $matches[1];
		$this->minute = (int) $matches[2];

		return $this;
	}

	public function getTime()
	{
		return array($this->hour, $this->minute);
	}

	public function next($previousDate)
	{
		// Randomize update time to minimize many requests from the plugin at once
		$minute = $this->minute + mt_rand(0, 15);

		// Schedule again for tomorrow
		return strtotime("tomorrow {$this->hour}:{$minute}", current_time('timestamp'));
	}
}
