<?php namespace CustomPost\Search\Counters;

use CustomPost\Search\Field;

class DatabaseCounterAll extends DatabaseCounterQuick
{
	public function calculateCounts(Field $field, $type)
	{
		$query = $this->executer->resolveQuery()->apply($this->query);

		$this->applySelects($query, $field, null);

		$result = $this->db->cached('first', $query->toSql());

		return $this->extractCounts($field, $result);
	}
}
