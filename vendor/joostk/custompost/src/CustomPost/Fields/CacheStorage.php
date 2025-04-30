<?php namespace CustomPost\Fields;

use JoostK\Wordpress\Support\ThemeStorage;

class CacheStorage extends ThemeStorage
{
	public function flush()
	{
		@unlink($this->path);
	}

	public function load()
	{
		return @unserialize(parent::load());
	}

	public function save(array $fields)
	{
		return $this->store(serialize($fields));
	}
}
