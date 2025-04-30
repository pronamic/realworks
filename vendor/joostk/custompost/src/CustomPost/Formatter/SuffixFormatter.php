<?php namespace CustomPost\Formatter;

class SuffixFormatter extends Formatter
{
	protected static $suffix = '';

	public static function setSuffix($suffix)
	{
		static::$suffix = $suffix;
	}

	public function render()
	{
		if ($this->value === null) return '';

		return $this->toDefault()->render() . static::$suffix;
	}
}
