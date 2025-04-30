<?php namespace CustomPost\Search\Renderers;

use CustomPost\Search\Renderer;
use CustomPost\Search\Form;
use CustomPost\Search\Field;
use CustomPost\Search\Option;

class Radios extends InputList
{
	protected function getType()
	{
		return 'radio';
	}
}
