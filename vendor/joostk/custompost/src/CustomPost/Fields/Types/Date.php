<?php namespace CustomPost\Fields\Types;

use DateTime;
use Carbon\Carbon;
use CustomPost\Fields\Field;
use CustomPost\Formatter\DateFormatter;

class Date extends Field
{
	protected static $defaults = array(
		'parseFormat' => 'Y-m-d',
	);

	protected static $defaultFormatterResolver;

	public function datatype()
	{
		return 'DATE';
	}

	public function parsed($value)
	{
		return $value ? Carbon::createFromFormat($this->parseFormat, $value)->setTime(0, 0, 0) : null;
	}

	public function transform($value)
	{
		if ($value instanceof DateTime) return Carbon::instance($value);

		return Carbon::createFromFormat('Y-m-d', $value)->setTime(0, 0, 0);
	}

	public function newDefaultFormatter()
	{
		return new DateFormatter($this);
	}
}
