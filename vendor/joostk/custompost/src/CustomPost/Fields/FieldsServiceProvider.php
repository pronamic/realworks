<?php namespace CustomPost\Fields;

use CustomPost\ServiceProvider;
use CustomPost\Database\ModelTable;
use CustomPost\Database\ArrayTable;
use CustomPost\Fields\Types\ArrayField;
use CustomPost\Database\ModelCollectionTable;

class FieldsServiceProvider extends ServiceProvider
{
	public function register()
	{
		$this->registerCache();

		$this->registerResolver();

		$this->registerManager();

		$this->watchManagerResolving();
	}

	protected function registerCache()
	{
		$this->entity['fields.cache'] = function($entity)
		{
			$path = $entity['config']['paths.storage'].'/cache/fields.dat';

			return new CacheStorage($path);
		};
	}

	protected function registerResolver()
	{
		$this->entity['fields.resolver'] = function($entity)
		{
			return new Resolver($entity, $entity['fields.cache']);
		};
	}

	protected function registerManager()
	{
		$me = $this;

		$this->entity->singleton('fields.manager', function($entity) use ($me)
		{
			$table = $entity['database.table'];

			$manager = new Manager($table, $entity['fields.resolver']->get());

			$table->setManager($manager);

			$manager->setSubtypeTableResolver(array($me, 'resolveSubtypeTable'));
			$manager->setArrayTableResolver(array($me, 'resolveArrayTable'));

			// Bind the instance in the container before initializing, that process is dependant on
			// the manager so it also resolves it from the container, leading to infinite recursion.
			$entity->instance('fields.manager', $manager);

			return $manager->initialize();
		});
	}

	protected function watchManagerResolving()
	{
		$this->entity->resolving('fields.manager', function($manager, $entity)
		{
			locate_template($entity['config']['paths.template'].'/functions.php', true);
		});
	}

	public function resolveSubtypeTable(Subtype $subtype)
	{
		$parent = $subtype->getParent()->getTable();
		$name = $this->getTableName($subtype);

		if ($subtype instanceof CollectionSubtype)
		{
			$table = new ModelCollectionTable($this->entity['db'], $name);
		}
		else
		{
			$table = new ModelTable($this->entity['db'], $name, 'parent_id');
		}

		return $table->setParent($parent->getName(), $parent->getPrimaryKey())->setManager($subtype);
	}

	public function resolveArrayTable(ArrayField $field)
	{
		$parent = $field->getParent()->getTable();
		$name = $this->getTableName($field);

		$table = new ArrayTable($this->entity['db'], $name);

		return $table->setParent($parent->getName(), $parent->getPrimaryKey());
	}

	protected function getTableName(BaseField $field)
	{
		$prefix = $this->entity['database.table']->getName();

		if (strlen($name = $prefix.'_'.$field->getFullName('_')) <= 64)
		{
			return $name;
		}

		return substr($prefix.'_'.sha1($field->getFullName('.')), 0, 64);
	}
}
