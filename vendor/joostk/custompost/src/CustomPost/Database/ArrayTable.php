<?php namespace CustomPost\Database;

use CustomPost\Contracts\DatabaseInterface;

class ArrayTable extends Table
{
	protected $datatype = 'VARCHAR(200)';

	public function __construct(DatabaseInterface $db, $name)
	{
		parent::__construct($db, $name, 'id', 'parent_id');
	}

	public function setDatatype($datatype)
	{
		$this->datatype = $datatype;

		return $this;
	}

	public function getValueColumn()
	{
		return 'value';
	}

	public function updateValues($id, array $values)
	{
		$this->delete($id);

		$data = array($this->referenceKey => $id);

		foreach ($values as $value)
		{
			$data['value'] = $value;

			$this->insert($data);
		}
	}

	public function all($id)
	{
		if ( ! $this->exists()) return array();

		return $this->db->column("SELECT `value` FROM {$this->name} WHERE `{$this->referenceKey}` = %d ORDER BY `{$this->primaryKey}` ASC", 0, $id);
	}

	public function delete($id)
	{
		if ($this->exists())
		{
			return $this->db->query("DELETE FROM `{$this->name}` WHERE `{$this->referenceKey}` = %d", $id);
		}
	}

	protected function getCreateQuery()
	{
		return "CREATE TABLE `{$this->name}` (\n" .
			"\t`{$this->primaryKey}` BIGINT(20) UNSIGNED AUTO_INCREMENT,\n" .
			"\t`{$this->referenceKey}` BIGINT(20) UNSIGNED,\n" .
			"\t`value` {$this->datatype},\n" .
			"\tPRIMARY KEY (`{$this->primaryKey}`),\n" .
			"\tINDEX (`{$this->referenceKey}`)\n" .
			// ",\tFOREIGN KEY (`{$this->referenceKey}`) REFERENCES {$this->parent[0]}({$this->parent[1]}) ON DELETE CASCADE\n" .
		");";
	}
}
