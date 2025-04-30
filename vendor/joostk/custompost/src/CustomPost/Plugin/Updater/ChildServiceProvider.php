<?php namespace CustomPost\Plugin\Updater;

use CustomPost\ServiceProvider;

class ChildServiceProvider extends ServiceProvider
{
	public function boot()
	{
		$this->watchUpdates();

		$this->watchDeletions();

		$this->watchUnpublishing();
	}

	protected function watchUpdates()
	{
		list($app, $entity) = array($this->app, $this->entity);

		$entity->parent()->listen('reader.completed', function($model, $reader, $element) use ($app, $entity)
		{
			$updateChild = function($model) use ($app, $entity, $element)
			{
				$updater = $app->make('updater.entity.child', $entity);

				$updater->update($model, $element);
			};

			$model->listen('updated', $updateChild);
			$model->listen('unchanged', $updateChild);
		});
	}

	protected function watchDeletions()
	{
		$entity = $this->entity;

		$entity->parent()->listen('post.deleted', function($model) use ($entity)
		{
			$children = $entity->search()->where('user', $model->lastId())->all();

			foreach ($children as $child)
			{
				$child->delete();
			}
		});
	}

	protected function watchUnpublishing()
	{
		list($app, $entity) = array($this->app, $this->entity);

		$entity->parent()->listen('post.unpublished', function($model) use ($app, $entity)
		{
			$updater = $app->make('updater.entity.child', $entity);

			$children = $entity->search()->where('user', $model->id())->all();

			foreach ($children as $child)
			{
				$updater->unavailable($child);
			}
		});
	}

	public function called($relation)
	{
		$entity = $this->entity;

		$entity->parent()->resolving('fields.manager', function($manager) use ($entity, $relation)
		{
			$manager->macroUnless($relation, function($model) use ($entity)
			{
				return $entity->search()->where('user', $model->id())->all();
			});
		});

		return $this;
	}

	public function hasParent($relation)
	{
		$entity = $this->entity;

		$entity->resolving('fields.manager', function($manager) use ($entity, $relation)
		{
			$manager->macroUnless($relation, function($model) use ($entity)
			{
				return $entity->parent()->find($model->user->value());
			});
		});

		return $this;
	}
}
