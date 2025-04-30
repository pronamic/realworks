<?php namespace Realworks\Alv\Selectors;

use SimpleXMLElement;
use CustomPost\Reader\AbstractReader;
use CustomPost\Reader\Xml\AbstractSelector;

class AddressSelector extends AbstractSelector
{
	public function perform(SimpleXMLElement $element, AbstractReader $reader)
	{
		$address = (string) head($element->xpath('./ObjectDetails/Adres/Nederlands/Straatnaam'));

		$number = (string) head($element->xpath('./ObjectDetails/Adres/Nederlands/Huisnummer/Hoofdnummer'));
		$addition = (string) head($element->xpath('./ObjectDetails/Adres/Nederlands/HuisnummerToevoeging'));

		$seriesFrom = (string) head($element->xpath('./ObjectDetails/Adres/Nederlands/Huisnummer/Reeks/Begin'));
		$seriesTo = (string) head($element->xpath('./ObjectDetails/Adres/Nederlands/Huisnummer/Reeks/Eind'));

		if ( ! empty($seriesFrom) and ! empty($seriesTo))
		{
			return "{$address} {$seriesFrom}–{$seriesTo}";
		}

		if ( ! empty($number))
		{
			$address .= " {$number}";
		}

		if ( ! empty($addition) and $addition !== 'ong')
		{
			if ($number)
			{
				$address .= ctype_digit($addition[0]) ? '-' : '';
			}
			else
			{
				$address .= ' ';
			}

			$address .= $addition;
		}

		return $address;
	}
}
