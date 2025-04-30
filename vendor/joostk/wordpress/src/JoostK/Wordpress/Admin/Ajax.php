<?php namespace JoostK\Wordpress\Admin;

use Exception;
use ErrorException;
use ReflectionMethod;
use JoostK\Illuminate\Container\Container;
use JoostK\Wordpress\TerminateException;

class Ajax
{
	protected $app;

	protected $nonce = 'ajax_nonce';

	public function __construct(Container $app)
	{
		$this->app = $app;
	}

	public function setNonceAction($action)
	{
		$this->nonce = $action;

		return $this;
	}

	public function route($route, $callback)
	{
		$me = $this;

		add_action("wp_ajax_{$route}", function() use ($me, $callback)
		{
			$me->handleRequest($callback);
		});
	}

	public function handleRequest($callback)
	{
		// $this->setupErrorHandling();

		$this->verifyNonce();
		$this->verifyUser();

		$response = $this->dispatch($callback);

		$this->sendResponse($response);
	}

	protected function setupErrorHandling()
	{
		set_error_handler(function($errno, $errstr, $errfile, $errline)
		{
			throw new ErrorException($errstr, $errno, $errno, $errfile, $errline);
		});
	}

	protected function verifyNonce()
	{
		if ( ! isset($_REQUEST['nonce']) or
		     ! wp_verify_nonce($_REQUEST['nonce'], $this->nonce))
		{
			$this->error(403);
		}
	}

	protected function verifyUser()
	{
		// TODO: look into user capabilities
		if ( ! current_user_can('publish_posts'))
		{
			$this->error(403);
		}
	}

	protected function dispatch($callback)
	{
		try
		{
			list($class, $method) = $callback;

			$controller = $this->app->make($class)->setApp($this->app);
			$parameters = $this->parameters($controller, $method);

			return call_user_func_array(array($controller, $method), $parameters);
		}
		catch (TerminateException $e)
		{
			throw $e;
		}
		catch (Exception $e)
		{
			error_log("AJAX Unhandled {$e}");

			return $this->error(500, $e->getMessage());
		}
	}

	protected function parameters($controller, $method)
	{
		$reflected = new ReflectionMethod($controller, $method);
		$parameters = array();

		foreach ($reflected->getParameters() as $param)
		{
			$parameters[$param->getName()] = $this->fetchParameter($param);
		}

		return $parameters;
	}

	protected function fetchParameter($param)
	{
		$name = $param->getName();

		if ( ! isset($_REQUEST[$name]))
		{
			if ($param->isDefaultValueAvailable()) return $param->getDefaultValue();

			$this->error(422, "Missing input data: {$name}");
		}

		return stripslashes_deep($_REQUEST[$name]);
	}

	protected function sendResponse($response)
	{
		$this->header('Content-Type: application/json');

		echo json_encode($response);

		$this->terminate();
	}

	protected function terminate()
	{
		exit;
	}

	protected function header($header, $replace = true, $code = 0)
	{
		header($header, $replace, $code);
	}

	protected function error($code, $description = 'Invalid AJAX Request')
	{
		$this->header("X-PHP-Response-Code: {$code}", true, $code);

		$this->sendResponse(array(
			'error' => $code,
			'description' => $description,
		));
	}
}
