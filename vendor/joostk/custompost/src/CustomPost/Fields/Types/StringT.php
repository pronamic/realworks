<?php namespace CustomPost\Fields\Types;

use CustomPost\Fields\Field;
use CustomPost\Formatter\StringFormatter;

class StringT extends Field
{
	protected static $defaults = array(
		'length' => 255,
	);

	protected static $defaultFormatterResolver;

	public function transform($value)
	{
		return $value === '' ? null : $value;
	}

	public function datatype()
	{
		return "VARCHAR({$this->length})";
	}

	public function newDefaultFormatter()
	{
		return new StringFormatter($this);
	}
}
