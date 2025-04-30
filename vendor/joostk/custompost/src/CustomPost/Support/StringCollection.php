<?php namespace CustomPost\Support;

use JoostK\Illuminate\Support\Collection as BaseCollection;

class StringCollection extends BaseCollection
{
	/**
	 * Concatenate values of a given key as a string.
	 *
	 * @param  string  $glue
	 * @param  string  $unused
	 * @return string
	 */
	public function implode($glue = null, $unused = null)
	{
		if (is_null($glue)) return implode($this->items);

		return implode($glue, $this->items);
	}

	public function contains($value)
	{
		return in_array($value, $this->items, true);
	}

	public function without()
	{
		return new static(array_diff($this->items, func_get_args()));
	}

	/**
	 * Get the collection of items as a plain array.
	 *
	 * @return array
	 */
	public function toArray()
	{
		return $this->items;
	}
}
