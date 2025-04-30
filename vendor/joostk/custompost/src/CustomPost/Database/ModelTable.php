<?php namespace CustomPost\Database;

use CustomPost\Fields\Manager;
use CustomPost\Fields\ParentInterface;

class ModelTable extends Table
{
	protected $manager;

	public function setManager(ParentInterface $manager)
	{
		$this->manager = $manager;

		return $this;
	}

	public function sync(Model $model)
	{
		$this->create();

		$this->syncColumns($model);
	}

	protected function syncColumns(Model $model)
	{
		$columns = $this->extractColumns($model);

		$added = array_udiff($columns, $this->columns, 'strcasecmp');

		if (count($added) > 0)
		{
			$this->addColumns($added);

			$this->columns = array_merge($this->columns, $added);
		}
	}

	protected function addColumns(array $columns)
	{
		$manager = $this->manager;

		$statements = array_map(function($column) use ($manager)
		{
			$sql = $manager->getField($column)->toSql();

			return $sql ? "ADD COLUMN {$sql}" : null;
		}, $columns);

		$this->alterTable(implode(",\n", array_filter($statements)));
	}

	protected function extractColumns(Model $model)
	{
		return array_keys(array_filter($model->getAttributes(), function($attribute)
		{
			return $attribute !== null;
		}));
	}

	public function updateColumn($column)
	{
		$this->fetchColumns();

		if ($this->exists() and in_array($column, $this->columns, true))
		{
			$sql = $this->manager->getField($column)->toSql();

			$this->dropIndices($column);
			$this->alterTable("CHANGE COLUMN `{$column}` {$sql}");
		}
	}

	public function renameColumn($from, $to)
	{
		$this->fetchColumns();

		if ($this->exists() and in_array($from, $this->columns, true))
		{
			$sql = $this->manager->getField($to)->toSql();

			$this->dropIndices($from);
			$this->alterTable("CHANGE COLUMN `{$from}` {$sql}");

			$this->columns = array_diff($this->columns, array($from));
			$this->columns[] = $to;
		}
	}

	public function dropIndices($name)
	{
		foreach ($this->db->all("SHOW INDEX FROM `{$this->name}` WHERE Column_name = %s", $name) as $index)
		{
			$this->alterTable("DROP INDEX `{$index['Key_name']}`");
		}
	}

	public function deleteColumn($column)
	{
		$this->fetchColumns();

		if ($this->exists() and in_array($column, $this->columns, true))
		{
			$this->alterTable("DROP COLUMN `{$column}`");

			$this->columns = array_diff($this->columns, array($column));
		}
	}

	protected function alterTable($statements)
	{
		$this->db->query("ALTER TABLE `{$this->name}`\n{$statements}");
	}
}
