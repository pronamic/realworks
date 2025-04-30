<?php namespace Realworks\Nieuwbouw\Project;

use CustomPost\ExtendedEntity;

class Entity extends ExtendedEntity
{
	protected $identifier = 'nieuwbouw';

	protected $enabled = false;

	public function labels()
	{
		return array(
			'title' => 'Nieuwbouw',
			'objects' => 'Nieuwbouw',
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
