<?php namespace CustomPost\Database;

use Exception;
use ArrayAccess;
use RuntimeException;
use CustomPost\Utils;
use CustomPost\Entity;
use CustomPost\Fields\Field;
use InvalidArgumentException;
use CustomPost\Fields\Subtype;
use CustomPost\Formatter\Listing;
use CustomPost\Formatter\Formatter;
use CustomPost\Support\StringCollection;
use CustomPost\Fields\CollectionSubtype;
use CustomPost\Fields\Types\ArrayField;
use CustomPost\Contracts\DatabaseInterface;
use CustomPost\Contracts\RepositoryInterface;

abstract class Model implements ModelInterface, ArrayAccess
{
	/**
	 * Table instance
	 *
	 * @var \CustomPost\Table
	 */
	protected $table;

	/**
	 * Repository to load relations from
	 *
	 * @var \CustomPost\Contracts\RepositoryInterface
	 */
	protected $repository;

	/**
	 * Whether the model already exists in the database or not
	 *
	 * @var bool
	 */
	protected $exists = false;

	/**
	 * This model's data
	 *
	 * @var array
	 */
	protected $attributes = array();

	/**
	 * This model's data, original values.
	 *
	 * @var array
	 */
	protected $original = array();

	/**
	 * This model's array attributes
	 *
	 * @var array
	 */
	protected $arrays = array();

	/**
	 * This model's submodels
	 *
	 * @var array
	 */
	protected $submodels = array();

	protected $rootModel;

	protected $parentModel;

	protected $lastId;

	public function __construct(RepositoryInterface $repository, Table $table)
	{
		$this->repository = $repository;
		$this->table = $table;
	}

	abstract protected function getField($key);

	abstract protected function getFields();

	public function getTable()
	{
		return $this->table;
	}

	public function setHierarchy(ModelInterface $root, ModelInterface $parent)
	{
		$this->rootModel = $root;
		$this->parentModel = $parent;

		return $this;
	}

	public function getRootModel()
	{
		return $this->rootModel;
	}

	public function getParentModel()
	{
		return $this->parentModel;
	}

	public function getPrimaryKey()
	{
		$key = $this->table->getPrimaryKey();

		return isset($this->attributes[$key]) ? $this->attributes[$key] : null;
	}

	public function setPrimaryKey($id)
	{
		$key = $this->table->getPrimaryKey();

		$this->lastId = $this->getPrimaryKey();

		return $this->attributes[$key] = $id;
	}

	public function getReferenceKey()
	{
		$key = $this->table->getReferenceKey();

		return isset($this->attributes[$key]) ? $this->attributes[$key] : null;
	}

	public function setReferenceKey($id)
	{
		$key = $this->table->getReferenceKey();

		return $this->attributes[$key] = $id;
	}

	public function setExists($exists)
	{
		$this->exists = $exists;

		return $this;
	}

	public function exists()
	{
		return $this->exists;
	}

	public function lastId()
	{
		return $this->getPrimaryKey() ?: $this->lastId;
	}

	public function getAttributes()
	{
		return $this->attributes;
	}

	public function setAttributes(array $attributes)
	{
		foreach ($attributes as $key => $value)
		{
			$this->set($key, $value);
		}

		return $this;
	}

	public function listing($fields)
	{
		$fields = is_array($fields) ? $fields : func_get_args();

		$formatters = array();
		$labels = array();

		foreach ($fields as $key => $value)
		{
			if (is_string($key))
			{
				$formatters[] = $this->traverse($key);
				$labels[$key] = $value;
			}
			else
			{
				$formatters[] = $this->traverse($value);
			}
		}

		return $this->newListing($formatters, $labels);
	}

	protected function newListing($formatters, $labels)
	{
		return new Listing($formatters, $labels);
	}

	public function clear()
	{
		$this->attributes = array(
			$this->table->getPrimaryKey() => $this->getPrimaryKey(),
			$this->table->getReferenceKey() => $this->getReferenceKey(),
		);

		return $this;
	}

	public function clearSubmodels()
	{
		foreach ($this->loadSubmodels() as $submodel)
		{
			$submodel->clear();
		}

		return $this;
	}

	public function set($key, $value)
	{
		$field = $this->getField($key);

		if ($field instanceof Subtype)
		{
			$this->setSubtype($field, $value);
		}
		elseif ($field instanceof ArrayField)
		{
			$this->setArrayValue($field, $value);
		}
		elseif ($field)
		{
			$this->setFieldValue($field, $value);
		}
		else
		{
			$this->setKeyValue($key, $value);
		}
	}

	protected function setKeyValue($key, $value)
	{
		if ($key === $this->table->getPrimaryKey())
		{
			$this->setPrimaryKey($value);
		}
		else if ($key === $this->table->getReferenceKey())
		{
			$this->setReferenceKey($value);
		}
		else
		{
			throw new InvalidArgumentException("Field with name [{$key}] does not exist.");
		}
	}

