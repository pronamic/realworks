<?php namespace Realworks\Wonen;

use Realworks\Common\CommonServiceProvider;
use JoostK\Wordpress\Support\ServiceProvider;
use CustomPost\Reader\Xml\XmlReaderServiceProvider;

class EntityServiceProvider extends ServiceProvider
{
	protected $entity;

	public function register()
	{
		$this->entity = new Entity;
		$this->entity->start();

		$this->app->addEntity($this->entity);

		$this->registerFacade();

		$this->registerProviders();

		$this->entity->watchFieldsUpdates(__DIR__);
	}

	protected function registerFacade()
	{
		Facade::setContainer($this->entity);

		if ( ! class_exists('Wonen'))
		{
			class_alias('Realworks\\Wonen\\Facade', 'Wonen');
		}

		if ( ! class_exists('Wonen\\Wonen'))
		{
			class_alias('Realworks\\Wonen\\Facade', 'Wonen\\Wonen');
		}
	}

	protected function registerProviders()
	{
		$this->entity->register(new CommonServiceProvider($this->entity, $this->app));
		$this->entity->register(new MediaServiceProvider($this->entity, $this->app));
		$this->entity->register(new XmlReaderServiceProvider($this->entity, $this->app));

		// $this->entity->singleton('updater.dirtycheck', function()
		// {
		// 	return new DateDirtyCheckStrategy;
		// });
	}

	public function boot()
	{
		$this->entity['migrator']->run(new Migrator);
		$this->entity['migrator']->run(new \Realworks\Common\PrimaryKeyMigrator);
	}
}
