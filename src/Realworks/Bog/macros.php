<?php

return array(
	'isKoop' => function($object)
	{
		return $object->koop->prijs->hasValue();
	},

	'isHuur' => function($object)
	{
		return $object->huur->prijs->hasValue();
	},

	'prijs' => function($object)
	{
		return $object->isKoop() ? $object->koop->prijs : $object->huur->prijs;

	},

	'adresPlaats' => function($object)
	{
		return "{$object->adresNederlands->postcode}, {$object->adresNederlands->plaats}";
	},
);
