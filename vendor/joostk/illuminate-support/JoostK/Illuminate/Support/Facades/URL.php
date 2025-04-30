<?php namespace JoostK\Illuminate\Support\Facades;

/**
 * @see \JoostK\Illuminate\Routing\UrlGenerator
 */
class URL extends Facade {

	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor() { return 'url'; }

}
