<?php namespace CustomPost\Support;

use CustomPost\Contracts\RepositoryInterface;

class PermalinkRewriter
{
	protected $repository;

	protected $postType;

	public function __construct(RepositoryInterface $repository, $postType)
	{
		$this->repository = $repository;
		$this->postType = $postType;
	}

	public function enable($title)
	{
		$me = $this;

		add_filter('register_post_type_args', function(array $args, $postType) use ($me, $title)
		{
			return $me->supports(array('post_type' => $postType)) ? $me->rewrite($title, $args) : $args;
		}, 10, 2);
	}

	public function supports($post)
	{
		return data_get($post, 'post_type') === $this->postType;
	}

	public function rewrite($title, array $args)
	{
		$parts = explode('#{/}', $title);

		if (count($parts) > 1)
		{
			$parts = $this->translate($parts);

			$this->rewritePermalinks($parts);

			$this->handleRequests($parts);

			$args['rewrite']['slug'] = $this->rewriteParts(array_get($args, 'rewrite.slug'), $parts);
			$args['rewrite']['walk_dirs'] = false;
		}

		return $args;
	}

	protected function translate(array $parts)
	{
		return array_slice(array_combine(array_map('md5', $parts), $parts), 0, -1);
	}

	protected function rewriteParts($slug, array $parts)
	{
		foreach ($parts as $tag => $part)
		{
			$slug .= "/%{$tag}%";
			$GLOBALS['wp']->add_query_var($tag);
			$GLOBALS['wp_rewrite']->add_rewrite_tag("%{$tag}%", '([^/]+)', "{$tag}=");
		}

		return trim($slug, '/');
	}

	protected function rewritePermalinks(array $parts)
	{
		$me = $this;

		add_action('post_type_link', function($link, $post, $leavename, $sample) use ($me, $parts)
		{
			return $me->supports($post) ? $me->permalink($link, $post, $parts) : $link;
		}, 10, 4);
	}

	public function permalink($link, $post, array $parts)
	{
		$model = $this->repository->find($post->ID) ?: $this->repository->activated();

		if ( ! $model)
		{
			return $link;
		}

		foreach ($parts as $tag => $part)
		{
			$value = sanitize_title($model->substitute($part)) ?: '-';

			$link = str_replace("/{$value}-", '/', $link);
			$link = str_replace("%{$tag}%", $value, $link);
		}

		return $link;
	}

	protected function handleRequests(array $parts)
	{
		$me = $this;

		add_filter('request', function($request) use ($me, $parts)
		{
			return $me->supports($request) ? $me->applyParts($request, $parts) : $request;
		});
	}

	public function applyParts($request, array $parts)
	{
		foreach (array_reverse(array_keys($parts)) as $part)
		{
			if ( ! isset($request[$part])) continue;

			$value = $request[$part];

			$request['name'] = $value.'-'.$request['name'];
			$request[$this->postType] = $value.'-'.$request[$this->postType];
		}

		return $request;
	}
}
