<?php namespace CustomPost\Database;

use Countable;
use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;
use JoostK\Illuminate\Support\Collection;
use CustomPost\Fields\CollectionSubtype;
use CustomPost\Contracts\RepositoryInterface;

class SubmodelCollection implements ModelInterface, ArrayAccess, Countable, IteratorAggregate
{
	protected $repository;

	protected $subtype;

	protected $loaded;

	protected $referenceKey;

	protected $rootModel;

	protected $parentModel;

	protected $items = array();

	public function __construct(RepositoryInterface $repository, CollectionSubtype $subtype)
	{
		$this->repository = $repository;
		$this->subtype = $subtype;
	}

	public function collection()
	{
		return new Collection($this->items);
	}

	public function getReferenceKey()
	{
		return $this->referenceKey;
	}

	public function getSubtype()
	{
		return $this->subtype;
	}

	public function getTable()
	{
		return $this->subtype->getTable();
	}

	public function getRootModel()
	{
		return $this->rootModel;
	}

	public function getParentModel()
	{
		return $this->parentModel;
	}

	public function setHierarchy(ModelInterface $root, ModelInterface $parent)
	{
		$this->rootModel = $root;
		$this->parentModel = $parent;

		$this->applyHierarchy();

		return $this;
	}

	protected function applyHierarchy()
	{
		foreach ($this->items as $model)
		{
			$model->setHierarchy($this->rootModel, $this->parentModel);
		}
	}

	public function save()
	{
		foreach ($this->items as $model)
		{
			$model->save();
		}

		$this->deleteUnavailableModels();

		$this->loaded = $this->items;
	}

	protected function deleteUnavailableModels()
	{
		$deleted = array_diff_key($this->loaded, $this->items);

		foreach ($deleted as $model)
		{
			$model->delete();
		}
	}

	public function setExists($exists)
	{
		foreach ($this->items as $model)
		{
			$model->setExists($exists);
		}

		return $this;
	}

	public function syncOriginal()
	{
		$this->loaded = $this->items;

		foreach ($this->items as $model)
		{
			$model->syncOriginal();
		}

		return $this;
	}

	public function delete()
	{
		foreach ($this->items as $model)
		{
			$model->delete();
		}
	}

	public function deleteRow()
	{
		foreach ($this->items as $model)
		{
			$model->deleteRow();
		}
	}

	public function clear()
	{
		$this->items = array();

		return $this;
	}

	public function clearSubmodels()
	{
		foreach ($this->items as $model)
		{
			$model->clearSubmodels();
		}

		return $this;
	}

	protected function loadModels()
	{
		if ($this->loaded === null)
		{
			$this->items = $this->loaded = $this->keyedModels(
				$this->repository->findSubtypeCollection($this->subtype, $this->referenceKey)
			);

			if ($this->rootModel) $this->applyHierarchy();
		}

		return $this->items;
	}

	protected function keyedModels(array $models)
	{
		$primaryField = $this->subtype->getPrimaryField();

		if ( ! $primaryField) return $models;

		$keyed = array();

		foreach ($models as $model)
		{
			$key = $model->get($primaryField->getName())->value();

			$keyed[$key] = $model;
		}

		return $keyed;
	}

	public function getModel($key)
	{
		if ( ! isset($this->items[$key]))
		{
			$this->items[$key] = $this->getLoadedModel($key) ?: $this->getNewModel();
		}

		return $this->items[$key];
	}

	protected function getLoadedModel($key)
	{
		return isset($this->loaded[$key]) ? $this->loaded[$key] : null;
	}

	protected function getNewModel()
	{
		$model = $this->repository->makeSubtype($this->subtype);

		$model->setHierarchy($this->rootModel, $this->parentModel);

		return $model;
	}

	public function setAttributes(array $values)
	{
		$models = array();

		foreach ($values as $key => $attributes)
		{
			$key = $this->transformKey($key, $attributes);

			$models[$key] = $this->getModel($key)->setAttributes($attributes);
		}

		$this->items = $models;
	}

	protected function transformKey($key, $attributes)
	{
		$primaryField = $this->subtype->getPrimaryField();

		return $primaryField ? array_get($attributes, $primaryField->getName(), $key) : $key;
	}

	public function set($key, $attributes)
	{
		$key = $this->transformKey($key, $attributes);

		$this->items[$key] = $this->getModel($key)->setAttributes($attributes);

		return $this;
	}

	public function setReferenceKey($id)
	{
		$this->referenceKey = $id;

		$this->loadModels();

		foreach ($this->items as $model)
		{
			$model->setReferenceKey($id);
		}

		return $id;
	}

	public function hasData()
	{
		foreach ($this->items as $model)
		{
			if ($model->hasData()) return true;
		}

		return false;
	}

	public function isDirty()
	{
		$deleted = array_diff_key($this->loaded, $this->items);

		if ($deleted) return true;

		foreach ($this->items as $model)
		{
			if ($model->isDirty()) return true;
		}

		return false;
	}

	public function getDirty()
	{
		$dirty = array();

		foreach ($this->items as $key => $model)
		{
			if ($model->isDirty()) $dirty[$key] = $model->getDirty();
		}

		return $dirty;
	}

	public function getData()
	{
		return array_values(array_map(function($model)
		{
			return $model->getData();
		}, $this->items));
	}

	/**
	 * Get an iterator for the items.
	 *
	 * @return ArrayIterator
	 */
	public function getIterator()
	{
		return new ArrayIterator($this->items);
	}

	/**
	 * Count the number of items in the collection.
	 *
	 * @return int
	 */
	public function count()
	{
		return count($this->items);
	}

	/**
	 * Determine if an item exists at an offset.
	 *
	 * @param  mixed  $key
	 * @return bool
	 */
	public function offsetExists($key)
	{
		return array_key_exists($key, $this->items);
	}

	/**
	 * Get an item at a given offset.
	 *
	 * @param  mixed  $key
	 * @return mixed
	 */
	public function offsetGet($key)
	{
		return $this->items[$key];
	}

	/**
	 * Set the item at a given offset.
	 *
	 * @param  mixed  $key
	 * @param  mixed  $value
	 * @return void
	 */
	public function offsetSet($key, $value)
	{
		if (is_null($key))
		{
			$this->items[] = $value;
		}
		else
		{
			$this->items[$key] = $value;
		}
	}

	/**
	 * Unset the item at a given offset.
	 *
	 * @param  string  $key
	 * @return void
	 */
	public function offsetUnset($key)
	{
		unset($this->items[$key]);
	}
}
