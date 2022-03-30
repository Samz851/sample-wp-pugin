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
global $wpdb;
    if ( ! current_user_can( 'manage_options' ) ) {
		return;
    }
    $options = get_option( 'ad_options' );
    $count = $wpdb->get_row("SELECT COUNT(*) AS THE_COUNT FROM $wpdb->usermeta WHERE (meta_key = 'ad_is_active' AND meta_value = '1')");
    ?>
    <div class="ld-pageheader">
        <h3><?php echo __('Dashboard', 'adept-drivers'); ?></h3>
    </div>
    <div class="dashboard-container">
        <div class="ad-integrations-status">
            <div>
                <div class="card-label">Tookan</div>
                <div class="card-content"><?php echo isset($options['ad_tookan_api']) ? '<i class="fa fa-check" aria-hidden="true"></i>' : '<i class="fa fa-times" aria-hidden="true"></i>';?></div>
            </div>
            <div>
                <div class="card-label">Zoho CRM</div>
                <div class="card-content"><?php echo isset($options['ad_zcrm_temp_token']) ? '<i class="fa fa-check" aria-hidden="true"></i>' : '<i class="fa fa-times" aria-hidden="true"></i>';?></div>
            </div>
            <div>
                <div class="card-label">Moodle LMS</div>
                <div class="card-content"><?php echo isset($options['ad_moodle_api_token']) ? '<i class="fa fa-check" aria-hidden="true"></i>' : '<i class="fa fa-times" aria-hidden="true"></i>';?></div>
            </div>
            <div>
                <div class="card-label">Google API</div>
                <div class="card-content"><?php echo isset($options['ad_google_api_key']) ? '<i class="fa fa-check" aria-hidden="true"></i>' : '<i class="fa fa-times" aria-hidden="true"></i>';?></div>
            </div>
        </div>
        <div class="ad-students-status">
        <div class="card-label">Total Students</div>
                <div class="card-content"><?php echo $count->THE_COUNT;?></div>
        </div>
        <div class="ad-bookings-status">
        <div class="card-label">Tookan</div>
                <div class="card-content"><?php echo isset($options['ad_tookan_api']) ? '<i class="fa fa-check" aria-hidden="true"></i>' : '<i class="fa fa-times" aria-hidden="true"></i>';?></div>
        </div>
        <div class="ad-agents-status">
        <div class="card-label">Tookan</div>
                <div class="card-content"><?php echo isset($options['ad_tookan_api']) ? '<i class="fa fa-check" aria-hidden="true"></i>' : '<i class="fa fa-times" aria-hidden="true"></i>';?></div>
        </div>
    </div>
</div>