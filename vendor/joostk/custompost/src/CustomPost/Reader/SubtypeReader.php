<?php namespace CustomPost\Reader;

use CustomPost\Fields\Subtype;

class SubtypeReader extends AbstractReader
{
	protected $subtype;

	protected $parent;

	public function __construct(Subtype $subtype, AbstractReader $parent)
	{
		$this->subtype = $subtype;
		$this->parent = $parent;
	}

	public function getFields()
	{
		return $this->subtype->getFields();
	}

	public function read(Data $data)
	{
		foreach ($this->entries($data) as $entry)
		{
			$model = $this->readEntry($entry);

			if ($model) return $model;
		}
	}

	protected function entries(Data $data)
	{
		return $data->entries($this->subtype->getSelector());
	}

	protected function findPost(Data $element)
	{
		return $this->parent->getCurrentPost()->get($this->subtype->getName());
	}
}
