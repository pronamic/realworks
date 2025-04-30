<?php namespace CustomPost\Search;

use BadMethodCallException;

class Form
{
	protected $manager;

	protected $query;

	protected $data = array();

	protected $counter;

	protected $orderings;

	protected $currentOrdering;

	protected static $renderers = array(
		'dropdown' => 'CustomPost\\Search\\Renderers\\Dropdown',
		'minDropdown' => 'CustomPost\\Search\\Renderers\\MinDropdown',
		'maxDropdown' => 'CustomPost\\Search\\Renderers\\MaxDropdown',
		'minMaxDropdown' => 'CustomPost\\Search\\Renderers\\MinMaxDropdown',
		'checkboxes' => 'CustomPost\\Search\\Renderers\\Checkboxes',
		'radios' => 'CustomPost\\Search\\Renderers\\Radios',
		'hidden' => 'CustomPost\\Search\\Renderers\\Hidden',
	);

	public function __construct(Entity $entity)
	{
		$this->entity = $entity;
	}

	public function count(Option $option, $type = null)
	{
		$counts = $this->counter->counts($option->getField(), $type);

		return $counts[$option->getValue()];
	}

	public function exists(Option $option)
	{
		$counts = $this->counter->counts($option->getField(), '__all');

		return $counts[$option->getValue()] > 0;
	}

	public function setCounter(Counter $counter)
	{
		$this->counter = $counter;

		return $this;
	}

	public function setData(array $data)
	{
		$this->data = $data;

		return $this;
	}

	public function getData()
	{
		return $this->data;
	}

	public function value($name, $default = null)
	{
		return isset($this->data[$name]) ? $this->data[$name] : $default;
	}

	public function setValue($name, $value)
	{
		$this->data[$name] = $value;

		return $this;
	}

	public function setOrderings(array $orderings, array $current = null)
	{
		$this->orderings = $orderings;
		$this->currentOrdering = $current;

		return $this;
	}

	public function orderings()
	{
		return $this->orderings;
	}

	public function currentOrdering()
	{
		return $this->currentOrdering;
	}

	public function ordering()
	{
		$current = $this->currentOrdering();

		return $current['orderby'];
	}

	public function listSelectedOptions(Field $field)
	{
		$name = $field->getName();

		$keys = isset($this->data[$name]) ? $this->data[$name] : null;

		// Unset the special relation key, not used as type
		if (is_array($keys)) unset($keys['relation']);

		return $field->getSelectedOptions($keys, $this->getParentPrefix($field));
	}

	protected function getParentPrefix(Field $field)
	{
		$parent = $field->getParent();

		if ($parent and $selected = $this->getSelectedOption($parent))
		{
			return $selected->getIdentifier()."\0";
		}
	}

	public function getSelectedOptions(Field $field, $type = null)
	{
		list($options, $types) = $this->listSelectedOptions($field);

		if ($type)
		{
			return isset($types[$type]) ? $types[$type] : array();
		}
		else
		{
			return $options;
		}
	}

	public function getSelectedOption(Field $field, $type = null)
	{
		$options = $this->getSelectedOptions($field, $type);

		return reset($options) ?: null;
	}

	public function isValueSelected(Field $field, $identifier, $type = null)
	{
		$selected = $this->getSelectedOptions($field, $type);

		// An empty value is used for the empty label, which is not part of
		// the options. Consider it selected when no options are selected.
		return isset($selected[$identifier]) or (empty($selected) and $this->isEmptyIdentifier($identifier));
	}

	protected function isEmptyIdentifier($identifier)
	{
		return $identifier === '' or ends_with($identifier, "\0");
	}

	public function isSelected(Option $option, $type = null)
	{
		return $this->isValueSelected($option->getField(), $option->getIdentifier(), $type);
	}

	public function getVisibleOptions(Field $field)
	{
		list($me, $parent) = array($this, $field->getParent());

		return array_values(array_filter($field->getOptions(), function(Option $option) use ($me, $parent)
		{
			if ($parent)
			{
				return $option->isEnabled() and $me->isValueSelected($parent, $option->getParentIdentifier());
			}
			else
			{
				return $option->isEnabled();
			}
		}));
	}

	public function getRelation(Field $field)
	{
		$name = $field->getName();

		if (isset($this->data[$name]) and is_array($values = $this->data[$name]) and isset($values['relation']))
		{
			return $this->parseRelation($values['relation']);
		}
	}

	protected function parseRelation($relation)
	{
		$relation = strtolower($relation);

		if (in_array($relation, array('and', 'or'))) return $relation;
	}

	public function field($name, array $options = array())
	{
		$field = $this->entity->getField($name);

		if ($field)
		{
			$key = $field->getSetting('renderer.default');

			return $this->renderAs($key, $name, $options);
		}
	}

	public function renderAs($key, $name, array $options = array())
	{
		if (isset(static::$renderers[$key]))
		{
			$class = static::$renderers[$key];

			return $this->render($name, new $class($options));
		}

		throw new BadMethodCallException("Renderer [{$key}] has not been registered.");
	}

	public function render($name, Renderer $renderer)
	{
		$field = $this->entity->getField($name);

		if ($field)
		{
			$renderer->setForm($this)->setField($field);

			return $renderer->render();
		}
	}

	public function options($name)
	{
		$field = $this->entity->getField($name);

		if ($field) return new OptionsRepresentation($field, $this);
	}

	public static function register($key, $class)
	{
		static::$renderers[$key] = $class;
	}

	public function __call($method, $parameters)
	{
		array_unshift($parameters, $method);

		return call_user_func_array(array($this, 'renderAs'), $parameters);
	}
}
