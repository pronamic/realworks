<?php namespace CustomPost\Search;

use CustomPost\Fields\Manager;
use WP_Query as WordpressQuery;

class SortOrder
{
	protected $manager;

	protected $builtinOrders = array(
		'none', 'ID', 'author', 'title', 'name', 'type', 'date', 'modified', 'parent',
		'rand', 'comment_count', 'menu_order', 'meta_value', 'meta_value_num', 'post__in',
	);

	public function __construct(Manager $manager)
	{
		$this->manager = $manager;
	}

	public function apply(Query $query, WordpressQuery $wpQuery)
	{
		$orderBy = $wpQuery->get('orderby', 'date');

		if (empty($orderBy) or in_array($orderBy, $this->builtinOrders)) return;

		foreach (explode(',', $orderBy) as $order)
		{
			$this->applyOrder($query, $wpQuery, trim($order));
		}
	}

	public function applyOrder(Query $query, WordpressQuery $wpQuery, $orderBy)
	{
		list($name, $direction) = $this->analyzeOrderBy($wpQuery, $orderBy);

		if ($field = $this->manager->traverse($name))
		{
			$query->orderBy($name, $direction);

			$wpQuery->set('orderby', 'none');
		}
	}

	public function analyzeOrderBy(WordpressQuery $wpQuery, $orderBy)
	{
		if (strpos($orderBy, ':') !== false)
		{
			return explode(':', $orderBy, 2);
		}
		else
		{
			return array($orderBy, $wpQuery->get('order', 'ASC'));
		}
	}
}
