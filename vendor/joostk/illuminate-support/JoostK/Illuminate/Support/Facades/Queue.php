<?php namespace JoostK\Illuminate\Support\Facades;

/**
 * @see \JoostK\Illuminate\Queue\QueueManager
 * @see \JoostK\Illuminate\Queue\Queue
 */
class Queue extends Facade {

	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor() { return 'queue'; }

}
