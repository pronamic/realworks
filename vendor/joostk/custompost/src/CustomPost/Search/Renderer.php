<?php namespace CustomPost\Search;

abstract class Renderer
{
	protected $form;

	protected $field;

	protected $options;

	public function __construct(array $options = array())
	{
		$this->options = $options;
	}

	public function setForm(Form $form)
	{
		$this->form = $form;

		return $this;
	}

	public function setField(Field $field)
	{
		$this->field = $field;

		return $this;
	}

	public function setOption($key, $value)
	{
		$this->options[$key] = $value;

		return $this;
	}

	public function getOption($key, $default = null)
	{
		return isset($this->options[$key]) ? $this->options[$key] : $default;
	}

	protected function getVisibleOptions()
	{
		return $this->form->getVisibleOptions($this->field);
	}

	protected function isEmptySelected()
	{
		return $this->form->isValueSelected($this->field, '');
	}

	protected function isVisible(Option $option, $selected)
	{
		if ($selected) return true;

		if ($this->getOption('hideWhenNoResults') and $this->count($option) == 0)
		{
			return false;
		}
		else if ($this->getOption('hideWhenNotExists') and ! $this->form->exists($option))
		{
			return false;
		}

		return true;
	}

	protected function attributes(array $attributes, $mergeOption = null, Option $option = null)
	{
		if ($mergeOption)
		{
			$attributes = array_merge($attributes, $this->getMergeOptions($mergeOption, $option));
		}

		$html = null;

		foreach ((array) $attributes as $key => $value)
		{
			if ($value === true) $value = $key;

			if (is_numeric($key)) $key = $value;

			if ($value or is_string($value)) $html .= ' '.$key.'="'.$this->escape($value).'"';
		}

		return $html;
	}

	protected function getMergeOptions($mergeOption, Option $option = null)
	{
		$attributes = (array) $this->getOption($mergeOption);

		if ($option)
		{
			$this->replace($attributes, '{{value}}', $option->getValue());
			$this->replace($attributes, '{{label}}', $option->getLabel());
			$this->replace($attributes, '{{count}}', $this->getOption('showCounts') ? $this->count($option) : '');
		}

		return $attributes;
	}

	protected function replace(array &$attributes, $search, $replace)
	{
		foreach ($attributes as &$value)
		{
			if (is_array($value)) $this->replace($value, $search, $replace);
			else $value = str_replace($search, $replace, $value);
		}
	}

	protected function count(Option $option)
	{
		return $this->form->count($option);
	}

	protected function escape($text)
	{
		return htmlentities($text, ENT_QUOTES, 'UTF-8', false);
	}

	abstract public function render();
}
