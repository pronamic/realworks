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
);
