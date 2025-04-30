<?php namespace CustomPost\Search\BindingUpdater;

use CustomPost\Search\Query;

interface SqlBuilderStrategy
{
	function applySelects(Query $query);

	function applyOptions(Query $query);
}
