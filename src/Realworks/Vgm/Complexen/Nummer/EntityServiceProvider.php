<?php namespace Realworks\Vgm\Complexen\Nummer;

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

		if ( ! class_exists('Vgm\Nummer'))
		{
			class_alias('Realworks\Vgm\Complexen\Nummer\Facade', 'Vgm\Nummer');
		}
	}

	protected function registerProviders()
	{
		$this->instance->register(new CommonServiceProvider($this->instance, $this->app));
		$this->instance->register(new MediaServiceProvider($this->instance, $this->app));
		$this->instance->register(new XmlReaderServiceProvider($this->instance, $this->app));
		$this->instance->register(new ChildServiceProvider($this->instance, $this->app))->called('nummers');

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
