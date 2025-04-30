<?php namespace CustomPost\Reader\Json;

class DottedPathSelector implements JsonSelector
{
	protected $path;

	protected $parser;

	public function __construct($path, $parser = null)
	{
		$this->path = $path;
		$this->parser = $parser;
	}

	public function select($data, $root)
	{
		$value = data_get($data, $this->path);

		if ($this->parser !== null)
		{
			return $this->parser->parse($value);
		}
		else
		{
			return $value;
		}
	}
}
