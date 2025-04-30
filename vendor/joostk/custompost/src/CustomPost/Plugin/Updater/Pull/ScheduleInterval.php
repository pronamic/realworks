<?php namespace CustomPost\Plugin\Updater\Pull;

interface ScheduleInterval
{
	public function next($previousDate);
}
