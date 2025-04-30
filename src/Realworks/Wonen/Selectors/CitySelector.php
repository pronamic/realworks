<?php namespace Realworks\Wonen\Selectors;

use SimpleXMLElement;
use CustomPost\Reader\AbstractReader;
use CustomPost\Reader\Xml\AbstractSelector;

class CitySelector extends AbstractSelector
{
	public function perform(SimpleXMLElement $element, AbstractReader $reader)
	{
		$city = (string) head($element->xpath('./ObjectDetails/Adres/Nederlands/Woonplaats'));

		if ($city)
		{
			return $city;
		}
		else
		{
			return (string) head($element->xpath('./ObjectDetails/Adres/Internationaal/Woonplaats'));
		}
	}
}
