<?php namespace CustomPost\Plugin\Logging;

use Symfony\Component\Finder\Finder;

class FilesystemBatchLogFinder extends AbstractFilesystemLogFinder
{
	protected function find($year, $month)
	{
		$path = "{$this->path}/{$year}/{$month}";

		if ( ! file_exists($path)) return array();

		return Finder::create()->files()->name('*.log')->in($path)->depth('== 1');
	}

	protected function newLog($path, $data)
	{
		return new Models\BatchLog($path, $data);
	}

	public function destroy($path)
	{
		$log = $this->get($path);

		if ( ! $log) return;

		foreach ($log->messages as $message)
		{
			if ($message->type === 'archive')
			{
				@unlink($this->path.'/'.$message->path);
			}
		}

		return parent::destroy($path);
	}
}