	protected function setFieldValue(Field $field, $value)
	{
		$name = $field->getName();

		try
		{
			return $this->attributes[$name] = $this->transform($field, $value);
		}
		catch (Exception $e)
		{
			throw new RuntimeException("Failed to set value [{$value}] in field [{$field->getFullName('.')}]: {$e->getMessage()}", 0, $e);
		}
	}

	protected function setArrayValue(ArrayField $field, $value)
	{
		$name = $field->getName();

		$this->getArrayValue($field);

		return $this->arrays[$name] = StringCollection::make($this->transform($field, $value));
	}

	protected function setSubtype(Subtype $subtype, array $values = null)
	{
		$submodel = $this->getSubtype($subtype);

		if ($values !== null)
		{
			$submodel->setAttributes($values);
		}
		else
		{
			$submodel->clear()->clearSubmodels();
		}
	}

	public function traverse($key)
	{
		$model = $this;

		foreach (explode('.', $key) as $segment)
		{
			if ( ! $model instanceof Model)
			{
				throw new InvalidArgumentException("Could not access field [{$segment}] of child type.");
			}

			$model = $model->get($segment);
		}

		return $model;
	}

	public function get($key)
	{
		$field = $this->getField($key);

		if ($field instanceof Subtype)
		{
			return $this->getSubtype($field);
		}
		elseif ($field instanceof ArrayField)
		{
			return $this->getArrayValue($field);
		}
		elseif ($field)
		{
			return $this->getFieldValue($field);
		}
		else
		{
			return $this->getKeyValue($key);
		}
	}

	protected function getKeyValue($key)
	{
		if ($key === $this->table->getPrimaryKey())
		{
			return $this->getPrimaryKey();
		}
		else if ($key === $this->table->getReferenceKey())
		{
			return $this->getReferenceKey();
		}
		else
		{
			throw new InvalidArgumentException("Field with name [{$key}] does not exist.");
		}
	}

	protected function getFieldValue(Field $field)
	{
		$name = $field->getName();

		$value = isset($this->attributes[$name]) ? $this->attributes[$name] : null;
		$original = isset($this->original[$name]) ? $this->original[$name] : null;

		return $field->newFormatter()->setModel($this->rootModel)->setValue($value)->setOriginal($original);
	}

	protected function getArrayValue(ArrayField $field)
	{
		$name = $field->getName();

		if ( ! isset($this->arrays[$name]))
		{
			$array = $this->exists ? $field->getTable()->all($this->getPrimaryKey()) : array();

			$this->arrays[$name] = $this->newCollection($array);
			$this->original[$name] = $this->newCollection($array);
		}

		$original = isset($this->original[$name]) ? $this->original[$name] : $this->newCollection(null);

		return $field->newFormatter()->setModel($this->rootModel)->setValue($this->arrays[$name])->setOriginal($original);
	}

	protected function newCollection($array)
	{
		return StringCollection::make($array);
	}

	protected function makeSubtype(Subtype $subtype)
	{
		if ($subtype instanceof CollectionSubtype)
		{
			$model = $this->repository->makeCollectionSubtype($subtype);

			$model->setReferenceKey($this->getPrimaryKey());
		}
		else
		{
			$model = $this->exists ? $this->repository->findSubtype($subtype, $this->getPrimaryKey()) : null;

			$model = $model ?: $this->repository->makeSubtype($subtype);
		}

		return $model->setHierarchy($this->rootModel, $this);
	}

	protected function getSubtype(Subtype $subtype)
	{
		$name = $subtype->getName();

		if ( ! isset($this->submodels[$name]))
		{
			return $this->submodels[$name] = $this->makeSubtype($subtype);
		}

		return $this->submodels[$name];
	}

	protected function loadSubmodels()
	{
		foreach ($this->getFields() as $field)
		{
			if ($field instanceof Subtype) $this->getSubtype($field);
		}

		return $this->submodels;
	}

	protected function loadArrayFields()
	{
		foreach ($this->getFields() as $field)
		{
			if ($field instanceof ArrayField) $this->getArrayValue($field);
		}

		return $this->arrays;
	}

	protected function transform(Field $field, $value)
	{
		return $value === null ? null : $field->transform($value);
	}

	public function isDirty($name = null)
	{
		if ($name === null) return $this->isModelDirty();

		$value = $this->get($name);

		if ($value instanceof Formatter)
		{
			$value = $value->toDefault();
		}

		return $value->isDirty();
	}

	public function isModelDirty()
	{
		foreach ($this->getFields() as $name => $field)
		{
			if ($this->isDirty($name)) return true;
		}

		return false;
	}

