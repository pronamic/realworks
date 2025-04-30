<?php namespace CustomPost\Database;

use CustomPost\Fields\Subtype;
use CustomPost\Contracts\RepositoryInterface;

class Submodel extends Model
{
	protected $subtype;

	public function __construct(RepositoryInterface $repository, Subtype $subtype)
	{
		$this->subtype = $subtype;

		parent::__construct($repository, $subtype->getTable());
	}

	protected function getField($key)
	{
		return $this->subtype->getField($key);
	}

	protected function getFields()
	{
		return $this->subtype->getFields();
	}

	public function save()
	{
		if ($this->hasData())
		{
			parent::save();
		}
		else
		{
			$this->deleteRow();
		}
	}
}
