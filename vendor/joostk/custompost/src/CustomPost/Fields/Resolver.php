<?php namespace CustomPost\Fields;

use Exception;
use CustomPost\Entity;

class Resolver
{
	protected $entity;

	protected $cache;

	public function __construct(Entity $entity, CacheStorage $cache = null)
	{
		$this->entity = $entity;
		$this->cache = $cache;
	}

	public function get()
	{
		return $this->entity->filtered('fields.augment',
			$this->resolveFromCache() ?: $this->resolveFresh()
		);
	}

	protected function resolveFromCache()
	{
		return $this->cache ? $this->cache->load() : null;
	}

	protected function resolveFresh()
	{
		$fields = $this->entity->getFieldDefinitions();

		if ($this->cache) $this->cacheFields($fields);

		return $fields;
	}

	protected function cacheFields(array $fields)
	{
		try
		{
			$this->cache->save($fields);
		}
		catch (Exception $e)
		{
			error_log((string) $e);
		}
	}
}
