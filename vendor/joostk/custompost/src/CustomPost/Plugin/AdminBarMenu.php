<?php namespace CustomPost\Plugin;

use CustomPost\Entity;

class AdminBarMenu
{
	protected $plugin;

	protected $position = 1;

	protected function __construct($plugin)
	{
		$this->plugin = $plugin;
	}

	public static function plugin(Plugin $plugin)
	{
		$menu = new static($plugin);

		$menu->addMenu(str_replace(' Plugin', '', $plugin->getTitle()));

		return $menu;
	}

	public function addEntities()
	{
		foreach ($this->plugin->entities() as $id => $entity)
		{
			if ( ! $entity->isEnabled() or ! $entity->isRoot()) continue;

			$parent = $this->id($id);
			$title = array_get($entity->labels(), 'title');

			$this->addItem($title, "{$id}/settings", array(
				'id' => $parent,
			));

			$this->addItem('Instellingen', "{$id}/settings", compact('parent'));
			$this->addItem('Zoekvelden', "{$id}/search", compact('parent'));
		}

		return $this;
	}

	protected function addMenu($title)
	{
		$me = $this;

		add_action('admin_bar_menu', function($menu) use ($me, $title)
		{
			$menu->add_menu(array(
				'id' => $me->id(),
				'title' => $title,
				'href' => $me->url('dashboard'),
			));
		}, 100);
	}

	public function addItem($title, $hash, array $data = array())
	{
		$me = $this;

		add_action('admin_bar_menu', function($menu) use ($me, $title, $hash, $data)
		{
			$menu->add_node(array_merge(array(
				'id' => $me->id($hash),
				'parent' => $me->id(),
				'title' => $title,
				'href' => $me->url($hash),
			), $data));
		}, $this->position++);

		return $this;
	}

	public function url($hash)
	{
		return $this->plugin->adminUrl($hash);
	}

	public function id($link = '')
	{
		$link = str_replace('/', '-', $link);

		return trim("{$this->plugin->getIdentifier()}-{$link}", '-');
	}
}
