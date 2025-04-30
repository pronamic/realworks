<?php namespace Realworks\Updater;

use RuntimeException;
use JoostK\Wordpress\Remote\Response;
use JoostK\Wordpress\Remote\RemoteInterface;
use CustomPost\Plugin\Updater\ApiSourceInterface;
use CustomPost\Plugin\Updater\UpdateNotAllowedException;

class RealworksApiSource implements ApiSourceInterface
{
	const DEFAULT_KOPPELING = 'WEBSITE';
	const URL = 'https://xml-publish.realworks.nl/servlets/ogexport';

	protected $remote;

	protected $koppeling = self::DEFAULT_KOPPELING;

	protected $user;

	protected $password;

	protected $offices;

	protected $identifier;

	protected $path;

	public function __construct(RemoteInterface $remote)
	{
		$this->remote = $remote;
	}

	public function setIdentifier($identifier)
	{
		$this->identifier = $identifier;

		return $this;
	}

	public function setPath($path)
	{
		$this->path = $path.'/archive.zip';

		return $this;
	}

	public function setCredentials($user, $password)
	{
		$this->user = $user;
		$this->password = $password;

		return $this;
	}

	public function setOffices($offices)
	{
		$this->offices = str_replace(' ', '', $offices);

		return $this;
	}

	public function setKoppeling($koppeling)
	{
		$this->koppeling = $koppeling ?: static::DEFAULT_KOPPELING;

		return $this;
	}

	public function getArchivePath()
	{
		if ( ! $this->hasExistingArchive())
		{
			if ( ! $this->isUpdateAllowed())
			{
				throw new UpdateNotAllowedException('Realworks staat het niet toe om bij te werken voor 8:30.');
			}

			$this->downloadArchive();
		}

		return $this->path;
	}

	public function downloadArchive()
	{
		$response = $this->requestArchive();

		if ( ! $response->isOk())
		{
			return $this->failWithRealworksResponse($response);
		}

		if ($response->getBody())
		{
			$this->writeData($response->getBody());
		}
	}

	protected function requestArchive()
	{
		try
		{
			return $this->remote->get($this->getDownloadUrl(), array(
				'timeout.connection' => 300,
				'stream' => true,
				'filename' => $this->path,
			));
		}
		catch (RuntimeException $e)
		{
			$this->readAndDeleteFailedArchive();

			return $this->remote->get($this->getDownloadUrl(), array(
				'timeout.connection' => 300,
			));
		}
	}

	protected function failWithRealworksResponse(Response $response)
	{
		$body = $response->getBody() ?: $this->readAndDeleteFailedArchive();

		preg_match('~<h1>(.*?)</h1>~', $body, $matches);

		$message = isset($matches[1]) ? $matches[1] : 'HTTP Status ' . $response->getStatus();

		throw new RuntimeException("Realworks gaf een foutmelding tijdens het downloaden van data:\n{$message}\n\nURL: {$this->getDownloadUrl()}");
	}

	protected function readAndDeleteFailedArchive()
	{
		if (file_exists($this->path))
		{
			$body = file_get_contents($this->path);

			unlink($this->path);

			return $body;
		}
	}

	protected function writeData($data)
	{
		if (file_put_contents($this->path, $data) === false)
		{
			throw new RuntimeException("Gedownloade data kon niet worden opgeslagen in [{$this->path}]");
		}
	}

	protected function getDownloadUrl()
	{
		$query = array_filter(array(
			'koppeling' => $this->koppeling,
			'versie' => $this->getFeedVersion(),
			'og' => strtoupper($this->identifier),
			'user' => $this->user,
			'password' => $this->password,
			'kantoor' => $this->offices,
		));

		return static::URL . '?' . http_build_query($query);
	}

	protected function getFeedVersion()
	{
		switch ($this->identifier)
		{
			case 'bog': return 20;
		}
	}

	protected function hasExistingArchive()
	{
		return file_exists($this->path);
	}

	protected function isUpdateAllowed()
	{
		$time = $this->time();
		$hour = intval(date('H', $time));
		$minute = intval(date('i', $time));

		return $hour >= 9 or ($hour === 8 and $minute >= 30);
	}

	protected function time()
	{
		return current_time('timestamp');
	}
}
