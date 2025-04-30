<?php namespace CustomPost\Fields\Types;

use CustomPost\Fields\Field;
use CustomPost\Formatter\TextFormatter;

class Text extends Field
{
	protected static $defaultFormatterResolver;

	public function datatype()
	{
		return 'TEXT';
	}

	public function newDefaultFormatter()
	{
		return new TextFormatter($this);
	}
}
