<?php namespace CustomPost\Search\Renderers;

use CustomPost\Search\Renderer;
use CustomPost\Search\Form;
use CustomPost\Search\Field;
use CustomPost\Search\Option;

class Dropdown extends Renderer
{
	public function render()
	{
		$this->openSelect();

		foreach ($this->getVisibleOptions() as $option)
		{
			$selected = $this->isSelected($option);

			if ($this->isVisible($option, $selected))
			{
				$this->renderOption($option, $selected);
			}
		}

		$this->closeSelect();
	}

	protected function isSelected(Option $option)
	{
		return $this->form->isSelected($option);
	}

	protected function openSelect()
	{
		$this->renderOpenSelect();

		$this->renderEmptyOption();
	}

	protected function renderOpenSelect()
	{
		$attributes = $this->attributes(array(
			'name' => $this->getName(),
			'id' => $this->getIdentifier(),
		), 'select');

		echo "<select{$attributes}>";
	}

	protected function renderEmptyOption()
	{
		$attributes = $this->attributes(array(
			'value' => '',
			'selected' => $this->isEmptySelected(),
		), 'emptyOption');

		echo "<option{$attributes}>", $this->getEmptyLabel(), '</option>';
	}

	protected function renderOption(Option $option, $selected)
	{
		$attributes = $this->attributes(array(
			'value' => $option->getValue(),
			'selected' => $selected,
		), 'option', $option);

		echo "<option{$attributes}>", $this->renderLabel($option), '</option>';
	}

	protected function renderLabel(Option $option)
	{
		echo $this->escape($option->getLabel());

		if ($this->getOption('showCounts'))
		{
			$this->renderLabelCount($option);
		}
	}

	protected function renderLabelCount(Option $option)
	{
		$count = $this->count($option);

		echo " ({$count})";
	}

	protected function closeSelect()
	{
		$this->renderCloseSelect();
	}

	protected function renderCloseSelect()
	{
		echo "</select>";
	}

	protected function getName()
	{
		return $this->field->getName();
	}

	protected function getIdentifier()
	{
		return $this->field->getName();
	}

	protected function getEmptyLabel()
	{
		return $this->getOption('emptyLabel');
	}
}
