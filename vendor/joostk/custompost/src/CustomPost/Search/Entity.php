<?php namespace CustomPost\Search;

class Entity
{
	protected $form;

	protected $defaultFormResolver;

	protected $fields;

	public function __construct(array $fields)
	{
		$this->fields = $fields;
	}

	public function reload(array $fields)
	{
		$this->fields = $fields;
	}

	public function setForm(Form $form)
	{
		$this->form = $form;

		return $this;
	}

	public function setDefaultFormResolver($resolver)
	{
		$this->defaultFormResolver = $resolver;

		return $this;
	}

	public function getForm()
	{
		if ($this->form === null)
		{
			$this->form = call_user_func($this->defaultFormResolver);
		}

		return $this->form;
	}

	public function getFields()
	{
		return $this->fields;
	}

	public function getField($key)
	{
		return isset($this->fields[$key]) ? $this->fields[$key] : null;
	}
}
