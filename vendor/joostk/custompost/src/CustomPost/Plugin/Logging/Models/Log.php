<?php namespace CustomPost\Plugin\Logging\Models;

use CustomPost\Plugin\Logging\LogFinderInterface;

class Log
{
	protected static $finder;

	public $path;

	public $date;

	public $completed;

	public $status;

	public $archive;

	public $messages;

	public function __construct($path, $data)
	{
		$this->path = $path;
		$this->date = $data->date;
		$this->completed = $data->completed;
		$this->status = $data->status;
		$this->archive = $this->verifyArchive($data);
		$this->messages = $data->messages;
	}

	public static function setFinder(LogFinderInterface $finder)
	{
		static::$finder = $finder;
	}

	protected function verifyArchive($data)
	{
		return isset($data->archive) && file_exists($data->archive) ? $data->archive : null;
	}

	protected function time($message)
	{
		return $message ? $message->time : date('H:i:s', current_time('timestamp'));
	}

	public function startTime()
	{
		return $this->time(reset($this->messages));
	}

	public function endTime()
	{
		if ( ! $this->completed) return $this->time(null);

		return $this->time(end($this->messages));
	}

	public function duration()
	{
		$start = strtotime("{$this->date} {$this->startTime()}");
		$end = strtotime("{$this->date} {$this->endTime()}");

		return $end - $start;
	}

	public function summary()
	{
		return array(
			'path' => $this->path,
			'completed' => $this->completed,
			'status' => $this->status,
			'archive' => $this->archive,
			'date' => $this->date,
			'start' => $this->startTime(),
			'end' => $this->endTime(),
			'duration' => $this->duration(),
		);
	}

	public function complete()
	{
		$messages = array();

		foreach ($this->messages as $message)
		{
			$messages[] = $this->readMessage($message);
		}

		return $this->summary() + array(
			'messages' => array_values(array_filter($messages)),
		);
	}

	protected function readMessage($message)
	{
		switch ($message->type)
		{
			case 'object': return $this->annotateObject($message);
			case 'archive': return $this->readArchive($message);
			default: return $message;
		}
	}

	protected function annotateObject($message)
	{
		if ($post = get_post($message->wpid))
		{
			$message->title = $post->post_title;
			$message->url = get_permalink($post->ID);
			$message->admin_url = get_edit_post_link($post->ID, '');
		}

		return $message;
	}

	protected function readArchive($message)
	{
		$log = static::$finder->get($message->path);

		if ( ! $log) return null;

		$message->time = $log->startTime();
		$message->status = $log->status;
		$message->completed = $log->completed;
		$message->archive = $log->archive;
		$message->entity = $log->entity;
		$message->user = $log->user;
		$message->stats = $log->stats;

		return $message;
	}
}
