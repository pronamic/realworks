<div class="wrap">
    <h2>Realworks API Instellingen</h2>
    <form method="post" action="">
        <input type="hidden" name="page" value="realworks-api-settings">
        <input type="hidden" name="_wpnonce" value="<?php echo $nonce; ?>">

        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="api_key">API Key</label>
                    </th>
                    <td>
                        <input type="text" id="api_key" name="api_key" value="<?php echo esc_attr($api_key); ?>" class="regular-text">
                        <p class="description">Voer hier de API key in die u van Realworks heeft ontvangen.</p>
                    </td>
                </tr>
            </tbody>
        </table>

        <?php submit_button(); ?>
    </form>
</div>
