<?php namespace CustomPost\Search\Serialization\Deserializer;

use stdClass;
use CustomPost\Search\Field;
use InvalidArgumentException;
use CustomPost\Fields\Manager;

class FieldParser
{
	protected $manager;

	protected $templates;

	protected $optionParser;

	protected $expressionParser;

	public function __construct(Manager $manager)
	{
		$this->manager = $manager;
		$this->expressionParser = new ExpressionParser($this);
		$this->optionParser = new OptionParser($this->expressionParser);
	}

	public function parse($name, stdClass $data)
	{
		$this->templates = $this->parseTemplates($data);

		$options = $this->parseOptions($data);

		$field = new Field($name, $options);

		$this->applyBinding($field, $data);

		$field->setSettings($this->parseSettings($data));

		return $field->setExpressionTemplates($this->templates);
	}

	protected function applyBinding(Field $field, stdClass $data)
	{
		if ($binding = object_get($data, 'binding'))
		{
			$binding = object_get($this->manager, $binding);

			if ($binding) $field->setBinding($binding);
		}
	}

	protected function parseTemplates($data)
	{
		$templates = array();

		foreach (object_get($data, 'templates', array()) as $name => $template)
		{
			$templates[$name] = $this->expressionParser->parse($template);
		}

		return $templates;
	}

	protected function parseOptions($data)
	{
		$options = array();

		foreach (object_get($data, 'options', array()) as $option)
		{
			$options[] = $this->optionParser->parse($option);
		}

		return $options;
	}

	protected function parseSettings($data)
	{
		$settings = object_get($data, 'settings', array());

		return json_decode(json_encode($settings), true);
	}

	public function resolveTemplate($template)
	{
		if (isset($this->templates[$template]))
		{
			return $this->templates[$template];
		}
		else
		{
			throw new InvalidArgumentException("Undefined template [{$template}].");
		}
	}
}
