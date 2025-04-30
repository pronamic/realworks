<?php namespace CustomPost\Fields\Types;

use CustomPost\Fields\Field;
use CustomPost\Formatter\DoubleFormatter;

class Double extends Field
{
	protected static $defaultFormatterResolver;

	public function datatype()
	{
		return 'DOUBLE';
	}

	public function transform($value)
	{
		return floatval($value);
	}

	public function newDefaultFormatter()
	{
		return new DoubleFormatter($this);
	}
}
