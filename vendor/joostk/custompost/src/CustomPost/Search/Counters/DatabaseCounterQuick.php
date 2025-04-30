<?php namespace CustomPost\Search\Counters;

use CustomPost\Search\Query;
use CustomPost\Search\Field;
use CustomPost\Search\Option;

class DatabaseCounterQuick extends DatabaseCounterStrategy
{
	public function calculateCounts(Field $field, $type)
	{
		$query = $this->executer->apply($this->query, $field, $type);

		$this->applySelects($query, $field, $type);

		$result = $this->db->cached('first', $query->toSql());

		return $this->extractCounts($field, $result);
	}

	protected function applySelects(Query $query, Field $field, $type)
	{
		$query->clearSelects()->clearLimits();

		foreach ($field->getEnabledOptions() as $option)
		{
			$sql = $this->prepareSql($query, $field, $option, $type);

			// Do not restrain when the option does not have any predicates
			if ($sql === null) $sql = '1';

			$query->select("SUM({$sql})", $this->alias($option));
		}
	}

	protected function prepareSql(Query $query, Field $field, Option $option, $type)
	{
		$sql = $query->prepareSql($option);

		// If a type has been specified, resolve a separate query to perform
		// the specified type on. This query will then provide us with the SQL
		// but adjusted for the given type. The above preparation of the option
		// on the original query is still of importance (although its resulting
		// SQL is not used) because it makes sure any necessary joins are also
		// available on the original query as well.
		if ($type)
		{
			$helper = $this->executer->resolveQuery();

			$field->performType($helper, $type, array($option));

			$sql = $helper->getWheres()->toSql();
		}

		return $sql;
	}

	protected function extractCounts(Field $field, array $result)
	{
		$counts = array();

		foreach ($field->getEnabledOptions() as $option)
		{
			$alias = $this->alias($option);

			$counts[$option->getValue()] = (int) $result[$alias];
		}

		return $counts;
	}

	protected function alias(Option $option)
	{
		return md5($option->getValue());
	}
}
