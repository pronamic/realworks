<?php namespace JoostK\Wordpress\Json;

use webignition\JsonPrettyPrinter\JsonPrettyPrinter;

class PrettyJsonEncoder implements JsonEncoderInterface
{
	protected $encoder;

	public function __construct()
	{
		$this->encoder = new JsonPrettyPrinter;
	}

	public function encode($data)
	{
		$this->encoder->reset();

		return $this->encoder->format(json_encode($data));
	}
}
