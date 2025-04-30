<?php namespace CustomPost\Search\Serialization\Deserializer;

use stdClass;
use InvalidArgumentException;
use CustomPost\Search\ExpressionGroup;
use CustomPost\Search\Expressions\Sql;
use CustomPost\Search\ExpressionTemplate;
use CustomPost\Search\Expressions\Binary;
use CustomPost\Search\Expressions\IsNull;
use CustomPost\Search\Expressions\Between;
use CustomPost\Search\ExpressionInterface;

class ExpressionParser
{
	protected $field;

	public function __construct(FieldParser $field)
	{
		$this->field = $field;
	}

	public function parse($data)
	{
		if (is_array($data))
		{
			return $this->parseExpressionsArray($data);
		}
		elseif (is_object($data))
		{
			return $this->parseExpression($data);
		}
		else
		{
			throw new InvalidArgumentException("Expected expression or array of expressions.");
		}
	}

	protected function parseExpressionsArray(array $data)
	{
		$relation = null;
		$expressions = array();

		foreach ($data as $object)
		{
			// String types represent the relation
			if (is_string($object))
			{
				$relation = $object;
			}

			// Arrays begin a new expression group
			elseif (is_array($object))
			{
				$expressions[] = $this->parseExpressionsArray($object);
			}

			// An object specifies the details of an expression
			elseif (is_object($object))
			{
				$expressions[] = $this->parseExpression($object);
			}

			// Everything else is invalid
			else
			{
				throw new InvalidArgumentException('Expected relation, array or expression object.');
			}
		}

		return new ExpressionGroup($relation, $expressions);
	}

	protected function parseExpression(stdClass $object)
	{
		if ( ! empty($object->template))
		{
			return $this->parseTemplateObject($object);
		}
		else
		{
			return $this->parseExpressionObject($object);
		}
	}

	protected function parseTemplateObject(stdClass $object)
	{
		$template = $this->field->resolveTemplate($object->template);

		$arguments = object_get($object, 'arguments', array());

		if ( ! is_array($arguments))
		{
			throw new InvalidArgumentException('Template arguments must be supplied as an array.');
		}

		return $template->makeWithArguments($object->template, $arguments);
	}

	protected function parseExpressionObject(stdClass $object)
	{
		$field = object_get($object, 'field');
		$operator = object_get($object, 'operator', '=');

		switch ($operator)
		{
			case 'null':
				return new IsNull($field);
			case 'notnull':
			case 'not null':
				return new IsNull($field, true);
			case 'sql':
				$expression = object_get($object, 'expression');

				return new Sql($field, $expression);
			case 'between':
				$min = object_get($object, 'min');
				$max = object_get($object, 'max');

				return new Between($field, $min, $max);
			default:
				$value = object_get($object, 'value');

				return new Binary($field, $operator, $value);
		}
	}
}
