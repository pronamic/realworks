<?php namespace CustomPost\Search;

class ExpressionTemplate
{
	protected $name;

	protected $arguments;

	public function __construct($name, array $arguments = array())
	{
		$this->name = $name;
		$this->arguments = $arguments;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getArguments()
	{
		return $this->arguments;
	}
}
