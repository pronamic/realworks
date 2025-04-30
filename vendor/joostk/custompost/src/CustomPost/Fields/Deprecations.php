<?php namespace CustomPost\Fields;

use Exception;

class Deprecations
{
	const TRANSIENT = '_deprecations';

	protected $prefix;

	protected $usages = array();

	public function __construct($prefix)
	{
		$this->prefix = $prefix;
	}

	public function report(Field $field, Exception $stack)
	{
		$this->usages[] = array(
			'field' => $field->getFullName('.'),
			'stack' => $stack->getTraceAsString(),
		);
	}

	public function getLocalUsages()
	{
		return $this->usages;
	}

	public function getCollectedUsages()
	{
		$deprecations = $this->load();

		if (empty($this->usages))
		{
			$this->deleteCurrentUrl($deprecations);
		}
		else
		{
			$this->applyCurrentUrl($deprecations);
		}

		return $deprecations;
	}

	protected function load()
	{
		$deprecations = get_transient($this->prefix.static::TRANSIENT);

		if ($deprecations === false)
		{
			return array();
		}

		return $deprecations;
	}

	public function persist()
	{
		$deprecations = $this->load();

		if (empty($this->usages))
		{
			$updated = $this->deleteCurrentUrl($deprecations);
		}
		else
		{
			$updated = $this->applyCurrentUrl($deprecations);
		}

		if ($updated)
		{
			if (empty($deprecations))
			{
				delete_transient($this->prefix.static::TRANSIENT);
			}
			else
			{
				set_transient($this->prefix.static::TRANSIENT, $deprecations, MONTH_IN_SECONDS);
			}
		}
	}

	protected function deleteCurrentUrl(array &$deprecations)
	{
		$currentUrl = $this->getCurrentUrl();

		if ( ! array_key_exists($currentUrl, $deprecations))
		{
			return false;
		}

		unset($deprecations[$currentUrl]);

		return true;
	}

	protected function applyCurrentUrl(array &$deprecations)
	{
		$currentUrl = $this->getCurrentUrl();

		$previousFields = array_get($deprecations, $currentUrl, array());
		$newFields = array_unique(array_map(function($usage)
		{
			return $usage['field'];
		}, $this->usages));
		sort($newFields);

		if ($previousFields === $newFields)
		{
			return false;
		}

		$deprecations[$currentUrl] = $newFields;

		return true;
	}

	protected function reset()
	{
		$this->usages = array();
		delete_transient($this->prefix.static::TRANSIENT);
	}

	protected function getCurrentUrl()
	{
		global $wp;
		if (empty($wp))
		{
			return '<unknown>';
		}

		return $wp->request;
	}
}
