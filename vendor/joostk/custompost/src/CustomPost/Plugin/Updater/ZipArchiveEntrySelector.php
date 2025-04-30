<?php namespace CustomPost\Plugin\Updater;

interface ZipArchiveEntrySelector
{
	public function shouldRead($name);
}
