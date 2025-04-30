<?php namespace CustomPost\Database;

use CustomPost\Contracts\DatabaseInterface;

class ModelCollectionTable extends ModelTable
{
	public function __construct(DatabaseInterface $db, $name)
	{
		parent::__construct($db, $name, 'id', 'parent_id');
	}

	public function all($id)
	{
		if ( ! $this->exists()) return array();

		return $this->db->all("SELECT * FROM `{$this->name}` WHERE `{$this->referenceKey}` = %d ORDER BY {$this->primaryKey} ASC", $id);
	}

	protected function getCreateQuery()
	{
		return "CREATE TABLE `{$this->name}` (\n" .
			"\t`{$this->primaryKey}` BIGINT(20) UNSIGNED AUTO_INCREMENT,\n" .
			"\t`{$this->referenceKey}` BIGINT(20) UNSIGNED,\n" .
			"\tPRIMARY KEY (`{$this->primaryKey}`),\n" .
			"\tINDEX (`{$this->referenceKey}`)\n" .
			// ",\tFOREIGN KEY (`{$this->referenceKey}`) REFERENCES {$this->parent[0]}({$this->parent[1]}) ON DELETE CASCADE\n" .
		");";
	}
}
