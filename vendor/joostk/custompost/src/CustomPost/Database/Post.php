<?php namespace CustomPost\Database;

use Exception;
use RuntimeException;
use CustomPost\Fields\Field;
use InvalidArgumentException;
use CustomPost\Fields\Manager;
use WP_Query as WordpressQuery;
use JoostK\Wordpress\Events\Events;
use JoostK\Wordpress\Support\Error;
use CustomPost\Formatter\Formatter;
use CustomPost\Search\SearchBuilder;
use CustomPost\Contracts\DatabaseInterface;
use CustomPost\Contracts\RepositoryInterface;
use JoostK\Wordpress\Iterators\QueryCollection;

class Post extends Model
{
	/**
	 * Field manager instance.
	 *
	 * @var \CustomPost\Fields\Manager
	 */
	protected $manager;

	/**
	 * Post configuration array.
	 *
	 * @var array
	 */
	protected $config;

	/**
	 * Event handler
	 *
	 * @var \JoostK\Wordpress\Events\Events
	 */
	protected $events;

	/**
	 * All meta values associated with this model.
	 *
	 * @var array
	 */
	protected $meta = array();

	/**
	 * List of registered event listeners.
	 *
	 * @var array
	 */
	protected $listeners;

	/**
	 * All cached methods when accessed through getters.
	 *
	 * @var array
	 */
	protected $cache = array();

	/**
	 * Cached method data.
	 *
	 * @var array
	 */
	protected $cached = array();

	/**
	 * Info for `wp_update_post` of the WordPress post that is queued for saving.
	 *
	 * @var array
	 */
	protected $pendingWordpressData = array();

	public function __construct(RepositoryInterface $repository, Manager $manager, array $config)
	{
		$this->manager = $manager;
		$this->config = $config;

		$this->rootModel = $this;

		parent::__construct($repository, $manager->getTable());
	}

	protected function getField($key)
	{
		return $this->manager->getField($key);
	}

	protected function getFields()
	{
		return $this->manager->getFields();
	}

	public function setEvents(Events $events = null)
	{
		$this->events = $events;

		return $this;
	}

	protected function setKeyValue($key, $value)
	{
		try
		{
			parent::setKeyValue($key, $value);
		}
		catch (InvalidArgumentException $e)
		{
			$this->meta[$key] = $value;
		}
	}

	protected function getKeyValue($key)
	{
		if ($this->manager->hasMacro($key))
		{
			return $this->manager->call($this, $key);
		}

		try
		{
			return parent::getKeyValue($key);
		}
		catch (InvalidArgumentException $e)
		{
			$this->loadMetaData();

			return isset($this->meta[$key]) ? $this->meta[$key] : null;
		}
	}

	public function getWordpressPost()
	{
		$id = $this->getPrimaryKey();

		return $id ? get_post($id) : null;
	}

	public function id()
	{
		return $this->getPrimaryKey();
	}

	public function primary()
	{
		$primary = $this->manager->getPrimaryField();

		return $this->get($primary->getFullName('.'));
	}

	public function loadMetaData()
	{
		if ($this->exists())
		{
			$meta = get_post_meta($this->getPrimaryKey());

			$this->meta = array_map(function($values)
			{
				return $values ? maybe_unserialize($values[0]) : null;
			}, $meta);
		}
	}

	public function custom($label, $value, $type = 'string')
	{
		$field = Field::make($type, $label)->label($label)->custom();

		$field->initialize($this->manager);

		return $field->newFormatter()->setModel($this->rootModel)->setValue($value)->setOriginal($value);
	}

	public function fireEvent($event)
	{
		if ($this->events)
		{
			$this->events->fire("post.{$event}", $this);
		}

		foreach ((array) array_get($this->listeners, $event) as $callback)
		{
			$callback($this);
		}
	}

	public function listen($event, $callback)
	{
		$this->listeners[$event][] = $callback;

		return $this;
	}

	protected function preventEmptyPrimaryKey()
	{
		if ($this->primary()->isEmpty())
		{
			throw new RuntimeException('Missing primary key, unable to save.');
		}
	}

	protected function beforeSave()
	{
		$this->preventEmptyPrimaryKey();

		parent::beforeSave();

		$this->fireEvent('save');

		if ($this->exists())
		{
			$this->fireEvent('update');

			$this->updateWordpressPost();
		}
		else
		{
			$this->fireEvent('create');

			$id = $this->insertWordpressPost();

			$this->setPrimaryKey($id);
		}
	}

	protected function afterSave($result)
	{
		if ($result)
		{
			$this->saveMetaData();
			$this->fireEvent('saved');
		}
		else
		{
			$this->delete();

			$this->setPrimaryKey(null);
		}

		parent::afterSave($result);
	}

