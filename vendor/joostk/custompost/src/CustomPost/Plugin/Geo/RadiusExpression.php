<?php namespace CustomPost\Plugin\Geo;

use CustomPost\Search\Expressions\Binary;

class RadiusExpression extends Binary
{
	protected $latitude;

	protected $longitude;

	public function __construct($radius, $latitude, $longitude)
	{
		parent::__construct('radius', '<=', (int) $radius);

		$this->latitude = $latitude;
		$this->longitude = $longitude;
	}

	public function coordinates()
	{
		if ($this->latitude and $this->longitude) return array(
			'latitude' => $this->latitude,
			'longitude' => $this->longitude,
		);
	}
}
