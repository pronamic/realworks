<?php namespace CustomPost\Search;

class Option extends ExpressionGroup
{
	protected $field;

	/**
	 * Whether the option is enabled or not
	 */
	protected $enabled;

	/**
	 * Whether the option is the default
	 */
	protected $default;

	/**
	 * The value of the option
	 */
	protected $value;

	/**
	 * The label of this field
	 */
	protected $label;

	/**
	 * Parent option's value
	 */
	protected $parents = array();

	public function __construct($value, $label, $enabled = true, $default = false, $relation = null, array $expressions = array())
	{
		$this->value = $value === null ? $this->valueFromLabel($label) : $value;
		$this->label = $label;
		$this->enabled = $enabled;
		$this->default = $default;

		parent::__construct($relation, $expressions);
	}

	public function setField(Field $field)
	{
		$this->field = $field;

		return $this;
	}

	public function getField()
	{
		return $this->field;
	}

	public function getValue()
	{
		return $this->value;
	}

	public function setLabel($label)
	{
		$this->label = $label;

		return $this;
	}

	public function getLabel()
	{
		return $this->label;
	}

	public function setEnabled($enabled)
	{
		$this->enabled = $enabled;

		return $this;
	}

	public function isDefault()
	{
		return $this->default;
	}

	public function setDefault($default)
	{
		$this->default = $default;

		return $this;
	}

	public function isEnabled()
	{
		return $this->enabled;
	}

	public function setParents(array $parents)
	{
		$this->parents = $parents;

		return $this;
	}

	public function getParents()
	{
		return $this->parents;
	}

	public function getParentIdentifier()
	{
		return implode("\0", array_pluck($this->parents, 'value'));
	}

	public function getIdentifier()
	{
		$parents = $this->getParentIdentifier();

		return $parents ? $parents."\0".$this->value : $this->value;
	}

	public function getParentOption()
	{
		$parent = $this->field->getParent();

		if ($parent) return $parent->getOption($this->getParentIdentifier());
	}

	protected function valueFromLabel($label)
	{
		return strtolower(trim(preg_replace('~[^a-z0-9-_]~i', '', $label), '_-'));
	}
}
