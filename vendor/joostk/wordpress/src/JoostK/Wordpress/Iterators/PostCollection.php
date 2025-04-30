<?php namespace JoostK\Wordpress\Iterators;

use Countable;
use ArrayAccess;
use IteratorAggregate;

class PostCollection implements IteratorAggregate, ArrayAccess, Countable
{
	protected $posts;

	public function __construct(array $posts = array())
	{
		$this->posts = $posts;
	}

	public function getIterator()
	{
		return new PostIterator($this->posts);
	}

	public function collection()
	{
		return new Collection($this->posts);
	}

	protected function morphCollection(Collection $collection)
	{
		return new PostCollection($collection->all());
	}

	protected function morphResult(Collection $collection, $result)
	{
		$this->posts = $collection->all();

		return $result;
	}

	public function __call($method, $parameters)
	{
		$collection = new Collection($this->posts);

		$result = call_user_func_array(array($collection, $method), $parameters);

		if ($result instanceof Collection)
		{
			return $this->morphCollection($result);
		}
		else
		{
			return $this->morphResult($collection, $result);
		}
	}

	public function count()
	{
		return count($this->posts);
	}

	public function offsetExists($key)
	{
		return array_key_exists($key, $this->posts);
	}

	public function offsetGet($key)
	{
		return $this->posts[$key];
	}

	public function offsetSet($key, $value)
	{
		if (is_null($key))
		{
			$this->posts[] = $value;
		}
		else
		{
			$this->posts[$key] = $value;
		}
	}

	public function offsetUnset($key)
	{
		unset($this->posts[$key]);
	}
}
