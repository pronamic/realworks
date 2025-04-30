<?php namespace Realworks\Common;

class PrimaryKeyMigrator {

	public function migratePrimaryKey($migrator, $entity)
	{
		$table = $entity['fields.manager']->getTable();

		if ($table->hasColumn('objectSystemID'))
		{
			$entity['db']->query('UPDATE '.$table->getName().' SET id = objectSystemID where objectSystemID IS NOT NULL');
		}
	}

}
