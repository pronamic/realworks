<?php namespace Realworks\Common;

use CustomPost\ServiceProvider;
use CustomPost\Plugin\Updater\AutoDelete\AutoDeleteServiceProvider;

class CommonServiceProvider extends ServiceProvider
{
	public function register()
	{
		$this->entity->register(new GeoServiceProvider($this->entity, $this->app));
		$this->entity->register(new AutoDeleteServiceProvider($this->entity, $this->app));

		$this->entity['post.class'] = 'Realworks\Common\Post';
	}

	public function boot()
	{
		$this->watchSearchEvent();
	}

	protected function watchSearchEvent()
	{
		$this->entity['events']->listen('search.before', 'Realworks\Common\AddressSearch@apply');
	}
}
