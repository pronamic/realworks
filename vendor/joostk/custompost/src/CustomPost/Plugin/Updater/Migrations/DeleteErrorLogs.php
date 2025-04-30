<?php namespace CustomPost\Plugin\Updater\Migrations;

use Exception;
use SplFileInfo;
use InvalidArgumentException;
use Symfony\Component\Finder\Finder;
use CustomPost\Plugin\Updater\PathResolver;

class DeleteErrorLogs
{
	protected $pathResolver;

	public function __construct(PathResolver $pathResolver)
	{
		$this->pathResolver = $pathResolver;
	}

	public function deleteErrorLogs()
	{
		foreach ($this->files() as $file)
		{
			$this->attemptDelete($file);
		}
	}

	protected function attemptDelete(SplFileInfo $file)
	{
		@unlink($file->getRealPath());
	}

	protected function files()
	{
		try
		{
			// Only search in numeric folders as those represent the years. Doing
			// so avoids scanning potentially very large media folders that may
			// also be located under the base path, but won't contain any error logs.
			return Finder::create()->files()->in($this->pathResolver->getBasePath() . '/[0-9]*')->name('*.phplog')->depth('== 2');
		}
		catch (InvalidArgumentException $e)
		{
			return array();
		}
	}
}
