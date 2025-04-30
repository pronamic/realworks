<?php namespace CustomPost\Search;

use InvalidArgumentException;
use CustomPost\Fields\Manager;
use CustomPost\Fields\Subtype;
use CustomPost\Database\Table;
use WP_Query as WordpressQuery;
use CustomPost\Fields\Types\Virtual;
use CustomPost\Fields\ParentInterface;
use CustomPost\Fields\Types\ArrayField;
use CustomPost\Fields\Field as BaseField;

class Query
{
	protected $manager;

	protected $joinedPosts = false;

	protected $joined = array();

	protected $fields = array();

	protected $appliedVirtualFields = array();

	public function __construct(Manager $manager, Query\Builder $builder, $postType)
	{
		$this->manager = $manager;
		$this->builder = $builder;
		$this->postType = $postType;
	}

	public function getBuilder()
	{
		return $this->builder;
	}

	public function getFields()
	{
		return $this->fields;
	}

	public function getField($field)
	{
		return array_get($this->fields, $field, array());
	}

	public function where($expression)
	{
		$sql = is_string($expression) ? $expression : $this->prepareSql($expression);

		$this->builder->where($sql);

		return $this;
	}

	public function compare($field, $operator, $value)
	{
		return $this->where(new Expressions\Binary($field, $operator, $value));
	}

	public function between($field, $min, $max)
	{
		return $this->where(new Expressions\Between($field, $min, $max));
	}

	public function isNull($field)
	{
		return $this->where(new Expressions\IsNull($field));
	}

	public function isNotNull($field)
	{
		return $this->where(new Expressions\IsNull($field, true));
	}

	public function sql($field, $expression)
	{
		return $this->where(new Expressions\Sql($field, $expression));
	}

	public function forget($expression)
	{
		$sql = $this->prepareSql($expression);

		$this->builder->forget($sql);

		return $this;
	}

	public function orderBy($field, $direction = 'ASC')
	{
		if ($field = $this->resolveField($field))
		{
			if ($field instanceof Virtual)
			{
				$this->orderByVirtual($field, $direction);
			}
			else
			{
				$this->orderByColumn($field, $direction);
			}
		}

		return $this;
	}

	protected function orderByVirtual(Virtual $field, $direction)
	{
		if ($this->didApplyVirtualField($field) and $column = $field->computedColumn())
		{
			$this->builder->orderBy($column, $direction);
		}
	}

	protected function orderByColumn(BaseField $field, $direction)
	{
		$parent = $field->getParent();
		$column = $field->getName();
		$table = $parent->getTable();

		if ($table->hasColumn($column))
		{
			$this->ensureJoin($table);

			$sql = $this->builder->wrapColumn($table->getName(), $column);

			$this->builder->orderBy($sql, $direction);
		}
	}

	public function apply(WordpressQuery $query)
	{
		$query->set('post_type', $this->postType);

		$this->builder->alterQuery($query);

		return $this;
	}

	protected function ensureWordpressJoin()
	{
		$table = $this->manager->getTable();

		if ( ! $this->joinedPosts and $table->exists())
		{
			$this->builder->join(
				$table->getName(),
				$table->getReferenceKey(),
				$this->builder->getDatabase()->getTableName('posts'),
				'ID'
			);

			$this->joinedPosts = true;
		}
	}

	public function prepareSql($expression)
	{
		if ($expression instanceof ExpressionGroup)
		{
			return $this->sqlForGroup($expression);
		}
		else if ($expression instanceof Expression)
		{
			return $this->sqlForExpression($expression);
		}
		else if (is_array($expression))
		{
			return $this->sqlWithBindings($expression);
		}
		else
		{
			return $expression;
		}
	}

	protected function sqlForGroup(ExpressionGroup $group)
	{
		$expressions = array();

		foreach ($group->getExpressions() as $expression)
		{
			$expressions[] = $this->prepareSql($expression);
		}

		switch (count($expressions))
		{
			case 0: return;
			case 1: return $expressions[0];
			default: return '(' . implode(' ' . $group->getRelation() . ' ', $expressions) . ')';
		}
	}

	protected function sqlForExpression(Expression $expression)
	{
		// An expression without a field name is handled as pure sql
		if ($expression->getField() === null)
		{
			return $this->sqlWithBindings($expression->toSql(null));
		}

		$this->rememberFieldExpression($expression);

		$sql = $this->buildSql($expression);

		if ($sql)
		{
			return $sql;
		}

		// When no field is returned and no exception has been thrown, the table or column does not exist.
		// This means that searching for null values succeeds and non-null expressions never succeed.
		else
		{
			return $expression->matchesWhenValueUnknown() ? '1' : '0';
		}
	}

	protected function sqlWithBindings(array $bindings)
	{
		$sql = array_shift($bindings);

		return $this->builder->prepare($sql, $bindings);
	}

	protected function rememberFieldExpression(Expression $expression)
	{
		$name = $expression->getField();

		$this->fields[$name][] = $expression;
	}

	protected function applyVirtualField(Virtual $field)
	{
		$this->appliedVirtualFields[get_class($field)] = true;
	}

