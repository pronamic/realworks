<?php namespace JoostK\Wordpress\Config;

use ArrayAccess;

class Config implements ArrayAccess
{
	protected $config;

	public function __construct(array $config = array())
	{
		$this->config = $config;
	}

	public function replace(array $config)
	{
		$this->config = array_replace_recursive($this->config, $config);

		return $this;
	}

	public function overwrite(array $config)
	{
		$this->config = $config;

		return $this;
	}

	public function set($key, $value)
	{
		array_set($this->config, $key, $value);

		return $this;
	}

	public function get($key = null, $default = null)
	{
		return array_get($this->config, $key, $default);
	}

	public function __set($key, $value)
	{
		$this->set($key, $value);
	}

	public function __get($key)
	{
		return $this->get($key);
	}

	public function __isset($key)
	{
		return $this->get($key) !== null;
	}

	public function __unset($key)
	{
		array_forget($this->config, $key);
	}

	public function offsetSet($key, $value)
	{
		$this->set($key, $value);
	}

	public function offsetGet($key)
	{
		return $this->get($key);
	}

	public function offsetExists($key)
	{
		return $this->get($key) !== null;
	}

	public function offsetUnset($key)
	{
		unset($this->config[$key]);
	}
}
