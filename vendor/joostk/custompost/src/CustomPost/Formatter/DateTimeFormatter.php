<?php namespace CustomPost\Formatter;

class DateTimeFormatter extends DateFormatter
{
	public function render($format = '%e %B %Y, %R')
	{
		return parent::render($format);
	}
}
