<?php namespace CustomPost\Reader\Json;

use Exception;
use RuntimeException;
use CustomPost\Reader\Data;
use CustomPost\Fields\Field;
use CustomPost\Reader\AbstractReader;

class JsonData implements Data
{
	protected $data;

	protected $root;

	public function __construct($data, $root = null)
	{
		$this->data = $data;
		$this->root = $root !== null ? $root : $data;
	}

	public function raw()
	{
		return $this->data;
	}

	public function entries($selector)
	{
		$entries = empty($selector) ? $this->data : data_get($this->data, $selector, array());

		if ($entries === null)
		{
			return array();
		}

		$list = is_array($entries) ? $entries : array($entries);

		return array_map(function($entry)
		{
			return new JsonData($entry, $this->data);
		}, $list);
	}

	public function get(Field $field, AbstractReader $reader)
	{
		$select = $field->select;

		if ( ! $select)
		{
			throw new RuntimeException("Field [{$field->getFullName('.')}] does not have a selector.");
		}

		$selector = $select instanceof JsonSelector ? $select : new DottedPathSelector($select, new JsonParser($field, $reader));

		try
		{
			return $selector->select($this->data, $this->root);
		}
		catch (Exception $e)
		{
			throw new RuntimeException("Failed to parse data in field [{$field->getFullName('.')}]: {$e->getMessage()}", 0, $e);
		}
	}
}
