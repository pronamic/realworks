<?php namespace Realworks\Vgm\Complexen\Nummer;

use CustomPost\ExtendedEntity;

class Entity extends ExtendedEntity
{
	protected $identifier = 'vgm-nummers';

	protected $enabled = false;

	public function labels()
	{
		return array(
			'title' => 'VGM — Nummers',
			'menu' => 'Nummers',
			'objects' => 'VGM nummers',
		);
	}

	public function getConfiguration()
	{
		return require __DIR__.'/config.php';
	}

	/**
	 * Returns an array of field definitions
	 */
	public function getFieldDefinitions()
	{
		return require __DIR__.'/fields.php';
	}

	public function getMacros()
	{
		return require __DIR__.'/macros.php';
	}
}
