<?php namespace CustomPost\Plugin\Logging;

use Symfony\Component\Finder\Finder;

class FilesystemArchiveLogFinder extends AbstractFilesystemLogFinder
{
	protected function find($year, $month)
	{
		$path = "{$this->path}/{$year}/{$month}";

		if ( ! file_exists($path)) return array();

		return Finder::create()->files()->name('*.log')->in($path)->depth('== 3');
	}

	protected function newLog($path, $data)
	{
		return new Models\ArchiveLog($path, $data);
	}
}
