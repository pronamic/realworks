<?php namespace CustomPost\Plugin\Updater\Sources;

use RuntimeException;
use CustomPost\Plugin\Updater\InputSourceInterface;

class FilesystemSource implements InputSourceInterface
{
	protected $path;

	public function __construct($path)
	{
		$this->path = $path;
	}

	public function getArchivePath()
	{
		if (file_exists($this->path))
		{
			return $this->path;
		}
		else
		{
			throw new RuntimeException("Geen data beschikbaar in [{$path}]");
		}
	}
}
