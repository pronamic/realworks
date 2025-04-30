<?php namespace CustomPost\Plugin\Updater\Media;

use CustomPost\Database\Post;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class Deleter
{
	protected $basePath;

	public function __construct($basePath)
	{
		$this->basePath = $basePath;
	}

	public function deleteMedia(Post $post)
	{
		foreach ($post->media() as $media)
		{
			wp_delete_post($media->ID, true);
		}

		if (is_dir($dir = "{$this->basePath}/{$post->primary()}"))
		{
			$this->deleteDirectory($dir);
		}
	}

	protected function deleteDirectory($dir)
	{
		$it = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
		$files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);

		foreach ($files as $file)
		{
			$file->isDir() ? rmdir($file->getRealPath()) : unlink($file->getRealPath());
		}

		rmdir($dir);
	}
}
