<?php namespace Realworks;

use CustomPost\Fields\Field;
use CustomPost\Plugin\AdminBarMenu;
use JoostK\Wordpress\Support\ServiceProvider;
use CustomPost\Plugin\Updater\Media\MediaServiceProvider;

class RealworksServiceProvider extends ServiceProvider
{
	public function register()
	{
		Facade::setContainer($this->app);

		$this->alias();

		$this->registerPackages($this->app);
	}

	protected function registerPackages($app)
	{
		$app->register(new Admin\AdminServiceProvider($app));
		$app->register(new Wonen\EntityServiceProvider($app));
		$app->register(new Bog\EntityServiceProvider($app));
		$app->register(new Nieuwbouw\Project\EntityServiceProvider($app));
		$app->register(new Alv\EntityServiceProvider($app));
		$app->register(new Vgm\Complexen\Project\EntityServiceProvider($app));
		$app->register(new Vgm\Units\EntityServiceProvider($app));
		$app->register(new Updater\UpdaterServiceProvider($app));
		$app->register(new MediaServiceProvider($app));

		$this->app->resolving('config.storage', function($storage)
		{
			$storage->setSubPath('realworks/config.php');
		});
	}

	protected function alias()
	{
		if ( ! class_exists('Realworks'))
		{
			class_alias('Realworks\\Facade', 'Realworks');
		}
	}

	public function boot()
	{
		$this->addAdminBarMenu();

		$this->addPageHeader();

		$this->registerFormatters();
	}

	protected function addAdminBarMenu()
	{
		AdminBarMenu::plugin($this->app)
			->addItem('Dashboard', 'dashboard')
			->addItem('Instellingen', 'settings')
			->addEntities()
			->addItem('Bijwerken', 'updater');
	}

	protected function addPageHeader()
	{
		add_action('wp_head', function()
		{
			echo PHP_EOL, '<!-- Wordpress CMS & Makelaar Plugin door Tussendoor B.V. (tussendoor.nl) -->', PHP_EOL, PHP_EOL;
		});
	}

	protected function registerFormatters()
	{
		Field::registerFormatter('area', 'Realworks\Common\Formatters\AreaFormatter');
		Field::registerFormatter('length', 'Realworks\Common\Formatters\LengthFormatter');
		Field::registerFormatter('volume', 'Realworks\Common\Formatters\VolumeFormatter');
	}
}
