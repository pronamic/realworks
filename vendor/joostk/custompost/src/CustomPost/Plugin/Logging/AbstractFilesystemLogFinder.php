<?php namespace CustomPost\Plugin\Logging;

use Symfony\Component\Finder\Finder;

abstract class AbstractFilesystemLogFinder implements LogFinderInterface
{
	protected $path;

	public function setBasePath($path)
	{
		$this->path = $path;

		return $this;
	}

	public function all($year, $month)
	{
		$logs = array();
		$month = str_pad($month, 2, '0', STR_PAD_LEFT);

		foreach ($this->find($year, $month) as $file)
		{
			$logs[] = $this->get($file->getPathname());
		}

		return $logs;
	}

	abstract protected function find($year, $month);

	public function get($path)
	{
		if (file_exists($path) or file_exists($path = $this->path.'/'.$path))
		{
			$data = json_decode(file_get_contents($path));

			$path = trim(str_replace(array($this->path, DIRECTORY_SEPARATOR), array('', '/'), $path), '/');

			if (isset($data->archive))
			{
				$data->archive = $this->path.'/'.$data->archive;
			}

			return $this->newLog($path, $data);
		}
	}

	abstract protected function newLog($path, $data);

	public function destroy($path)
	{
		if (file_exists($path = $this->path.'/'.$path))
		{
			return unlink($path);
		}
	}
}
