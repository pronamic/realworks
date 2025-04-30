<?php namespace CustomPost\Search\Counters;

use CustomPost\Search\Field;
use CustomPost\Search\Option;

class DatabaseCounterVerboseVirtual extends DatabaseCounterVerbose
{
	protected function apply(Field $field, Option $option, $type)
	{
		$query = $this->executer->apply($this->query, $field, $type);

		$this->applyType($field, $option, $query, $type);

		return $query;
	}
}
