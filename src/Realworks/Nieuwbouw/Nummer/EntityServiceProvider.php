<?php namespace Realworks\Nieuwbouw\Nummer;

use CustomPost\ServiceProvider;
use Realworks\Common\CommonServiceProvider;
use CustomPost\Plugin\Updater\ChildServiceProvider;
use CustomPost\Reader\Xml\XmlReaderServiceProvider;

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

		$this->entity->watchFieldsUpdates(__DIR__);
	}

	protected function registerFacade()
	{
		Facade::setContainer($this->instance);

		if ( ! class_exists('Nieuwbouw\Nummer'))
		{
			class_alias('Realworks\Nieuwbouw\Nummer\Facade', 'Nieuwbouw\Nummer');
		}
	}

	protected function registerProviders()
	{
		$this->instance->register(new CommonServiceProvider($this->instance, $this->app));
		$this->instance->register(new MediaServiceProvider($this->instance, $this->app));
		$this->instance->register(new XmlReaderServiceProvider($this->instance, $this->app));
		$this->instance->register(new ChildServiceProvider($this->instance, $this->app))->called('nummers')->hasParent('type');

		// $this->entity->singleton('updater.dirtycheck', function()
		// {
		// 	return new DateDirtyCheckStrategy;
		// });
	}

	public function boot()
	{
		$this->entity['migrator']->run(new \Realworks\Common\PrimaryKeyMigrator);
	}
}
