<?php namespace CustomPost\Search\BindingUpdater;

use CustomPost\Search\Field;
use CustomPost\Search\Query;
use CustomPost\Search\Option;
use CustomPost\Search\Expression;
use InvalidArgumentException;

class ArrayFieldSqlBuilderStrategy implements SqlBuilderStrategy
{
	protected $field;

	public function __construct(Field $field)
	{
		$this->field = $field;
	}

	public function applySelects(Query $query)
	{
		$parent = $this->field->getParent();

		if ($parent !== null)
		{
			throw new InvalidArgumentException("Searchfield [{$this->field->getName()}] has a parent [{$parent->getName()}], which is not supported for fields that have a multivalued binding.");
		}

		$binding = $this->field->getBinding();
		$table = $binding->getTable();

		if ($table->exists())
		{
			$column = $query->wrapColumn($table->getName(), $table->getValueColumn());

			$query->ensureJoin($table);
			$query->select($column, $binding->getName());
			$query->where("{$column} IS NOT NULL");

			return true;
		}
		else
		{
			return false;
		}
	}

	public function applyOptions(Query $query)
	{
		foreach ($this->field->getOptions() as $option)
		{
			$where = $this->buildWhere($query, $option);

			if ($where) $query->where("NOT({$where})");
		}
	}

	protected function buildWhere(Query $query, Option $option)
	{
		$expressions = $option->getExpressions();

		switch (count($expressions))
		{
			case 0: return;
			case 1: return $this->buildExpressionSql($query, $expressions[0]);
			default: throw new InvalidArgumentException("Searchfield [{$this->field->getName()}] has multiple conditions, which is not supported for fields that have a multivalued binding.");
		}
	}

	protected function buildExpressionSql(Query $query, Expression $expression)
	{
		// Note: the below code is mirroring the implementation from Query::buildExpressionSql, adapted to deal with array fields differently.
		// The reason is that the Query class builds queries to determine if a post in itself should be matched, whereas the binding updater
		// is interested to query all values from array fields for which no search option currently exists. Therefore, it is interested in
		// the values itself. For example, consider the case there post 1 has values A and B, both of which are represented in a search option.
		// Now, post 1 is updated so that it has values A, B and C. We then need to create an option for C, but if we were to take the existing
		// options into account we'd typically disregard post 1 completely, as it matches the criteria of the existing search options. Therefore,
		// values A and B need to be excluded by themselves, not because their parent is already matched. Since the Query class only supports
		// building queries to match posts, it is not suitable for creating the desired query.

		$table = $this->field->getBinding()->getTable();
		$builder = $query->getBuilder();

		$wrapped = $builder->wrapColumn($table->getName(), $table->getValueColumn());

		$bindings = (array) $expression->toSql($wrapped);

		$sql = array_shift($bindings);

		return $builder->prepare($sql, $bindings);
	}
}
