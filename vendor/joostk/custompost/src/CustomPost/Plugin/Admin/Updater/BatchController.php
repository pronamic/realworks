<?php namespace CustomPost\Plugin\Admin\Updater;

use CustomPost\Plugin\Logging\FilesystemBatchLogFinder;

class BatchController extends LogController
{
	public function __construct(FilesystemBatchLogFinder $finder)
	{
		parent::__construct($finder);
	}
}
