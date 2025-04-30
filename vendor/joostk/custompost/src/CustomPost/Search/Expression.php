<?php namespace CustomPost\Search;

abstract class Expression extends BaseExpression
{
	protected $field;

	public function __construct($field)
	{
		$this->field = $field;
	}

	public function getField()
	{
		return $this->field;
	}

	public static function binary($field, $operator, $value)
	{
		return new Expressions\Binary($field, $operator, $value);
	}

	public static function between($field, $min, $max)
	{
		return new Expressions\Between($field, $min, $max);
	}

	public static function isNull($field)
	{
		return new Expressions\IsNull($field);
	}

	public static function isNotNull($field)
	{
		return new Expressions\IsNull($field, true);
	}

	public static function sql($field, $expression)
	{
		return new Expressions\Sql($field, $expression);
	}

	public function perform(Query $query, $operator = null)
	{
		$query->where($this);
	}

	public function matchesWhenValueUnknown()
	{
		return false;
	}

	public function isNegated()
	{
		return false;
	}

	public function substitute($key, $value)
	{
		$this->replace($this->field, $key, $value);
	}

	protected function replace(&$var, $key, $value)
	{
		$var = str_replace('#{'.$key.'}', $value, $var);
	}

	public function matches($value)
	{
		return false;
	}

	abstract public function toSql($field);

	abstract public function toArray();

	public function __toString()
	{
		// Expressions are temporarily attached as query var of a WP_Query instance in order to apply the search expression.
		// Some plugins, e.g. WPML, expect query vars to be a string, so we fake each expression instance to be convertable
		// to string here. The string representation below is arbitrarily chosen and should not be relied upon by anything.
		$sql = $this->toSql($this->field);

		return is_array($sql) ? call_user_func_array('sprintf', $sql) : $sql;
	}
}
