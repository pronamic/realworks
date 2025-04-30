<?php namespace CustomPost\Reader\Xml;

use SimpleXMLElement;
use CustomPost\Reader\AbstractReader;

interface XmlSelector
{
	public function perform(SimpleXMLElement $element, AbstractReader $reader);
}
