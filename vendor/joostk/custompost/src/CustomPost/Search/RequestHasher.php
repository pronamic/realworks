<?php namespace CustomPost\Search;

class RequestHasher
{
	public function hash(array $data)
	{
		$filtered = $this->filter($data);

		return $this->toUrlFormat(
			base64_encode(gzdeflate(json_encode($filtered)))
		);
	}

	public function decode($hash)
	{
		$hash = $this->fromUrlFormat($hash);

		$data = @json_decode(gzinflate(base64_decode($hash)), true);

		return is_array($data) ? $data : array();
	}

	protected function toUrlFormat($hash)
	{
		return rtrim(strtr($hash, '+/', '-_'), '=');
	}

	protected function fromUrlFormat($hash)
	{
		return str_pad(strtr($hash, '-_', '+/'), strlen($hash) % 4, '=', STR_PAD_RIGHT);
	}

	protected function filter(array $data)
	{
		foreach ($data as $key => &$value)
		{
			if (is_array($value))
			{
				$value = $this->filter($value);
			}

			// Only actual search values should be present in the hash, so filter out internal keys
			if (starts_with($key, '__'))
			{
				$value = null;
			}
		}

		return array_filter($data);
	}
}
