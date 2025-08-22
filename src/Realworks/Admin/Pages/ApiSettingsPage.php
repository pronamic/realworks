<?php namespace Realworks\Admin\Pages;

class ApiSettingsPage extends BasePage
{
    const OPTION_NAME = 'realworks_api_key';

    public function registerMenu()
    {
        return add_submenu_page(
            'realworks',
            'API Instellingen',
            'API Instellingen',
            'manage_options',
            'realworks-api-settings',
            array($this, 'show')
        );
    }

    public function show()
    {
        if (isset($_POST['submit']) && check_admin_referer('realworks_api_settings_nonce')) {
            $this->saveSettings();
        }

        $data = $this->data();
        echo $this->view->load('admin.pages.api-settings', $data);
    }

    protected function saveSettings()
    {
        $apiKey = sanitize_text_field($_POST['api_key']);
        update_option(self::OPTION_NAME, $apiKey);

        echo '<div class="updated"><p>Instellingen opgeslagen.</p></div>';
    }

    public function data()
    {
        return array(
            'api_key' => get_option(self::OPTION_NAME, ''),
            'nonce' => wp_create_nonce('realworks_api_settings_nonce'),
        );
    }
}