	protected function didApplyVirtualField(Virtual $field)
	{
		return isset($this->appliedVirtualFields[get_class($field)]);
	}

	protected function buildSql(Expression $expression)
	{
		$field = $this->resolveField($expression->getField());

		// Subtypes require joining the subtable
		if ($field instanceof Subtype)
		{
			return $this->buildSubtypeSql($field, $expression);
		}

		// Array fields require a special treatment
		elseif ($field instanceof ArrayField)
		{
			return $this->buildArrayFieldSql($field, $expression);
		}

		// Virtual fields specify their clauses in a callback
		elseif ($field instanceof Virtual)
		{
			return $this->buildVirtualFieldSql($field, $expression);
		}

		// Otherwise just use the column if it's a leaf.
		elseif ($field)
		{
			return $this->buildFieldSql($field, $expression);
		}
	}

	protected function buildSubtypeSql(Subtype $subtype, Expression $expression)
	{
		// If a subtype is queried, assert that only IS NULL checks are possible.
		if ( ! $expression instanceof Expressions\IsNull)
		{
			throw new InvalidArgumentException('The requested expression can not be evaluated for non-leaf fields.');
		}

		$table = $subtype->getTable();

		if ($table->exists())
		{
			$this->ensureJoin($table);

			return $this->buildExpressionSql($expression, $table->getName(), $table->getReferenceKey());
		}
	}

	protected function buildArrayFieldSql(ArrayField $field, Expression $expression)
	{
		$table = $field->getTable();

		if ($table->exists())
		{
			$this->ensureJoin($field->getParent()->getTable());

			if ($expression instanceof Expressions\IsNull)
			{
				$negated = ! $expression->isNot();
				$where = '';
			}
			else
			{
				$negated = $expression->isNegated();
				$where  = ' AND ';
				$where .= $negated ? 'NOT(' : '';
				$where .= $this->buildExpressionSql($expression, $table->getName(), $table->getValueColumn(), false);
				$where .= $negated ? ')' : '';
			}

			$parent = $field->getParent()->getTable();

			$childId = $this->builder->wrapColumn($table->getName(), $table->getReferenceKey());
			$parentId = $this->builder->wrapColumn($parent->getName(), $parent->getReferenceKey());

			return ($negated ? 'NOT ' : '') . "EXISTS (SELECT 1 FROM `{$table->getName()}` WHERE {$childId} = {$parentId}{$where})";
		}
	}

	protected function buildVirtualFieldSql(Virtual $field, Expression $expression)
	{
		if ($this->didApplyVirtualField($field)) return '1';

		$this->ensureWordpressJoin();

		$sql = $field->perform($this, $expression);

		// Do not constrain the query when the field does not apply any predicates
		if ($sql === null) return '1';

		$this->applyVirtualField($field);

		return $this->prepareSql($sql);
	}

	protected function buildFieldSql(BaseField $field, Expression $expression)
	{
		$parent = $field->getParent();
		$column = $field->getName();
		$table = $parent->getTable();

		if ($table->hasColumn($column))
		{
			$this->ensureJoin($table);

			return $this->buildExpressionSql($expression, $table->getName(), $column);
		}
	}

	protected function buildExpressionSql(Expression $expression, $table, $field, $matchNull = true)
	{
		$wrapped = $this->builder->wrapColumn($table, $field);

		$bindings = (array) $expression->toSql($wrapped);

		$sql = array_shift($bindings);

		if ($matchNull and $expression->isNegated())
		{
			$sql = "({$sql} OR {$wrapped} IS NULL)";
		}

		return $this->builder->prepare($sql, $bindings);
	}

	protected function resolveField($field)
	{
		$parent = $this->manager;

		if ($parent->getTable()->exists())
		{
			$parts = explode('.', $field);
			$key = array_pop($parts);

			foreach ($parts as $part)
			{
				$child = $parent->getField($part);

				$parent = $this->assertParent($child, $part);
			}

			return $this->assertChild($parent->getField($key), $field);
		}
	}

	protected function assertParent($parent, $part)
	{
		if ( ! $parent instanceof ParentInterface)
		{
			throw new InvalidArgumentException("The requested field could not be resolved, [{$part}] does not exist or is a leaf.");
		}

		return $parent;
	}

	protected function assertChild($child, $field)
	{
		if ( ! $child)
		{
			throw new InvalidArgumentException("The field [{$field}] in the expression does not exist.");
		}

		return $child;
	}

	public function ensureJoin(Table $rightTable)
	{
		$this->ensureWordpressJoin();

		$table = $rightTable->getName();

		if ($rightTable !== $this->manager->getTable() and ! isset($this->joined[$table]))
		{
			$this->builder->join(
				$table,
				$rightTable->getReferenceKey(),
				$this->manager->getTable()->getName(),
				$this->manager->getTable()->getReferenceKey(),
				'LEFT'
			);

			$this->joined[$table] = true;
		}

		return $this;
	}

	public function __call($method, $parameters)
	{
		return call_user_func_array(array($this->builder, $method), $parameters);
	}
}
