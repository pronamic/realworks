<?php namespace CustomPost\Plugin\Logging;

interface LogFinderInterface
{
	public function all($year, $month);

	public function get($path);

	public function destroy($path);
}
