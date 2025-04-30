<?php namespace CustomPost\Support;

use CustomPost\Entity;
use CustomPost\Fields\ParentInterface;

class EntityInfo
{
	protected $entity;

	public function __construct(Entity $entity)
	{
		$this->entity = $entity;
	}

	public function get()
	{
		return array(
			'identifier' => $this->entity->getIdentifier(),
			'enabled' => $this->entity->isEnabled(),
			'hierarchy' => $this->hierarchy(),
			'config' => $this->entity['config']->get(),
			'search' => $this->entity['search.fields.storage']->load(),
			'fields' => $this->getFields(),
			'authors' => $this->getAuthors(),
			'labels' => $this->entity->labels(),
		);
	}

	protected function hierarchy()
	{
		return array(
			'root' => $this->entity->isRoot(),
			'children' => array_keys($this->entity->children()),
		);
	}

	protected function getFields()
	{
		return $this->getChildFields($this->entity['fields.manager']);
	}

	protected function getChildFields(ParentInterface $parent)
	{
		$fields = array();

		foreach ($parent->getFields() as $field)
		{
			if ($field instanceof ParentInterface)
			{
				$fields = array_merge($fields, $this->getChildFields($field));
			}
			else
			{
				$values = $field->get('values', array());

				$fields[] = array(
					'name' => $field->getFullName('.'),
					'label' => $field->getLabel(),
					'labeled' => $values,
					'values' => array_map('strval', array_keys($values)),
					'type' => class_basename($field),
				);
			}
		}

		return $fields;
	}

	protected function getAuthors()
	{
		return get_users(array('fields' => array('ID', 'display_name')));
	}
}
