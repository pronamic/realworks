<?php namespace CustomPost\Database;

use CustomPost\Entity;
use CustomPost\Fields\Subtype;
use CustomPost\ServiceProvider;
use CustomPost\Fields\CollectionSubtype;

class DatabaseServiceProvider extends ServiceProvider
{
	public function register()
	{
		$this->registerPost();

		$this->registerSubmodel();

		$this->registerSubmodelCollection();

		$this->registerDatabase();

		$this->registerTable();

		$this->registerRepository();

		$this->registerMigrator();
	}

	protected function registerPost()
	{
		$this->entity['post.class'] = 'CustomPost\Database\Post';

		$this->entity['post'] = function($entity)
		{
			$class = $entity['post.class'];

			$post = new $class($entity['repository'], $entity['fields.manager'], $entity['config']['post']);

			return $post->setEvents($entity['events']);
		};
	}

	protected function registerSubmodel()
	{
		$this->entity['submodel'] = function($entity, Subtype $subtype)
		{
			return new Submodel($entity['repository'], $subtype);
		};
	}

	protected function registerSubmodelCollection()
	{
		$this->entity['submodel.array'] = function($entity, CollectionSubtype $subtype)
		{
			return new SubmodelCollection($entity['repository'], $subtype);
		};
	}

	protected function registerDatabase()
	{
		$this->entity->singleton('db', function()
		{
			return new WordpressDatabase($GLOBALS['wpdb']);
		});
	}

	protected function registerTable()
	{
		$this->entity->singleton('database.table', function($entity)
		{
			$name = $entity['db']->getTablePrefix() . $entity['config']['table.name'];

			$table = new ModelTable($entity['db'], $name, 'wordpress_id');

			return $table->setParent($entity['db']->getTableName('posts'), 'ID');
		});
	}

	protected function registerRepository()
	{
		$this->entity->singleton('repository', function($entity)
		{
			return new DatabaseRepository($entity);
		});
	}

	protected function registerMigrator()
	{
		$this->entity->singleton('migrator', function($entity)
		{
			return new Migrator($entity);
		});
	}

	public function boot()
	{
		$this->activateSavedModels();
	}

	protected function activateSavedModels()
	{
		$entity = $this->entity;

		$entity['events']->listen('post.save', function($post) use ($entity)
		{
			$entity['repository']->activate($post);
		});

		$entity['events']->listen('post.saved', function($post) use ($entity)
		{
			$entity['repository']->activate(null);
		});
	}
}
