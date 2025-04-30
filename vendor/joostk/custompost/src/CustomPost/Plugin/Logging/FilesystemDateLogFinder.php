<?php namespace CustomPost\Plugin\Logging;

use SplFileInfo;
use InvalidArgumentException;
use Symfony\Component\Finder\Finder;

class FilesystemDateLogFinder implements LogDateFinderInterface
{
	protected $path;

	public function setBasePath($path)
	{
		$this->path = $path;

		return $this;
	}

	public function latest()
	{
		$latest = null;

		foreach ($this->files() as $file)
		{
			if ($latest === null or $file->getMTime() > $latest->getMTime())
			{
				$latest = $file;
			}
		}

		return $latest ? $latest->getRealPath() : null;
	}

	public function all()
	{
		$dates = array();

		foreach ($this->files() as $file)
		{
			list($year, $month) = $this->extractDate($file);

			$dates["{$year}/{$month}"] = array(
				'id' => "{$year}/{$month}",
				'year' => $year,
				'month' => $month,
				'logs' => array_get($dates, "{$year}/{$month}.logs", 0) + 1,
			);
		}

		return array_values($dates);
	}

	protected function files()
	{
		try
		{
			// Only search in numeric folders as those represent the years. Doing
			// so avoids scanning potentially very large media folders that may
			// also be located under the base path.
			return Finder::create()->files()->in($this->path . '/[0-9]*')->name('*.log')->depth('== 2');
		}
		catch (InvalidArgumentException $e)
		{
			return array();
		}
	}

	protected function extractDate(SplFileInfo $file)
	{
		list($log, $day, $month, $year) = array_reverse(explode(DIRECTORY_SEPARATOR, $file->getRealPath()));

		return array($year, $month, $day, $log);
	}
}
