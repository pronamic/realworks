<?php namespace CustomPost\Search\Query;

class Group
{
	protected $relation;

	protected $expressions = array();

	public function __construct($relation = null)
	{
		$this->relation = $relation ?: 'AND';
	}

	public function addExpression($expression)
	{
		$this->expressions[] = $expression;
	}

	public function hasExpressions()
	{
		return count($this->expressions) > 0;
	}

	public function getExpressions()
	{
		return $this->expressions;
	}

	public function getRelation()
	{
		return $this->relation;
	}

	public function forget($sql)
	{
		foreach ($this->expressions as $key => $expression)
		{
			if ($expression === $sql) unset($this->expressions[$key]);
		}
	}

	public function toSql()
	{
		switch (count($this->expressions))
		{
			case 0: return;
			case 1: return reset($this->expressions);
			default: return '(' . implode(" {$this->relation} ", $this->expressions) . ')';
		}
	}
}
