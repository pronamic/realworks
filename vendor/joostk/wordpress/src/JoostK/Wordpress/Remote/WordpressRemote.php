<?php namespace JoostK\Wordpress\Remote;

use JoostK\Wordpress\Support\Error;

class WordpressRemote implements RemoteInterface
{
	protected $options = array(
		'retry.attempts' => 1,
		'retry.sleep' => 2,

		// Set long timeout by default as only cURL allows for setting specific
		// connection timeout, in which case this timeout is replaced by timeout.total
		'timeout' => 300,
		'timeout.connection' => 5,
		'timeout.total' => 0,
	);

	public function options(array $options)
	{
		$this->options = array_merge($this->options, $options);

		return $this;
	}

	public function get($url, array $options = array())
	{
		return $this->request($url, $options);
	}

	public function post($url, array $options = array())
	{
		return $this->request($url, $options, 'wp_remote_post');
	}

	public function request($url, array $options = array(), $callback = 'wp_remote_get')
	{
		$options = array_merge($this->options, $options);

		add_action('http_api_curl', array($this, 'curlImproveTimeout'), 10, 2);

		$response = call_user_func($callback, $url, $options);

		remove_action('http_api_curl', array($this, 'curlImproveTimeout'), 10);

		if (is_wp_error($response))
		{
			if ($options['retry.attempts'] <= 1)
			{
				Error::handle($response);
			}

			sleep($options['retry.sleep']);

			$options['retry.attempts']--;

			return $this->request($url, $options, $callback);
		}

		return new Response(
			wp_remote_retrieve_body($response),
			wp_remote_retrieve_response_code($response),
			wp_remote_retrieve_headers($response)
		);
	}

	public function curlImproveTimeout($handle, $args)
	{
		if (isset($args['timeout.connection']))
		{
			curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, $args['timeout.connection']);
			curl_setopt($handle, CURLOPT_TIMEOUT, $args['timeout.total']);
		}
	}
}
