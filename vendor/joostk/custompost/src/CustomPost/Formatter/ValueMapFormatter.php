<?php namespace CustomPost\Formatter;

class ValueMapFormatter extends Formatter
{
	public function render()
	{
		if ($this->value === null) return '';

		$values = $this->field->get('values', array());

		return array_get($values, $this->value, $this->value);
	}
}
