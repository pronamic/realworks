<?php namespace CustomPost\Formatter;

use Countable;
use IteratorAggregate;
use CustomPost\Fields\Types\ArrayField;
use CustomPost\Support\StringCollection;
use JoostK\Illuminate\Support\Collection;

class ArrayFieldFormatter extends Formatter implements Countable, IteratorAggregate
{
	public function __construct(ArrayField $field)
	{
		parent::__construct($field);
	}

	public function setValue($value)
	{
		// HACK: Incoming value may not have been resolved to a collection
		if ( ! $value instanceof Collection)
		{
			$value = StringCollection::make($value);
		}

		$this->value = $value;

		return $this;
	}

	public function isEmpty()
	{
		return parent::isEmpty() or $this->value->isEmpty();
	}

	public function isDirty()
	{
		return $this->value->all() !== $this->original->all();
	}

	public function diff()
	{
		$added = array_values(array_diff($this->value->all(), $this->original->all()));
		$removed = array_values(array_diff($this->original->all(), $this->value->all()));

		return compact('added', 'removed');
	}

	public function getData()
	{
		return $this->value->all();
	}

	public function render($separator = ', ')
	{
		$values = $this->field->get('values');

		$collection = $values ? $this->mapValues($values) : $this->value;

		return $collection->implode($separator);
	}

	protected function mapValues(array $values)
	{
		return $this->value->map(function($value) use ($values)
		{
			return array_get($values, $value, $value);
		});
	}

	public function is($value)
	{
		return count(array_intersect($this->value->all(), func_get_args())) > 0;
	}

	public function count()
	{
		return $this->value->count();
	}

	public function getIterator()
	{
		return $this->value->getIterator();
	}

	public function __call($method, $parameters)
	{
		return call_user_func_array(array($this->value, $method), $parameters);
	}
}
