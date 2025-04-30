<?php namespace CustomPost\Plugin\Geo;

use CustomPost\Formatter\Formatter;

class RadiusFormatter extends Formatter
{
	public function render($decimal = ',', $thousands = '.')
	{
		if ($this->value === null) return '';

		if ($this->value < 1.0)
		{
			return number_format(round($this->value * 1000 / 50) * 50, 0, $decimal, $thousands).' m';
		}
		else
		{
			return number_format($this->value, $this->value < 10.0 ? 1 : 0, $decimal, $thousands).' km';
		}
	}
}
