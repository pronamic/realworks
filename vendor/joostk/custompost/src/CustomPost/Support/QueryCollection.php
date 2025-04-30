<?php namespace CustomPost\Support;

use CustomPost\Entity;
use WP_Query as WordpressQuery;
use JoostK\Wordpress\Iterators\QueryCollection as BaseCollection;

class QueryCollection extends BaseCollection
{
	protected $entity;

	public function __construct(Entity $entity, WordpressQuery $query)
	{
		$this->entity = $entity;

		parent::__construct($query);
	}

	public function next()
	{
		if (parent::next())
		{
			return $this->entity->getCurrentPost();
		}
	}

	public function getIterator()
	{
		return new QueryIterator($this->entity, $this->query);
	}
}
