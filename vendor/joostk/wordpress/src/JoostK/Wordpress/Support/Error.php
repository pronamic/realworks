<?php namespace JoostK\Wordpress\Support;

use WP_Error;

class Error
{
	public static function handle($error)
	{
		if (is_object($error) and $error instanceof WP_Error)
		{
			throw new WordpressException($error);
		}
	}
}
