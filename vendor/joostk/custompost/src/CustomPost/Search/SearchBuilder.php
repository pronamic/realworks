<?php namespace CustomPost\Search;

use Countable;
use ArrayAccess;
use IteratorAggregate;
use CustomPost\Database\Post;
use WP_Query as WordpressQuery;
use CustomPost\Search\Expression;
use CustomPost\Formatter\Formatter;
use CustomPost\Entity as BaseEntity;
use CustomPost\Search\Expressions\Sql;
use CustomPost\Support\QueryCollection;
use CustomPost\Search\Expressions\IsNull;
use CustomPost\Search\Expressions\Binary;
use CustomPost\Search\Expressions\Between;

class SearchBuilder implements IteratorAggregate, ArrayAccess, Countable
{
	protected $args = array();

	protected $stack = array(array());

	protected $query;

	public function __construct(BaseEntity $entity)
	{
		$this->entity = $entity;
	}

	public function where($field, $operator = null, $value = null)
	{
		if ($field instanceof Expression)
		{
			return $this->apply($field);
		}

		if (func_num_args() === 2)
		{
			list($operator, $value) = array('=', $operator);
		}

		return $this->apply(new Binary($field, $operator, $value));
	}

	public function whereNull($field)
	{
		return $this->apply(new IsNull($field));
	}

	public function whereNotNull($field)
	{
		return $this->apply(new IsNull($field, true));
	}

	public function whereBetween($field, $min, $max)
	{
		return $this->apply(new Between($field, $min, $max));
	}

	public function whereSql($field, $sql)
	{
		return $this->apply(new Sql($field, $sql));
	}

	public function matching(Formatter $field, $operator = '=')
	{
		if ( ! $field->field()->get('custom'))
		{
			return $this->where($field->name(), $operator, $field->value());
		}
	}

	protected function apply($expression)
	{
		$this->stack[count($this->stack) - 1][] = $expression;

		return $this;
	}

	public function applyQueryDeprecated($query)
	{
		$this->stack[count($this->stack) - 1] = $query;

		return $this->go();
	}

	public function arg($key, $value)
	{
		$this->args[$key] = $value;

		return $this;
	}

	public function args($args)
	{
		$this->args = wp_parse_args($args, $this->args);

		return $this;
	}

	public function all()
	{
		return $this->arg('posts_per_page', -1);
	}

	public function amount($amount)
	{
		return $this->arg('posts_per_page', $amount);
	}

	public function order($field, $direction = 'asc')
	{
		$order = array_get($this->args, 'orderby');

		return $this->arg('orderby', $order ? "{$order},{$field}:{$direction}" : "{$field}:{$direction}");
	}

	public function orderBy($field, $direction = 'asc')
	{
		return $this->order($field, $direction);
	}

	public function ascending($field)
	{
		return $this->order($field, 'asc');
	}

	public function descending($field)
	{
		return $this->order($field, 'desc');
	}

	public function without($ids)
	{
		$ids = is_array($ids) ? $ids : func_get_args();

		return $this->arg('post__not_in', array_map(function($id)
		{
			return $id instanceof Post ? $id->id() : $id;
		}, $ids));
	}

	public function push($relation = 'and')
	{
		array_push($this->stack, array());

		return $this->apply($relation);
	}

	public function pop()
	{
		$scope = array_pop($this->stack);

		return $this->apply($scope);
	}

	public function maps()
	{
		return $this->entity['maps']->paged($this->go()->getQuery());
	}

	public function go()
	{
		if ($this->query) return $this->query;

		$type = $this->entity->getPostType();

		return $this->query = new QueryCollection($this->entity, new WordpressQuery(array_merge($this->args, array(
			'post_type' => $type,
			$type => end($this->stack),
		))));
	}

	public function getIterator()
	{
		return $this->go()->getIterator();
	}

	public function count()
	{
		return $this->all()->arg('fields', 'ids')->go()->total();
	}

	public function offsetExists($key)
	{
		return $this->go()->offsetExists($key);
	}

	public function offsetGet($key)
	{
		return $this->go()->offsetGet($key);
	}

	public function offsetSet($key, $value)
	{
		return $this->go()->offsetSet($key, $value);
	}

	public function offsetUnset($key)
	{
		return $this->go()->offsetUnset($key);
	}

	public function __call($method, $parameters)
	{
		return call_user_func_array(array($this->go(), $method), $parameters);
	}

	public function __get($key)
	{
		return $this->go()->{$key};
	}
}