	protected function rollback(Exception $exception)
	{
		parent::rollback($exception);

		try
		{
			// Manually delete the post. delete() will not do anything if the post did not exist,
			// and we want to make sure the WordPress post is deleted to avoid trouble later on.
			wp_delete_post($this->getPrimaryKey(), true);

			$this->deleteRow();
		}
		catch (Exception $e)
		{
			error_log($e);

			throw $exception;
		}
	}

	protected function saveMetaData()
	{
		$id = $this->getPrimaryKey();

		foreach ($this->meta as $key => $value)
		{
			if ($value === null)
			{
				delete_post_meta($id, $key);
			}
			else
			{
				update_post_meta($id, $key, $value);
			}
		}
	}

	public function update(array $data)
	{
		$this->pendingWordpressData = array_merge($this->pendingWordpressData, $data);
	}

	protected function updateWordpressPost()
	{
		$data = $this->wordpressPostData(true);

		if (is_array($data))
		{
			$data['ID'] = $this->getPrimaryKey();

			$result = wp_update_post($data, /* wp_error */ true);

			Error::handle($result);
		}
	}

	protected function insertWordpressPost()
	{
		$data = $this->wordpressPostData(false);

		$data['post_type'] = $this->config['type'];

		$result = wp_insert_post($data, /* wp_error */ true);

		Error::handle($result);

		return $result;
	}

	public function delete()
	{
		if ($this->exists())
		{
			$id = $this->getPrimaryKey();

			$this->deleteRow();

			wp_delete_post($id, true);
		}
	}

	public function deleteRow()
	{
		if ($this->exists())
		{
			$this->fireEvent('delete');

			parent::deleteRow();

			$this->fireEvent('deleted');
		}
	}

	protected function wordpressPostData($update)
	{
		return array_merge(array(
			'post_title' => $this->substitute(preg_replace('~#{/}\s?~', ' ', $this->config['wordpress']['title'])),
			'post_content' => $this->substitute($this->config['wordpress']['content']),
			'post_author' => $this->config['wordpress']['author'] ?: get_current_user_id(),
			'post_status' => $this->determineStatus(),
			'post_name' => '',
		), $this->pendingWordpressData);
	}

	public function isModelDirty()
	{
		return parent::isModelDirty() ?: $this->isWordpressPostDirty();
	}

	public function isWordpressPostDirty()
	{
		if (is_null($post = $this->getWordpressPost())) return false;

		$data = $this->wordpressPostData(true);

		return trim($data['post_title']) !== trim($post->post_title)
		    or $data['post_author'] !== (int) $post->post_author
		    or $data['post_status'] !== $post->post_status;
	}

	public function substitute($template)
	{
		$me = $this;

		return preg_replace_callback('~#\{(.*?)\}~', function($matches) use ($me)
		{
			$value = $me->traverse($matches[1]);

			if ($value instanceof Formatter) $value = $value->render();

			return is_scalar($value) ? $value : '';
		}, $template);
	}

	protected function determineStatus()
	{
		$config = array_get($this->config, 'wordpress.status');
		$fields = array_get($config, 'fields', array());

		foreach ($fields as $field)
		{
			if ($status = $this->determineStatusFromField($field)) return $status;
		}

		return array_get($config, 'default', 'publish');
	}

	protected function determineStatusFromField($field)
	{
		$value = (string) $this->traverse($field['name'])->value();

		foreach (array_get($field, 'cases', array()) as $match => $status)
		{
			if ($value === (string) $match) return $status;
		}
	}

	public function media($args = array())
	{
		return $this->newQueryCollection(new WordpressQuery(wp_parse_args($args, array(
			'post_parent' => $this->getPrimaryKey(),
			'post_type' => 'attachment',
			'post_status' => 'inherit',
			'posts_per_page' => -1,
			'order' => 'ASC',
			'orderby' => 'meta_value_num',
			'meta_key' => '_media_position',
		))));
	}

	protected function newQueryCollection(WordpressQuery $query)
	{
		return new QueryCollection($query);
	}

	protected function cached($key)
	{
		if (isset($this->cached[$key]))
		{
			return $this->cached[$key];
		}
		else
		{
			return $this->cached[$key] = $this->evaluateForCache($this->{$key}());
		}
	}

	protected function evaluateForCache($data)
	{
		if ($data instanceof SearchBuilder)
		{
			return $data->go();
		}
		else
		{
			return $data;
		}
	}

	public function __get($key)
	{
		if (in_array($key, $this->cache) or $this->manager->hasMacro($key))
		{
			return $this->cached($key);
		}
		else
		{
			return parent::__get($key);
		}
	}

	public function __call($method, $parameters)
	{
		return $this->manager->call($this, $method, $parameters);
	}

	public function __sleep()
	{
		// A Post may be included in $wp_query->query_vars for it to be automatically
		// available in included templates. Disable serialization of this object to
		// prevent attempting to serialize the full object graph which would error out.
		return array();
	}
}
