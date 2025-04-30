<?php namespace CustomPost\Providers;

use CustomPost\Search\Form;
use WP_Query as WordpressQuery;
use CustomPost\ServiceProvider;
use CustomPost\Search\Expression;

class WordpressServiceProvider extends ServiceProvider
{
	public function boot()
	{
		$this->registerWordpressPostType();

		$this->registerPostBinding();

		$this->registerEagerLoading();

		$this->registerDeletePostHook();

		$this->registerSearchHook();

		$this->registerArchiveTitle();

		$this->registerArchiveTemplate();

		$this->registerSingleTemplate();

		$this->register404Redirect();

		$this->registerLiveSearchData();
	}

	protected function registerWordpressPostType()
	{
		if ($this->entity->isEnabled())
		{
			register_post_type(
				$this->entity['config']['post.type'],
				$this->entity['config']['post.register']
			);
		}
	}

	protected function registerPostBinding()
	{
		$entity = $this->entity;

		add_action('the_post', function($post) use ($entity)
		{
			if ($post->post_type === $entity['config']['post.type'])
			{
				$object = $entity['repository']->find($post->ID);

				$entity->setCurrentPost($object);

				if ($object) $entity['events']->fire('post.loaded', array($object, $post));
			}
			else
			{
				$entity->setCurrentPost(null);
			}
		});
	}

	protected function registerEagerLoading()
	{
		$entity = $this->entity;

		add_filter('posts_results', function($posts, $wpQuery) use ($entity)
		{
			if ($wpQuery->get('post_type') === $entity['config']['post.type'])
			{
				$entity['repository']->fetch(array_pluck($posts, 'ID'));
			}

			return $posts;
		}, 10, 2);
	}

	protected function registerDeletePostHook()
	{
		$entity = $this->entity;

		add_action('before_delete_post', function($id) use ($entity)
		{
			$post = get_post($id);

			if ($post and $post->post_type === $entity['config']['post.type'])
			{
				$model = $entity['repository']->find($id);

				if ($model) $model->deleteRow();
			}
		});
	}

	protected function registerSearchHook()
	{
		list($me, $entity) = array($this, $this->entity);

		add_action('parse_query', function(WordpressQuery $wpQuery) use ($me, $entity)
		{
			$postType = $entity['config']['post.type'];

			if ($postType !== $wpQuery->get('post_type')) return;

			if (($wpQuery->is_main_query() and $wpQuery->is_archive() and ! is_admin()) or $wpQuery->get('__custompost.search'))
			{
				$me->setupSearchForm($wpQuery);

				$me->applyArchiveArgs($wpQuery);
			}
			else
			{
				$expressions = $me->extractSearchExpressions($wpQuery, $postType);

				$entity['search.arrayexecuter']->apply($wpQuery, $expressions);

				$wpQuery->set('suppress_filters', false);
			}
		});
	}

	public function extractSearchExpressions(WordpressQuery $query, $postType)
	{
		$expressions = $query->get($postType) ?: array();

		if (is_array($expressions) or $expressions instanceof Expression)
		{
			unset($query->query[$postType], $query->query_vars[$postType]);

			return $expressions;
		}
	}

	public function setupSearchForm(WordpressQuery $wpQuery)
	{
		if ( ! $wpQuery->get('__custompost.handled'))
		{
			$form = $this->entity['search.form']->setData($_REQUEST);

			$this->applyOrdering($form, $wpQuery);

			$executer = $this->entity->make('search.executer', $form);
			$executer->apply($wpQuery);

			$counter = $this->entity->make('search.counter', array($form, $wpQuery));
			$form->setCounter($counter);

			if ($wpQuery->is_main_query())
			{
				$this->entity['search.entity']->setForm($form);
			}

			$wpQuery->set('__custompost.handled', true);
		}
	}

	protected function applyOrdering(Form $form, WordpressQuery $wpQuery)
	{
		$orderings = (array) $this->entity['config']['search.orderings'];
		$orderBy = $wpQuery->get('orderby') ?: null;
		$current = reset($orderings) ?: null;

		foreach ($orderings as &$ordering)
		{
			$ordering['current'] =
				($orderBy === $ordering['orderby']) ||
				($orderBy === null and ! empty($ordering['default']));

			if ($ordering['current']) $current = $ordering;
		}

		$form->setOrderings($orderings, $current);

		if ($current) $wpQuery->set('orderby', $current['orderby']);
	}

	public function applyArchiveArgs(WordpressQuery $wpQuery)
	{
		foreach ((array) $this->entity['config']['archive.query'] as $key => $value)
		{
			$wpQuery->set($key, $value);
		}
	}

	protected function registerArchiveTitle()
	{
		$entity = $this->entity;

		add_filter('post_type_archive_title', function($title, $postType) use ($entity)
		{
			if ($postType === $entity['config']['post.type'])
			{
				return $entity['config']['archive.title'];
			}
			else
			{
				return $title;
			}
		}, 10, 2);
	}

	protected function registerArchiveTemplate()
	{
		$entity = $this->entity;

		add_filter('archive_template', function($template) use ($entity)
		{
			if ( ! is_post_type_archive($entity['config']['post.type'])) return $template;

			if (isset($_REQUEST['__live']))
			{
				wp_send_json($entity['search.live']->process());
			}
			else
			{
				return locate_template($entity['config']['paths.template'].'/archive.php');
			}
		});
	}

	protected function registerSingleTemplate()
	{
		$entity = $this->entity;

		add_filter('single_template', function($template) use ($entity)
		{
			if (get_post_type() !== $entity['config']['post.type']) return $template;

			return locate_template($entity['config']['paths.template'].'/single.php');
		});
	}

	protected function register404Redirect()
	{
		$entity = $this->entity;

		$redirect = $entity['config']['post.wordpress.404-redirect'];

		if ($redirect) add_action('template_redirect', function() use ($entity, $redirect)
		{
			if (is_404() and get_query_var('post_type') === $entity['config']['post.type'])
			{
				wp_redirect(home_url($redirect), 301);
				exit;
			}
		});
	}

	protected function registerLiveSearchData()
	{
		if (isset($_REQUEST['__hash']))
		{
			$data = $this->entity['search.hasher']->decode($_REQUEST['__hash']);

			$_GET = array_merge($_GET, $data);
			$_POST = array_merge($_POST, $data);
			$_REQUEST = array_merge($_REQUEST, $data);
		}
	}
}
