<?php namespace CustomPost\Search\Expressions;

use CustomPost\Search\Query;
use CustomPost\Search\Expression;

class Binary extends Expression
{
	protected $operator;

	protected $value;

	public function __construct($field, $operator, $value)
	{
		$this->operator = strtoupper($operator);
		$this->value = $value;

		parent::__construct($field);
	}

	public function getOperator()
	{
		return $this->operator;
	}

	public function getValue()
	{
		return $this->value;
	}

	public function matchesWhenValueUnknown()
	{
		return $this->isNegated();
	}

	public function isNegated()
	{
		return in_array($this->operator, array('!=', '<>', 'NOT LIKE'));
	}

	public function perform(Query $query, $operator = null)
	{
		$original = $this->operator;

		if ($operator !== null and $this->operator === '=')
		{
			$this->operator = $operator;
		}

		parent::perform($query);

		$this->operator = $original;
	}

	public function substitute($key, $value)
	{
		$this->replace($this->operator, $key, $value);
		$this->replace($this->value, $key, $value);

		parent::substitute($key, $value);
	}

	public function matches($value)
	{
		$value = strtolower($value);
		$compare = strtolower($this->value);

		switch ($this->operator)
		{
			case '=':
			case '==': return $value == $compare;
			case '!=':
			case '<>': return $value != $compare;
			case '>': return $value > $compare;
			case '>=': return $value >= $compare;
			case '<': return $value < $compare;
			case '<=': return $value <= $compare;
			default: return false;
		}
	}

	public function toSql($field)
	{
		$operator = $this->operator === '==' ? '=' : $this->operator;

		return array("{$field} {$operator} %s", $this->value);
	}

	public function toArray()
	{
		return array(
			'field' => $this->field,
			'operator' => $this->operator,
			'value' => $this->value,
		);
	}
}
