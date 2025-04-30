<?php namespace Realworks\Vgm\Units;

use CustomPost\ExtendedEntity;

class Entity extends ExtendedEntity
{
	protected $identifier = 'vgmunits';

	protected $enabled = false;

	public function labels()
	{
		return array(
			'title' => 'VGM Units',
			'objects' => 'VGM Units',
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
		return require __DIR__.'/../../Wonen/fields.php';
	}

	public function getMacros()
	{
		return require __DIR__.'/../../Wonen/macros.php';
	}
}
