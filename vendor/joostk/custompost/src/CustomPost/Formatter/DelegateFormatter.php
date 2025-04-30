<?php namespace CustomPost\Formatter;

abstract class DelegateFormatter extends Formatter
{
	public function isEmpty()
	{
		return $this->toDefault()->isEmpty();
	}

	public function isDirty()
	{
		return $this->toDefault()->isDirty();
	}

	public function diff()
	{
		return $this->toDefault()->diff();
	}

	public function getData()
	{
		return $this->toDefault()->getData();
	}

	public function __call($method, $parameters)
	{
		return call_user_func_array(array($this->toDefault(), $method), $parameters);
	}
}
