<?php namespace CustomPost\Plugin\Updater;

use Closure;
use RuntimeException;
use Rhumsaa\Uuid\Uuid;

class PathResolver
{
	protected $base;

	public function __construct($path)
	{
		$this->setBasePath($this->determineBasePath($path));
	}

	public function setBasePath($path)
	{
		$this->base = rtrim($path, DIRECTORY_SEPARATOR).'/';

		return $this;
	}

	public function getBasePath()
	{
		return $this->base;
	}

	public function nextDailyFile($ext)
	{
		return $this->determineNextFile(

			$this->getDailyPath(), $ext

		);
	}

	public function nextEntityFile($entity, $user, $ext)
	{
		return $this->determineNextFile(

			$this->getEntityPath($entity, $user), $ext

		);
	}

	public function nextBatchLog()
	{
		return $this->nextDailyFile('.log');
	}

	public function nextEntityLog($entity, $user)
	{
		return $this->nextEntityFile($entity, $user, '.log');
	}

	public function getDailyPath()
	{
		return $this->resolvePath(

			date('Y/m/d', $this->time())

		);
	}

	public function getEntityPath($entity, $user)
	{
		$user = preg_replace('~[^\w\-_.]~', '_', $user);

		return $this->resolvePath(

			$this->getDailyPath()."/{$entity}/{$user}"

		);
	}

	public function determineNextFile($path, $ext)
	{
		return $path.'/'.Uuid::uuid4()->toString().$ext;
	}

	protected function determineBasePath($path)
	{
		$upload = wp_upload_dir();

		return $upload['basedir'].'/'.$path;
	}

	protected function resolvePath($path)
	{
		$absolute = $this->base . $path;

		if ( ! wp_mkdir_p($absolute))
		{
			throw new RuntimeException("De map {$absolute} kon niet worden aangemaakt. Schrijfrechten op deze map zijn noodzakelijk.");
		}

		return $path;
	}

	protected function time()
	{
		return current_time('timestamp');
	}
}
