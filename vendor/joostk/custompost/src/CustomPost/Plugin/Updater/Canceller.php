<?php namespace CustomPost\Plugin\Updater;

use CustomPost\Contracts\DatabaseInterface;

class Canceller
{
	const OPTION = 'canceller_token';

	protected $db;

	protected $prefix;

	public function __construct(DatabaseInterface $db)
	{
		$this->db = $db;
	}

	public function setPrefix($prefix)
	{
		$this->prefix = $prefix.'_';

		return $this;
	}

	public function cancel()
	{
		if ( ! $this->isCancelled())
		{
			add_option($this->prefix.static::OPTION, true);

			return true;
		}
		else
		{
			return false;
		}
	}

	public function isCancelled()
	{
		return null !== $this->db->first(
			"SELECT 1 FROM {$this->db->getTableName('options')}
			 WHERE option_name = %s LIMIT 1",

			$this->prefix.static::OPTION
		);
	}

	public function check()
	{
		if ($this->isCancelled())
		{
			$this->clear();

			throw new UpdateCancelledException;
		}
	}

	public function clear()
	{
		delete_option($this->prefix.static::OPTION);
	}
}
