<?php namespace CustomPost\Search\Renderers;

use CustomPost\Search\Renderer;
use CustomPost\Search\Form;
use CustomPost\Search\Field;
use CustomPost\Search\Option;

class MinDropdown extends Dropdown
{
	protected function isSelected(Option $option)
	{
		return $this->form->isSelected($option, 'min');
	}

	protected function isEmptySelected()
	{
		return $this->form->isValueSelected($this->field, '', 'min');
	}

	protected function getName()
	{
		return parent::getName() . '[min]';
	}

	protected function getIdentifier()
	{
		return parent::getIdentifier() . '-min';
	}

	protected function getEmptyLabel()
	{
		return $this->getOption('emptyLabelMin');
	}

	protected function count(Option $option)
	{
		return $this->form->count($option, 'min');
	}
}
