<?php namespace CustomPost\Search;

use InvalidArgumentException;

class ExpressionGroup extends BaseExpression
{
	protected $relation;

	protected $expressions;

	public function __construct($relation = null, array $expressions = array())
	{
		$this->expressions = $expressions;

		$this->setRelation($relation);
	}

	public function addExpression(ExpressionInterface $expression)
	{
		$this->expressions[] = $expression;

		return $this;
	}

	public function setExpressions(array $expressions)
	{
		$this->expressions = $expressions;

		return $this;
	}

	public function hasExpressions()
	{
		return count($this->expressions) > 0;
	}

	public function getExpression($index)
	{
		return isset($this->expressions[$index]) ? $this->expressions[$index] : null;
	}

	public function getExpressions()
	{
		return $this->expressions;
	}

	public function setRelation($relation)
	{
		$relation = strtoupper($relation) ?: 'AND';

		if (in_array($relation, array('AND', 'OR')))
		{
			$this->relation = $relation;
		}
		else
		{
			throw new InvalidArgumentException("Relation [{$relation}] is invalid.");
		}

		return $this;
	}

	public function getRelation()
	{
		return $this->relation;
	}

	public function perform(Query $query, $operator = null)
	{
		if ( ! $this->hasExpressions()) return;

		$query->push($this->relation);

		foreach ($this->expressions as $expression)
		{
			$expression->perform($query, $operator);
		}

		$query->pop();
	}

	public function matches($value)
	{
		if ($this->relation === 'AND')
		{
			return $this->matchesAll($value);
		}
		else
		{
			return $this->matchesAny($value);
		}
	}

	protected function matchesAll($value)
	{
		foreach ($this->expressions as $expression)
		{
			if ( ! $expression->matches($value)) return false;
		}

		return true;
	}

	protected function matchesAny($value)
	{
		foreach ($this->expressions as $expression)
		{
			if ($expression->matches($value)) return true;
		}

		return false;
	}

	public function substitute($key, $value)
	{
		foreach ($this->expressions as $expression)
		{
			$expression->substitute($key, $value);
		}
	}
}
