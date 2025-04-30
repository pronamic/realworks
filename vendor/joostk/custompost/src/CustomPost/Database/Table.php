<?php namespace CustomPost\Database;

use CustomPost\Contracts\DatabaseInterface;

class Table
{
	protected $db;

	protected $name;

	protected $primaryKey;

	protected $referenceKey;

	protected $exists;

	protected $columns;

	protected $created = false;

	protected $parent;

	public function __construct(DatabaseInterface $db, $name, $primaryKey, $referenceKey = null)
	{
		$this->db = $db;
		$this->name = $name;
		$this->primaryKey = $primaryKey;
		$this->referenceKey = $referenceKey ?: $primaryKey;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getPrimaryKey()
	{
		return $this->primaryKey;
	}

	public function getReferenceKey()
	{
		return $this->referenceKey;
	}

	public function setParent($table, $key)
	{
		$this->parent = array($table, $key);

		return $this;
	}

	public function find($id, $key = null)
	{
		if ($this->exists())
		{
			$key = $key ?: $this->referenceKey;

			if (is_array($id))
			{
				$params = implode(',', array_fill(0, count($id), '%s'));
				return $id ? $this->db->all("SELECT * FROM `{$this->name}` WHERE `{$key}` IN ({$params})", $id) : array();
			}
			else
			{
				return $this->db->first("SELECT * FROM `{$this->name}` WHERE `{$key}` = %s LIMIT 1", $id);
			}
		}
		else
		{
			return is_array($id) ? array() : null;
		}
	}

	public function has($id, $key = null)
	{
		if ($this->exists())
		{
			$key = $key ?: $this->primaryKey;

			$count = $this->db->first("SELECT COUNT(1) FROM `{$this->name}` WHERE `{$key}` = %s LIMIT 1", $id);

			return reset($count) > 0;
		}
		else
		{
			return false;
		}
	}

	public function insert(array $data)
	{
		$this->create();

		return $this->db->insert($this->name, $this->filteredData($data));
	}

	public function lastInsertId()
	{
		return $this->db->lastInsertId();
	}

	public function update(array $data, $key)
	{
		$this->create();

		return $this->db->update($this->name, $this->filteredData($data), array($this->primaryKey => $key));
	}

	protected function filteredData(array $data)
	{
		$filtered = array();

		foreach ($this->columns as $column)
		{
			$filtered[$column] = isset($data[$column]) ? $data[$column] : null;
		}

		return $filtered;
	}

	public function delete($id)
	{
		if ($this->exists())
		{
			return $this->db->query("DELETE FROM `{$this->name}` WHERE `{$this->primaryKey}` = %s", $id);
		}
	}

	public function create()
	{
		if ( ! $this->exists())
		{
			$this->db->query($this->getCreateQuery());

			$this->created = $this->exists = true;
		}

		$this->fetchColumns();
	}

	protected function getCreateQuery()
	{
		return "CREATE TABLE `{$this->name}` (\n" .
		       "\t{$this->primaryKey} BIGINT(20) UNSIGNED,\n" .
		       "\tPRIMARY KEY (`{$this->primaryKey}`)\n" .
		       // ",\tFOREIGN KEY (`{$this->referenceKey}`) REFERENCES {$this->parent[0]}({$this->parent[1]}) ON DELETE CASCADE\n" .
		       ");";
	}

	public function hasColumn($column)
	{
		return $this->exists() ? in_array($column, $this->fetchColumns()) : false;
	}

	public function exists()
	{
		if ($this->exists === null)
		{
			$this->exists = !! $this->db->cached('first', "SHOW TABLES LIKE '{$this->name}'");
		}

		return $this->exists;
	}

	protected function fetchColumns()
	{
		if ($this->exists() and $this->columns === null)
		{
			$this->columns = $this->db->cached('column', "SHOW COLUMNS FROM `{$this->name}`");
		}

		return $this->columns;
	}
}
