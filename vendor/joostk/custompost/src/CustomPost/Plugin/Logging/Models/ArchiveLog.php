<?php namespace CustomPost\Plugin\Logging\Models;

class ArchiveLog extends Log
{
	public $entity;

	public $user;

	public $stats;

	public function __construct($path, $data)
	{
		parent::__construct($path, $data);

		$this->entity = $data->entity;
		$this->user = $data->user;
		$this->stats = $data->stats;
	}

	public function summary()
	{
		return parent::summary() + array(
			'entity' => $this->entity,
			'user' => $this->user,
			'stats' => $this->stats,
		);
	}

	public function complete()
	{
		$data = parent::complete();

		if ( ! $this->completed)
		{
			$data['messages'] = array_reverse($data['messages']);
		}

		return $data;
	}
}
