<?php namespace CustomPost\Formatter;

use CustomPost\Fields\Types\Date;

class DateFormatter extends Formatter
{
	public function __construct(Date $field)
	{
		parent::__construct($field);
	}

	public function date()
	{
		return $this->value;
	}

	public function isDirty()
	{
		return $this->value != $this->original;
	}

	public function render($format = '%e %B %Y')
	{
		if ($this->value === null) return '';

		return trim(strftime($format, $this->value->getTimestamp()));
	}

	public function __call($method, $parameters)
	{
		if ($this->value)
		{
			return call_user_func_array(array($this->value, $method), $parameters);
		}
	}
}
