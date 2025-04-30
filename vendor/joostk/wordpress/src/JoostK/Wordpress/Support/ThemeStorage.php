<?php namespace JoostK\Wordpress\Support;

use RuntimeException;
use JoostK\Illuminate\Support\Str;

class ThemeStorage
{
	protected $path;

	public function __construct($subPath = null)
	{
		$this->setSubPath($subPath);
	}

	public function setPath($path)
	{
		$this->path = $path;

		return $this;
	}

	public function setSubPath($subPath)
	{
		return $this->setPath(get_stylesheet_directory().'/'.$subPath);
	}

	public function getBasePath()
	{
		return dirname($this->path);
	}

	public function getPath()
	{
		return $this->path;
	}

	public function load()
	{
		if (file_exists($this->path))
		{
			return $this->shouldInclude() ? require $this->path : file_get_contents($this->path);
		}
	}

	protected function shouldInclude()
	{
		return preg_match('/\.php$/', $this->path);
	}

	protected function store($data)
	{
		if (wp_mkdir_p($this->getBasePath()) and @file_put_contents($this->path, $data) !== false)
		{
			return true;
		}
		else
		{
			throw new FilesystemException($this->path);
		}
	}
}
