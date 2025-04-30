<?php namespace JoostK\Illuminate\Support\Contracts;

interface MessageProviderInterface {

	/**
	 * Get the messages for the instance.
	 *
	 * @return \JoostK\Illuminate\Support\MessageBag
	 */
	public function getMessageBag();

}
