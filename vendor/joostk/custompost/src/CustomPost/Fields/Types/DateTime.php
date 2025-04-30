<?php namespace CustomPost\Fields\Types;

use Carbon\Carbon;
use CustomPost\Formatter\DateTimeFormatter;

class DateTime extends Date
{
	protected static $defaults = array(
		'parseFormat' => 'Y-m-d H:i:s',
	);

	protected static $defaultFormatterResolver;

	public function datatype()
	{
		return 'DATETIME';
	}

	public function parsed($value)
	{
		return $value ? Carbon::createFromFormat($this->parseFormat, $value) : null;
	}

	public function transform($value)
	{
		if ($value instanceof \DateTime) return Carbon::instance($value);

		return Carbon::createFromFormat('Y-m-d H:i:s', $value);
	}

	public function newDefaultFormatter()
	{
		return new DateTimeFormatter($this);
	}
}
