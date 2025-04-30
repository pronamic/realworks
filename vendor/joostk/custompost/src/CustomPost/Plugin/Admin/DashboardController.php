<?php namespace CustomPost\Plugin\Admin;

use JoostK\Wordpress\Admin\BaseController;

class DashboardController extends BaseController
{
	public function index()
	{
		return $this->app['admin.dashboard']->get();
	}
}
