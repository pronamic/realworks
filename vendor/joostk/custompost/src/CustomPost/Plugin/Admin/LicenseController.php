<?php namespace CustomPost\Plugin\Admin;

use JoostK\Wordpress\Admin\BaseController;

class LicenseController extends BaseController
{
	public function verify($license)
	{
		return array(
			'status' => $this->app['plugin.licenser']->verify($license),
			'license' => $license,
		);
	}
}
