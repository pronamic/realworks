<?php namespace JoostK\Wordpress\Json;

class NativeJsonEncoder implements JsonEncoderInterface
{
	public function encode($data)
	{
		return json_encode($data, defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : 0);
	}
}
