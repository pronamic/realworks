<?php namespace CustomPost\Search\Query;

use WP_Query as WordpressQuery;
use CustomPost\Contracts\DatabaseInterface;

class Builder
{
	protected $query;

	protected $db;

	protected $distinct = false;

	protected $clearSelects = false;

	protected $clearLimits = false;

	protected $selects = array();

	protected $joins = array();

	protected $wheres = array();

	protected $orderBy = array();

	public function __construct(DatabaseInterface $db)
	{
		$this->db = $db;

		$this->initialize();
	}

	protected function initialize()
	{
		$this->push();
	}

	public function getDatabase()
	{
		return $this->db;
	}

	public function getWheres()
	{
		return reset($this->wheres);
	}

	public function distinct($distinct = true)
	{
		$this->distinct = $distinct;

		return $this;
	}

	public function clearSelects($clear = true)
	{
		$this->clearSelects = $clear;

		return $this;
	}

	public function clearLimits($clear = true)
	{
		$this->clearLimits = $clear;

		return $this;
	}

	public function select($select, $alias = null)
	{
		if ($alias) $select .= " AS `{$alias}`";

		$this->selects[] = $select;

		return $this;
	}

	public function orderBy($sql, $direction = 'ASC')
	{
		$this->orderBy[$sql] = $this->parseDirection($direction) ?: 'ASC';

		return $this;
	}

	protected function parseDirection($direction)
	{
		$direction = strtoupper($direction);

		if (in_array($direction, array('ASC', 'DESC'))) return $direction;
	}

	public function join($rightTable, $rightColumn, $leftTable, $leftColumn, $type = "INNER")
	{
		$left = $this->wrapColumn($leftTable, $leftColumn);
		$right = $this->wrapColumn($rightTable, $rightColumn);

		$this->joins[] = "{$type} JOIN `{$rightTable}` ON {$left} = {$right}";

		return $this;
	}

	public function joinSubquery($query, $alias, $rightColumn, $leftTable, $leftColumn = null, $type = "INNER")
	{
		$left = $this->wrapColumn($leftTable, $leftColumn ?: $rightColumn);
		$right = $this->wrapColumn($alias, $rightColumn);

		$this->joins[] = "{$type} JOIN ({$query}) AS {$alias} ON {$left} = {$right}";

		return $this;
	}

	public function push($relation = null)
	{
		$this->wheres[] = new Group($relation);

		return $this;
	}

	public function pop()
	{
		$group = array_pop($this->wheres);

		if ($group->hasExpressions())
		{
			$this->where($group->toSql());
		}

		return $this;
	}

	public function where($expression)
	{
		end($this->wheres)->addExpression($expression);

		return $this;
	}

	public function forget($sql)
	{
		foreach ($this->wheres as $group)
		{
			$group->forget($sql);
		}

		return $this;
	}

	public function toSql()
	{
		// We will setup a filter which will bomb out of the query execution process
		add_filter('posts_request', array($this, 'catchGeneratedQuery'), 99999999, 2);

		try
		{
			// Let Wordpress generate the query, the filter will prevent it from actually executing
			$this->query->get_posts();
		}
		catch (GeneratedQueryResult $result)
		{
			return $result->getQuery();
		}
	}

	public function catchGeneratedQuery($request, WordpressQuery $query)
	{
		if ($this->query !== $query) return $request;

		remove_filter('posts_request', array($this, 'catchGeneratedQuery'), 99999999);

		throw new GeneratedQueryResult($request);
	}

	public function alterQuery(WordpressQuery $query)
	{
		$this->query = $query;

		add_filter('posts_clauses', array($this, 'alterQueryClauses'), 10, 2);
	}

	public function alterQueryClauses($clauses, WordpressQuery $query)
	{
		if ($this->query !== $query) return $clauses;

		remove_filter('posts_clauses', array($this, 'alterQueryClauses'));

		if ($this->distinct) $clauses['distinct'] = 'DISTINCT';

		if ($this->selects)
		{
			if ($this->clearSelects) $clauses['fields'] = implode(', ', $this->selects);
			else $clauses['fields'] .= ', ' . implode(', ', $this->selects);
		}

		if ($this->joins) $clauses['join'] .= $this->compileJoins();

		if ($this->orderBy) $clauses['orderby'] = $this->compileOrders();

		if ($this->clearLimits) $clauses['limits'] = null;

		$clauses['where'] .= $this->compileWheres();

		return $clauses;
	}

	protected function compileJoins()
	{
		return  ' ' . implode(' ', $this->joins);
	}

	protected function compileWheres()
	{
		if (($where = reset($this->wheres)->toSql()) !== null)
		{
			return " AND {$where}";
		}
	}

	protected function compileOrders()
	{
		$orders = array();

		foreach ($this->orderBy as $sql => $direction)
		{
			$orders[] = "{$sql} {$direction}";
		}

		return implode(', ', $orders);
	}

	public function prepare($sql, array $bindings = array())
	{
		return $this->db->prepare($sql, $bindings);
	}

	public function wrapColumn($table, $column)
	{
		return "`{$table}`.`{$column}`";
	}
}
