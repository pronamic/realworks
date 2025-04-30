<?php namespace CustomPost\Reader\Json;

use CustomPost\Reader\Reader;
use CustomPost\Reader\RootReader;

class JsonReader implements Reader
{
	protected $reader;

	public function __construct(RootReader $reader)
	{
		$this->reader = $reader;
	}

	public function read($files)
	{
		$models = array();
		$files = is_array($files) ? $files : array($files);

		foreach ($files as $file)
		{
			$data = is_string($file) ? json_decode($file) : $file;

			$models = array_merge($models, $this->reader->read(new JsonData($data)));
		}

		return $models;
	}
}
