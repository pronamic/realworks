<?php namespace Realworks\Admin\Pages;

use CustomPost\Plugin\Admin\BasePage as Page;

abstract class BasePage extends Page
{
	protected function isDebug()
	{
		return defined('REALWORKS_DEVELOP');
	}
}
