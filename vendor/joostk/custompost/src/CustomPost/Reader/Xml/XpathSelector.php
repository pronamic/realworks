<?php namespace CustomPost\Reader\Xml;

use Closure;
use SimpleXMLElement;
use ReflectionMethod;
use ReflectionFunction;
use CustomPost\Fields\Field;
use CustomPost\Reader\AbstractReader;

class XpathSelector extends AbstractSelector
{
	protected $expression;

	public function __construct($expression, $parser = null)
	{
		$this->expression = $expression;
		$this->parser = $parser;
	}

	public static function make($expression, $parser = null)
	{
		return new static($expression, $parser);
	}

	public function perform(SimpleXMLElement $element, AbstractReader $reader)
	{
		$data = $element->xpath($this->expression);

		// Reduce the result to a single element
		if ( ! $this->multiple)
		{
			$data = count($data) > 0 ? reset($data) : null;
		}

		return $this->callParserCallback($data, $reader, $element);
	}

	protected function callParserCallback($data, AbstractReader $reader, SimpleXMLElement $element)
	{
		if ($this->parser)
		{
			list($raw, $always) = $this->determineCallbackProperties();

			if ( ! $raw) $data = $this->stringifyData($data);

			if ($always or $data !== null)
			{
				$data = $this->callParser($data, $reader, $element);
			}

			if ($raw) $data = $this->stringifyData($data);
		}
		else
		{
			$data = $this->stringifyData($data);
		}

		return $this->field ? $this->field->parsed($data) : $data;
	}

	protected function determineCallbackProperties()
	{
		$reflector = $this->getReflectorForParser();

		$parameters = $reflector->getParameters();
		$value = $parameters[0];

		$raw = $value->getClass() ? true : false;
		$always = $value->isOptional();

		return array($raw, $always);
	}

	protected function getReflectorForParser()
	{
		if (is_object($this->parser) and ! $this->parser instanceof Closure)
		{
			return new ReflectionMethod($this->parser, 'parse');
		}
		else if (is_string($this->parser))
		{
			return new ReflectionMethod($this->parser);
		}
		else
		{
			return new ReflectionFunction($this->parser);
		}
	}

	protected function callParser($data, AbstractReader $reader, SimpleXMLElement $element)
	{
		if (is_callable($this->parser))
		{
			return call_user_func($this->parser, $data, $reader, $element);
		}
		else
		{
			return call_user_func(array($this->parser, 'parse'), $data, $reader, $element);
		}
	}

	protected function stringifyData($data)
	{
		if (is_array($data))
		{
			foreach ($data as &$value)
			{
				$value = $this->stringifyData($value);
			}
		}
		elseif ($data instanceof SimpleXMLElement)
		{
			$data = (string) $data;
		}

		return $data === '' ? null : $data;
	}
}
