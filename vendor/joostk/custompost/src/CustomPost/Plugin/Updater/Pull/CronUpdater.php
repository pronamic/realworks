<?php namespace CustomPost\Plugin\Updater\Pull;

use JoostK\Wordpress\Events\Events;
use CustomPost\Plugin\Updater\Batch\Updater;

class CronUpdater
{
	protected $updater;

	protected $scheduler;

	protected $events;

	public function __construct(Updater $updater, Scheduler $scheduler, Events $events)
	{
		$this->updater = $updater;
		$this->scheduler = $scheduler;
		$this->events = $events;
	}

	public function update()
	{
		if ($this->updater->update())
		{
			return $this->scheduler->reschedule();
		}

		// When we are not yet allowed to update, reschedule for one hour later. This may happen on DST changes.
		else if ($this->updater->wasNotAllowed())
		{
			return $this->scheduler->delay(60);
		}

		// When retrying reached its limits, reschedule for next day anyway
		else if ( ! $this->retry())
		{
			return $this->scheduler->reschedule();
		}
	}

	protected function retry()
	{
		if ($this->scheduler->retry()) return true;

		$max = $this->scheduler->getMaxAttempts();

		$this->events->fire('updater.failed.attempts', array($max));

		return false;
	}
}
