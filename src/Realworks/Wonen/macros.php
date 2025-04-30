<?php

return array(
	'isKoop' => function($woning)
	{
		return $woning->koopprijs->hasValue();
	},

	'isHuur' => function($woning)
	{
		return $woning->huurprijs->hasValue();
	},

	'prijs' => function($woning)
	{
		return $woning->isKoop() ? $woning->koopprijs : $woning->huurprijs;
	},

	'adresPlaats' => function($woning)
	{
		if ($woning->adresNederlands->hasData())
		{
			return "{$woning->adresNederlands->postcode}, {$woning->adresNederlands->plaats}";
		}
		else
		{
			return trim("{$woning->adresInternationaal->adresregel1} {$woning->adresInternationaal->plaats}, {$woning->adresInternationaal->land}");
		}
	},
);
