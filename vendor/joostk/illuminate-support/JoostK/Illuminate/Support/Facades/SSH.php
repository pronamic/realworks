<?php namespace JoostK\Illuminate\Support\Facades;

/**
 * @see \JoostK\Illuminate\Remote\RemoteManager
 * @see \JoostK\Illuminate\Remote\Connection
 */
class SSH extends Facade {

	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor() { return 'remote'; }

}
