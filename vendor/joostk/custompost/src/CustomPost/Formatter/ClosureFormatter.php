<?php namespace CustomPost\Formatter;

use Closure;
use CustomPost\Fields\Field;

class ClosureFormatter extends DelegateFormatter
{
	protected $callback;

	public function __construct(Field $field, Closure $callback)
	{
		$this->callback = $callback;

		parent::__construct($field);
	}

	public function render()
	{
		$parameters = func_get_args();

		array_unshift($parameters, $this);
		array_unshift($parameters, $this->value);

		return call_user_func_array($this->callback, $parameters);
	}
}
