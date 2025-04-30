<?php namespace CustomPost\Formatter;

class Listing
{
	protected static $__templates = array(
		'title' => '#{title}',
		'before' => '<dl>',
		'item' => '<dt>#{label}</dt><dd>#{value}</dd>',
		'between' => '',
		'after' => '</dl>',
		'empty' => '#{message}',
	);

	protected $fields;

	protected $labels = array();

	protected $data = array();

	protected $title;

	protected $templates = array();

	protected $empty;

	protected $renderer;

	protected $renderers = array();

	public function __construct(array $fields, array $labels = array())
	{
		$this->fields = $fields;

		$this->copyTemplates();
		$this->extractLabels($labels);
	}

	protected function copyTemplates()
	{
		$this->templates = static::$__templates;
	}

	protected function extractLabels(array $labels)
	{
		foreach ($labels as $field => $label)
		{
			if (is_array($label))
			{
				$this->label($field, array_get($label, 'label'));
				$this->data($field, $label);
			}
			else
			{
				$this->label($field, $label);
			}
		}
	}

	public static function templates(array $templates)
	{
		static::$__templates = array_merge(static::$__templates, $templates);
	}

	public function title($title)
	{
		$this->title = $title;

		return $this;
	}

	public function before($before)
	{
		$this->templates['before'] = $before;

		return $this;
	}

	public function between($between)
	{
		$this->templates['between'] = $between;

		return $this;
	}

	public function after($after)
	{
		$this->templates['after'] = $after;

		return $this;
	}

	public function item($item)
	{
		$this->templates['item'] = $item;

		return $this;
	}

	public function label($name, $label)
	{
		$this->labels[$name] = $label;

		return $this;
	}

	public function data($name, $data)
	{
		$this->data[$name] = $data;

		return $this;
	}

	public function ifEmpty($empty)
	{
		$this->empty = $empty;

		return $this;
	}

	public function fields($callback)
	{
		$this->renderer = $callback;

		return $this;
	}

	public function field($field, $callback)
	{
		$this->renderers[$field] = $callback;

		return $this;
	}

	public function isEmpty()
	{
		foreach ($this->fields as $field)
		{
			if ( ! $field->isEmpty()) return false;
		}

		return true;
	}

	public function render()
	{
		ob_start();

		if ($this->isEmpty())
		{
			$this->renderEmpty();
		}
		else
		{
			$this->renderFields();
		}

		return ob_get_clean();
	}

	protected function renderEmpty()
	{
		if ($this->empty)
		{
			$this->renderTitle();

			echo str_replace('#{message}', value($this->empty), $this->templates['empty']);
		}
	}

	protected function renderFields()
	{
		$this->renderTitle();

		$separator = $this->templates['before'];

		foreach ($this->fields as $field)
		{
			if ( ! $field->isEmpty())
			{
				echo value($separator);

				$this->renderField($field);

				$separator = $this->templates['between'];
			}
		}

		echo value($this->templates['after']);
	}

	protected function renderTitle()
	{
		if ($this->title)
		{
			echo str_replace('#{title}', value($this->title), $this->templates['title']);
		}
	}

	protected function renderField(Formatter $formatter)
	{
		$name = $formatter->getField()->getFullName('.');

		$label = isset($this->labels[$name]) ? $this->labels[$name] : $formatter->label();
		$data = isset($this->data[$name]) ? (array) $this->data[$name] : null;
		$template = isset($this->renderers[$name]) ? $this->renderers[$name] : array_pull($data, 'template', $this->templates['item']);

		if (is_callable($template))
		{
			echo call_user_func($template, $formatter, $label, $data);
		}
		else if ($this->renderer)
		{
			echo call_user_func($this->renderer, $formatter, $label, $data);
		}
		else
		{
			echo $this->renderTemplate($formatter, $template, $label, $data);
		}
	}

	protected function renderTemplate(Formatter $formatter, $template, $label, $data)
	{
		$replace = array(
			'#{label}' => $label,
			'#{value}' => $this->getFormatterValue($formatter, $data),
			'#{data}' => '',
		);

		foreach ((array) $data as $key => $value)
		{
			$replace['#{data.'.$key.'}'] = is_array($value)
				? $value = $formatter->apply($value)
				: $value;

			if ($key === 0) $replace['#{data}'] = $value;
		}

		return str_replace(array_keys($replace), array_values($replace), $template);
	}

	protected function getFormatterValue(Formatter $formatter, &$data)
	{
		if ($style = array_pull($data, 'formatter')) $formatter = $formatter->formatter($style);

		return call_user_func_array(array($formatter, 'render'), (array) array_pull($data, 'render'));
	}

	public function __toString()
	{
		return $this->render();
	}
}
