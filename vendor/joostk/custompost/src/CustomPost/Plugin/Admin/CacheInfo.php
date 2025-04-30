<?php namespace CustomPost\Plugin\Admin;

use CustomPost\Entity;
use CustomPost\Plugin\Plugin;
use CustomPost\Fields\CacheStorage;

class CacheInfo
{
	protected $app;

	public function __construct(Plugin $app)
	{
		$this->app = $app;
	}

	public function get()
	{
		return array(
			'entities' => $this->entities(),
		);
	}

	public function entities()
	{
		$entities = array();

		foreach ($this->app->entities() as $entity)
		{
			$entities[] = $this->inspectEntity($entity);
		}

		return $entities;
	}

	protected function inspectEntity(Entity $entity)
	{
		return array(
			'entity' => $entity->getIdentifier(),
			'title' => array_get($entity->labels(), 'title'),
			'fields' => $this->inspectFieldsCache($entity['fields.cache']),
		);
	}

	protected function inspectFieldsCache(CacheStorage $storage = null)
	{
		if ( ! is_null($storage) and file_exists($storage->getPath()))
		{
			$present = true;
			$modified = date('Y-m-d\TH:i:s', filemtime($storage->getPath()) + get_option('gmt_offset') * HOUR_IN_SECONDS) ?: null;
		}
		else
		{
			$present = false;
			$modified = null;
		}

		return compact('present', 'modified');
	}
}
