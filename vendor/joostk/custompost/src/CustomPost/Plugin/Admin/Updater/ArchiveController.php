<?php namespace CustomPost\Plugin\Admin\Updater;

use CustomPost\Plugin\Logging\FilesystemArchiveLogFinder;

class ArchiveController extends LogController
{
	public function __construct(FilesystemArchiveLogFinder $finder)
	{
		parent::__construct($finder);
	}
}
