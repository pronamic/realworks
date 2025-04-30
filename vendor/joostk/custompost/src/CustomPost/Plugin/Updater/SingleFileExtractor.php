<?php namespace CustomPost\Plugin\Updater;

class SingleFileExtractor implements ArchiveExtractorInterface
{
	public function extract($path)
	{
		return file_get_contents($path);
	}
}
