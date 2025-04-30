<?php namespace CustomPost\Search\Counters;

use CustomPost\Search\Query;
use CustomPost\Search\Field;
use CustomPost\Search\Option;
use CustomPost\Search\Executer;
use WP_Query as WordpressQuery;
use CustomPost\Contracts\DatabaseInterface;

abstract class DatabaseCounterStrategy
{
	protected $executer;

	protected $query;

	protected $db;

	public function setDependencies(Executer $executer, WordpressQuery $query, DatabaseInterface $db)
	{
		$this->executer = $executer;
		$this->query = $query;
		$this->db = $db;
	}

	public abstract function calculateCounts(Field $field, $type);
}
