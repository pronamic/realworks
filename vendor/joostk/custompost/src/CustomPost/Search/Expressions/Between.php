<?php namespace CustomPost\Search\Expressions;

use CustomPost\Search\Expression;

class Between extends Expression
{
	protected $min;

	protected $max;

	public function __construct($field, $min, $max)
	{
		$this->min = $min;
		$this->max = $max;

		parent::__construct($field);
	}

	public function getMin()
	{
		return $this->min;
	}

	public function getMax()
	{
		return $this->max;
	}

	public function substitute($key, $value)
	{
		$this->replace($this->min, $key, $value);
		$this->replace($this->max, $key, $value);

		parent::substitute($key, $value);
	}

	public function matches($value)
	{
		return $value >= $this->min and $value <= $this->max;
	}

	public function toSql($field)
	{
		return array("{$field} BETWEEN %s AND %s", $this->min, $this->max);
	}

	public function toArray()
	{
		return array(
			'field' => $this->field,
			'operator' => 'between',
			'min' => $this->min,
			'max' => $this->max,
		);
	}
}
