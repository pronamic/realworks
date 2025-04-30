<?php namespace CustomPost\Plugin\Updater\Media;

use CustomPost\Contracts\DatabaseInterface;

class ProcessingQueue
{
	protected $db;

	protected $table;

	protected $exists;

	public function __construct(DatabaseInterface $db, $table)
	{
		$this->db = $db;
		$this->table = $table;
	}

	public function push($id, $path)
	{
		$this->ensureTableExists();

		$this->db->insert($this->table, compact('id', 'path'));

		$this->schedule();
	}

	public function scheduled()
	{
		return wp_next_scheduled(Processor::EVENT);
	}

	public function schedule()
	{
		if ($this->scheduled() === false)
		{
			wp_schedule_single_event(time(), Processor::EVENT);
		}
	}

	public function delete($media)
	{
		$this->ensureTableExists();

		$this->db->query("DELETE FROM `{$this->table}` WHERE `id` = %d", $media['id']);
	}

	public function peek()
	{
		$this->ensureTableExists();

		return $this->db->first("SELECT * FROM `{$this->table}` LIMIT 1");
	}

	public function resume()
	{
		if ($this->peek())
		{
			$this->schedule();
		}
	}

	protected function ensureTableExists()
	{
		if ($this->exists) return;

		$this->db->query("CREATE TABLE IF NOT EXISTS `{$this->table}` (
			`id` BIGINT(9) UNSIGNED NOT NULL,
			`path` VARCHAR(1000) NOT NULL,
			PRIMARY KEY (`id`)
		);");

		$this->exists = true;
	}
}
