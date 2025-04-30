<?php namespace JoostK\Wordpress\Support;

use JoostK\Illuminate\Container\Container;

class ProviderContainer extends Container
{
	protected $booted = false;

	protected $providers = array();

	protected $skipped = array();

	protected $replacements = array();

	public function boot()
	{
		if ( ! $this->booted)
		{
			foreach ($this->providers as $provider)
			{
				$provider->boot();
			}

			$this->booted = true;
		}
	}

	public function register($provider)
	{
		if (is_string($provider))
		{
			$provider = new $provider($this);
		}

		$provider = $this->replacement($provider);

		$class = get_class($provider);

		if ( ! isset($this->skipped[$class]))
		{
			$provider->register();

			if ($this->booted) $provider->boot();

			return $this->providers[$class] = $provider;
		}
	}

	protected function replacement($provider)
	{
		$class = get_class($provider);

		if (isset($this->replacements[$class]))
		{
			return $provider->replaceWith($this->replacements[$class]);
		}
		else
		{
			return $provider;
		}
	}

	public function skip($provider)
	{
		unset($this->providers[$provider]);

		$this->skipped[$provider] = true;
	}

	public function replace($provider, $replacement)
	{
		if (is_string($replacement))
		{
			if (isset($this->providers[$provider]))
			{
				$replacement = $this->providers[$provider]->replaceWith($replacement);

				$this->register($replacement);
			}
			else
			{
				$this->replacements[$provider] = $replacement;
			}
		}
		else
		{
			$this->register($replacement);
		}

		$this->skip($provider);
	}
}
