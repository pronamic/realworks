<?php namespace CustomPost\Reader\Json;

use Closure;
use ReflectionMethod;
use ReflectionFunction;
use CustomPost\Fields\Field;
use CustomPost\Reader\AbstractReader;

class JsonParser
{
	protected $field;

	protected $reader;

	protected $multiple;

	protected $parser;

	public function __construct(Field $field, AbstractReader $reader)
	{
		$this->field = $field;
		$this->reader = $reader;
		$this->multiple = $field->multiple;
		$this->parser = $field->parser;
	}

	public function parse($data)
	{
		if ($this->multiple and ! is_array($data))
		{
			$data = $data === null ? array() : array($data);
		}
		else if ( ! $this->multiple)
		{
			if (is_array($data))
			{
				$data = count($data) > 0 ? reset($data) : null;
			}

			if ($data === '')
			{
				$data = null;
			}
		}

		return $this->callParserCallback($data);
	}

	protected function callParserCallback($data)
	{
		if ($this->parser)
		{
			$always = $this->shouldAlwaysCall();

			if ($always or $data !== null)
			{
				$data = $this->callParser($data);
			}
		}

		return $this->field ? $this->field->parsed($data) : $data;
	}

	protected function callParser($data)
	{
		if (is_callable($this->parser))
		{
			return call_user_func($this->parser, $data, $this->reader);
		}
		else
		{
			return call_user_func(array($this->parser, 'parse'), $data, $this->reader);
		}
	}

	protected function shouldAlwaysCall()
	{
		$reflector = $this->getReflectorForParser();

		$parameters = $reflector->getParameters();
		$value = $parameters[0];

		return $value->isOptional();
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
}
