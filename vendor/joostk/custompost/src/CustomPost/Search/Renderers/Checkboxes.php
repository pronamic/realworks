<?php namespace CustomPost\Search\Renderers;

class Checkboxes extends InputList
{
	public function render()
	{
		parent::render();

		$this->renderPresenceValue();
	}

	protected function renderPresenceValue()
	{
		$attributes = $this->attributes(array(
			'type' => 'hidden',
			'name' => $this->getName(),
		), 'hidden');

		echo "<input{$attributes} />";
	}

	protected function getName()
	{
		return parent::getName() . '[]';
	}

	protected function getType()
	{
		return 'checkbox';
	}
}
