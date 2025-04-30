<?php namespace CustomPost\Reader\Xml;

use SimpleXmlElement;
use CustomPost\Reader\Reader;
use CustomPost\Reader\RootReader;

class XmlReader implements Reader
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
			$data = $file instanceof SimpleXmlElement
				? $file
				: new SimpleXmlElement($file, LIBXML_NOCDATA | LIBXML_NOERROR);

			$models = array_merge($models, $this->reader->read(new XmlData($data)));
		}

		return $models;
	}
}
