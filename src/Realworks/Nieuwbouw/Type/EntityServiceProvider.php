<?php namespace Realworks\Nieuwbouw\Type;

use CustomPost\ServiceProvider;
use CustomPost\Plugin\Updater\ChildServiceProvider;
use CustomPost\Reader\Xml\XmlReaderServiceProvider;
use Realworks\Nieuwbouw\Nummer\EntityServiceProvider as NummerServiceProvider;
use Realworks\Nieuwbouw\Project\EntityServiceProvider as ProjectServiceProvider;

class EntityServiceProvider extends ServiceProvider
{
	protected $instance;

	public function register()
	{
		$this->instance = new Entity;
		$this->instance->start();

		$this->app->addEntity($this->instance);
		$this->entity->addChild($this->instance);

		$this->registerFacade();

		$this->registerProviders();

		$this->registerPostClass();

		$this->entity->watchFieldsUpdates(__DIR__);
	}

	protected function registerFacade()
	{
		Facade::setContainer($this->instance);

		if ( ! class_exists('Nieuwbouw\Type'))
		{
			class_alias('Realworks\Nieuwbouw\Type\Facade', 'Nieuwbouw\Type');
		}
	}

	protected function registerProviders()
	{
		$this->app->register(new NummerServiceProvider($this->instance, $this->app));

		$this->instance->register(new AdditionalFieldsProvider($this->instance, $this->app));
		$this->instance->register(new MediaServiceProvider($this->instance, $this->app));
		$this->instance->register(new XmlReaderServiceProvider($this->instance, $this->app));
		$this->instance->register(new ChildServiceProvider($this->instance, $this->app))->called('types')->hasParent('project');

		// $this->entity->singleton('updater.dirtycheck', function()
		// {
		// 	return new DateDirtyCheckStrategy;
		// });
	}

	protected function registerPostClass()
	{
		$this->instance['post.class'] = 'Realworks\Common\Post';
	}

	public function boot()
	{
		$this->entity['migrator']->run(new \Realworks\Common\PrimaryKeyMigrator);
	}
}
