<?php namespace CustomPost\Plugin\Geo;

use CustomPost\Fields\Field;
use CustomPost\ServiceProvider;

abstract class GeoServiceProvider extends ServiceProvider
{
	public function register()
	{
		$this->registerCoordinateUpdater();

		$this->registerMaps();

		$this->registerMacros();

		$this->addFields();
	}

	protected function registerCoordinateUpdater()
	{
		$this->app->bindIf('updater.coordinates', function($app, $logger = null)
		{
			return new CoordinatesUpdater($app['coordinates'], $logger);
		});
	}

	protected function registerMaps()
	{
		$this->entity->singleton('maps', function($entity)
		{
			return new Maps($entity);
		});
	}

	protected function registerMacros()
	{
		$this->entity->resolving('fields.manager', function($manager, $entity)
		{
			$manager->macroUnless('hasLocation', function($post)
			{
				return $post->latitude->hasValue() and $post->longitude->hasValue();
			});

			$manager->macroUnless('nearby', function($post, $radius) use ($entity)
			{
				return $entity->search()->where(new RadiusExpression($radius, $post->latitude->value(), $post->longitude->value()));
			});

			$manager->macroUnless('map', function($post) use ($entity)
			{
				if ($post->hasLocation) return array(
					'latitude' => $post->latitude->value(),
					'longitude' => $post->longitude->value(),
					'template' => $entity->template('map-info')->cache()->render(),
				);
			});
		});
	}

	protected function addFields()
	{
		$me = $this;

		$this->entity->filter('fields.augment', function(array $fields) use ($me)
		{
			return array_merge($fields, $me->additionalFields(), array(
				$me->modifyLatitude(Field::double('latitude')->formatter('string')),
				$me->modifyLongitude(Field::double('longitude')->formatter('string')),
				$me->modifyRadius(Field::virtual('radius', 'CustomPost\Plugin\Geo\RadiusField')),
			));
		});
	}

	public function additionalFields()
	{
		return array();
	}

	public function modifyLatitude(Field $field)
	{
		return $field;
	}

	public function modifyLongitude(Field $field)
	{
		return $field;
	}

	public function modifyRadius(Field $field)
	{
		return $field;
	}

	public function boot()
	{
		$this->registerLiveSearchData();

		$this->registerRadiusData();

		$this->setCoordinatesResolver();

		$this->registerEventListener();
	}

	protected function registerLiveSearchData()
	{
		$entity = $this->entity;

		$entity->filter('search.live.data', function($data, $live) use ($entity)
		{
			return with(new LiveSearchData($entity, $live))->augment($data);
		});
	}

	protected function registerRadiusData()
	{
		$this->entity->listen('post.loaded', function($model, $post)
		{
			$model->set('radius', property_exists($post, 'radius') ? $post->radius : null);
		});
	}

	protected function setCoordinatesResolver()
	{
		$app = $this->app;

		RadiusField::setCoordinatesFetcherResolver(function() use ($app)
		{
			return $app['coordinates'];
		});
	}

	protected function registerEventListener()
	{
		$me = $this;

		$this->entity->listen('post.save', function($model) use ($me)
		{
			$me->updateModel($model);
		});

		$this->entity->listen('post.updated', function($model) use ($me)
		{
			if ($me->updateModel($model)) $model->save();
		});
	}

	public function updateModel($model)
	{
		if ( ! $this->entity->config('coordinates')) return;

		$empty = $model->latitude->isEmpty() or $model->longitude->isEmpty();

		if ($empty or $this->requiresUpdate($model))
		{
			$me = $this;

			return $this->resolveUpdater()->update($model, $this->address($model), function($model, array $data) use ($me)
			{
				$me->assignGeoData($model, $data);
			});
		}
	}

	public function assignGeoData($model, array $data)
	{
		return array();
	}

	protected function resolveUpdater()
	{
		$logger = $this->entity['updater.logger'];

		return $this->app->make('updater.coordinates', $logger);
	}

	abstract protected function requiresUpdate($model);

	abstract protected function address($model);
}
