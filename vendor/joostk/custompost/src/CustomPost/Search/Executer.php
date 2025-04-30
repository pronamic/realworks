<?php namespace CustomPost\Search;

use Closure;
use WP_Query as WordpressQuery;
use JoostK\Wordpress\Events\Events;

class Executer
{
	protected $entity;

	protected $sortOrder;

	protected $events;

	protected $queryResolver;

	protected $form;

	protected $predicate;

	public function __construct(Entity $entity, SortOrder $sortOrder, Form $form, Events $events)
	{
		$this->entity = $entity;
		$this->sortOrder = $sortOrder;
		$this->form = $form;
		$this->events = $events;
	}

	public function setQueryResolver($resolver)
	{
		$this->queryResolver = $resolver;

		return $this;
	}

	public function setPredicate(Closure $predicate)
	{
		$this->predicate = $predicate;

		return $this;
	}

	public function resolveQuery()
	{
		return call_user_func($this->queryResolver);
	}

	public function apply(WordpressQuery $wpQuery, Field $subject = null, $type = null, Query $query = null)
	{
		$query = $query ?: $this->resolveQuery();

		$this->events->fire('search.before', array($this->form, $query, $wpQuery, $subject));

		$this->performSearch($query, $subject, $type);

		$this->sortOrder->apply($query, $wpQuery);

		$this->events->fire('search.after', array($this->form, $query, $wpQuery, $subject));

		return $query->apply($wpQuery);
	}

	protected function performSearch(Query $query, Field $subject = null, $type = null)
	{
		foreach ($this->entity->getFields() as $field)
		{
			// If a type has been specified, always perform the field. The specified
			// type will simply be skipped then, allowing the other available types
			// to still be performed. Otherwise, verify that this field has to be included
			// in the search, such that the specified subject field will be skipped,
			// e.g. to be able to count that field's options.
			if ($type or $this->includesField($field, $subject))
			{
				$this->performField($query, $field, $type);
			}
		}
	}

	protected function includesField(Field $field, Field $subject = null)
	{
		return ! $subject or ! $this->predicate or call_user_func($this->predicate, $field, $subject);
	}

	protected function performField(Query $query, Field $field, $type)
	{
		list($selected, $types) = $this->form->listSelectedOptions($field);

		$this->fireFieldEvent($query, $field, $selected, $types);

		if (count($types) > 0)
		{
			// If a type has been specified, it has to be skipped from being performed
			// to allow for e.g. counting of results.
			if ($type) unset($types[$type]);

			$field->performTypes($query, $types);
		}
		elseif (count($selected) > 0)
		{
			$relation = $this->form->getRelation($field);

			$field->perform($query, $selected, $relation);
		}
	}

	protected function fireFieldEvent(Query $query, Field $field, array $selected, array $types)
	{
		$this->events->fire('search.field: ' . $field->getName(), array($this->form, $query, $field, $selected, $types));
	}
}
