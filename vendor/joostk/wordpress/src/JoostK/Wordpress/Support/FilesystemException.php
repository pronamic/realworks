<?php namespace JoostK\Wordpress\Support;

use RuntimeException;

class FilesystemException extends RuntimeException
{
	protected $path;

	public function __construct($path)
	{
		$this->path = $path;
	}

	public function getPath()
	{
		return $this->path;
	}
}
