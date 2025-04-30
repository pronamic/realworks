<?php namespace CustomPost\Formatter;

use CustomPost\Fields\Field;

class FunctionFormatter extends DelegateFormatter
{
	protected $callback;

	public function __construct(Field $field, $callback)
	{
		$this->callback = $callback;

		parent::__construct($field);
	}

	public function render()
	{
		$parameters = func_get_args();

		array_unshift($parameters, $this->value);

		return call_user_func_array($this->callback, $parameters);
	}
}
