<?php namespace CustomPost\Support;

use CustomPost\ServiceProvider;

class SupportServiceProvider extends ServiceProvider
{
	public function register()
	{
		$this->registerEntityInfo();

		$this->registerFieldsMarkdown();

		$this->registerPermalinkRewriter();
	}

	protected function registerEntityInfo()
	{
		$this->entity->singleton('info', function($entity)
		{
			return new EntityInfo($entity);
		});
	}

	protected function registerFieldsMarkdown()
	{
		$this->entity->singleton('fields.markdown', function($entity)
		{
			return new MarkdownFieldsDumper($entity['fields.manager']);
		});
	}

	protected function registerPermalinkRewriter()
	{
		$this->entity->singleton('permalink.rewriter', function($entity)
		{
			return new PermalinkRewriter($entity['repository'], $entity['config']['post.type']);
		});
	}

	public function boot()
	{
		$this->entity['permalink.rewriter']->enable($this->entity['config']['post.wordpress.title']);
	}
}
