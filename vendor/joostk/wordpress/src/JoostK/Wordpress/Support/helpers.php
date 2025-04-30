<?php

if ( ! function_exists('array_diff_assoc_recursive'))
{
	function array_diff_assoc_recursive($array1, $array2)
	{
		$difference = array();

		foreach ($array1 as $key => $value)
		{
			if (is_array($value))
			{
				if (isset($array2[$key]) and is_array($array2[$key]))
				{
					$diff = array_diff_assoc_recursive($value, $array2[$key]);

					if (count($diff) > 0)
					{
						$difference[$key] = array_keys($value) === range(0, count($value) - 1) ? $value : $diff;
					}
				}
				else
				{
					$difference[$key] = $value;
				}
			}
			elseif ( ! array_key_exists($key, $array2) or $array2[$key] !== $value)
			{
				$difference[$key] = $value;
			}
		}

		return $difference;
	}
}
