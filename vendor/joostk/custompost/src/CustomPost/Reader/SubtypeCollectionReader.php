<?php namespace CustomPost\Reader;

use CustomPost\Fields\Subtype;

class SubtypeCollectionReader extends SubtypeReader
{
	protected $collection;

	protected $id = 0;

	public function read(Data $data)
	{
		$this->collection = parent::findPost($data)->clear();

		foreach ($this->entries($data) as $entry)
		{
			$this->readEntry($entry);
		}

		return $this->collection;
	}

	protected function findPost(Data $data)
	{
		$primaryField = $this->collection->getSubtype()->getPrimaryField();

		if ($primaryField and $primaryField->hasSelector())
		{
			$id = $data->get($primaryField, $this);
		}
		else
		{
			$id = $this->id++;
		}

		return $this->collection->getModel($id);
	}
}
