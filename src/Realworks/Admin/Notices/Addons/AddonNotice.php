<?php namespace Realworks\Admin\Notices\Addons;

use CustomPost\Plugin\Admin\Notices\StickyNotice;

class AddonNotice implements StickyNotice
{
	public function name()
	{
		return 'addon_notice';
	}

	public function show()
	{
		add_action('admin_enqueue_scripts', function()
		{
			wp_enqueue_style('realworks-notice-addon', plugins_url('src/Realworks/Admin/Notices/Addons/styles.css', REALWORKS));
		});

		add_action('all_admin_notices', array($this, 'render'));
	}

	public function render()
	{
		$banner = plugins_url('src/Realworks/Admin/Notices/Addons/banner.png', REALWORKS);

		require __DIR__ . '/notice.html';
	}
}
