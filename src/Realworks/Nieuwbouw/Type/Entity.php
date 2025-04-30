<?php namespace Realworks\Nieuwbouw\Type;

use CustomPost\ExtendedEntity;
use Realworks\Nieuwbouw\Project\Entity as ProjectEntity;

class Entity extends ExtendedEntity
{
	protected $identifier = 'nieuwbouw-types';

	protected $enabled = false;

	public function labels()
	{
		return array(
			'title' => 'Nieuwbouw — Types',
			'menu' => 'Types',
			'objects' => 'Nieuwbouwtypes',
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
