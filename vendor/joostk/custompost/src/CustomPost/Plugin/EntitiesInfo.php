<?php namespace CustomPost\Plugin;

class EntitiesInfo
{
	protected $entities;

	public function __construct(array $entities)
	{
		$this->entities = $entities;
	}

	public function get()
	{
		$data = array();

		foreach ($this->entities as $key => $entity)
		{
			$data[$key] = $entity['info']->get();
		}

		return $data;
	}
}
