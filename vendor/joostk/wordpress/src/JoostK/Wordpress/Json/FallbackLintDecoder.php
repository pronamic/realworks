<?php namespace JoostK\Wordpress\Json;

class FallbackLintDecoder implements JsonDecoderInterface
{
	public function __construct()
	{
		$this->decoder = new JsonLintDecoder;
	}

	public function decode($json)
	{
		$result = json_decode($json);

		if ($result === null and json_last_error() !== JSON_ERROR_NONE)
		{
			return $this->decoder->decode($json);
		}

		return $result;
	}
}
