<?php namespace CustomPost\Search\Renderers;

use CustomPost\Search\Renderer;
use CustomPost\Search\Form;
use CustomPost\Search\Field;
use CustomPost\Search\Option;

class Hidden extends Renderer
{
	public function render()
	{
		$attributes = $this->attributes(array(
			'type' => 'hidden',
			'name' => $this->getName(),
			'id' => $this->getIdentifier(),
			'value' => $this->getValue(),
		), 'input');

		echo "<input{$attributes} />";
	}

	protected function getValue()
	{
		$selected = $this->form->getSelectedOptions($this->field);

		return key($selected) ?: '';
	}

	protected function getName()
	{
		return $this->field->getName();
	}

	protected function getIdentifier()
	{
		return $this->field->getName();
	}
}
