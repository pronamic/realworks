<?php namespace CustomPost\Search\Serialization;

use CustomPost\Search\Field;
use CustomPost\Search\Option;
use CustomPost\Search\ExpressionGroup;
use CustomPost\Search\ExpressionTemplate;
use CustomPost\Search\ExpressionInterface;

class Serializer
{
	public function serialize(array $fields)
	{
		$data = array();

		foreach ($fields as $name => $field)
		{
			$data[$name] = $this->serializeField($field);
		}

		return $data;
	}

	protected function serializeField(Field $field)
	{
		$data = array();

		if ($parent = $field->getParent()) $data['parent'] = $parent->getName();
		if ($binding = $field->getBinding()) $data['binding'] = $binding->getFullName('.');
		if ($options = $field->getOptions()) $data['options'] = $this->serializeOptions($options);
		if ($settings = $field->getSettings()) $data['settings'] = $settings;
		if ($templates = $field->getExpressionTemplates()) $data['templates'] = $this->serializeTemplates($templates);

		return $data;
	}

	protected function serializeOptions(array $options)
	{
		$data = array();

		foreach ($options as $option)
		{
			$data[] = $this->serializeOption($option);
		}

		return $data;
	}

	protected function serializeTemplates(array $templates)
	{
		$data = array();

		foreach ($templates as $name => $expression)
		{
			$data[$name] = $this->serializeExpression($expression);
		}

		return $data;
	}

	protected function serializeOption(Option $option)
	{
		$data = array();
		$data['value'] = $option->getValue();
		$data['label'] = $option->getLabel();

		if ($option->isEnabled() === false) $data['enabled'] = false;
		if ($option->isDefault() === true) $data['default'] = true;

		if ($parents = $option->getParents()) $data['parents'] = $parents;
		if ($expressions = $this->serializeExpression($option, $data)) $data['expressions'] = $expressions;

		return $data;
	}

	protected function serializeExpression(ExpressionInterface $expression, array &$data = null)
	{
		if ($template = $expression->getTemplate())
		{
			return $this->serializeExpressionTemplate($template, $data);
		}
		elseif ($expression instanceof ExpressionGroup)
		{
			return $this->serializeExpressionGroup($expression, $data);
		}
		else
		{
			return $expression->toArray();
		}
	}

	protected function serializeExpressionTemplate(ExpressionTemplate $template, array &$data = null)
	{
		if ($template->getName() === 'default' and $data !== null and ! isset($data['arguments']))
		{
			$data['arguments'] = $template->getArguments();

			return null;
		}
		else
		{
			return array(
				'template' => $template->getName(),
				'arguments' => $template->getArguments(),
			);
		}
	}

	protected function serializeExpressionGroup(ExpressionGroup $group, array &$data = null)
	{
		$expressions = $group->getExpressions();

		if (count($expressions) === 1) return $this->serializeExpression(reset($expressions), $data);

		$group = array(strtolower($group->getRelation()));

		foreach ($expressions as $expression)
		{
			$group[] = $this->serializeExpression($expression, $data);
		}

		return array_values(array_filter($group));
	}
}
