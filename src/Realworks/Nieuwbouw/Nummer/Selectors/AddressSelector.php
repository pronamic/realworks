<?php namespace Realworks\Nieuwbouw\Nummer\Selectors;

use SimpleXMLElement;
use CustomPost\Reader\AbstractReader;
use CustomPost\Reader\Xml\AbstractSelector;

class AddressSelector extends AbstractSelector
{
	public function perform(SimpleXMLElement $element, AbstractReader $reader)
	{
		$address = (string) head($element->xpath('./BouwNummerDetails/Adres/Straatnaam'));

		$number = (string) head($element->xpath('./BouwNummerDetails/Adres/Huisnummer'));
		$addition = (string) head($element->xpath('./BouwNummerDetails/Adres/HuisnummerToevoeging'));
		$bouwnummer = (string) head($element->xpath('./BouwnummerCode'));

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

		if (strtolower($address) === 'bouwnummer')
		{
			$address .= " {$bouwnummer}";
		}

		return $address;
	}
}
