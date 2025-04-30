<?php namespace JoostK\Wordpress\Events;

use JoostK\Illuminate\Events\Dispatcher;
use JoostK\Illuminate\Container\Container;

class Events extends Dispatcher
{
	protected $identifier;

	public function __construct(Container $container = null, $identifier = null)
	{
		parent::__construct($container);

		$this->identifier = $identifier;
	}

	public function setIdentifier($identifier)
	{
		$this->identifier = $identifier;

		return $this;
	}

	public function fire($event, $payload = array(), $halt = false)
	{
		if ( ! is_array($payload)) $payload = array($payload);

		$response = parent::fire($event, $payload, $halt);

		array_unshift($payload, "{$this->identifier}: {$event}");

		call_user_func_array('do_action', $payload);

		return $response;
	}
}
