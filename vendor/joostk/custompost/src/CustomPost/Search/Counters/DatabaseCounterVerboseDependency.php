<?php namespace CustomPost\Search\Counters;

use CustomPost\Search\Field;
use CustomPost\Search\Option;

class DatabaseCounterVerboseDependency extends DatabaseCounterVerbose
{
	protected function apply(Field $field, Option $option, $type)
	{
		$query = $this->executer->resolveQuery();

		// It is necessary to apply the option's predicate before applying all others,
		// because the option's field values have to be known for the dependent virtual
		// field so that it can read the value it requires from the query.
		$this->applyType($field, $option, $query, $type);

		// Only then apply all other fields and supply the query we already setup
		return $this->executer->apply($this->query, $field, $type, $query);
	}
}
