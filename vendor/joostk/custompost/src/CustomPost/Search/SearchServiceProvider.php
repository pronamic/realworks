<?php namespace CustomPost\Search;

use CustomPost\Search\Query;
use WP_Query as WordpressQuery;
use CustomPost\ServiceProvider;
use CustomPost\Search\Executer;
use CustomPost\Search\ArrayExecuter;
use CustomPost\Search\Query\Builder;
use CustomPost\Search\Serialization\Serializer;
use CustomPost\Search\Serialization\Deserializer;

class SearchServiceProvider extends ServiceProvider
{
	public function register()
	{
		$this->registerEntity();

		$this->registerFieldsStorage();

		$this->registerSerializer();

		$this->registerDeserializer();

		$this->registerForm();

		$this->registerBuilder();

		$this->registerSortOrder();

		$this->registerExecuter();

		$this->registerArrayExecuter();

		$this->registerQuery();

		$this->registerCounter();

		$this->registerBindingUpdater();

		$this->registerLiveSearch();

		$this->registerRequestHasher();

		$this->registerRewriter();
	}

	protected function registerEntity()
	{
		$me = $this;

		$this->entity->singleton('search.entity', function($entity) use ($me)
		{
			$entity = new Entity($me->readSearchFields());

			return $entity->setDefaultFormResolver(array($me, 'resolveDefaultForm'));
		});
	}

	public function resolveDefaultForm()
	{
		$form = $this->entity['search.form'];

		$counter = $this->entity->make('search.counter', array($form, new WordpressQuery));

		return $form->setCounter($counter);
	}

	public function readSearchFields()
	{
		$stored = $this->entity['search.fields.storage']->load();

		return $this->entity['search.fields.deserializer']->deserialize($stored);
	}

	protected function registerFieldsStorage()
	{
		$this->entity->singleton('search.fields.storage', function($entity)
		{
			return new FieldDefinitionsThemeStorage(
				$entity['json.encoder'],
				$entity['json.decoder'],
				$entity['config']['paths.storage'] . '/search.json'
			);
		});
	}

	protected function registerSerializer()
	{
		$this->entity->singleton('search.fields.serializer', function($entity)
		{
			return new Serializer;
		});
	}

	protected function registerDeserializer()
	{
		$this->entity->singleton('search.fields.deserializer', function($entity)
		{
			return new Deserializer($entity['fields.manager']);
		});
	}

	protected function registerForm()
	{
		$this->entity['search.form'] = function($entity)
		{
			return new Form($entity['search.entity']);
		};
	}

	protected function registerBuilder()
	{
		$this->entity['search.builder'] = function($entity)
		{
			return new Builder($entity['db']);
		};
	}

	protected function registerSortOrder()
	{
		$this->entity['search.sort'] = function($entity)
		{
			return new SortOrder($entity['fields.manager']);
		};
	}

	protected function registerExecuter()
	{
		$this->entity['search.executer'] = function($entity, Form $form)
		{
			$executer = new Executer($entity['search.entity'], $entity['search.sort'], $form, $entity['events']);

			return $executer->setQueryResolver(function() use ($entity)
			{
				return $entity['search.query'];
			});
		};
	}

	protected function registerArrayExecuter()
	{
		$this->entity['search.arrayexecuter'] = function($entity)
		{
			$executer = new ArrayExecuter($entity['search.sort']);

			return $executer->setQueryResolver(function() use ($entity)
			{
				return $entity['search.query'];
			});
		};
	}

	protected function registerQuery()
	{
		$this->entity['search.query'] = function($entity)
		{
			return new Query(
				$entity['fields.manager'],
				$entity['search.builder'],
				$entity['config']['post.type']
			);
		};
	}

	protected function registerCounter()
	{
		$this->entity['search.counter'] = function($entity, array $parameters)
		{
			list($form, $wpQuery) = $parameters;

			$executer = $entity->make('search.executer', $form);

			$counter = new Counters\DatabaseCounter($executer, $entity['fields.manager'], $entity['db']);

			return $counter->setQuery($wpQuery);
		};
	}

	protected function registerBindingUpdater()
	{
		$this->entity['search.bindingupdater'] = function($entity)
		{
			$updater = new BindingUpdater($entity['search.entity'], $entity['db']);

			return $updater->setQueryResolver(function() use ($entity)
			{
				return $entity['search.query'];
			});
		};
	}

	protected function registerLiveSearch()
	{
		$this->entity['search.live'] = function($entity)
		{
			return new LiveSearch($entity, $entity['search.entity']->getForm(), $entity['search.hasher'], $GLOBALS['wp_query']);
		};
	}

	protected function registerRequestHasher()
	{
		$this->entity->singleton('search.hasher', function()
		{
			return new RequestHasher;
		});
	}

	protected function registerRewriter()
	{
		$this->entity->singleton('search.rewriter', function($entity)
		{
			return new Rewriter($entity->getPostType());
		});
	}

	public function boot()
	{
		$this->watchUpdatedEvent();

		$this->enableRewriter();
	}

	protected function watchUpdatedEvent()
	{
		$this->entity['events']->listen('entity.updated', function($entity)
		{
			if ($entity['search.bindingupdater']->update())
			{
				$entity->saveSearchFields();
			}
		});
	}

	protected function enableRewriter()
	{
		$me = $this;

		$this->registerRewrites();

		$this->entity['search.rewriter']->enable();

		$this->entity['events']->listen('config.save', function() use ($me)
		{
			$me->registerRewrites();

			flush_rewrite_rules();
		});
	}

	public function registerRewrites()
	{
		$pages = (array) $this->entity->config('archive.pages');

		$this->entity['search.rewriter']->rewrite($pages);
	}
}
