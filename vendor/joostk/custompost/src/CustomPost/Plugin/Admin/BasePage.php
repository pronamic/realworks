<?php namespace CustomPost\Plugin\Admin;

use JoostK\Wordpress\Admin\Page;

abstract class BasePage extends Page
{
	public function loadScripts()
	{
		parent::loadScripts();

		if ($this->isDebug())
		{
			wp_enqueue_script('requirejs', plugins_url('assets/custompost/js/vendor/require.js', $this->app->getPluginPath()), array('jquery', 'jquery-ui-sortable', 'underscore', 'backbone'));
			wp_enqueue_script('require-cp-config', plugins_url('assets/custompost/js/config.js', $this->app->getPluginPath()), array('requirejs'));
			wp_enqueue_script('require-cp-legacy', plugins_url('assets/custompost/js/legacy.js', $this->app->getPluginPath()), array('require-cp-config'));
			wp_enqueue_script($this->app->getIdentifier(), plugins_url('assets/js/config.js', $this->app->getPluginPath()), array('require-cp-config'));

			wp_localize_script('requirejs', 'require', array(
				'baseUrl' => plugins_url('assets/custompost/js/vendor', $this->app->getPluginPath()),
			));
		}
		else
		{
			wp_enqueue_script($this->app->getIdentifier(), plugins_url('assets/js/libs.js', $this->app->getPluginPath()), array('jquery', 'jquery-ui-sortable', 'underscore', 'backbone'));
		}

		wp_localize_script($this->app->getIdentifier(), 'CustomPost', array(
			'config' => $this->config(),
			'data' => $this->data(),
		));
	}

	public function loadStyles()
	{
		parent::loadStyles();

		wp_enqueue_style('style', plugins_url('assets/css/style.css', $this->app->getPluginPath()));
	}

	protected function config()
	{
		return array(
			'ajaxUrl' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('custompost_nonce'),
		);
	}

	protected function data()
	{
		return array();
	}

	abstract protected function isDebug();
}
