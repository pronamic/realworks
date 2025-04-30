<?php namespace CustomPost\Support;

use CustomPost\Fields\Manager;
use CustomPost\Fields\ParentInterface;

class MarkdownFieldsDumper
{
	protected $manager;

	public function __construct(Manager $manager)
	{
		$this->manager = $manager;
	}

	public function dump()
	{
		ob_start();

		$this->row('Veld', 'Naam', 'Type');
		$this->row('----', '----', '----');

		$this->traverse($this->manager);

		return ob_get_clean();
	}

	protected function traverse(ParentInterface $parent, $level = 0)
	{
		$indent = str_repeat('&nbsp;', $level * 4);

		foreach ($parent->getFields() as $field)
		{
			echo $indent, $this->dumpField($field, $level);
		}
	}

	protected function dumpField($field, $level)
	{
		$type = $this->getType($field);

		if ($field instanceof ParentInterface)
		{
			$this->bold($field->getName(), $field->getFullName('.'), $type);

			$this->traverse($field, $level + 1);
		}
		else
		{
			$this->row($field->getLabel(), $field->getFullName('.'), $type);
		}
	}

	protected function getType($field)
	{
		switch ($type = class_basename($field))
		{
			case 'StringT': return 'String';
			case 'ArrayField': return 'Array';
			case 'CollectionSubtype': return 'Collection';
			default: return $type;
		}
	}

	protected function row()
	{
		echo implode(' | ', func_get_args()), PHP_EOL;
	}

	protected function bold()
	{
		echo '__'.implode('__ | __', func_get_args()).'__', PHP_EOL;
	}
}
