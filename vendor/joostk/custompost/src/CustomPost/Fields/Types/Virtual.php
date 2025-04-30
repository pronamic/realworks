<?php namespace CustomPost\Fields\Types;

use BadMethodCallException;
use CustomPost\Fields\Field;
use CustomPost\Search\Query;
use CustomPost\Search\Expression;

abstract class Virtual extends Field
{
	protected static $defaultFormatterResolver;

	public function datatype()
	{
		throw new BadMethodCallException("Virtual fields do not have a datatype.");
	}

	public function toSql()
	{
		return null;
	}

	public function computedColumn()
	{
		return null;
	}

	abstract public function perform(Query $query, Expression $expression);
}
