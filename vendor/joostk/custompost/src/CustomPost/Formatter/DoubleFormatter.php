<?php namespace CustomPost\Formatter;

class DoubleFormatter extends Formatter
{
	public function render($decimals = 2, $decimal = ',', $thousands = '.')
	{
		if ($this->value === null) return '';

		return number_format($this->value, $decimals, $decimal, $thousands);
	}

	public function isDirty()
	{
		return round($this->value, 10) !== round($this->original, 10);
	}
}
