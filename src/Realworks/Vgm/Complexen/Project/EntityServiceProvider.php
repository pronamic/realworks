<?php namespace Realworks\Vgm\Complexen\Project;

use Realworks\Common\CommonServiceProvider;
use JoostK\Wordpress\Support\ServiceProvider;
use CustomPost\Reader\Xml\XmlReaderServiceProvider;
use Realworks\Vgm\Complexen\Type\EntityServiceProvider as TypeServiceProvider;

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

		if ( ! class_exists('Vgm'))
		{
			class_alias('Realworks\Vgm\Complexen\Project\Facade', 'Vgm');
		}

		if ( ! class_exists('Vgm\Project'))
		{
			class_alias('Realworks\Vgm\Complexen\Project\Facade', 'Vgm\Project');
		}
	}

	protected function registerProviders()
	{
		$this->app->register(new TypeServiceProvider($this->entity, $this->app));

		$this->entity->register(new AdditionalFieldsProvider($this->entity, $this->app));
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
		$this->entity['migrator']->run(new \Realworks\Common\PrimaryKeyMigrator);
	}
}
