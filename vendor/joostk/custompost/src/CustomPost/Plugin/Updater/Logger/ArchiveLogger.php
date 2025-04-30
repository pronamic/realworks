<?php namespace CustomPost\Plugin\Updater\Logger;

use JoostK\Wordpress\Json\JsonEncoderInterface;

class ArchiveLogger extends JsonLogger implements ArchiveLoggerInterface
{
	protected $parent;

	protected $entity;

	protected $user;

	protected $changed = false;

	protected $stats = array(
		'total' => 0,
		'done' => 0,
		'changed' => 0,
	);

	public function __construct(JsonEncoderInterface $encoder, LoggerInterface $parent)
	{
		parent::__construct($encoder);

		$this->parent = $parent;
	}

	public function parent()
	{
		return $this->parent;
	}

	public function setStatus($status)
	{
		parent::setStatus($status);

		$this->parent->setStatus($status);
	}

	public function setSource($entity, $user)
	{
		$this->entity = $entity;
		$this->user = $user;

		$this->write();

		return $this;
	}

	public function total($total)
	{
		$this->stats['total'] = $total;

		$this->write();

		return $this;
	}

	public function data()
	{
		return parent::data() + array(
			'entity' => $this->entity,
			'user' => $this->user,
			'stats' => $this->stats,
		);
	}

	public function object($model, $state)
	{
		$this->trackChanges($state !== 'unchanged');

		$this->stats['done']++;

		parent::object($model, $state);
	}

	public function trackChanges($changed, $internal = true)
	{
		if ($internal or ! $this->changed)
		{
			$this->stats['changed'] += $this->changed = $changed;
		}

		$this->parent->trackChanges($changed, false);

		if ( ! $internal) $this->write();
	}
}
