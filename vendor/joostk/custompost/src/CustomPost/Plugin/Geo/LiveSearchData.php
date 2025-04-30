<?php namespace CustomPost\Plugin\Geo;

use CustomPost\Entity;
use WP_Query as WordpressQuery;
use CustomPost\Search\LiveSearch;

class LiveSearchData
{
	protected $entity;

	protected $live;

	public function __construct(Entity $entity, LiveSearch $live)
	{
		$this->entity = $entity;
		$this->live = $live;
	}

	public function augment(array $data)
	{
		return $data + array(
			'maps' => $this->maps(),
		);
	}

	protected function maps()
	{
		$maps = $this->entity['maps'];
		$query = $this->live->getQuery();

		switch ($this->live->getForm()->value('__maps'))
		{
			case 'all': return $maps->all($query);
			case 'paged': return $maps->paged($query);
		}
	}
}
