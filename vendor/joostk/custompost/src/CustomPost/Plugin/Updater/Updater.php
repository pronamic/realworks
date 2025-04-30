<?php namespace CustomPost\Plugin\Updater;

use Exception;
use CustomPost\Plugin\LicenseVerifier;
use JoostK\Illuminate\Container\Container;
use CustomPost\Plugin\Updater\Logger\BatchLoggerInterface;

abstract class Updater
{
	protected $container;

	protected $logger;

	protected $canceller;

	protected $pathResolver;

	protected $licenser;

	protected $config;

	protected $notAllowed = false;

	public function __construct(Container $container, BatchLoggerInterface $logger, Canceller $canceller, PathResolver $pathResolver, LicenseVerifier $licenser, $config)
	{
		$this->container = $container;
		$this->logger = $logger;
		$this->canceller = $canceller;
		$this->pathResolver = $pathResolver;
		$this->licenser = $licenser;
		$this->config = (array) $config;
	}

	public function logger()
	{
		return $this->logger;
	}

	public function getId()
	{
		return $this->logger->getId();
	}

	public function wasNotAllowed()
	{
		return $this->notAllowed;
	}

	public function background()
	{
		$response = array('id' => $this->getId());

		ignore_user_abort(true);
		ob_start();
		echo json_encode($response);
		header('Connection: close');
		header('Content-Type: application/json');
		header('Content-Encoding: none');
		header('Content-Length: ' . ob_get_length());
		echo str_repeat(' ', 1024 * 64);
		ob_end_flush();
		flush();

		return $this;
	}

	protected function resolveSource($entity, array $settings)
	{
		return $this->container->make('updater.source', array($entity, $settings));
	}

	public function update()
	{
		$this->verifyLicense();
		$this->prepareEnvironment();

		$this->logger->info('Bijwerken gestart.');

		$result = false;

		try
		{
			$result = $this->applyUpdate();

			$this->logger->info($result ? 'Bijwerken voltooid.' : 'Bijwerken mislukt.');
		}
		catch (UpdateCancelledException $e)
		{
			$this->logger->info('Bijwerken geannuleerd.');

			$result = true;
		}
		catch (UpdateNotAllowedException $e)
		{
			$this->logger->error($e->getMessage());

			$this->notAllowed = true;

			$this->logger->info('Bijwerken mislukt.');
		}

		$this->logger->close();

		wp_cache_flush();

		return $result;
	}

	abstract protected function applyUpdate();

	protected function updateEntity(EntityUpdater $updater, InputSourceInterface $source)
	{
		$this->canceller->check();

		try
		{
			$path = $source->getArchivePath();

			$this->canceller->check();

			return $this->processData($updater, $path);
		}
		catch (UpdateCancelledException $e)
		{
			throw $e;
		}
		catch (UpdateNotAllowedException $e)
		{
			throw $e;
		}
		catch (Exception $e)
		{
			error_log((string) $e);

			$this->logger->error($e->getMessage());

			return false;
		}
	}

	protected function processData(EntityUpdater $updater, $path)
	{
		$updater->processArchive($path);

		return true;
	}

	private function verifyLicense()
	{
		if ($this->licenser->status() === 'blocked')
		{
			throw new RuntimeException('Verwerken van objecten is niet beschikbaar vanwege een geblokkerde licentie.');
		}
	}

	protected function prepareEnvironment()
	{
		if (array_get($this->config, 'debug', false))
		{
			$this->enableDebugMode();
		}

		ini_set('memory_limit', array_get($this->config, 'memory-limit', '1024M'));

		wp_cache_flush();
		set_time_limit(0);
		ignore_user_abort(true);
		register_shutdown_function(array($this, 'shutdown'));

		$this->canceller->clear();
	}

	/**
	 * Debug mode is disabled by default as it writes the errors into a location that may be publicly reachable, exposing
	 * sensitive information.
	 */
	protected function enableDebugMode()
	{
		$this->logger->info('Debugging modus staat aan');

		ini_set('log_errors', '1');
		ini_set('error_log', $this->pathResolver->getBasePath().$this->pathResolver->getDailyPath().'/error.phplog');
	}

	public function shutdown()
	{
		if (($error = error_get_last()) !== null and $error['type'] & E_ERROR)
		{
			$this->logger->error("Onverwacht probleem: {$error['message']}");
		}
	}
}
