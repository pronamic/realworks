<?php namespace CustomPost\Plugin\Updater\Logger;

use RuntimeException;
use JoostK\Wordpress\Json\JsonEncoderInterface;

class JsonLogger implements LoggerInterface
{
	protected $encoder;

	protected $basePath;

	protected $path;

	protected $written = false;

	protected $completed = false;

	protected $status = Status::SUCCESS;

	protected $archive;

	protected $messages = array();

	protected $id = 1;

	public function __construct(JsonEncoderInterface $encoder)
	{
		$this->encoder = $encoder;

		$this->enforceClosing();
	}

	protected function enforceClosing()
	{
		register_shutdown_function(array($this, 'enforcedClose'));
	}

	public function enforcedClose()
	{
		// Don't bother closing when we have already completed or when it has never been written yet
		if ( ! $this->completed and $this->written)
		{
			$this->close();
		}
	}

	public function setPaths($basePath, $path)
	{
		$this->basePath = rtrim($basePath, DIRECTORY_SEPARATOR).'/';
		$this->path = $path;

		return $this;
	}

	public function getId()
	{
		// The ID is invalid unless we have written the log
		if ( ! $this->written) $this->write();

		return $this->path;
	}

	public function data()
	{
		return array(
			'date' => date('Y-m-d', $this->time()),
			'completed' => $this->completed,
			'status' => $this->status,
			'archive' => $this->archive,
			'messages' => $this->messages,
		);
	}

	public function close()
	{
		$this->completed = true;

		$this->write();
	}

	public function write()
	{
		if (@file_put_contents($this->basePath.$this->path, $this->encoder->encode($this->data())) === false)
		{
			throw new RuntimeException("Logbestand kan niet worden geschreven in [{$this->basePath}{$this->path}]");
		}

		$this->written = true;
	}

	public function setStatus($status)
	{
		$allowed = array(Status::SUCCESS);

		switch ($status)
		{
			case Status::ERROR:
				$allowed[] = Status::WARNING;
		}

		if (in_array($this->status, $allowed))
		{
			$this->status = $status;
		}
	}

	public function trackChanges($changed, $force = true)
	{
		// No tracking done
	}

	public function setArchive($archive)
	{
		$this->archive = str_replace($this->basePath, '', $archive);
	}

	public function log($message)
	{
		$this->append(__FUNCTION__, compact('message'));
	}

	public function info($message)
	{
		$this->append(__FUNCTION__, compact('message'));
	}

	public function warning($message)
	{
		$this->setStatus(Status::WARNING);

		$this->append(__FUNCTION__, compact('message'));
	}

	public function error($message)
	{
		$this->setStatus(Status::ERROR);

		$this->append(__FUNCTION__, compact('message'));
	}

	public function object($model, $state)
	{
		$this->append('object', array(
			'state' => $state,
			'id' => $model->primary()->render(),
			'wpid' => (int) $model->getPrimaryKey(),
		));
	}

	public function changes(array $changes)
	{
		$this->append('changes', compact('changes'));
	}

	public function archive($path)
	{
		$this->append('archive', compact('path'));
	}

	public function pop()
	{
		array_pop($this->messages);

		return $this;
	}

	protected function append($type, array $data)
	{
		$this->messages[] = array_merge($data, array(
			'mid' => $this->id++,
			'type' => $type,
			'time' => date('H:i:s', $this->time()),
		));

		$this->write();
	}

	protected function time()
	{
		return current_time('timestamp');
	}
}
