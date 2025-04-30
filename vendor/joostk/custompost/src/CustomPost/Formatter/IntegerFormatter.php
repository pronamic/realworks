<?php namespace CustomPost\Formatter;

class IntegerFormatter extends Formatter
{
	public function n($singular, $plural)
	{
		return $this->value === 1 ? $singular : $plural;
	}

	public function render($decimals = 0, $decimal = ',', $thousands = '.')
	{
		if ($this->value === null) return '';

		return number_format($this->value, $decimals, $decimal, $thousands);
	}

	public function apply(array $parameters)
	{
		return $this->n($parameters[0], $parameters[1]);
	}
}
