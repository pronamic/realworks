<?php namespace Realworks\Vgm\Complexen\Type;

use CustomPost\ExtendedEntity;
use Realworks\Vgm\Complexen\Project\Entity as ProjectEntity;

class Entity extends ExtendedEntity
{
	protected $identifier = 'vgm-types';

	protected $enabled = false;

	public function labels()
	{
		return array(
			'title' => 'VGM — Types',
			'menu' => 'Types',
			'objects' => 'VGM types',
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
