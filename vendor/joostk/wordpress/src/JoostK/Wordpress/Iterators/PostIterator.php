<?php namespace JoostK\Wordpress\Iterators;

use Iterator;

class PostIterator implements Iterator
{
	protected $posts;

	public function __construct(array $posts)
	{
		$this->posts = $posts;
	}

	public function rewind()
	{
		reset($this->posts);
	}

	public function current()
	{
		return current($this->posts);
	}

	public function key()
	{
	    return key($this->posts);
	}

	public function next()
	{
		next($this->posts);
	}

	public function valid()
	{
		if (key($this->posts) !== null)
		{
			$GLOBALS['post'] = $post = current($this->posts);
			setup_postdata($post);

			return true;
		}
		else
		{
			wp_reset_query();
			wp_reset_postdata();

			return false;
		}
	}
}
