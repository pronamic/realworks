<?php namespace Realworks\Nieuwbouw\Nummer;

use CustomPost\ExtendedEntity;

class Entity extends ExtendedEntity
{
	protected $identifier = 'nieuwbouw-nummers';

	protected $enabled = false;

	public function labels()
	{
		return array(
			'title' => 'Nieuwbouw — Nummers',
			'menu' => 'Nummers',
			'objects' => 'Nieuwbouwnummers',
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
