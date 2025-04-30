<?php namespace CustomPost\Search;

use WP_Query as WordpressQuery;
use CustomPost\Formatter\Formatter;

class ArrayExecuter
{
	protected $sortOrder;

	protected $queryResolver;

	public function __construct(SortOrder $sortOrder)
	{
		$this->sortOrder = $sortOrder;
	}

	public function setQueryResolver($resolver)
	{
		$this->queryResolver = $resolver;

		return $this;
	}

	public function apply(WordpressQuery $wpQuery, $expressions)
	{
		$query = call_user_func($this->queryResolver);

		$this->performSearch($query, $expressions);

		$this->sortOrder->apply($query, $wpQuery);

		return $query->apply($wpQuery);
	}

	protected function performSearch(Query $query, $expressions)
	{
		if (is_array($expressions))
		{
			$this->performGroup($query, $expressions);
		}
		elseif ($expressions instanceof Expression)
		{
			$query->where($expressions);
		}
	}

	protected function performGroup(Query $query, array $group)
	{
		$relation = $this->determineRelation($group);

		$query->push($relation);

		foreach ($group as $field => $expression)
		{
			if (is_string($field))
			{
				$query->compare($field, '=', $this->expressionValue($expression));
			}
			elseif ($expression instanceof Formatter)
			{
				$this->performFieldExpression($query, $expression);
			}
			elseif (is_string($expression))
			{
				$this->performStringExpression($query, $expression);
			}
			else
			{
				$this->performSearch($query, $expression);
			}
		}

		$query->pop();
	}

	protected function expressionValue($expression)
	{
		if ($expression instanceof Formatter)
		{
			return $expression->value();
		}
		else
		{
			return (string) $expression;
		}
	}

	protected function determineRelation(&$group)
	{
		foreach ($group as $index => $expression)
		{
			if (is_string($expression) and in_array(strtolower($expression), array('and', 'or')))
			{
				unset($group[$index]);

				return $expression;
			}
		}
	}

	protected function performFieldExpression(Query $query, Formatter $field)
	{
		if ( ! $field->field()->get('custom'))
		{
			$query->compare($field->name(), '=', $field->value());
		}
	}

	protected function performStringExpression(Query $query, $expression)
	{
		list($field, $args) = explode(' ', $expression, 2);

		$operator = strtolower(ltrim($args));

		if (in_array($operator, array('null', 'is null')))
		{
			$query->isNull($field);
		}

		elseif (in_array($operator, array('not null', 'notnull', 'is not null')))
		{
			$query->isNotNull($field);
		}

		elseif (starts_with($operator, 'between'))
		{
			list($min, $max) = explode(' ', preg_replace('~\s+and\s+~i', ' ', substr($args, 8)), 2);

			$query->between($field, $min, $max);
		}

		elseif (starts_with($operator, 'sql'))
		{
			$expression = substr($args, 4);

			$query->sql($field, $expression);
		}

		else
		{
			list($operator, $value) = explode(' ', $args, 2);

			$query->compare($field, $operator, $value);
		}
	}
}
