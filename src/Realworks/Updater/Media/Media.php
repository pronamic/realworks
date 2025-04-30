<?php namespace Realworks\Updater\Media;

use CustomPost\Plugin\Updater\Media\Media as MediaObject;

class Media extends MediaObject
{
	public function isThumbnail()
	{
		return $this->isGroup('HoofdFoto');
	}

	public function isImage()
	{
		return in_array($this->group, array('HoofdFoto', 'Foto', 'Plattegrond'));
	}

	public function getId()
	{
		if (preg_match('~objectmedia\\/(\d+)~i', $this->url, $matches))
		{
			return $matches[1];
		}
	}

	public function getExtension()
	{
		if (preg_match('~objectmedia\\/\d+(\\.\w+)~i', $this->url, $matches))
		{
			return $matches[1];
		}

		switch ($this->group)
		{
			case 'HoofdFoto':
			case 'Foto':
			case 'Plattegrond':
				return '.jpg';
			case 'Brochure':
				return '.pdf';
		}
	}
}
