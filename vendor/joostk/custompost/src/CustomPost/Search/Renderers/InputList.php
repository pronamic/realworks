<?php namespace CustomPost\Search\Renderers;

use CustomPost\Search\Renderer;
use CustomPost\Search\Form;
use CustomPost\Search\Field;
use CustomPost\Search\Option;

abstract class InputList extends Renderer
{
	public function render()
	{
		foreach ($this->getVisibleOptions() as $option)
		{
			$selected = $this->form->isSelected($option);

			if ($this->isVisible($option, $selected))
			{
				$this->renderOption($option, $selected);
			}
		}
	}

	protected function renderOption(Option $option, $selected)
	{
		$this->openLabel($option);

		$this->renderInput($option, $selected);

		$this->renderLabel($option);

		$this->closeLabel();
	}

	protected function openLabel(Option $option)
	{
		$attributes = $this->attributes(array(
			'for' => $this->getIdentifier($option),
		), 'label', $option);

		echo "<label{$attributes}>";
	}

	protected function renderInput(Option $option, $selected)
	{
		$attributes = $this->attributes(array(
			'type' => $this->getType(),
			'name' => $this->getName(),
			'id' => $this->getIdentifier($option),
			'value' => $option->getValue(),
			'checked' => $selected,
		), 'input', $option);

		echo "<input{$attributes} /> ";
	}

	protected function getName()
	{
		return $this->field->getName();
	}

	abstract protected function getType();

	protected function renderLabel(Option $option)
	{
		echo $option->getLabel();

		if ($this->getOption('showCounts'))
		{
			$this->renderLabelCount($option);
		}
	}

	protected function renderLabelCount(Option $option)
	{
		$attributes = $this->attributes(array(), 'count');

		$count = $this->form->count($option);

		echo " <span{$attributes}>({$count})</span>";
	}

	protected function closeLabel()
	{
		echo '</label>';
	}

	protected function getIdentifier(Option $option)
	{
		return $this->field->getName().'-'.$option->getValue();
	}
}
