<?php namespace CustomPost\Plugin\Admin;

use JoostK\Wordpress\Admin\BaseController;

class CacheController extends BaseController
{
	public function flush($entity)
	{
		$instance = $this->app->entity($entity);

		$instance['fields.cache']->flush();

		return $this->app['admin.dashboard']->get();
	}
}
