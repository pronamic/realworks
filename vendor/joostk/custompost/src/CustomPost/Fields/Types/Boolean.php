<?php namespace CustomPost\Fields\Types;

use CustomPost\Fields\Field;
use CustomPost\Formatter\BooleanFormatter;

class Boolean extends Field
{
	protected static $defaults = array(
		'trueValue' => 'true',
	);

	protected static $defaultFormatterResolver;

	public function datatype()
	{
		return 'BOOL';
	}

	public function parsed($value)
	{
		return (is_bool($value) or $value === null) ? $value : $value === $this->trueValue;
	}

	public function transform($value)
	{
		return (bool) $value;
	}

	public function newDefaultFormatter()
	{
		return new BooleanFormatter($this);
	}
}
