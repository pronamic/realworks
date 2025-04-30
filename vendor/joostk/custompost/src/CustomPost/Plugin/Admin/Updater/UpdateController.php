<?php namespace CustomPost\Plugin\Admin\Updater;

use JoostK\Wordpress\Admin\BaseController;

class UpdateController extends BaseController
{
	public function update()
	{
		$this->app['updater']->background()->update();

		exit;
	}

	public function upload($entity = null, $user = null)
	{
		if (empty($_FILES['file']))
		{
			return $this->error(422, 'Geen bestand geüpload.');
		}

		$this->app['updater.upload']->prepare($_FILES['file'], $entity, $user)->background()->update();

		exit;
	}

	public function cancel()
	{
		$result = $this->app['updater.canceller']->cancel();

		return compact('result');
	}
}
