<?php namespace CustomPost\Plugin\Admin\Updater;

use Exception;
use JoostK\Wordpress\Admin\BaseController;
use CustomPost\Plugin\Logging\LogFinderInterface;

class LogController extends BaseController
{
	protected $finder;

	public function __construct(LogFinderInterface $finder)
	{
		$this->finder = $finder;
	}

	public function index($year, $month)
	{
		$logs = $this->finder->all($year, $month);

		return $this->each($logs, function($log)
		{
			return $log->summary();
		});
	}

	public function show($path)
	{
		$log = $this->finder->get($path);

		if ( ! $log) return $this->error(404);

		return $log->complete();
	}

	public function destroy($path)
	{
		$result = $this->finder->destroy($path);

		return compact('result');
	}

	public function retry($path)
	{
		$log = $this->finder->get($path);

		if ( ! $log) return $this->error(404);

		if ( ! $log->archive)
		{
			throw new Exception("Data niet meer beschikbaar!");
		}

		$this->app['updater.retry']->background()->retry($log);

		exit;
	}
}
