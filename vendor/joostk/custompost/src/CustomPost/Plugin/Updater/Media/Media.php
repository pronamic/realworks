<?php namespace CustomPost\Plugin\Updater\Media;

abstract class Media
{
	protected $type;

	protected $group;

	protected $url;

	protected $description;

	protected $modified;

	protected $position;

	protected $meta;

	public function __construct($type, $group, $url, $description, $modified, $position = null, $meta = array())
	{
		$this->type = $type;
		$this->group = $group;
		$this->url = $url;
		$this->description = $description;
		$this->modified = $modified;
		$this->position = (int) $position;
		$this->meta = $meta;
	}

	public function getGroup()
	{
		return $this->group;
	}

	public function getType()
	{
		return $this->type;
	}

	public function getUrl()
	{
		return $this->url;
	}

	public function getDescription()
	{
		return $this->description;
	}

	public function getPosition()
	{
		return $this->position;
	}

	public function getMeta()
	{
		return $this->meta;
	}

	public function getModified()
	{
		return $this->modified;
	}

	public function isGroup($group)
	{
		return $this->group == $group;
	}

	public function isType($type)
	{
		return $this->type == $type;
	}

	abstract public function isThumbnail();

	abstract public function isImage();

	public function getId()
	{
		$parts = parse_url($this->url);

		$path = trim(array_get($parts, 'path', ''), '/');
		$path = preg_replace('~\.\w+$~', '', $path);

		if (isset($parts['query']))
		{
			$path .= '?' . $parts['query'];
		}

		return str_replace('/', '-', $path);
	}

	public function getExtension()
	{
		$path = parse_url($this->url, PHP_URL_PATH);
		$extension = pathinfo($path, PATHINFO_EXTENSION);

		if ( ! empty($extension))
		{
			return ".${extension}";
		}
		elseif ($this->isImage())
		{
			return '.jpg';
		}
	}
}
