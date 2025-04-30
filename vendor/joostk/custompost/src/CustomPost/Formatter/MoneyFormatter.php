<?php namespace CustomPost\Formatter;

class MoneyFormatter extends Formatter
{
	protected static $currency = '&euro; ';

	protected static $decimals = 2;

	protected static $suffix = '';

	public static function setCurrency($currency)
	{
		static::$currency = $currency;
	}

	public static function setDecimals($decimals = 2)
	{
		static::$decimals = $decimals;
	}

	public static function setSuffix($suffix)
	{
		static::$suffix = $suffix;
	}

	public function render($currency = null, $suffix = null, $decimals = null)
	{
		if ($this->value === null) return '';

		if ($currency === null) $currency = static::$currency;
		if ($decimals === null) $decimals = static::$decimals;
		if ($suffix === null) $suffix = static::$suffix;

		return $currency . number_format($this->value, $decimals, ',', '.') . $suffix;
	}
}
