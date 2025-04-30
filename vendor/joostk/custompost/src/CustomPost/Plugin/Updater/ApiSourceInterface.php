<?php namespace CustomPost\Plugin\Updater;

interface ApiSourceInterface extends InputSourceInterface
{
	public function setIdentifier($identifier);

	public function setCredentials($user, $password);

	public function setPath($path);
}