	public function getDirty()
	{
		$dirty = array();

		foreach ($this->getFields() as $name => $field)
		{
			$field = $this->get($name);

			if ($field->isDirty()) $dirty[$name] = $field->getDirty();
		}

		return $dirty;
	}

	public function getData()
	{
		$data = array();

		foreach ($this->getFields() as $name => $field)
		{
			$data[$name] = $this->get($name)->getData();
		}

		return $data;
	}

	public function syncOriginal()
	{
		$this->original = $this->attributes;

		foreach ($this->arrays as $key => $array)
		{
			$this->original[$key] = $this->newCollection($array->all());
		}

		return $this;
	}

	public function delete()
	{
		return $this->deleteRow();
	}

	public function deleteRow()
	{
		if ($this->exists())
		{
			$this->table->delete($this->getPrimaryKey());

			$this->deleteArrayFields();
			$this->deleteSubmodels();

			$this->setPrimaryKey(null);
			$this->exists = false;
		}
	}

	public function deleteArrayFields()
	{
		foreach ($this->getFields() as $field)
		{
			if ($field instanceof ArrayField)
			{
				$field->getTable()->delete($this->getPrimaryKey());
			}
		}
	}

	public function deleteSubmodels()
	{
		foreach ($this->loadSubmodels() as $submodel)
		{
			$submodel->delete();
		}

		$this->submodels = array();
	}

	public function save()
	{
		$this->beforeSave();

		try
		{
			$result = $this->attemptSave();
		}
		catch (Exception $e)
		{
			$this->rollback($e);

			throw $e;
		}

		$this->afterSave($result);

		$this->exists = ($this->exists or $result);

		return $result;
	}

	protected function attemptSave()
	{
		$this->table->sync($this);

		$result = $this->saveAttributes() !== false;

		if ($result)
		{
			$this->syncOriginal();
			$this->saveArrayValues();
			$this->saveSubmodels();
		}

		return $result;
	}

	protected function saveAttributes()
	{
		if ($this->exists())
		{
			$result = $this->table->update($this->attributes, $this->getPrimaryKey());
		}
		else
		{
			$result = $this->table->insert($this->attributes);

			$this->handlePrimaryKey();
		}

		return $result;
	}

	protected function handlePrimaryKey()
	{
		if ($this->table->getPrimaryKey() !== $this->table->getReferenceKey())
		{
			$id = $this->table->lastInsertId();

			$this->setPrimaryKey($id);
		}
	}

	protected function saveArrayValues()
	{
		foreach ($this->arrays as $name => $values)
		{
			$table = $this->getField($name)->getTable();

			$table->updateValues($this->getPrimaryKey(), $values->toArray());
		}
	}

	protected function saveSubmodels()
	{
		foreach ($this->submodels as $submodel)
		{
			$submodel->setReferenceKey($this->getPrimaryKey());

			$submodel->save();
		}
	}

	protected function beforeSave()
	{
		// Override to perform additional logic
	}

	protected function afterSave($result)
	{
		// Override to perform additional logic
	}

	protected function rollback(Exception $exception)
	{
		// Override to perform additional logic
	}

	public function hasData()
	{
		return $this->hasAttributes() or $this->hasSubmodels() or $this->hasArrayValues();
	}

	public function hasAttributes()
	{
		$data = $this->attributes;

		unset(
			$data[$this->table->getPrimaryKey()],
			$data[$this->table->getReferenceKey()]
		);

		return count(array_filter($data, function($attribute)
		{
			return $attribute !== null;
		})) > 0;
	}

	public function hasSubmodels()
	{
		foreach ($this->loadSubmodels() as $submodel)
		{
			if ($submodel->hasData()) return true;
		}

		return false;
	}

	public function hasArrayValues()
	{
		foreach ($this->loadArrayFields() as $array)
		{
			if (count($array) > 0) return true;
		}

		return false;
	}

	public function offsetSet($key, $value)
	{
		$this->set($key, $value);
	}

	public function offsetGet($key)
	{
		return $this->get($key);
	}

	public function offsetExists($key)
	{
		$value = $this->get($key);

		if ($value instanceof Formatter)
		{
			return ! $value->isEmpty();
		}
		elseif ($value instanceof Model)
		{
			return $value->hasData();
		}
		else
		{
			return $value !== null;
		}
	}

	public function offsetUnset($key)
	{
		unset($this->attributes[$key]);
		unset($this->arrays[$key]);

		if (isset($this->submodels[$key]))
		{
			$this->submodels[$key]->clear();
		}
	}

	public function __set($key, $value)
	{
		return $this->set($key, $value);
	}

	public function __get($key)
	{
		return $this->get($key);
	}

	public function __isset($key)
	{
		return $this->offsetExists($key);
	}

	public function __unset($key)
	{
		$this->offsetUnset($key);
	}
}
