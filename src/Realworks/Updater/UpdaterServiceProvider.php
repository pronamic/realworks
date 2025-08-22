<?php namespace Realworks\Updater;

use Realworks\Admin\Pages;
use Realworks\Updater\RealworksApiClient;
use CustomPost\Plugin\Updater\Pull\PullServiceProvider;
use CustomPost\Plugin\Updater\Batch\UpdaterServiceProvider as BaseServiceProvider;

class UpdaterServiceProvider extends BaseServiceProvider
{
	public function register()
	{
		parent::register();

		$this->app->register(new PullServiceProvider($this->app));

		$this->registerSource();
	}

	protected function registerSource()
	{
		$this->app['updater.source'] = function($app, $parameters)
		{
			list($entity, $settings) = $parameters;

			$apiKey = get_option(Pages\ApiSettingsPage::OPTION_NAME, '');
			$apiClient = new RealworksApiClient($apiKey);
			$source = new RealworksApiSource($app['remote'], $apiClient);

			return $source
				->setKoppeling(array_get($settings, 'koppeling'))
				->setOffices(array_get($settings, 'offices'));
		};
	}

	public function boot()
	{
		parent::boot();

		$this->registerFailureEvents();
	}

	protected function registerFailureEvents()
	{
		$app = $this->app;

		$this->app['events']->listen('updater.failed.attempts', function($max) use ($app)
		{
			if ($app['config']['updater.notify.enabled'])
			{
				$subject = 'Realworks bijwerken mislukt';
				$view = $app['view']->load('emails.updater.attempts', compact('max'));

				wp_mail($app['config']['updater.notify.email'], $subject, $view);
			}
		});
	}
}
