<?php namespace CustomPost\Plugin\Updater\Sources;

use RuntimeException;
use CustomPost\Plugin\Updater\InputSourceInterface;

class PhpInputSource implements InputSourceInterface
{
	protected $path;

	public function setPath($path)
	{
		$this->path = $path;

		return $this;
	}

	public function getArchivePath()
	{
		$data = file_get_contents('php://input');

		if (trim($data) === '')
		{
			throw new RuntimeException("Geen data beschikbaar in request.");
		}

		$this->writeData($data);

		return $this->path;
	}

	protected function writeData($data)
	{
		if (file_put_contents($this->path, $data) === false)
		{
			throw new RuntimeException("Data kon niet worden opgeslagen in [{$this->path}]");
		}
	}
}
