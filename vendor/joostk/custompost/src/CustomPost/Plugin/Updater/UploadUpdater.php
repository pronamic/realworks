<?php namespace CustomPost\Plugin\Updater;

use RuntimeException;
use CustomPost\Plugin\Plugin;

class UploadUpdater
{
	protected $errors = array(
		UPLOAD_ERR_INI_SIZE => 'Het geüploade bestand is groter dan de server toestaat.',
		UPLOAD_ERR_FORM_SIZE => 'Het geüploade bestand is groter dan is toegestaan.',
		UPLOAD_ERR_PARTIAL => 'Het bestand was slechts gedeeltelijk geüpload.',
		UPLOAD_ERR_NO_FILE => 'Geen bestand geüpload.',
		UPLOAD_ERR_NO_TMP_DIR => 'Geen tijdelijke map beschikbaar om bestand in op te slaan.',
		UPLOAD_ERR_CANT_WRITE => 'Het geüploade bestand kon niet worden opgeslagen.',
		UPLOAD_ERR_EXTENSION => 'Het geüploade bestand is geweigerd door een PHP extensie.',
	);

	protected $plugin;

	protected $updater;

	protected $pathResolver;

	protected $log;

	public function __construct(Plugin $plugin, Updater $updater, PathResolver $pathResolver)
	{
		$this->plugin = $plugin;
		$this->updater = $updater;
		$this->pathResolver = $pathResolver;
	}

	public function background()
	{
		$this->updater->background();

		return $this;
	}

	public function prepare(array $file, $entity, $user)
	{
		$this->verifyFile($file);
		$this->verifyEntity($entity);

		$path = $this->moveFile($file, $entity);

		$this->log = (object) array(
			'archive' => $path,
			'entity' => $entity,
			'user' => $user ?: '__uploaded__',
		);

		return $this;
	}

	public function update()
	{
		return $this->updater->retry($this->log);
	}

	protected function verifyFile(array $file)
	{
		if ( ! isset($file['name'], $file['tmp_name'], $file['error']) or $file['error'])
		{
			$error = array_get($this->errors, array_get($file, 'error', -1), 'Onbekende fout opgetreden bij verwerken van geüploade bestand.');

			throw new RuntimeException($error);
		}
	}

	protected function verifyEntity($entity)
	{
		if ($this->plugin->entity($entity) === null)
		{
			throw new RuntimeException('Ongeldig type object opgegeven.');
		}
	}

	protected function moveFile(array $file, $entity)
	{
		$ext = pathinfo($file['name'], PATHINFO_EXTENSION);

		$path = $this->pathResolver->getBasePath().$this->pathResolver->nextEntityFile($entity, 'uploaded', ".{$ext}");

		if (@move_uploaded_file($file['tmp_name'], $path) === false)
		{
			throw new RuntimeException('Geüploade bestand kon niet worden opgeslagen.');
		}

		return $path;
	}
}
