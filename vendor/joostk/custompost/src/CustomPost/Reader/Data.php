<?php namespace CustomPost\Reader;

use CustomPost\Fields\Field;

interface Data
{
	public function raw();

	/**
	 * @param $selector string
	 * @return array[Data]
	 */
	public function entries($selector);

	/**
	 * @return mixed
	 */
	public function get(Field $field, AbstractReader $reader);
}
