<?php namespace CustomPost\Formatter;

class ArrayFieldListFormatter extends ArrayFieldFormatter
{
	public function render($list = '<ul>#{items}</ul>', $item = '<li>#{item}</li>')
	{
		$items = '';

		foreach ($this->value->all() as $value)
		{
			$items .= str_replace('#{item}', $value, $item);
		}

		return str_replace('#{items}', $items, $list);
	}
}
