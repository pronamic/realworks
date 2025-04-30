<?php namespace JoostK\Wordpress\Iterators;

use Iterator;
use WP_Query as WordpressQuery;

class QueryIterator implements Iterator
{
	protected $query;

	public function __construct(WordpressQuery $query)
	{
		$this->query = $query;
	}

	public function rewind()
	{
		$this->query->rewind_posts();
	}

	public function current()
	{
		return $this->query->post;
	}

	public function key()
	{
	    return $this->query->current_post;
	}

	public function next()
	{
		// Nothing to do, `WP_Query::the_post` is executed up-front during the call to `valid`, instead of afterwards here in `next`.
	}

	public function valid()
	{
		if ($this->query->have_posts())
		{
			$this->query->the_post();

			return true;
		}
		else
		{
			wp_reset_postdata();

			return false;
		}
	}
}
