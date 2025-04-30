<?php namespace Realworks;

use CustomPost\Plugin\Plugin;

class Realworks extends Plugin
{
	protected $path = REALWORKS;

	protected $api = '749165200b70713fdca466?id=18';

	protected $identifier = 'realworks';

	protected $title = 'Makelaar plugin';

	public function start()
	{
		parent::start();

		$this->register(new RealworksServiceProvider($this));
	}

	public function getConfiguration()
	{
		return require __DIR__.'/config.php';
	}

	public function wonen($object = null)
	{
		return $object ? $this['wonen']->make($object) : $this['wonen'];
	}

	public function bog($object = null)
	{
		return $object ? $this['bog']->make($object) : $this['bog'];
	}
}
