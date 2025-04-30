<?php namespace Realworks\Vgm\Units;

use Realworks\Wonen\MediaServiceProvider;
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

		$this->entity->watchFieldsUpdates(__DIR__.'/../../Wonen');
	}

	protected function registerFacade()
	{
		Facade::setContainer($this->entity);

		if ( ! class_exists('Vgm\\Unit'))
		{
			class_alias('Realworks\\Vgm\\Units\\Facade', 'Vgm\\Unit');
		}
	}

	protected function registerProviders()
	{
		$this->entity->register(new CommonServiceProvider($this->entity, $this->app));
		$this->entity->register(new MediaServiceProvider($this->entity, $this->app));
		$this->entity->register(new XmlReaderServiceProvider($this->entity, $this->app));
	}

	public function boot()
	{
		$this->entity['migrator']->run(new \Realworks\Common\PrimaryKeyMigrator);
	}
}
