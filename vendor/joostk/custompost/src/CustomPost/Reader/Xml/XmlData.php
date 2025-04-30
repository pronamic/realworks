<?php namespace CustomPost\Reader\Xml;

use Exception;
use RuntimeException;
use SimpleXMLElement;
use CustomPost\Reader\Data;
use CustomPost\Fields\Field;
use CustomPost\Reader\AbstractReader;

class XmlData implements Data
{
	protected $element;

	public function __construct(SimpleXMLElement $element)
	{
		$this->element = $element;
	}

	public function raw()
	{
		return $this->element;
	}

	public function entries($selector)
	{
		return array_map(function(SimpleXMLElement $entry)
		{
			return new XmlData($entry);
		}, $this->element->xpath($selector));
	}

	public function get(Field $field, AbstractReader $reader)
	{
		$selector = $this->makeSelector($field);

		try
		{
			return $selector->perform($this->element, $reader);
		}
		catch (Exception $e)
		{
			throw new RuntimeException("Failed to parse data in field [{$field->getFullName('.')}]: {$e->getMessage()}", 0, $e);
		}

	}

	protected function makeSelector(Field $field)
	{
		$select = $field->select;

		if ( ! $select)
		{
			throw new RuntimeException("Field [{$field->getFullName('.')}] does not have a selector.");
		}

		$selector = $select instanceof XmlSelector ? $select : new XpathSelector($select);

		if ($field->parser)   $selector->parser($field->parser);
		if ($field->multiple) $selector->multiple();

		return $selector->setField($field);
	}
}
