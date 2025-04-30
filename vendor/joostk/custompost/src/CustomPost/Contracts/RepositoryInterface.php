<?php namespace CustomPost\Contracts;

use CustomPost\Fields\Subtype;
use CustomPost\Fields\CollectionSubtype;

interface RepositoryInterface
{
	public function make();

	public function makeSubtype(Subtype $subtype);

	public function makeCollectionSubtype(CollectionSubtype $subtype);

	public function find($id, $key = null);

	public function findSubtype(Subtype $subtype, $id);

	public function findSubtypeCollection(CollectionSubtype $subtype, $id);
}
