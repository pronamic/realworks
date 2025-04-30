<?php namespace CustomPost\Plugin\Updater\Media;

use RuntimeException;
use JoostK\Wordpress\Remote\RemoteInterface;

class HttpFetcher implements Fetcher
{
	protected $remote;

	public function __construct(RemoteInterface $remote)
	{
		$this->remote = $remote;
	}

	public function fetch(Media $media, $path)
	{
		$response = $this->download($media, $path);

		if ( ! $response->isOk())
		{
			$this->deleteMedia($path);

			throw new RuntimeException("Download mislukt vanwege HTTP status {$response->getStatus()}.");
		}

		if ($response->getBody())
		{
			$this->writeData($media, $path, $response->getBody());
		}
	}

	protected function download(Media $media, $path)
	{
		try
		{
			return $this->remote->get($media->getUrl(), array(
				'stream' => true,
				'filename' => $path,
			));
		}
		catch (RuntimeException $e)
		{
			return $this->remote->get($media->getUrl());
		}
	}

	protected function writeData(Media $media, $path, $data)
	{
		if (file_put_contents($path, $data) === false)
		{
			throw new RuntimeException("Data kon niet worden opgeslagen in [{$path}].");
		}

		chmod($path, 0666);
	}

	protected function deleteMedia($path)
	{
		if (file_exists($path))
		{
			unlink($path);
		}
	}
}
