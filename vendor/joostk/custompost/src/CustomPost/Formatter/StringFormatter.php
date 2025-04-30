<?php namespace CustomPost\Formatter;

class StringFormatter extends Formatter
{
	public function render()
	{
		return (string) $this->value;
	}
}
