<?php namespace CustomPost\Plugin\Updater\Pull;

use InvalidArgumentException;

class BetweenTimesScheduleInterval implements ScheduleInterval
{
	protected $delegate;

	protected $earliestTime;

	protected $latestTime;

	public function __construct(ScheduleInterval $delegate, $earliestTime, $latestTime)
	{
		$this->delegate = $delegate;
		$this->earliestTime = $this->parseTime($earliestTime, 'Vroegste');
		$this->latestTime = $this->parseTime($latestTime, 'Laatste');
	}

	protected function parseTime($time, $type)
	{
		if ( ! preg_match('~^([01]?\d|2[0-3]):([0-5]\d)$~', $time, $matches))
		{
			throw new InvalidArgumentException("{$type} tijdstip moet ingegeven worden als 'uu:mm'.");
		}

		$hour = (int) $matches[1];
		$minute = (int) $matches[2];

		return array($hour, $minute);
	}

	public function getEarliestTime()
	{
		return $this->earliestTime;
	}

	public function getLatestTime()
	{
		return $this->latestTime;
	}

	public function next($previousDate)
	{
		$date = $this->delegate->next($previousDate);

		if ($date === null)
		{
			return null;
		}

		// If the proposed time is earlier than the earliest time, shift the scheduled date to the earliest date
		$earliestDate = strtotime("{$this->earliestTime[0]}:{$this->earliestTime[1]}", $date);
		if ($date < $earliestDate)
		{
			return $earliestDate;
		}

		// If the proposed time is later than the latest time, shift the scheduled date to the earliest moment the next day.
		$latestDate = strtotime("{$this->latestTime[0]}:{$this->latestTime[1]}", $date);
		if ($date > $latestDate)
		{
			$tomorrowEarliest = strtotime("tomorrow {$this->earliestTime[0]}:{$this->earliestTime[1]}", $date);

			return $tomorrowEarliest;
		}

		return $date;
	}
}
