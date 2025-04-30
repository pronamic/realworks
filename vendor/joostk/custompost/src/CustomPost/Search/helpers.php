<?php

use CustomPost\Search\Expressions\Sql;
use CustomPost\Search\Expressions\IsNull;
use CustomPost\Search\Expressions\Binary;
use CustomPost\Search\Expressions\Between;

function where($field, $operator)
{
	_deprecated_function('where', '20151224', '::where(...)');

	switch ($operator)
	{
		case 'null':
		case 'is null':
			return new IsNull($field);
		case 'not null':
		case 'is not null':
			return new IsNull($field, true);
		case 'between':
			$min = func_get_arg(2);
			$max = func_get_arg(3);

			return new Between($field, $min, $max);
		case 'sql':
			$expression = func_get_arg(2);

			return new Sql($field, $expression);
		default:
			if (func_num_args() === 2)
			{
				return new Sql($field, $operator);
			}
			else
			{
				$value = func_get_arg(2);

				return new Binary($field, $operator, $value);
			}
	}
}
