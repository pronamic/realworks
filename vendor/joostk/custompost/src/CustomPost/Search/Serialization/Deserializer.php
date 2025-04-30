<?php namespace CustomPost\Search\Serialization;

use CustomPost\Fields\Manager;
use JoostK\Wordpress\Json\JsonDecoderInterface;

class Deserializer
{
	protected $fieldParser;

	protected $fields;

	public function __construct(Manager $manager)
	{
		$this->fieldParser = new Deserializer\FieldParser($manager);
	}

	public function deserialize($data)
	{
		$this->fields = array();

		foreach ($data as $name => $field)
		{
			$this->fields[$name] = $this->fieldParser->parse($name, $field);
		}

		$this->buildFieldHierarchy($data);

		return $this->fields;
	}

	protected function buildFieldHierarchy($data)
	{
		foreach ($data as $name => $field)
		{
			$this->applyParent($name, $field);
		}
	}

	protected function applyParent($name, $data)
	{
		if ($parent = object_get($data, 'parent'))
		{
			$parent = $this->getField($parent);

			if ($parent) $this->fields[$name]->setParent($parent);
		}
	}

	public function getField($field)
	{
		return isset($this->fields[$field]) ? $this->fields[$field] : null;
	}
}
