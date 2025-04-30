<?php namespace CustomPost\Plugin\Updater\Pull;

use JoostK\Wordpress\Config\Config;

class ScheduleIntervalResolver
{
	public function fromConfig(Config $config)
	{
		$primary = $this->resolvePrimaryInterval($config);

		if (isset($config['updater.schedule.earliest']) and isset($config['updater.schedule.latest']))
		{
			return new BetweenTimesScheduleInterval($primary, $config['updater.schedule.earliest'], $config['updater.schedule.latest']);
		}
		else
		{
			return $primary;
		}
	}

	protected function resolvePrimaryInterval(Config $config)
	{
		// Legacy time configuration
		if (isset($config['updater.time']))
		{
			return new DailyScheduleInterval($config['updater.time']);
		}

		switch ($config['updater.schedule.interval'])
		{
			case 'daily':
				return new DailyScheduleInterval($config['updater.schedule.time']);
			case 'periodic':
				return new PeriodicScheduleInterval($config['updater.schedule.period'], $config['updater.schedule.unit']);
			case 'disabled':
			default:
				return new DisabledScheduleInterval;
		}
	}
}
