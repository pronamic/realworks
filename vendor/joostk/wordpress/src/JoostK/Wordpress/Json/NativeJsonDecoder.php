<?php namespace JoostK\Wordpress\Json;

class NativeJsonDecoder implements JsonDecoderInterface
{
	public function decode($json)
	{
		return json_decode($json);
	}
}
