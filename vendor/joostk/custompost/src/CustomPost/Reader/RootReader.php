<?php namespace CustomPost\Reader;

use CustomPost\Fields\Manager;
use CustomPost\Contracts\RepositoryInterface;

class RootReader extends AbstractReader
{
	protected $manager;

	protected $repository;

	protected $root;

	public function __construct(Manager $manager, RepositoryInterface $repository, $root)
	{
		$this->manager = $manager;
		$this->repository = $repository;
		$this->root = $root;
	}

	public function getFields()
	{
		return $this->manager->getFields();
	}

	public function read(Data $data)
	{
		$models = array();

		foreach ($this->entries($data) as $entry)
		{
			$model = $this->readEntry($entry);

			if ($model)
			{
				$this->fire('reader.completed', array($model, $this, $entry->raw()));

				if ( ! $this->skip) $models[] = $model;
			}
		}

		return $models;
	}

	protected function entries(Data $data)
	{
		return $data->entries($this->root);
	}

	protected function findPost(Data $data)
	{
		$primaryField = $this->manager->getPrimaryField();

		if ($primaryField and $primaryField->hasSelector())
		{
			$value = $data->get($primaryField, $this);

			$post = $this->repository->find($value, $primaryField->getName());

			if ($post) return $post;
		}

		return $this->repository->make();
	}
}
