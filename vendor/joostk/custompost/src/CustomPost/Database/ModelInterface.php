<?php namespace CustomPost\Database;

interface ModelInterface
{
	public function setHierarchy(ModelInterface $root, ModelInterface $parent);

	public function getReferenceKey();

	public function getTable();

	public function getRootModel();

	public function getParentModel();

	public function save();

	public function setExists($exists);

	public function syncOriginal();

	public function delete();

	public function deleteRow();

	public function clear();

	public function clearSubmodels();

	public function setAttributes(array $values);

	public function setReferenceKey($id);

	public function hasData();

	public function isDirty();

	public function getDirty();

	public function getData();
}
