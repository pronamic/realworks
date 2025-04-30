<?php namespace CustomPost\Plugin\Admin\Updater;

use DateTime;
use JoostK\Wordpress\Admin\BaseController;
use CustomPost\Plugin\Logging\LogDateFinderInterface;

class DateController extends BaseController
{
	protected $finder;

	public function __construct(LogDateFinderInterface $finder)
	{
		$this->finder = $finder;
	}

	public function index()
	{
		return $this->finder->all() ?: $this->newDate(new DateTime);
	}

	protected function newDate(DateTime $date)
	{
		return array(
			'year' => $date->format('Y'),
			'month' => $date->format('m'),
			'logs' => 0,
		);
	}
}
