<?php namespace CustomPost\Search;

class OptionsRepresentation
{
	protected $field;

	protected $form;

	protected $withDisabled = false;

	protected $ignoreWhenNoResults = false;

	protected $ignoreWhenNotExists = false;

	protected $flat = false;

	public function __construct(Field $field, Form $form)
	{
		$this->field = $field;
		$this->form = $form;
	}

	public function withDisabled($flag = true)
	{
		$this->withDisabled = $flag;

		return $this;
	}

	public function ignoreWhenNoResults($flag = true)
	{
		$this->ignoreWhenNoResults = $flag;

		return $this;
	}

	public function ignoreWhenNotExists($flag = true)
	{
		$this->ignoreWhenNotExists = $flag;

		return $this;
	}

	public function nested()
	{
		$this->flat = false;

		return $this;
	}

	public function flat()
	{
		$this->flat = true;

		return $this;
	}

	protected function represent(Option $option)
	{
		return array(
			'value' => $option->getValue(),
			'label' => $option->getLabel(),
		);
	}

	public function get()
	{
		if ($this->flat)
		{
			return $this->buildFlat();
		}
		else
		{
			return $this->buildNested();
		}
	}

	public function json()
	{
		return json_encode($this->get());
	}

	protected function buildNested()
	{
		$data = array();

		foreach ($this->allOptions() as $option)
		{
			$list =& $data;

			foreach ($option->getParents() as $parent)
			{
				if ( ! isset($list[$parent['value']]))
				{
					$list[$parent['value']] = array();
				}

				$list =& $list[$parent['value']];
			}

			$list[] = $this->represent($option);
		}

		return $data;
	}

	protected function buildFlat()
	{
		$data = array();

		foreach ($this->allOptions() as $option)
		{
			$data[] = $this->represent($option) + array(
				'parents' => array_combine(
					array_pluck($option->getParents(), 'name'),
					array_pluck($option->getParents(), 'value')
				),
			);
		}

		return $data;
	}

	protected function allOptions()
	{
		list($form, $ignoreWhenNoResults, $ignoreWhenNotExists) = array($this->form, $this->ignoreWhenNoResults, $this->ignoreWhenNotExists);

		$options = $this->withDisabled ? $this->field->getOptions() : $this->field->getEnabledOptions();

		return array_filter($options, function($option) use ($form, $ignoreWhenNoResults, $ignoreWhenNotExists)
		{
			if ($ignoreWhenNoResults and $form->count($option) == 0)
			{
				return false;
			}
			else if ($ignoreWhenNotExists and ! $form->exists($option))
			{
				return false;
			}

			return true;
		});
	}
}
