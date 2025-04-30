<?php namespace CustomPost\Fields;

use InvalidArgumentException;

abstract class BaseField
{
	protected $name;

	protected $parent;

	public function __construct($name)
	{
		$this->name = $this->validateName($name);
	}

	public function getName()
	{
		return $this->name;
	}

	public function getParent()
	{
		return $this->parent;
	}

	public function initialize(ParentInterface $parent)
	{
		$this->parent = $parent;

		return $this;
	}

	public function getFullName($glue = null)
	{
		$names = $this->parent->getFullName();

		$names[] = $this->name;

		return $glue === null ? $names : implode($glue, $names);
	}

	protected function validateName($name)
	{
		if ( ! preg_match('/^[a-z_]\w*$/i', $name))
		{
			throw new InvalidArgumentException("Field name [{$name}] contains invalid characters.");
		}

		return $name;
	}

	public function __sleep()
	{
		return array_diff(array_keys(get_object_vars($this)), array('parent'));
	}
}
