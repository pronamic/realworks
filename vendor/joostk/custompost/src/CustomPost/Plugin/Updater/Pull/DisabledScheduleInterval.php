<?php namespace CustomPost\Plugin\Updater\Pull;

class DisabledScheduleInterval implements ScheduleInterval
{
	public function next($previousDate)
	{
		return null;
	}
}
