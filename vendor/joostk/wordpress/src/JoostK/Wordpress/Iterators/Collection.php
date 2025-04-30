<?php namespace JoostK\Wordpress\Iterators;

use JoostK\Illuminate\Support\Collection as BaseCollection;

class Collection extends BaseCollection
{
	public function sample($amount = 1)
	{
		switch ($amount = min($amount, $this->count()))
		{
			case 0: return new static;
			case 1: return new static(array(parent::random(1)));
			default: return new static(array_values(parent::random($amount)));
		}
	}
}
