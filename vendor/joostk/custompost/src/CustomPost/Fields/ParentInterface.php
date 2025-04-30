<?php namespace CustomPost\Fields;

use CustomPost\Fields\Types\ArrayField;

interface ParentInterface
{
	public function setPrimaryField(Field $field);

	public function getFullName($glue = null);

	public function getField($name);

	public function getFields();

	public function resolveSubtypeTable(Subtype $subtype);

	public function resolveArrayTable(ArrayField $field);

	public function getTable();
}
