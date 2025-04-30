<?php namespace CustomPost\Fields;

use BadMethodCallException;
use CustomPost\Fields\Types\ArrayField;

class Subtype extends BaseField implements ParentInterface
{
	protected $selector;

	protected $table;

	protected $fields;

	protected $multiple = false;

	public function __construct($name, $selector, array $fields)
	{
		$this->selector = $selector;
		$this->fields = $fields;

		parent::__construct($name);
	}

	public static function make($name, $selector, array $fields)
	{
		return new static($name, $selector, $fields);
	}

	public static function collection($name, $selector, array $fields)
	{
		return new CollectionSubtype($name, $selector, $fields);
	}

	public function initialize(ParentInterface $parent)
	{
		parent::initialize($parent);

		$this->table = $parent->resolveSubtypeTable($this);

		$initialized = array();

		foreach ($this->fields as $field)
		{
			$field->initialize($this);

			$initialized[$field->getName()] = $field;
		}

		$this->fields = $initialized;
	}

	public function resolveSubtypeTable(Subtype $subtype)
	{
		return $this->parent->resolveSubtypeTable($subtype);
	}

	public function resolveArrayTable(ArrayField $field)
	{
		return $this->parent->resolveArrayTable($field);
	}

	public function getFields()
	{
		return $this->fields;
	}

	public function getField($key)
	{
		return isset($this->fields[$key]) ? $this->fields[$key] : null;
	}

	public function getSelector()
	{
		return $this->selector;
	}

	public function getTable()
	{
		return $this->table;
	}

	public function setPrimaryField(Field $field)
	{
		throw new BadMethodCallException('The primary field must be in the root of the field declarations.');
	}

	public function __get($key)
	{
		return $this->getField($key);
	}

	public function __isset($key)
	{
		return isset($this->fields[$key]);
	}
}
