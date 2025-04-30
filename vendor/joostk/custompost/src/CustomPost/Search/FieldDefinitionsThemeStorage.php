<?php namespace CustomPost\Search;

use stdClass;
use JoostK\Wordpress\Support\ThemeStorage;
use JoostK\Wordpress\Json\JsonEncoderInterface;
use JoostK\Wordpress\Json\JsonDecoderInterface;

class FieldDefinitionsThemeStorage extends ThemeStorage
{
	protected $encoder;

	protected $deencoder;

	public function __construct(JsonEncoderInterface $encoder, JsonDecoderInterface $decoder, $subPath)
	{
		$this->encoder = $encoder;
		$this->decoder = $decoder;

		parent::__construct($subPath);
	}

	public function load()
	{
		$data = parent::load();

		return $this->decoder->decode($data ?: '{}');
	}

	public function save($fields)
	{
		$data = $this->encoder->encode($fields ?: new stdClass);

		return $this->store($data);
	}
}
