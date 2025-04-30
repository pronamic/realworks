<?php namespace Realworks\Common\Formatters;

use CustomPost\Formatter\Formatter;

class CityFormatter extends Formatter
{
	public function render()
	{
		$city = ucfirst(strtr(ucwords(strtolower($this->value)), array(
			'Van ' => 'van ',
			'Aan ' => 'aan ',
			'De ' => 'de ',
			'Den ' => 'den ',
			'Ad ' => 'ad ',
			'A/d ' => 'a/d ',
			'Het ' => 'het ',
			'Ij' => 'IJ',
		)));

		return preg_replace_callback('/-(\w)/', function($matches)
		{
			return '-'.strtoupper($matches[1]);
		}, $city);
	}
}
