<?php namespace CustomPost\Formatter;

use RuntimeException;
use CustomPost\Fields\Field;
use CustomPost\Database\Model;

class Formatter
{
	protected $field;

	protected $model;

	protected $value;

	protected $original;

	public function __construct(Field $field)
	{
		$this->field = $field;
	}

	public function getField()
	{
		return $this->field;
	}

	public function field()
	{
		return $this->getField();
	}

	public function setModel(Model $model = null)
	{
		$this->model = $model;

		return $this;
	}

	public function getModel()
	{
		if ($this->model === null)
		{
			throw new RuntimeException("A model is currently not available for field [{$this->field->getName()}].");
		}

		return $this->model;
	}

	public function model()
	{
		return $this->getModel();
	}

	public function post()
	{
		return $this->getModel();
	}

	public function setValue($value)
	{
		$this->value = $value;

		return $this;
	}

	public function value()
	{
		return $this->value;
	}

	public function getData()
	{
		return $this->value;
	}

	public function setOriginal($original)
	{
		$this->original = $original;

		return $this;
	}

	public function getOriginal()
	{
		return $this->original;
	}

	public function isEmpty()
	{
		return $this->value === null;
	}

	public function hasValue()
	{
		return ! $this->isEmpty();
	}

	public function is($value)
	{
		return in_array($this->value, func_get_args(), true);
	}

	public function isnt($value)
	{
		return ! $this->is($value);
	}

	public function isDirty()
	{
		return $this->value !== $this->original;
	}

	public function getDirty()
	{
		return $this->isDirty() ? $this : null;
	}

	public function diff()
	{
		return array(
			'from' => with(clone $this)->setValue($this->original)->render(),
			'to' => $this->render(),
		);
	}

	public function label()
	{
		return $this->field->getLabel();
	}

	public function name($glue = '.')
	{
		return $this->field->getFullName($glue);
	}

	public function formatter($formatter)
	{
		return $this->copyState(
			$this->field->makeFormatter($formatter)
		);
	}

	public function toDefault()
	{
		return $this->copyState(
			$this->field->newDefaultFormatter()
		);
	}

	protected function copyState(Formatter $formatter)
	{
		return $formatter->setModel($this->model)->setValue($this->value)->setOriginal($this->original);
	}

	public function render()
	{
		return (string) $this->value;
	}

	public function apply(array $parameters)
	{
		return implode(', ', $parameters);
	}

	public function __toString()
	{
		return $this->render();
	}

	public function __sleep()
	{
		// Formatters may be used in queries, in which case an attempt is made to serialize
		// them. We prevent serialization here to avoid serializing the full object graph.
		return array();
	}
}
