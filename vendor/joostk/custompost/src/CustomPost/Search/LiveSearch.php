<?php namespace CustomPost\Search;

use WP_Query as WordpressQuery;
use CustomPost\Entity as BaseEntity;
use JoostK\Wordpress\Iterators\QueryIterator;

class LiveSearch
{
	protected $entity;

	protected $form;

	protected $hasher;

	protected $query;

	public function __construct(BaseEntity $entity, Form $form, RequestHasher $hasher, WordpressQuery $query)
	{
		$this->entity = $entity;
		$this->form = $form;
		$this->hasher = $hasher;
		$this->query = $query;
	}

	public function getForm()
	{
		return $this->form;
	}

	public function getQuery()
	{
		return $this->query;
	}

	public function process()
	{
		return $this->entity->filtered('search.live.data',
			$this->isInfinite() ? $this->processInfinite() : $this->processPaged(),
			$this
		);
	}

	protected function isInfinite()
	{
		return $this->form->value('__infinite') !== null;
	}

	protected function processInfinite()
	{
		$items = array();
		$template = $this->form->value('__infinite');

		foreach (new QueryIterator($this->query) as $post)
		{
			$items[] = $this->entity->template($template)->cache()->render();
		}

		return array(
			'items' => $items,
			'next' => get_next_posts_page_link($this->query->max_num_pages),
			'hash' => $this->hasher->hash($this->form->getData()),
		);
	}

	protected function processPaged()
	{
		return array(
			'total' => (int) $this->query->found_posts,
			'hash' => $this->hasher->hash($this->form->getData()),
			'templates' => $this->templates(),
		);
	}

	protected function templates()
	{
		$templates = array();

		foreach ((array) $this->form->value('__templates') as $template)
		{
			$templates[$template] = $this->entity->template($template)->render();
		}

		return $templates;
	}
}
