<?php namespace CustomPost\Plugin\Updater;

use RuntimeException;
use JoostK\Illuminate\Container\Container;

class ExtensionAwareExtractor implements ArchiveExtractorInterface
{
	protected $app;

	public function __construct(Container $app)
	{
		$this->app = $app;
	}

	public function extract($path)
	{
		$ext = pathinfo($path, PATHINFO_EXTENSION);

		return $this->resolve($ext)->extract($path);
	}

	protected function resolve($ext)
	{
		if (method_exists($this, $method = 'resolve'.ucfirst(strtolower($ext))))
		{
			return $this->{$method}();
		}
		else
		{
			throw new RuntimeException("Bestandstype [{$ext}] is niet ondersteund.");
		}
	}

	protected function resolveZip()
	{
		return new NativeArchiveExtractor($this->app['updater.extractor.zip-archive.entry-selector']);
	}

	protected function resolveXml()
	{
		return new SingleFileExtractor;
	}

	protected function resolveJson()
	{
		return new SingleFileExtractor;
	}
}
