<?php namespace JoostK\Illuminate\Support\Facades;

/**
 * @see \JoostK\Illuminate\Auth\AuthManager
 * @see \JoostK\Illuminate\Auth\Guard
 */
class Auth extends Facade {

	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor() { return 'auth'; }

}
