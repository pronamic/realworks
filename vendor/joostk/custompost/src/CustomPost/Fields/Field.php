<?php namespace CustomPost\Fields;

use Closure;
use BadMethodCallException;
use InvalidArgumentException;
use CustomPost\Formatter\Formatter;
use CustomPost\Formatter\ClosureFormatter;
use CustomPost\Formatter\FunctionFormatter;

abstract class Field extends BaseField
{
	protected $name;

	protected static $defaults = array();

	protected $attributes;

	protected $formatter;

	protected static $defaultFormatterResolver;

	protected static $types = array(
		'string' => 'CustomPost\\Fields\\Types\\StringT',
		'text' => 'CustomPost\\Fields\\Types\\Text',
		'int' => 'CustomPost\\Fields\\Types\\Integer',
		'integer' => 'CustomPost\\Fields\\Types\\Integer',
		'double' => 'CustomPost\\Fields\\Types\\Double',
		'float' => 'CustomPost\\Fields\\Types\\Double',
		'bool' => 'CustomPost\\Fields\\Types\\Boolean',
		'boolean' => 'CustomPost\\Fields\\Types\\Boolean',
		'date' => 'CustomPost\\Fields\\Types\\Date',
		'datetime' => 'CustomPost\\Fields\\Types\\DateTime',
		'multi' => 'CustomPost\\Fields\\Types\\ArrayField',
		'multiple' => 'CustomPost\\Fields\\Types\\ArrayField',
		'listing' => 'CustomPost\\Fields\\Types\\ArrayField',
		'collection' => 'CustomPost\\Fields\\Types\\ArrayField',
	);

	protected static $formatters = array(
		'string' => 'CustomPost\\Formatter\\StringFormatter',
		'bool' => 'CustomPost\\Formatter\\BooleanFormatter',
		'boolean' => 'CustomPost\\Formatter\\BooleanFormatter',
		'date' => 'CustomPost\\Formatter\\DateFormatter',
		'datetime' => 'CustomPost\\Formatter\\DateTimeFormatter',
		'double' => 'CustomPost\\Formatter\\DoubleFormatter',
		'float' => 'CustomPost\\Formatter\\DoubleFormatter',
		'integer' => 'CustomPost\\Formatter\\IntegerFormatter',
		'array' => 'CustomPost\\Formatter\\ArrayFieldFormatter',
		'list' => 'CustomPost\\Formatter\\ArrayFieldListFormatter',
		'money' => 'CustomPost\\Formatter\\MoneyFormatter',
	);

	public function __construct($name)
	{
		$this->attributes = $this->registerDefaults();

		parent::__construct($name);
	}

	public function initialize(ParentInterface $parent)
	{
		if ($this->primary)
		{
			$parent->setPrimaryField($this);
		}

		parent::initialize($parent);
	}

	public function getName()
	{
		return $this->name;
	}

	public function getLabel()
	{
		if ($this->label) return $this->label;

		$label = preg_replace('/(?x)
			([A-Z][a-z]) # Detect start of words
			/', '_\\1', $this->name);

		$label = preg_replace('/(?x)
			(?| # Branch reset group, to reset capturing group numbering
				([a-z])([A-Z]) # Detect boundary between lowercase and uppercase
			|
				([A-Za-z])([0-9]) # Detect boundary between text and numbers
			)/', '\\1_\\2', $label);

		$parts = explode('_', $label);

		foreach ($parts as &$part)
		{
			$part = $part === strtoupper($part) ? $part : lcfirst($part);
		}

		return ucfirst(implode(' ', $parts));
	}

	public function hasSelector()
	{
		return $this->select !== null;
	}

	public static function setFormatterResolver($resolver)
	{
		$this->formatterResolver = $resolver;

		return $this;
	}

	public static function setDefaultFormatterResolver($resolver)
	{
		static::$defaultFormatterResolver = $resolver;
	}

	public static function registerFormatter($key, $class)
	{
		static::$formatters[$key] = $class;
	}

	public function newFormatter()
	{
		if ($this->formatter)
		{
			return $this->makeFormatter($this->formatter);
		}
		else if (static::$defaultFormatterResolver)
		{
			return static::$defaultFormatterResolver();
		}
		else
		{
			return $this->newDefaultFormatter();
		}
	}

	public function newDefaultFormatter()
	{
		return new Formatter($this);
	}

	public function makeFormatter($formatter)
	{
		if ($formatter instanceof Closure)
		{
			return new ClosureFormatter($this, $formatter);
		}

		$formatter = array_get(static::$formatters, $formatter, $formatter);

		if (class_exists($formatter))
		{
			return new $formatter($this);
		}
		else if (function_exists($formatter))
		{
			return new FunctionFormatter($this, $formatter);
		}
		else
		{
			throw new InvalidArgumentException("Formatter type [{$formatter}] is neither a class nor a function.");
		}
	}

	public function formatter($formatter)
	{
		$this->formatter = $formatter;

		return $this;
	}

	public function hasVirtualDependency($flag = null)
	{
		if ($flag === null) return $this->get('hasVirtualDependency', false);
		else return $this->set('hasVirtualDependency', $flag);
	}

	public function transform($value)
	{
		return $value;
	}

	public function parsed($value)
	{
		return $value;
	}

	public function values(array $values)
	{
		$this->formatter('CustomPost\Formatter\ValueMapFormatter');

		$values += $this->get('values', array());

		return $this->set('values', $values);
	}

	public function set($key, $value)
	{
		array_set($this->attributes, $key, $value);

		return $this;
	}

	public function get($key, $default = null)
	{
		return array_get($this->attributes, $key, $default);
	}

	public function __get($key)
	{
		return $this->get($key);
	}

	public function __set($key, $value)
	{
		$this->set($key, $value);
	}

	public function __call($method, $parameters)
	{
		$value = $this->extractValue($parameters);

		return $this->set($method, $value);
	}

	protected function extractValue(array $parameters)
	{
		switch (count($parameters))
		{
			case 0:
				return true;
			case 1:
				return $parameters[0];
			default:
				return $parameters;
		}
	}

	public function registerDefaults()
	{
		return static::$defaults;
	}

	public function toSql()
	{
		return "`{$this->name}` {$this->datatype()}" . ($this->unique ? ' UNIQUE' : '');
	}

	abstract protected function datatype();

	public static function register($type, $class)
	{
		static::$types[$type] = $class;
	}

	public static function virtual($name, $class)
	{
		return new $class($name);
	}

	public static function make($type, $name)
	{
		if (isset(static::$types[$type]))
		{
			$class = static::$types[$type];

			return new $class($name);
		}

		throw new BadMethodCallException("Cannot instantiate CustomPost\\Fields\\Field of class [{$type}].");
	}

	public static function __callStatic($method, $parameters)
	{
		array_unshift($parameters, $method);

		return forward_static_call_array('static::make', $parameters);
	}
}
