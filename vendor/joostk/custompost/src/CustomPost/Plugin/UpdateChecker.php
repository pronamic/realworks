<?php namespace CustomPost\Plugin;

use PluginUpdateChecker_3_1 as PluginUpdateChecker;

class UpdateChecker
{
	protected $plugin;

	protected $updater;

	public function __construct(Plugin $plugin)
	{
		$this->plugin = $plugin;
	}

	public function enable($license)
	{
		$this->updater = new PluginUpdateChecker($this->url($license), $this->plugin->getPluginPath(), $this->plugin->getIdentifier());

		$this->updater->scheduler->maybeCheckForUpdates();
	}

	protected function url($license)
	{
		return $this->plugin->getPluginApiUrl().'&'.http_build_query(array(
			'action' => 'get_metadata',
			'license' => $license,
		));
	}

	public function getVersion()
	{
		$data = get_plugin_data($this->plugin->getPluginPath(), false, false);

		return $data['Version'];
	}

	public function getUpdate()
	{
		if ($this->updater and $update = $this->updater->getUpdate()) return array(
			'version' => $update->version,
			'notice' => $update->upgrade_notice,
			'url' => admin_url('plugin-install.php?tab=plugin-information&plugin='.$this->plugin->getIdentifier().'&section=changelog&TB_iframe=true&width=600&height=800'),
		);
	}
}
