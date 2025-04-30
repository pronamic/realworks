<?php namespace CustomPost\Search\Counters;

use CustomPost\Search\Query;
use CustomPost\Search\Field;
use CustomPost\Search\Option;

abstract class DatabaseCounterVerbose extends DatabaseCounterStrategy
{
	public function calculateCounts(Field $field, $type)
	{
		$counts = array();

		foreach ($field->getEnabledOptions() as $option)
		{
			$query = $this->apply($field, $option, $type);

			$query->clearSelects()->clearLimits();
			$query->select('COUNT(1)', 'count');

			$result = $this->db->cached('first', $query->toSql());

			$counts[$option->getValue()] = (int) $result['count'];
		}

		return $counts;
	}

	protected abstract function apply(Field $field, Option $option, $type);

	protected function applyType(Field $field, Option $option, Query $query, $type)
	{
		if ($type)
		{
			$field->performType($query, $type, array($option));
		}
		else
		{
			$field->perform($query, array($option));
		}
	}
}
