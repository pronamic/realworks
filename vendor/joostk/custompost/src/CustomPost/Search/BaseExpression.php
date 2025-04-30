<?php namespace CustomPost\Search;

abstract class BaseExpression implements ExpressionInterface
{
	protected $field;

	protected $template;

	public function setTemplate(ExpressionTemplate $template = null)
	{
		$this->template = $template;

		return $this;
	}

	public function getTemplate()
	{
		return $this->template;
	}

	public function makeWithArguments($name, array $arguments)
	{
		$expression = unserialize(serialize($this));

		foreach ($arguments as $index => $argument)
		{
			$expression->substitute($index, $argument);
		}

		return $expression->setTemplate(new ExpressionTemplate($name, $arguments));
	}
}
