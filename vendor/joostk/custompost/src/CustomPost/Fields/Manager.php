<?php namespace CustomPost\Fields;

use BadMethodCallException;
use InvalidArgumentException;
use CustomPost\Database\Post;
use CustomPost\Database\Table;
use CustomPost\Fields\Types\ArrayField;

class Manager implements ParentInterface
{
	protected $table;

	protected $fields;

	protected $primaryField;

	protected $subtypeTableResolver;

	protected $arrayTableResolver;

	protected $macros = array();

	public function __construct(Table $table, array $fields)
	{
		$this->table = $table;
		$this->fields = $fields;
	}

	public function initialize()
	{
		$initialized = array();

		foreach ($this->fields as $field)
		{
			$field->initialize($this);

			$initialized[$field->getName()] = $field;
		}

		$this->fields = $initialized;

		return $this;
	}

	public function getPrimaryField()
	{
		return $this->primaryField;
	}

	public function setPrimaryField(Field $primaryField)
	{
		$this->primaryField = $primaryField;

		return $this;
	}

	public function getTable()
	{
		return $this->table;
	}

	public function setSubtypeTableResolver($resolver)
	{
		$this->subtypeTableResolver = $resolver;

		return $this;
	}

	public function setArrayTableResolver($resolver)
	{
		$this->arrayTableResolver = $resolver;

		return $this;
	}

	public function resolveSubtypeTable(Subtype $subtype)
	{
		return call_user_func($this->subtypeTableResolver, $subtype);
	}

	public function resolveArrayTable(ArrayField $field)
	{
		return call_user_func($this->arrayTableResolver, $field);
	}

	public function getFullName($glue = null)
	{
		return $glue === null ? array() : null;
	}

	public function getFields()
	{
		return $this->fields;
	}

	public function getField($key)
	{
		return isset($this->fields[$key]) ? $this->fields[$key] : null;
	}

	public function traverse($key)
	{
		if (empty($key)) return $this;

		$field = $this;

		foreach (explode('.', $key) as $segment)
		{
			if ( ! $field instanceof ParentInterface)
			{
				throw new InvalidArgumentException("Could not access field [{$segment}] of child type.");
			}

			$field = $field->getField($segment);
		}

		return $field;
	}

	public function hasMacro($method)
	{
		return isset($this->macros[$method]);
	}

	public function macro($method, $callback)
	{
		$this->macros[$method] = $callback;

		return $this;
	}

	public function macroUnless($method, $callback)
	{
		if ( ! $this->hasMacro($method))
		{
			$this->macro($method, $callback);
		}

		return $this;
	}

	public function call(Post $post, $method, array $parameters = array())
	{
		if (isset($this->macros[$method]))
		{
			array_unshift($parameters, $post);

			return call_user_func_array($this->macros[$method], $parameters);
		}

		throw new BadMethodCallException("Macro method [{$method}] does not exist.");
	}

	public function __get($key)
	{
		return $this->getField($key);
	}

	public function __isset($key)
	{
		return isset($this->fields[$key]);
	}
}
