<?php namespace CustomPost\Search\Renderers;

use CustomPost\Search\Renderer;
use CustomPost\Search\Form;
use CustomPost\Search\Field;
use CustomPost\Search\Option;

class MaxDropdown extends Dropdown
{
	protected function isSelected(Option $option)
	{
		return $this->form->isSelected($option, 'max');
	}

	protected function isEmptySelected()
	{
		return $this->form->isValueSelected($this->field, '', 'max');
	}

	protected function openSelect()
	{
		$this->renderOpenSelect();

		if ( ! $this->getOption('showMaxEmptyAtBottom'))
		{
			$this->renderEmptyOption();
		}
	}

	protected function closeSelect()
	{
		if ($this->getOption('showMaxEmptyAtBottom'))
		{
			$this->renderEmptyOption();
		}

		$this->renderCloseSelect();
	}

	protected function getName()
	{
		return parent::getName() . '[max]';
	}

	protected function getIdentifier()
	{
		return parent::getIdentifier() . '-max';
	}

	protected function getEmptyLabel()
	{
		return $this->getOption('emptyLabelMax');
	}

	protected function count(Option $option)
	{
		return $this->form->count($option, 'max');
	}
}
