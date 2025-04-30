<?php namespace JoostK\Wordpress\Iterators;

use WP_Query as WordpressQuery;

class QueryCollection extends PostCollection
{
	protected $query;

	public function __construct(WordpressQuery $query)
	{
		$this->query = $query;

		parent::__construct($query->posts);
	}

	public function getQuery()
	{
		return $this->query;
	}

	public function total()
	{
		return (int) $this->query->found_posts;
	}

	public function posts()
	{
		return new PostCollection($this->posts);
	}

	protected function morphResult(Collection $collection, $result)
	{
		return $result;
	}

	public function rewind()
	{
		$this->query->rewind_posts();
	}

	public function has()
	{
		return $this->query->have_posts();
	}

	public function next()
	{
		if ($this->query->have_posts())
		{
			$this->query->the_post();

			return $this->query->post;
		}
		else
		{
			wp_reset_postdata();
		}
	}

	public function getIterator()
	{
		return new QueryIterator($this->query);
	}

	public function __call($method, $parameters)
	{
		if (method_exists($this->query, $method))
		{
			return call_user_func_array(array($this->query, $method), $parameters);
		}
		else
		{
			return parent::__call($method, $parameters);
		}
	}

	public function __get($key)
	{
		return isset($this->query->$key) ? $this->query->$key : null;
	}
}
