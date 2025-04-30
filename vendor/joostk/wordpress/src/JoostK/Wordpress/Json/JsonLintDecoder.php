<?php namespace JoostK\Wordpress\Json;

use Seld\JsonLint\JsonParser;

class JsonLintDecoder implements JsonDecoderInterface
{
	protected $parser;

	public function __construct()
	{
		$this->parser = new JsonParser;
	}

	public function decode($json)
	{
		return $this->parser->parse($json);
	}
}
