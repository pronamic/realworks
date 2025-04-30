<?php namespace Realworks\Admin\Pages;

class MainPage extends BasePage
{
	public function show()
	{
		echo $this->view->load('admin.pages.main');
	}

	public function registerMenu()
	{
		return add_menu_page(
			'Makelaar',
			'Makelaar',
			'edit_posts',
			'realworks',
			array($this, 'show'),
			'dashicons-admin-home',
			'99.31415'
		);
	}

	public function loadScripts()
	{
		parent::loadScripts();

		wp_enqueue_script('realworks-main', plugins_url('assets/js/main.js', REALWORKS), array('jquery-ui-sortable', 'realworks'));

		add_thickbox();
		wp_enqueue_script('plugin-install');
	}

	public function data()
	{
		return array(
			'settings' => $this->app['config']->get(),
			'entities' => $this->app['admin.entities.info']->get(),
			'dashboard' => $this->app['admin.dashboard']->get(),
		);
	}
}
