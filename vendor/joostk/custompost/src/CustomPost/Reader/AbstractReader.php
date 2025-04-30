<?php namespace CustomPost\Reader;

use SimpleXMLElement;
use RuntimeException;
use CustomPost\Fields\Field;
use CustomPost\Fields\Subtype;
use JoostK\Wordpress\Events\Events;
use CustomPost\Fields\CollectionSubtype;

abstract class AbstractReader
{
	protected $events;

	protected $currentPost;

	protected $skip;

	protected $acceptPrematurely;

	public function setEvents(Events $events = null)
	{
		$this->events = $events;

		return $this;
	}

	public function getCurrentPost()
	{
		return $this->currentPost;
	}

	abstract public function getFields();

	abstract public function read(Data $data);

	protected function readEntry(Data $data)
	{
		$this->currentPost = $this->findPost($data);
		$this->skip = $this->acceptPrematurely = false;

		foreach ($this->getFields() as $name => $field)
		{
			if ($field instanceof CollectionSubtype)
			{
				$value = $this->readCollection($field, $data);
			}
			else if ($field instanceof Subtype)
			{
				$value = $this->readSubtype($field, $data);
			}
			else if ($field->hasSelector())
			{
				$value = $data->get($field, $this);

				$this->currentPost->$name = $value;
			}
			else
			{
				$value = null;
			}

			$this->fire('reader.read: '.$field->getFullName('.'), array($value, $this, $field, $data->raw()));

			if ($this->skip) return;
			if ($this->acceptPrematurely) break;
		}

		return $this->currentPost;
	}

	protected function readCollection(Subtype $subtype, Data $data)
	{
		$reader = new SubtypeCollectionReader($subtype, $this);

		return $reader->read($data);
	}

	protected function readSubtype(Subtype $subtype, Data $data)
	{
		$reader = new SubtypeReader($subtype, $this);

		return $reader->read($data);
	}

	protected function fire($event, $payload = array())
	{
		if ($this->events)
		{
			$this->events->fire($event, $payload);
		}
	}

	public function skip()
	{
		$this->skip = true;

		return $this;
	}

	public function __internal_acceptPrematurely()
	{
		// Used in A&V to accept an object when processing a "delete", where the original data must not be touched. This is sensitive
		// to definition order of fields so must not be relied upon from consumers, hence this is marked internal.
		$this->acceptPrematurely = true;

		return $this;
	}

	abstract protected function findPost(Data $data);
}
