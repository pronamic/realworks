<?php namespace CustomPost\Plugin;

use CustomPost\Formatter\MoneyFormatter;
use CustomPost\Formatter\BooleanFormatter;
use JoostK\Wordpress\Support\ServiceProvider;
use JoostK\Wordpress\Json\JsonServiceProvider;
use JoostK\Wordpress\View\ViewServiceProvider;
use JoostK\Wordpress\Config\ConfigServiceProvider;
use JoostK\Wordpress\Events\EventsServiceProvider;
use JoostK\Wordpress\Remote\RemoteServiceProvider;
use JoostK\Wordpress\Support\SupportServiceProvider;
use CustomPost\Migrations\MigrationsServiceProvider;

class PluginServiceProvider extends ServiceProvider
{
	public function register()
	{
		$this->registerPackages($this->app);

		$this->registerUpdater();

		$this->registerLicenser();

		$this->registerEntitiesInfo();
	}

	protected function registerPackages($app)
	{
		$app->register(new MigrationsServiceProvider($app));
		$app->register(new JsonServiceProvider($app));
		$app->register(new ViewServiceProvider($app));
		$app->register(new ConfigServiceProvider($app));
		$app->register(new EventsServiceProvider($app));
		$app->register(new RemoteServiceProvider($app));
		$app->register(new SupportServiceProvider($app));
	}

	protected function registerUpdater()
	{
		$this->app->singleton('plugin.updater', function($app)
		{
			return new UpdateChecker($app);
		});
	}

	protected function registerLicenser()
	{
		$this->app->singleton('plugin.licenser', function($app)
		{
			return new LicenseVerifier($app, $app['remote']);
		});
	}

	protected function registerEntitiesInfo()
	{
		$this->app->singleton('admin.entities.info', function($app)
		{
			return new EntitiesInfo($app->entities());
		});
	}

	public function boot()
	{
		$app = $this->app;

		$this->app->resolving('events', function($events) use ($app)
		{
			$events->setIdentifier($app->getIdentifier());
		});

		$this->enableUpdater();

		$this->setViewPath();

		$this->registerActivationHooks();

		$this->insertLicenseNotice();

		$this->insertUpdateNotice();

		$this->loadPluginsFunctions();

		$this->registerScripts();

		$this->configureFormatters();
	}

	protected function enableUpdater()
	{
		if ($this->app['plugin.licenser']->licensed())
		{
			$this->app['plugin.updater']->enable($this->app['plugin.licenser']->license());
		}
	}

	protected function setViewPath()
	{
		$this->app->resolving('view', function($view, $app)
		{
			$basePath = realpath(dirname($app->getPluginPath()).'/assets/views');

			$view->setBasePath($basePath);
		});
	}

	protected function registerActivationHooks()
	{
		$app = $this->app;

		register_activation_hook($this->app->getPluginPath(), function() use ($app)
		{
			$app['events']->fire('activated');
		});

		register_deactivation_hook($this->app->getPluginPath(), function() use ($app)
		{
			$app['events']->fire('deactivated');
		});
	}

	protected function insertLicenseNotice()
	{
		if ( ! $this->app['plugin.licenser']->licensed())
		{
			AdminNotice::show($this->app['plugin.licenser']->notice(), $this->app->getTitle(), $this->app->adminUrl('dashboard/license'));
		}
	}

	protected function insertUpdateNotice()
	{
		if ($update = $this->app['plugin.updater']->getUpdate())
		{
			$notice = $update['notice'] ?: 'Versie %2$s van %1$s is beschikbaar.';

			AdminNotice::show($notice.' <a href="%3$s">Nu bijwerken</a>.', $this->app->getTitle(), $update['version'], $this->app->adminUrl('dashboard/update'));
		}
	}

	protected function loadPluginsFunctions()
	{
		locate_template($this->app->getIdentifier().'/functions.php', true);
	}

	protected function registerScripts()
	{
		wp_register_script('custompost-eventable', plugin_dir_url($this->app->getPluginPath()).'/assets/custompost/js/frontend/eventable.js', array('jquery'), false, true);
		wp_register_script('custompost-liveform', plugin_dir_url($this->app->getPluginPath()).'/assets/custompost/js/frontend/liveform.js', array('jquery', 'custompost-eventable'), false, true);
		wp_register_script('custompost-slider', plugin_dir_url($this->app->getPluginPath()).'/assets/custompost/js/frontend/slider.js', array('jquery', 'jquery-ui-slider'), false, true);
		wp_register_script('custompost-rangeslider', plugin_dir_url($this->app->getPluginPath()).'/assets/custompost/js/frontend/rangeslider.js', array('jquery', 'jquery-ui-slider'), false, true);
		wp_register_script('custompost-showmore', plugin_dir_url($this->app->getPluginPath()).'/assets/custompost/js/frontend/showmore.js', array('jquery'), false, true);
	}

	protected function configureFormatters()
	{
		MoneyFormatter::setDecimals(0);
		MoneyFormatter::setSuffix(',-');

		BooleanFormatter::setTrueFalse('Ja', 'Nee');
	}
}
