<?php namespace CustomPost\Search;

use CustomPost\Fields\Field as BaseField;

class Field
{
	/**
	 * Binding to a data field to auto populate
	 */
	protected $binding;

	/**
	 * Associative array with expression templates
	 */
	protected $expressionTemplates = array();

	/**
	 * Name of the searchfield
	 */
	protected $name;

	/**
	 * Options of the search field
	 */
	protected $options = array();

	/**
	 * Settings of the search field
	 */
	protected $settings = array();

	/**
	 * Parent of this field
	 */
	protected $parent;

	/**
	 * Children of this field
	 */
	protected $children = array();

	public function __construct($name, array $options)
	{
		$this->name = $name;
		$this->options = $this->keyedOptions($options);
	}

	public function getName()
	{
		return $this->name;
	}

	public function getOptions()
	{
		return $this->options;
	}

	public function getSetting($key, $default = null)
	{
		return array_get($this->settings, $key, $default);
	}

	public function setSettings(array $settings)
	{
		$this->settings = $settings;

		return $this;
	}

	public function getSettings()
	{
		return $this->settings;
	}

	public function setSetting($key, $value)
	{
		array_set($this->settings, $key, $value);

		return $this;
	}

	public function getBinding()
	{
		return $this->binding;
	}

	public function setBinding(BaseField $binding)
	{
		$this->binding = $binding;

		return $this;
	}

	public function getParent()
	{
		return $this->parent;
	}

	public function setParent(Field $parent)
	{
		$this->unsetParent();

		$this->parent = $parent;

		$parent->addChild($this);

		return $this;
	}

	public function unsetParent()
	{
		if ($this->parent)
		{
			$this->parent->removeChild($this);

			$this->parent = null;
		}

		return $this;
	}

	protected function addChild(Field $child)
	{
		$this->children[$child->name] = $child;
	}

	protected function removeChild(Field $child)
	{
		unset($this->children[$child->name]);
	}

	public function setExpressionTemplates(array $templates)
	{
		$this->expressionTemplates = $templates;

		return $this;
	}

	public function getExpressionTemplates()
	{
		return $this->expressionTemplates;
	}

	public function addOption(Option $new)
	{
		$identifier = $new->getIdentifier();
		$index = count($this->options);
		$prev = null;

		foreach (array_reverse($this->options) as $option)
		{
			if (strnatcasecmp($identifier, $option->getIdentifier()) < 0)
			{
				// If the previously handled value (thus the next option as we're traversing in reverse) precedes
				// this option, we have reached the point where manually prioritized options are. We want to
				// manintain these options and thus not put the new option in between, so we stop here.
				if ($prev and strnatcasecmp($prev->getIdentifier(), $option->getIdentifier()) < 0)
				{
					break;
				}

				$index--;
			}

			// If the option we're inserting is smaller than the currently handled option,
			// we apparently found the correct alphabetical position, so we're done.
			else
			{
				break;
			}

			$prev = $option;
		}

		// Essentially array_splice, but that doesn't retain keys
		$this->options =
			array_slice($this->options, 0, $index, true) +
			array($new->getIdentifier() => $new->setField($this)) +
			array_slice($this->options, $index, null, true);
	}

	public function getSelectedOptions($values, $prefix = null)
	{
		$options = array();
		$types = array();
		$isEmpty = true;

		foreach ((array) $values as $key => $value)
		{
			$value = $prefix . $value;

			// If the key contains anything other than digits, it's considered as a subkey.
			// This is used for e.g. min/max fields. We'll recursively handle the subkey values.
			if (ctype_digit((string) $key))
			{
				$option = $this->getOption($value);

				if ($option and $option->isEnabled()) $options[$value] = $option;

				$isEmpty = false;
			}
			else
			{
				list($types[$key]) = $this->getSelectedOptions($value);
			}
		}

		$options = $isEmpty ? $this->getDefaultOptions() : $options;

		return array($options, $types);
	}

	public function getOption($identifier)
	{
		return isset($this->options[$identifier]) ? $this->options[$identifier] : null;
	}

	public function getEnabledOptions()
	{
		return array_filter($this->options, function(Option $option)
		{
			return $option->isEnabled();
		});
	}

	public function getDefaultOptions()
	{
		return array_filter($this->options, function(Option $option)
		{
			return $option->isEnabled() and $option->isDefault();
		});
	}

	public function getDefaultRelation()
	{
		return $this->getSetting('relation', 'or');
	}

	public function isDescendantOf(Field $field)
	{
		return $this->parent === $field or ($this->parent and $this->parent->isDescendantOf($field));
	}

	public function perform(Query $query, array $selected, $relation = null)
	{
		$relation = $relation ?: $this->getDefaultRelation();

		$query->push($relation);

		$this->performExpressions($query, $selected);

		$query->pop();
	}

	protected function performExpressions(Query $query, array $selected, $operator = null)
	{
		foreach ($selected as $option)
		{
			$option->perform($query, $operator);
		}
	}

	public function performTypes(Query $query, array $types)
	{
		$query->push('and');

		foreach ($types as $type => $selected)
		{
			$this->performType($query, $type, $selected);
		}

		$query->pop();
	}

	public function performType(Query $query, $type, array $selected)
	{
		switch ($type)
		{
			case 'min':
				return $this->performExpressions($query, $selected, '>=');
			case 'max':
				return $this->performExpressions($query, $selected, '<=');
		}
	}

	protected function keyedOptions(array $options)
	{
		$result = array();

		foreach ($options as $option)
		{
			$result[$option->getIdentifier()] = $option->setField($this);
		}

		return $result;
	}
}
