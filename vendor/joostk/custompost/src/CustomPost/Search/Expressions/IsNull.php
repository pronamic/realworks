<?php namespace CustomPost\Search\Expressions;

use CustomPost\Search\Expression;

class IsNull extends Expression
{
	protected $not;

	public function __construct($field, $not = false)
	{
		$this->not = $not;

		parent::__construct($field);
	}

	public function matchesWhenValueUnknown()
	{
		return ! $this->not;
	}

	public function isNot()
	{
		return $this->not;
	}

	public function matches($value)
	{
		return $this->not ? $value !== null : $value === null;
	}

	public function toSql($field)
	{
		if ($this->not)
		{
			return "{$field} IS NOT NULL";
		}
		else
		{
			return "{$field} IS NULL";
		}
	}

	public function toArray()
	{
		return array(
			'field' => $this->field,
			'operator' => $this->not ? 'not null' : 'null',
		);
	}
}
