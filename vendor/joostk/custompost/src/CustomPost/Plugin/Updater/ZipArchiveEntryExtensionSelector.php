<?php namespace CustomPost\Plugin\Updater;

class ZipArchiveEntryExtensionSelector implements ZipArchiveEntrySelector
{
	/**
	 * The extension of the files to select, should be lowercased and include the dot.
	 */
	protected $extension;

	public function __construct($extension)
	{
		$this->extension = $extension;
	}

	public function shouldRead($name)
	{
		if (substr(basename($name), 0, 1) === '.')
		{
			// Skip hidden files.
			return false;
		}

		return strtolower(substr($name, -strlen($this->extension))) === $this->extension;
	}
}
