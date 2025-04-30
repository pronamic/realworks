<?php namespace Realworks\Bog\Media;

use SimpleXMLElement;
use Realworks\Updater\Media\Media;

class Reader
{
	public function read(SimpleXMLElement $element)
	{
		$media = array();

		foreach ($element->xpath('.//MediaLijst/Media') as $item)
		{
			$media[] = new Media(
				'foto',
				(string) $item->Groep,
				(string) $item->URL,
				(string) $item->MediaOmschrijving,
				(string) $item->MediaUpdate
			);
		}

		return $media;
	}
}
