<?php namespace CustomPost\Formatter;

class TextFormatter extends StringFormatter
{
	public function diff()
	{
		return array(
			'chars' => strlen($this->value) - strlen($this->original),
			'total' => strlen($this->original),
		);
	}
}
