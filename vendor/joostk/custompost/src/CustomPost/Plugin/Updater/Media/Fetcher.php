<?php namespace CustomPost\Plugin\Updater\Media;

interface Fetcher
{
	public function fetch(Media $media, $path);
}
