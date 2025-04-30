<?php namespace JoostK\Wordpress\Remote;

class Response
{
	protected $body;

	protected $status;

	protected $headers;

	public function __construct($body, $status, $headers = array())
	{
		$this->body = $body;
		$this->status = (int) $status;
		$this->headers = $headers;
	}

	public function getBody()
	{
		return $this->body;
	}

	public function getStatus()
	{
		return $this->status;
	}

	public function getHeaders()
	{
		return $this->headers;
	}

	public function isOk()
	{
		return $this->status === 200;
	}
}
