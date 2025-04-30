<?php namespace CustomPost\Search\Renderers;

use CustomPost\Search\Renderer;

class MinMaxDropdown extends Renderer
{
	public function render()
	{
		$name = $this->field->getName();

		$this->form->minDropdown($name, $this->options);
		$this->form->maxDropdown($name, $this->options);
	}
}
