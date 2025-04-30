<?php namespace CustomPost\Support;

class RangeField
{
	protected static $template = '#{min} t/m #{max}';

	public static function setTemplate($template)
	{
		static::$template = $template;
	}

	public static function range($min, $max)
	{
		if ($min->isEmpty()) return;

		if ($min->value() === $max->value()) return $min->render();

		return str_replace(
			array('#{min}', '#{max}'),
			array($min->render(), $max->render()),
			static::$template
		);
	}
}
