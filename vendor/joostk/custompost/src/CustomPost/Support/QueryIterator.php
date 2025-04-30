<?php namespace CustomPost\Support;

use CustomPost\Entity;
use WP_Query as WordpressQuery;
use JoostK\Wordpress\Iterators\QueryIterator as BaseIterator;

class QueryIterator extends BaseIterator
{
	protected $entity;
	protected $current;

	public function __construct(Entity $entity, WordpressQuery $query)
	{
		$this->entity = $entity;

		parent::__construct($query);
	}

	public function current()
	{
		return $this->current;
	}

	public function valid()
	{
		// CAUTION: Calling `parent::valid()` advances the iterator pointer!
		$isValid = parent::valid();

		// Capture the current post
		$this->current = $this->entity->getCurrentPost();

		// Skip over entries for which no current post exists, which may happen in dodgy situations
		while ($isValid and $this->current === null)
		{
			// First call `next` to follow the Iterator protocol
			$this->next();

			// Then advance to the next item
			$isValid = parent::valid();

			// Capture the new current post as long as the iterator is valid
			$this->current = $isValid ? $this->entity->getCurrentPost() : null;
		}

		return $isValid;
	}
}
