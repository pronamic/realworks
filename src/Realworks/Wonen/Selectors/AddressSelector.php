<?php namespace Realworks\Wonen\Selectors;

use SimpleXMLElement;
use CustomPost\Reader\AbstractReader;
use CustomPost\Reader\Xml\AbstractSelector;

class AddressSelector extends AbstractSelector
{
	public function perform(SimpleXMLElement $element, AbstractReader $reader)
	{
		$street = (string) head($element->xpath('./ObjectDetails/Adres/Nederlands/Straatnaam'));

		if ($street)
		{
			return $this->dutchAddress($element, $street);
		}
		else
		{
			return $this->internationalAddress($element);
		}
	}

	protected function dutchAddress(SimpleXMLElement $element, $address)
	{
		$number = (string) head($element->xpath('./ObjectDetails/Adres/Nederlands/Huisnummer'));
		$addition = (string) head($element->xpath('./ObjectDetails/Adres/Nederlands/HuisnummerToevoeging'));

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

	protected function internationalAddress(SimpleXMLElement $element)
	{
		return (string) head($element->xpath('./ObjectDetails/Adres/Internationaal/Adresregel1'));
	}
}
