<?php namespace CustomPost\Providers;

use CustomPost\ServiceProvider;
use JoostK\Wordpress\Json\NativeJsonEncoder;
use JoostK\Wordpress\Json\PrettyJsonEncoder;
use JoostK\Wordpress\Json\FallbackLintDecoder;

class JsonServiceProvider extends ServiceProvider
{
	public function register()
	{
		$this->registerEncoder();

		$this->registerDecoder();
	}

	protected function registerEncoder()
	{
		$this->entity->singleton('json.encoder', function()
		{
			return defined('JSON_PRETTY_PRINT') ? new NativeJsonEncoder : new PrettyJsonEncoder;
		});
	}

	protected function registerDecoder()
	{
		$this->entity->singleton('json.decoder', function()
		{
			return new FallbackLintDecoder;
		});
	}
}
