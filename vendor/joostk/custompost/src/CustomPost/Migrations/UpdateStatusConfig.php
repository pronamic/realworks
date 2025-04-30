<?php namespace CustomPost\Migrations;

use Exception;
use CustomPost\ServiceProvider;

class UpdateStatusConfig extends ServiceProvider
{
	public function boot()
	{
		if ($this->migratePostStatusConfig())
		{
			return $this->save();
		}
	}

	protected function migratePostStatusConfig()
	{
		$config = $this->entity['config']->get('post.wordpress.status');

		if (isset($config['field']) and isset($config['cases']))
		{
			$config['fields'] = array(
				array(
					'name' => $config['field'],
					'cases' => $config['cases'],
				),
			);

			unset($config['field'], $config['cases']);

			$this->entity['config']->set('post.wordpress.status', $config);

			return true;
		}
	}

	protected function save()
	{
		try
		{
			$this->entity->saveConfiguration();
		}
		catch (Exception $e)
		{
			//
		}
	}
}
