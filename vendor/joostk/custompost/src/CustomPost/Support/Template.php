<?php namespace CustomPost\Support;

use CustomPost\Entity;

class Template
{
	protected $entity;

	protected $name;

	protected $duration;

	public function __construct(Entity $entity, $name)
	{
		$this->entity = $entity;
		$this->name = $name;
	}

	public function cache($duration = 43200)
	{
		$this->duration = $duration;

		return $this;
	}

	public function cached($duration = 43200)
	{
		return $this->cache($duration);
	}

	public function render()
	{
		if ($key = $this->cacheKey() and $template = wp_cache_get($key, 'templates')) return $template;

		// Setting the current post as query var causes it to be available in
		// the template without having to explicitely specify it as global.
		set_query_var($this->config('post.global'), $this->entity->getCurrentPost());

		ob_start();
		get_template_part($this->config('paths.template').'/'.$this->name);
		$template = ob_get_clean();

		set_query_var($this->config('post.global'), null);

		if ($key) wp_cache_set($key, $template, 'templates', $this->duration);

		return $template;
	}

	protected function cacheKey()
	{
		if ($this->duration !== null)
		{
			$post = $this->entity->getCurrentPost();

			return $post ? $this->name.'_'.$post->getPrimaryKey() : $name;
		}
	}

	protected function config($key)
	{
		return $this->entity['config'][$key];
	}

	public function __toString()
	{
		return $this->render();
	}
}
