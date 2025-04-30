<?php namespace CustomPost\Database;

use CustomPost\Fields\Subtype;
use CustomPost\Fields\CollectionSubtype;
use JoostK\Illuminate\Container\Container;
use CustomPost\Contracts\RepositoryInterface;

class DatabaseRepository implements RepositoryInterface
{
	protected $container;

	protected $cache = array();

	protected $activated;

	public function __construct(Container $container)
	{
		$this->container = $container;
	}

	public function make()
	{
		return $this->container->make('post');
	}

	public function activate(Model $model = null)
	{
		$this->activated = $model;
	}

	public function activated()
	{
		return $this->activated;
	}

	public function makeSubtype(Subtype $subtype)
	{
		$key = $subtype->getFullName('.');

		if ($this->container->bound("submodel: {$key}"))
		{
			return $this->container->make("submodel: {$key}", $subtype);
		}
		else
		{
			return $this->container->make('submodel', $subtype);
		}
	}

	public function makeCollectionSubtype(CollectionSubtype $subtype)
	{
		return $this->container->make('submodel.array', $subtype);
	}

	public function find($id, $key = null)
	{
		if ($key === null and isset($this->cache[$id]))
		{
			return $this->cache[$id];
		}
		else
		{
			return $this->cache($this->handleFind($this->make(), $id, $key));
		}
	}

	public function findSubtype(Subtype $subtype, $id)
	{
		return $this->handleFind($this->makeSubtype($subtype), $id);
	}

	public function findSubtypeCollection(CollectionSubtype $subtype, $id)
	{
		$models = array();

		foreach ($subtype->getTable()->all($id) as $data)
		{
			$models[] = $this->hydrate($this->makeSubtype($subtype), $data);
		}

		return $models;
	}

	protected function handleFind(Model $model, $id, $key = null)
	{
		$data = $model->getTable()->find($id, $key);

		if ($data) return $this->hydrate($model, $data);
	}

	protected function hydrate(Model $model, $data)
	{
		$model->setAttributes($data);
		$model->setExists(true);
		$model->syncOriginal();

		return $model;
	}

	public function cache(Model $model = null)
	{
		return $model ? $this->cache[$model->getPrimaryKey()] = $model : null;
	}

	public function flush()
	{
		$this->cache = array();

		return $this;
	}

	public function fetch(array $ids)
	{
		$table = $this->container['database.table'];

		$fetch = array_diff($ids, array_keys($this->cache));

		foreach ($table->find($fetch) as $data)
		{
			$this->cache($this->hydrate($this->make(), $data));
		}
	}
}
