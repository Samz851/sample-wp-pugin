<?php
/**
 * Display Settings Page
 * 
 * @package Adept_Drivers
 * @subpackage Adept_Drivers/partials
 * @author Samer Alotaibi <sam@samiscoding.com>
 */
?>
<div id="ld-settings" class="adminpage">
<?php
    if ( ! current_user_can( 'manage_options' ) ) {
		return;
		}
		
		// add error/update messages
		
		// check if the user have submitted the settings
		// wordpress will add the "settings-updated" $_GET parameter to the url
		if ( isset( $_GET['settings-updated'] ) ) {
		// add settings saved message with the class of "updated"
		add_settings_error( 'ad-api-settings', 'ad-api-settings', __( 'Settings Saved', 'wpq' ), 'updated' );
		}
		
		// show error/update messages
		settings_errors( 'ad-api_messages' );
    ?>
    <div class="ld-pageheader">
        <h3><?php echo __('Settings Page', 'adept-drivers'); ?></h3>
    </div>
    <div>
        <div class="ad-api-inputs">
            <form action="options.php" method="post">
                <?php
                // output security fields for the registered setting "wpq"
                settings_fields( 'ad-api-settings' );
                // output setting sections and their fields
                // (sections are registered for "wpq", each field is registered to a specific section)
                do_settings_sections( 'ad-api-settings' );
                // output save settings button
                submit_button( 'Save Settings' );
                ?>
            </form>
        </div>
    </div>
</div>