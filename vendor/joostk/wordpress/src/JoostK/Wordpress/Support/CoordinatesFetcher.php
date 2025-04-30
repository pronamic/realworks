<?php namespace JoostK\Wordpress\Support;

use RuntimeException;
use JoostK\Wordpress\Remote\RemoteInterface;

class CoordinatesFetcher
{
	const URL = 'https://maps.googleapis.com/maps/api/geocode/json';

	protected $remote;

	protected $apiKey;

	protected $limited = false;

	public function __construct(RemoteInterface $remote, $apiKey = null)
	{
		$this->remote = $remote;
		$this->apiKey = $apiKey;
	}

	public function fetch($addresses)
	{
		foreach ((array) $addresses as $address)
		{
			if ($result = $this->fetchAddress($address)) return $result;
		}
	}

	protected function fetchAddress($address, $first = true)
	{
		if ($this->limited)
		{
			throw new RuntimeException("De locatie van [{$address}] kon niet worden bepaald: OVER_QUERY_LIMIT.");
		}

		$json = $this->requestData($address);

		switch ($json->status)
		{
			case 'OK':
				return $this->parseCoordinates($json);
			case 'ZERO_RESULTS':
				return;
			case 'OVER_QUERY_LIMIT':
				if ($first)
				{
					$this->wait();

					return $this->fetchAddress($address, false);
				}

				$this->limited = true;

				/* Fall-through intended */
			default:
				throw new RuntimeException("De locatie van [{$address}] kon niet worden bepaald: {$json->status}.");
		}
	}

	protected function wait()
	{
		sleep(2);
	}

	protected function requestData($address)
	{
		$url = static::URL.'?'.http_build_query(array_filter(array('address' => $address, 'language' => 'nl', 'key' => $this->apiKey)));

		$response = $this->remote->get($url);

		if ( ! $response->isOk())
		{
			throw new RuntimeException("De locatie van [{$address}] kon niet worden bepaald: HTTP Status {$response->getStatus()}.");
		}

		return json_decode($response->getBody());
	}

	protected function parseCoordinates($json)
	{
		$latitude = data_get($json, 'results.0.geometry.location.lat');
		$longitude = data_get($json, 'results.0.geometry.location.lng');
		$address = array();

		foreach (data_get($json, 'results.0.address_components', array()) as $component)
		{
			if (isset($component->types[0], $component->long_name))
			{
				$address[$component->types[0]] = $component->long_name;
			}
		}

		return compact('latitude', 'longitude', 'address');
	}
}
