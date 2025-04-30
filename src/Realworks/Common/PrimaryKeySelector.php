<?php namespace Realworks\Common;

use RuntimeException;
use SimpleXMLElement;
use CustomPost\Reader\AbstractReader;
use CustomPost\Reader\Xml\AbstractSelector;

class PrimaryKeySelector extends AbstractSelector
{
	public function perform(SimpleXMLElement $element, AbstractReader $reader)
	{
		$system = (string) head($element->xpath('./ObjectSystemID'));
		$tiara = (string) head($element->xpath('./ObjectTiaraID'));

		if ($system) return $system;
		if ($tiara) return $tiara;

		throw new RuntimeException("Object heeft geen unieke identificatie en kan niet worden verwerkt.");
	}
}
