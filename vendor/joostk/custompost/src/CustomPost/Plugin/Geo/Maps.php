<?php namespace CustomPost\Plugin\Geo;

use CustomPost\Entity;
use WP_Query as WordpressQuery;
use CustomPost\Support\QueryCollection;

class Maps
{
	protected $entity;

	public function __construct(Entity $entity)
	{
		$this->entity = $entity;
	}

	public function all(WordpressQuery $query)
	{
		$query = new WordpressQuery(array_merge($query->query, array(
			'nopaging' => true,
			'__custompost.search' => true,
		)));

		return $this->forQuery($query);
	}

	public function paged(WordpressQuery $query)
	{
		return $this->forQuery($query);
	}

	protected function forQuery(WordpressQuery $query)
	{
		$maps = array();

		foreach (new QueryCollection($this->entity, $query) as $post)
		{
			if ($map = $post->map()) $maps[] = $map;
		}

		return $maps;
	}
}
