<?php namespace CustomPost\Search;

use Exception;
use InvalidArgumentException;
use WP_Query as WordpressQuery;
use CustomPost\Fields\Types\Virtual;
use CustomPost\Fields\Types\ArrayField;
use CustomPost\Contracts\DatabaseInterface;

class BindingUpdater
{
	protected $entity;

	protected $query;

	protected $queryResolver;

	public function __construct(Entity $entity, DatabaseInterface $db)
	{
		$this->entity = $entity;
		$this->db = $db;

		$this->query = new WordpressQuery(array(
			'post_status' => get_post_stati(),
		));
	}

	public function setQuery(WordpressQuery $query)
	{
		$this->query = $query;

		return $this;
	}

	public function setQueryResolver($resolver)
	{
		$this->queryResolver = $resolver;

		return $this;
	}

	public function update()
	{
		$dirty = false;

		foreach ($this->entity->getFields() as $field)
		{
			if ($field->getBinding())
			{
				$this->verifyBinding($field);

				$dirty |= $this->updateField($field);
			}
		}

		return (bool) $dirty;
	}

	protected function verifyBinding(Field $field)
	{
		$binding = $field->getBinding();

		if ($binding instanceof Virtual)
		{
			$name = $binding->getFullName('.');

			throw new InvalidArgumentException("Field [{$field->getName()}] can not be bound to virtual field [{$name}]");
		}
	}

	protected function updateField(Field $field)
	{
		$groups = $this->retrieveNewValues($field);

		foreach ($groups as $values)
		{
			$value = $values[$field->getBinding()->getName()];
			$label = $this->determineOptionLabel($field, $value);
			$expression = $this->buildExpression($field, $value);

			$option = new Option(null, $label);
			$option->setTemplate($expression->getTemplate());
			$option->addExpression($expression);
			$option->setParents($this->buildParents($field, $values));

			$field->addOption($option);
		}

		return count($groups) > 0;
	}

	protected function determineOptionLabel(Field $field, $value)
	{
		$formatter = $field->getBinding()->newFormatter()->setValue(
			$field->getBinding()->transform($value)
		);

		try
		{
			return $formatter->render();
		}
		catch (Exception $e)
		{
			return $formatter->toDefault()->render();
		}
	}

	protected function buildExpression(Field $field, $value)
	{
		$templates = $field->getExpressionTemplates();

		if (isset($templates['default']))
		{
			return $templates['default']->makeWithArguments('default', array($value));
		}
		else
		{
			return new Expressions\Binary($field->getBinding()->getFullName('.'), '=', $value);
		}
	}

	protected function buildParents(Field $field, array $values)
	{
		$parent = $field->getParent();

		$parents = array();

		while ($parent)
		{
			$value = $values[$parent->getBinding()->getName()];

			$parents[] = array(
				'field' => $parent->getName(),
				'value' => $this->guessMatchingOption($parent, $value)->getValue(),
			);

			$parent = $parent->getParent();
		}

		return array_reverse($parents);
	}

	protected function guessMatchingOption(Field $field, $value)
	{
		foreach ($field->getOptions() as $option)
		{
			if ($option->matches($value)) return $option;
		}

		return new Option(null, $value);
	}

	protected function retrieveNewValues(Field $field)
	{
		$query = call_user_func($this->queryResolver);

		$query->apply($this->query)->distinct()->clearSelects()->clearLimits();

		$strategy = $this->determineSqlBuilderStrategy($field);

		if ($strategy->applySelects($query))
		{
			$strategy->applyOptions($query);

			$sql = $query->toSql();

			return $this->db->all($sql);
		}
		else
		{
			return array();
		}
	}

	protected function determineSqlBuilderStrategy(Field $field)
	{
		if ($field->getBinding() instanceof ArrayField)
		{
			return new BindingUpdater\ArrayFieldSqlBuilderStrategy($field);
		}
		else
		{
			return new BindingUpdater\RegularFieldSqlBuilderStrategy($field);
		}
	}
}
