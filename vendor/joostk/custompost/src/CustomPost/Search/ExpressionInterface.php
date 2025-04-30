<?php namespace CustomPost\Search;

interface ExpressionInterface
{
	public function perform(Query $query, $operator = null);

	public function matches($value);

	public function substitute($key, $value);

	public function makeWithArguments($name, array $arguments);

	public function setTemplate(ExpressionTemplate $template = null);

	public function getTemplate();
}
