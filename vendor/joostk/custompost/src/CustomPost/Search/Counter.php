<?php namespace CustomPost\Search;

use WP_Query as WordpressQuery;

abstract class Counter
{
	protected $executer;

	protected $query;

	protected $counts = array();

	public function __construct(Executer $executer)
	{
		$this->executer = $executer;

		$this->setExecuterPredicate();
	}

	public function setQuery(WordpressQuery $query)
	{
		$this->query = $query;

		return $this;
	}

	public function counts(Field $field, $type = null)
	{
		$name = $field->getName() . $type;

		if ( ! isset($this->counts[$name]))
		{
			return $this->counts[$name] = $this->calculateCounts($field, $type);
		}
		else
		{
			return $this->counts[$name];
		}
	}

	abstract protected function calculateCounts(Field $field, $type);

	protected function setExecuterPredicate()
	{
		$this->executer->setPredicate(function(Field $field, Field $subject)
		{
			// We should not apply the subject field when building the query. It is also important that
			// child fields are not included in the query, because they won't apply without the parent.
			if ($field === $subject or $field->isDescendantOf($subject))
			{
				return false;
			}
			else
			{
				return true;
			}
		});
	}
}
