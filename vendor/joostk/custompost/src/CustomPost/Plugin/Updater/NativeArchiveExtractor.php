<?php namespace CustomPost\Plugin\Updater;

use ZipArchive;
use RuntimeException;

class NativeArchiveExtractor implements ArchiveExtractorInterface
{
	protected $entrySelector;

	public function __construct(ZipArchiveEntrySelector $entrySelector)
	{
		$this->entrySelector = $entrySelector;
	}

	public function extract($path)
	{
		$archive = zip_open($path);

		if ( ! is_resource($archive))
		{
			$message = $this->getErrorMessage($archive);

			throw new RuntimeException("Zip bestand kon niet worden geopend: [{$message}]", $archive);
		}

		$files = array();

		while ($entry = zip_read($archive))
		{
			$name = zip_entry_name($entry);

			if ($this->entrySelector->shouldRead($name))
			{
				$files[] = $this->readEntry($archive, $entry);
			}
		}

		zip_close($archive);

		return $files;
	}

	protected function readEntry($archive, $entry)
	{
		if (zip_entry_open($archive, $entry, 'r'))
		{
			$data = zip_entry_read($entry, zip_entry_filesize($entry));

			zip_entry_close($entry);

			return $data;
		}
		else
		{
			zip_close($archive);

			throw new RuntimeException('Zip item kon niet worden geopend.');
		}
	}

	protected function getErrorMessage($code)
	{
		$errors = array(
			ZipArchive::ER_MULTIDISK => 'Multi-disk zip bestanden zijn niet ondersteund',
			ZipArchive::ER_SEEK => 'Seek error',
			ZipArchive::ER_READ => 'Leesfout',
			ZipArchive::ER_WRITE => 'Schrijffout',
			ZipArchive::ER_CRC => 'CRC fout',
			ZipArchive::ER_NOENT => 'Bestand niet gevonden',
			ZipArchive::ER_OPEN => 'Bestand kan niet worden geopend',
			ZipArchive::ER_TMPOPEN => 'Tijdelijk bestand kan niet worden aangemaakt',
			ZipArchive::ER_ZLIB => 'Zlib fout',
			ZipArchive::ER_MEMORY => 'Memory allocation failure',
			ZipArchive::ER_COMPNOTSUPP => 'Compressie methode niet ondersteund',
			ZipArchive::ER_EOF => 'Premature EOF',
			ZipArchive::ER_NOZIP => 'Het gedownloade bestand is geen ziparchief',
			ZipArchive::ER_INTERNAL => 'Interne fout',
			ZipArchive::ER_INCONS => 'Zip archive inconsistent',
		);

		return array_get($errors, $code, 'Een onbekende fout is opgetreden tijdens het openen van het ziparchief');
	}
}
