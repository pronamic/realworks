<?php namespace CustomPost\Contracts;

interface DatabaseInterface
{
	public function insert($table, array $data);

	public function lastInsertId();

	public function update($table, array $data, array $where);

	public function query($query, $data = array());

	public function first($query, $data = array());

	public function all($query, $data = array());

	public function column($query, $offset = 0, $data = array());

	public function prepare($query, array $data);

	public function getTablePrefix();

	public function getTableName($table);
}
