<?php namespace CustomPost\Search\Query;

use Exception;

class GeneratedQueryResult extends Exception
{
	protected $query;

	public function __construct($query)
	{
		$this->query = $query;
	}

	public function getQuery()
	{
		return $this->query;
	}
}
