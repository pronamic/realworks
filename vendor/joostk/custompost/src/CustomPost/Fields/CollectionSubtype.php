<?php namespace CustomPost\Fields;

class CollectionSubtype extends Subtype
{
	protected $primaryField;

	public function getPrimaryField()
	{
		return $this->primaryField;
	}

	public function setPrimaryField(Field $primaryField)
	{
		$this->primaryField = $primaryField;

		return $this;
	}
}
