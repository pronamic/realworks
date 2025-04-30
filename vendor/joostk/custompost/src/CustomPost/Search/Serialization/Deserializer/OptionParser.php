<?php namespace CustomPost\Search\Serialization\Deserializer;

use stdClass;
use CustomPost\Search\Option;
use CustomPost\Search\ExpressionGroup;
use CustomPost\Search\ExpressionInterface;

class OptionParser
{
	protected $expressionParser;

	public function __construct(ExpressionParser $expressionParser)
	{
		$this->expressionParser = $expressionParser;
	}

	public function parse(stdClass $data)
	{
		$label = object_get($data, 'label');
		$value = object_get($data, 'value');
		$parents = object_get($data, 'parents', array());
		$enabled = object_get($data, 'enabled', true);
		$default = object_get($data, 'default', false);
		$arguments = object_get($data, 'arguments', array());
		$expressions = object_get($data, 'expressions', array());

		$this->handleDefaultTemplate($expressions, $arguments);

		$expression = $this->expressionParser->parse($expressions);

		$option = new Option($value, $label, $enabled, $default);
		$option->setParents($this->convertParents($parents));

		$this->applyExpression($option, $expression);

		return $option;
	}

	protected function handleDefaultTemplate(&$expressions, $arguments)
	{
		if ($arguments)
		{
			$expressions = is_array($expressions) ? $expressions : array($expressions);

			array_unshift($expressions, (object) array(
				'template' => 'default',
				'arguments' => $arguments,
			));

			if (count($expressions) === 1) $expressions = reset($expressions);
		}
	}

	protected function applyExpression(Option $option, ExpressionInterface $expression)
	{
		if ($expression instanceof ExpressionGroup)
		{
			$option->setRelation($expression->getRelation());
			$option->setExpressions($expression->getExpressions());
		}
		else
		{
			$option->addExpression($expression);
		}

		$option->setTemplate($expression->getTemplate());
	}

	protected function convertParents(array $parents)
	{
		foreach ($parents as &$parent)
		{
			$parent = (array) $parent;
		}

		return $parents;
	}
}
