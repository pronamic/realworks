<?php namespace JoostK\Wordpress\Remote;

interface RemoteInterface
{
	public function get($url, array $options = array());

	public function post($url, array $options = array());
}
