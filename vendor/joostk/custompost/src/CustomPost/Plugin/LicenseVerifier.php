<?php namespace CustomPost\Plugin;

use Exception;
use JoostK\Wordpress\Remote\RemoteInterface;

class LicenseVerifier
{
	protected $plugin;

	protected $remote;

	protected $option;

	public function __construct(Plugin $plugin, RemoteInterface $remote)
	{
		$this->plugin = $plugin;
		$this->remote = $remote;
		$this->option = '_'.$plugin->getIdentifier().'_license';
	}

	public function license()
	{
		return array_get($this->remembered(), 'license');
	}

	public function licensed()
	{
		return $this->status() === 'valid';
	}

	public function status()
	{
		$status = null;

		// If no transient available, data has expired.
		if (get_transient($this->option) === false)
		{
			$status = $this->verify($this->license());

			if ($status) set_transient($this->option, true, DAY_IN_SECONDS);
		}

		return $status ?: array_get($this->remembered(), 'status');
	}

	public function notice()
	{
		switch ($this->status())
		{
			case 'blocked': return 'De licentie voor %1$s is geblokkeerd.';
			case 'expired': return 'De licentie voor %1$s is verlopen.';
			case 'exceeded': return 'Het maximaal aantal activaties (' . array_get($this->remembered(), 'maxActivations', 'maximum onbekend') . ') voor %1$s is bereikt.';
			case 'empty': return '%1$s is nog niet geregistreerd, <a href="%2$s">voer een licentiecode in</a>.';
			default: return 'De licentie voor %1$s is incorrect, <a href="%2$s">voer een licentiecode in</a>.';
		}
	}

	public function verify($license)
	{
		if (empty($license))
		{
			delete_option($this->option);

			return 'empty';
		}

		try
		{
			$response = $this->request($license);
		}
		catch (Exception $e)
		{
			error_log((string) $e);

			return null;
		}

		return $response->isOk() ? $this->handleResponse($response, $license) : null;
	}

	protected function request($license)
	{
		return $this->remote->get($this->plugin->getPluginApiUrl().'&'.http_build_query(array(
			'action' => 'check_license',
			'license' => $license,
			'host' => site_url(),
		)));
	}

	protected function handleResponse($response, $license)
	{
		$data = json_decode($response->getBody(), true);

		if (isset($data['license']))
		{
			$maxActivations = array_get($data, 'max_activations');

			$this->remember($license, $data['license'], $maxActivations);

			return $data['license'];
		}
	}

	protected function remember($license, $status, $maxActivations)
	{
		if (in_array($status, array('valid', 'blocked', 'expired', 'exceeded')))
		{
			update_option($this->option, compact('license', 'status', 'maxActivations'));
		}
		else
		{
			delete_option($this->option);
		}
	}

	protected function remembered()
	{
		$license = get_option($obsolete = $this->plugin->getIdentifier().'_license_code');

		if ($license !== false)
		{
			$this->remember($license, 'valid');

			delete_option($obsolete);
		}

		return get_option($this->option, array(
			'license' => '',
			'status' => 'empty',
		));
	}
}
