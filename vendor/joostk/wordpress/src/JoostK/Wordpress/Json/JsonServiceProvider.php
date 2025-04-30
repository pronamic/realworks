<?php namespace JoostK\Wordpress\Json;

use JoostK\Wordpress\Support\ServiceProvider;

class JsonServiceProvider extends ServiceProvider
{
	public function register()
	{
		$this->registerEncoder();

		$this->registerDecoder();
	}

	protected function registerEncoder()
	{
		$this->app->singleton('json.encoder', function()
		{
			return defined('JSON_PRETTY_PRINT') ? new NativeJsonEncoder : new PrettyJsonEncoder;
		});
	}

	protected function registerDecoder()
	{
		$this->app->singleton('json.decoder', function()
		{
			return new FallbackLintDecoder;
		});
	}
}
