<?php namespace CustomPost;

use CustomPost\Database\Post;
use JoostK\Illuminate\Container\Container;
use JoostK\Wordpress\Support\ProviderContainer;

abstract class Entity extends ProviderContainer
{
	protected $identifier;

	protected $currentPost;

	protected $enabled = true;

	protected $children = array();

	protected $parent;

	public function getIdentifier()
	{
		return $this->identifier;
	}

	abstract public function labels();

	abstract public function getConfiguration();

	/**
	 * Returns an array of field definitions
	 *
	 * @return array
	 */
	abstract public function getFieldDefinitions();

	public function getMacros()
	{
		return array();
	}

	public function start()
	{
		$this->register(new Migrations\UpdateStatusConfig($this));
		$this->register(new Support\SupportServiceProvider($this));
		$this->register(new Providers\ConfigServiceProvider($this));
		$this->register(new Providers\EventsServiceProvider($this));
		$this->register(new Providers\WordpressServiceProvider($this));
		$this->register(new Providers\JsonServiceProvider($this));
		$this->register(new Providers\MacrosServiceProvider($this));
		$this->register(new Database\DatabaseServiceProvider($this));
		$this->register(new Fields\FieldsServiceProvider($this));
		$this->register(new Search\SearchServiceProvider($this));
	}

	public function setCurrentPost(Post $post = null)
	{
		$global = $this['config']['post.global'];

		if ( ! isset($GLOBALS[$global]) or $GLOBALS[$global] === $this->currentPost)
		{
			$GLOBALS[$global] = $post;
		}

		$this->currentPost = $post;
	}

	public function getCurrentPost()
	{
		return $this->currentPost;
	}

	public function addChild(Entity $child)
	{
		$this->children[$child->getIdentifier()] = $child;

		$child->setParent($this);

		return $this;
	}

	public function children()
	{
		return $this->children;
	}

	public function setParent(Entity $parent)
	{
		$this->parent = $parent;

		return $this;
	}

	public function parent()
	{
		return $this->parent;
	}

	public function isRoot()
	{
		return $this->parent === null;
	}

	public function setEnabled($enabled)
	{
		$this->enabled = $enabled;

		foreach ($this->children as $child)
		{
			$child->setEnabled($enabled);
		}

		return $this;
	}

	public function isEnabled()
	{
		return $this->enabled;
	}
}
