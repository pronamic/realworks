<?php namespace JoostK\Wordpress\View;

use Exception;
use InvalidArgumentException;

class View
{
	protected $basePath;

	public function __construct($basePath = null)
	{
		$this->basePath = $basePath;
	}

	public function setBasePath($basePath)
	{
		$this->basePath = $basePath;

		return $this;
	}

	public function load($view, array $data = array())
	{
		$path = $this->basePath.'/'.str_replace('.', '/', $view).'.php';

		if (file_exists($path))
		{
			return $this->render($path, $data);
		}
		else
		{
			throw new InvalidArgumentException("View [{$view}] does not exist at {$path}");
		}
	}

	protected function render($__path, array $__data = array())
	{
		ob_start();

		extract($__data, EXTR_SKIP);

		try
		{
			include $__path;
		}
		catch (Exception $e)
		{
			ob_end_clean();

			throw $e;
		}

		return ob_get_clean();
	}
}
