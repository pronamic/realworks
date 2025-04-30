<?php namespace JoostK\Wordpress\Admin;

use JoostK\Illuminate\Container\Container;

class BaseController
{
	protected $app;

	public static function to($method)
	{
		return array(get_called_class(), $method);
	}

	public function setApp(Container $app)
	{
		$this->app = $app;

		return $this;
	}

	protected function error($code, $description = '')
	{
		header("X-PHP-Response-Code: {$code}", true, $code);

		return array(
			'error' => $code,
			'description' => $description,
		);
	}

	protected function each(array $items, $callback)
	{
		return array_map($callback, $items);
	}
}
