<?php namespace CustomPost\Formatter;

class BooleanFormatter extends Formatter
{
	protected static $true = 'Yes';

	protected static $false = 'No';

	public static function setTrue($true)
	{
		static::$true = $true;
	}

	public static function setFalse($false)
	{
		static::$false = $false;
	}

	public static function setTrueFalse($true, $false)
	{
		static::$true = $true;
		static::$false = $false;
	}

	public function isTrue($ifNull = false)
	{
		return $this->value === null ? $ifNull : $this->value === true;
	}

	public function isFalse($ifNull = true)
	{
		return $this->value === null ? $ifNull : $this->value === false;
	}

	public function boolValue()
	{
		return $this->value;
	}

	public function render($true = null, $false = null)
	{
		if ($this->value === null) return '';

		if ($true === null) $true = static::$true;
		if ($false === null) $false = static::$false;

		return $this->value ? $true : $false;
	}
}
