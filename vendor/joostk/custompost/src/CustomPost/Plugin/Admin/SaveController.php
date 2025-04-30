<?php namespace CustomPost\Plugin\Admin;

use RuntimeException;
use CustomPost\ExtendedEntity;
use JoostK\Illuminate\Container\Container;
use JoostK\Wordpress\Admin\BaseController;
use JoostK\Wordpress\Support\FilesystemException;

class SaveController extends BaseController
{
	public function save($data)
	{
		return array(
			'settings' => $this->saveConfig($this->app, $data['settings']),
			'entities' => $this->saveEntities($data['entities']),
		);
	}

	protected function saveEntities(array $entities)
	{
		foreach ($entities as $entity => &$data)
		{
			$entity = $this->app->entity($entity);

			$data = array(
				'settings' => $this->saveConfig($entity, $data['settings']),
				'search' => $this->saveSearchFields($entity, $data['search']),
			);
		}

		return $entities;
	}

	protected function saveConfig(Container $container, $config)
	{
		try
		{
			$config = json_decode($config, true);

			$container['config']->overwrite($config);

			return $container->saveConfiguration();
		}
		catch (FilesystemException $e)
		{
			$this->handleFilesystemException($e);
		}
	}

	protected function saveSearchFields(ExtendedEntity $entity, $fields)
	{
		try
		{
			$fields = json_decode($fields);

			$entity->updateSearchFields($fields);

			return $entity['search.fields.storage']->load();
		}
		catch (FilesystemException $e)
		{
			$this->handleFilesystemException($e);
		}
	}

	protected function handleFilesystemException(FilesystemException $e)
	{
		throw new RuntimeException("File [{$e->getPath()}] could not be written.");
	}
}
