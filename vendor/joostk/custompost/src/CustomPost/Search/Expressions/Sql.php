<?php namespace CustomPost\Search\Expressions;

use CustomPost\Search\Expression;

class Sql extends Expression
{
	protected $expression;

	public function __construct($field, $expression)
	{
		$this->expression = $expression;

		parent::__construct($field);
	}

	public function getExpression()
	{
		return $this->expression;
	}

	public function substitute($key, $value)
	{
		$this->replace($this->expression, $key, $value);

		parent::substitute($key, $value);
	}

	public function toSql($field)
	{
		if (empty($field))
		{
			return $this->expression;
		}
		else
		{
			return "{$field} {$this->expression}";
		}
	}

	public function toArray()
	{
		return array(
			'field' => $this->field,
			'operator' => 'sql',
			'expression' => $this->expression,
		);
	}
}
