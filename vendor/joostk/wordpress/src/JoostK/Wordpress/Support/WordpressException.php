<?php namespace JoostK\Wordpress\Support;

use WP_Error;
use RuntimeException;

class WordpressException extends RuntimeException
{
	public function __construct(WP_Error $error)
	{
		parent::__construct($error->get_error_message());

		$this->code = $error->get_error_code();
	}
}
