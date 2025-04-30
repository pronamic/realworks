<?php namespace CustomPost\Plugin\Geo;

use Closure;
use RuntimeException;
use JoostK\Wordpress\Support\CoordinatesFetcher;
use CustomPost\Plugin\Updater\Logger\LoggerInterface;

class CoordinatesUpdater
{
	protected $fetcher;

	protected $logger;

	public function __construct(CoordinatesFetcher $fetcher, LoggerInterface $logger = null)
	{
		$this->fetcher = $fetcher;
		$this->logger = $logger;
	}

	public function update($model, $addresses, Closure $callback = null)
	{
		try
		{
			$this->log('info', 'Downloaden van Google Maps coördinaten.');

			if ($data = $this->fetcher->fetch($addresses))
			{
				$this->assignCoordinates($model, $data);

				if ($callback) $callback($model, $data);

				return true;
			}
			else
			{
				$this->log('info', 'Geen coördinaten bekend.');
			}
		}
		catch (RuntimeException $e)
		{
			$this->log('warning', $e->getMessage());
		}
	}

	protected function assignCoordinates($model, array $data)
	{
		$model->latitude = $data['latitude'];
		$model->longitude = $data['longitude'];
	}

	public function log($type, $message)
	{
		if ($this->logger)
		{
			$this->logger->{$type}($message);
		}
	}
}
