<?php namespace CustomPost\Search\BindingUpdater;

use CustomPost\Search\Field;
use CustomPost\Search\Query;
use CustomPost\Search\Option;
use InvalidArgumentException;

class RegularFieldSqlBuilderStrategy implements SqlBuilderStrategy
{
	protected $field;

	public function __construct(Field $field)
	{
		$this->field = $field;
	}

	public function applySelects(Query $query)
	{
		$parent = $this->field;

		while ($parent)
		{
			$binding = $parent->getBinding();

			if ( ! $binding)
			{
				throw new InvalidArgumentException("Searchfield [{$this->field->getName()}] its parent [{$parent->getName()}] must also be bound.");
			}

			$table = $binding->getParent()->getTable();

			if ($table->exists() and $table->hasColumn($binding->getName()))
			{
				$column = $query->wrapColumn($table->getName(), $binding->getName());

				$query->ensureJoin($table);
				$query->select($column);
				$query->where("{$column} IS NOT NULL");
			}
			else
			{
				return false;
			}

			$parent = $parent->getParent();
		}

		return true;
	}

	public function applyOptions(Query $query)
	{
		foreach ($this->field->getOptions() as $option)
		{
			$wheres = $this->buildWheres($query, $option);

			if ($wheres) $query->where('NOT(('.implode(') AND (', $wheres).'))');
		}
	}

	protected function buildWheres(Query $query, Option $option)
	{
		$wheres = array();

		while ($option)
		{
			if ($sql = $query->prepareSql($option))
			{
				$wheres[] = $sql;
			}

			$option = $option->getParentOption();
		}

		return $wheres;
	}
}
