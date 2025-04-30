<?php namespace JoostK\Illuminate\Support\Facades;

/**
 * @see \JoostK\Illuminate\Session\SessionManager
 * @see \JoostK\Illuminate\Session\Store
 */
class Session extends Facade {

	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor() { return 'session'; }

}
