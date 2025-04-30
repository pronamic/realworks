<?php namespace CustomPost\Search\Counters;

use CustomPost\Search\Field;
use CustomPost\Fields\Manager;
use CustomPost\Search\Counter;
use CustomPost\Search\Executer;
use CustomPost\Fields\Types\Virtual;
use CustomPost\Search\ExpressionGroup;
use CustomPost\Fields\Types\ArrayField;
use CustomPost\Fields\Field as BaseField;
use CustomPost\Contracts\DatabaseInterface;

class DatabaseCounter extends Counter
{
	protected $manager;

	protected $db;

	public function __construct(Executer $executer, Manager $manager, DatabaseInterface $db)
	{
		$this->manager = $manager;
		$this->db = $db;

		parent::__construct($executer);
	}

	protected function determineCounter(Field $field, $type)
	{
		if ($type === '__all') return new DatabaseCounterAll;

		foreach ($field->getOptions() as $option)
		{
			if ($counter = $this->determineGroupCounter($option)) return $counter;
		}

		return new DatabaseCounterQuick;
	}

	protected function determineGroupCounter(ExpressionGroup $group)
	{
		foreach ($group->getExpressions() as $expression)
		{
			if ($expression instanceof ExpressionGroup)
			{
				if ($this->determineGroupCounter($expression)) return true;
			}
			else
			{
				$field = $this->manager->traverse($expression->getField());

				if ($counter = $this->determineFieldCounter($field)) return $counter;
			}
		}
	}

	protected function determineFieldCounter(BaseField $field)
	{
		if ($field instanceof Virtual)
		{
			return new DatabaseCounterVerboseVirtual;
		}
		else if ($field->hasVirtualDependency() or $field instanceof ArrayField)
		{
			return new DatabaseCounterVerboseDependency;
		}
	}

	protected function calculateCounts(Field $field, $type)
	{
		$counter = $this->determineCounter($field, $type);

		$counter->setDependencies($this->executer, $this->query, $this->db);

		return $counter->calculateCounts($field, $type);
	}
}
