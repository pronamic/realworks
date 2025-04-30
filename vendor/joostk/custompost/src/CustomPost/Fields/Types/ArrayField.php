<?php namespace CustomPost\Fields\Types;

use CustomPost\Fields\Field;
use CustomPost\Fields\ParentInterface;
use CustomPost\Formatter\ArrayFieldFormatter;

class ArrayField extends Field
{
	protected $table;

	protected static $defaults = array(
		'datatype' => 'VARCHAR(200)',
		'multiple' => true,
	);

	protected static $defaultFormatterResolver;

	public function initialize(ParentInterface $parent)
	{
		parent::initialize($parent);

		$this->table = $parent->resolveArrayTable($this);
		$this->table->setDatatype($this->datatype);
	}

	public function datatype()
	{
		return $this->datatype;
	}

	public function getTable()
	{
		return $this->table;
	}

	public function transform($value)
	{
		return (array) $value;
	}

	public function values(array $values)
	{
		parent::values($values);

		$this->formatter = null;

		return $this;
	}

	public function newDefaultFormatter()
	{
		return new ArrayFieldFormatter($this);
	}
}
