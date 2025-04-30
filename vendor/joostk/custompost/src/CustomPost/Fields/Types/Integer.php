<?php namespace CustomPost\Fields\Types;

use CustomPost\Fields\Field;
use CustomPost\Formatter\IntegerFormatter;

class Integer extends Field
{
	protected static $defaults = array(
		'unsigned' => false,
	);

	protected static $defaultFormatterResolver;

	public function datatype()
	{
		return 'INT' . ($this->unsigned ? ' UNSIGNED' : '');
	}

	public function transform($value)
	{
		return intval($value);
	}

	public function newDefaultFormatter()
	{
		return new IntegerFormatter($this);
	}
}
