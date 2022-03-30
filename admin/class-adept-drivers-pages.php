<?php
require plugin_dir_path( __DIR__ ) . '/vendor/autoload.php';

class Adept_Drivers_Pages {

    /**
	 * Register settings page section
	 * 
	 * @since 1.0.0
	 */
	public function register_wpq_settings(){
		register_setting( 'ad-api-settings', 'ad_options' );

		 // register a new section in the "wpq" page
		add_settings_section(
		'ad-api_section_options',
		__( 'API Keys', 'adept-drivers' ),
		[$this, 'ad_section_options_cb'],
		'ad-api-settings'
		);

		 // register a new field in the "wpq_section_developers" section, inside the "wpq" page
		add_settings_field(
			'ad_tookan_key_field', // as of WP 4.6 this value is used only internally
			// use $args' label_for to populate the id inside the callback
			__( 'Tookan Key', 'adept-driver' ),
			[$this, 'ad_tookan_key_field_cb'],
			'ad-api-settings',
			'ad-api_section_options',
			[
			'label_for' => 'ad_tookan_api',
			'class' => 'ad_tookan_api'
			]
		);

		//register a new field for Zoho CRM Keys

		add_settings_field(
			'ad_zcrm_api_id',
			__( 'Zoho CRM Client ID', 'adept-drivers'),
			[$this, 'ad_zcrm_CID_field_cb'],
			'ad-api-settings',
			'ad-api_section_options',
			[
				'label_for' => 'ad_zcrm_cid',
				'class' => 'ad_zcrm_cid'
			]
		);

		add_settings_field(
			'ad_zcrm_api_sec',
			__( 'Zoho CRM Client Secret', 'adept-drivers'),
			[$this, 'ad_zcrm_Csecret_field_cb'],
			'ad-api-settings',
			'ad-api_section_options',
			[
				'label_for' => 'ad_zcrm_csecret',
				'class' => 'ad_zcrm_csecret'
			]
		);

		add_settings_field(
			'ad_zcrm_api_email',
			__( 'Zoho CRM Client Email', 'adept-drivers'),
			[$this, 'ad_zcrm_email_field_cb'],
			'ad-api-settings',
			'ad-api_section_options',
			[
				'label_for' => 'ad_zcrm_email',
				'class' => 'ad_zcrm_email'
			]
		);

		add_settings_field(
			'ad_zcrm_api_redirect_uri',
			__( 'Zoho CRM Redirect URI', 'adept-drivers'),
			[$this, 'ad_zcrm_redirect_field_cb'],
			'ad-api-settings',
			'ad-api_section_options',
			[
				'label_for' => 'ad_zcrm_redirect_uri',
				'class' => 'ad_zcrm_redirect_uri'
			]
		);

		add_settings_field(
			'ad_zcrm_api_temp_token',
			__( 'Zoho CRM Temporary Token', 'adept-drivers'),
			[$this, 'ad_zcrm_temp_token_cb'],
			'ad-api-settings',
			'ad-api_section_options',
			[
				'label_for' => 'ad_zcrm_temp_token',
				'class' => 'ad_zcrm_temp_token'
			]
		);

		add_settings_field(
			'ad_moodle_api_token',
			__( 'LMS API Token', 'adept-drivers'),
			[$this, 'ad_moodle_api_token_cb'],
			'ad-api-settings',
			'ad-api_section_options',
			[
				'label_for' => 'ad_moodle_api_token',
				'class' => 'ad_moodle_api_token'
			]
		);

		add_settings_field(
			'ad_moodle_company_id',
			__( 'LMS Company ID', 'adept-drivers'),
			[$this, 'ad_moodle_company_id_cb'],
			'ad-api-settings',
			'ad-api_section_options',
			[
				'label_for' => 'ad_moodle_company_id',
				'class' => 'ad_moodle_company_id'
			]
			);
		
		add_settings_field(
			'ad_google_api_key',
			__( 'Google Geocoding API', 'adept-drivers'),
			[$this, 'ad_google_api_key_cb'],
			'ad-api-settings',
			'ad-api_section_options',
			[
				'label_for' => 'ad_google_api_key',
				'class' => 'ad_google_api_key'
			]
		);

		// // register a new section in the "settings" page
		// add_settings_section(
		// 	'wpquotes_section_options_recaptcha',
		// 	__( 'reCAPTCHA Settings', 'wpquotes' ),
		// 	[$this, 'wpquotes_section_options_cb'],
		// 	'wp-quotes-settings'
		// );
		// // register a new section in the "settings" page
		// add_settings_section(
		// 	'wpquotes_section_options_custom_css',
		// 	__( 'Custom Styles', 'wpquotes' ),
		// 	[$this, 'wpquotes_section_options_cb'],
		// 	'wp-quotes-settings'
		// );

		// // register a new field
		// add_settings_field(
		// 	'wpq_custom_css', // as of WP 4.6 this value is used only internally
		// 	// use $args' label_for to populate the id inside the callback
		// 	__( 'Custom CSS Rules', 'wpquotes' ),
		// 	[$this, 'wpq_custom_css_cb'],
		// 	'wp-quotes-settings',
		// 	'wpquotes_section_options_custom_css',
		// 	[
		// 	'label_for' => 'wpq_custom_css',
		// 	'class' => 'wpq_custom_css'
		// 	]
		// );

		// // register a new field in the "wpq_section_developers" section, inside the "wpq" page
		// add_settings_field(
		// 	'wpq_reCAPTCHA_site_key', // as of WP 4.6 this value is used only internally
		// 	// use $args' label_for to populate the id inside the callback
		// 	__( 'reCAPTCHA Site Key', 'wpquotes' ),
		// 	[$this, 'wpq_reCAPTCHA_site_key_cb'],
		// 	'wp-quotes-settings',
		// 	'wpquotes_section_options_recaptcha',
		// 	[
		// 	'label_for' => 'wpq_reCAPTCHA_site_key',
		// 	'class' => 'wpq_reCAPTCHA_site_key'
		// 	]
		// );
		// // register a new field in the "wpq_section_developers" section, inside the "wpq" page
		// add_settings_field(
		// 	'wpq_reCAPTCHA_secret_key', // as of WP 4.6 this value is used only internally
		// 	// use $args' label_for to populate the id inside the callback
		// 	__( 'reCAPTCHA Secret Key', 'wpquotes' ),
		// 	[$this, 'wpq_reCAPTCHA_secret_key_cb'],
		// 	'wp-quotes-settings',
		// 	'wpquotes_section_options_recaptcha',
		// 	[
		// 	'label_for' => 'wpq_reCAPTCHA_secret_key',
		// 	'class' => 'wpq_reCAPTCHA_secret_key'
		// 	]
		// );
		// // register a new field in the "wpq_section_developers" section, inside the "wpq" page
		// add_settings_field(
		// 	'wpq_reCAPTCHA_type', // as of WP 4.6 this value is used only internally
		// 	// use $args' label_for to populate the id inside the callback
		// 	__( 'reCAPTCHA Type', 'wpquotes' ),
		// 	[$this, 'wpq_reCAPTCHA_type_cb'],
		// 	'wp-quotes-settings',
		// 	'wpquotes_section_options_recaptcha',
		// 	[
		// 	'label_for' => 'wpq_reCAPTCHA_type',
		// 	'class' => 'wpq_reCAPTCHA_type'
		// 	]
		// );
		
    }
    
	/**
	 * Callback for Options Page
	 * 
	 * @since 1.0.0
	 * 
	 * @param Array arguements defined in add_settings_section()
	 */
    public function ad_section_options_cb($args){
        ?>
		<p id="<?php echo esc_attr( $args['id'] ); ?>"></p>
		<?php
    }

    /**
	 * Callback for options field
	 * 
	 * @since 1.0.0
	 * 
	 * @param Array arguements defined in add_settings_field()
	 */
	public function ad_tookan_key_field_cb( $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = get_option( 'ad_options' );
		// output the field
		?>
		<input type="text" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="ad_options[<?php echo esc_attr( $args['label_for'] ); ?>]" 
		value="<?php echo isset( $options[ $args['label_for'] ] ) ? $options[ $args['label_for'] ] : '' ; ?>" />
		<?php
	}

	/**
	 * Callback for options field
	 * 
	 * @since 1.0.0
	 * 
	 * @param Array arguements defined in add_settings_field()
	 */
	public function ad_zcrm_CID_field_cb( $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = get_option( 'ad_options' );
		// output the field
		?>
		<input type="text" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="ad_options[<?php echo esc_attr( $args['label_for'] ); ?>]" 
		value="<?php echo isset( $options[ $args['label_for'] ] ) ? $options[ $args['label_for'] ] : '' ; ?>" />
		<?php
	}

	/**
	 * Callback for options field
	 * 
	 * @since 1.0.0
	 * 
	 * @param Array arguements defined in add_settings_field()
	 */
	public function ad_zcrm_Csecret_field_cb( $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = get_option( 'ad_options' );
		// output the field
		?>
		<input type="text" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="ad_options[<?php echo esc_attr( $args['label_for'] ); ?>]" 
		value="<?php echo isset( $options[ $args['label_for'] ] ) ? $options[ $args['label_for'] ] : '' ; ?>" />
		<?php
	}

		/**
	 * Callback for options field
	 * 
	 * @since 1.0.0
	 * 
	 * @param Array arguements defined in add_settings_field()
	 */
	public function ad_zcrm_email_field_cb( $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = get_option( 'ad_options' );
		// output the field
		?>
		<input type="email" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="ad_options[<?php echo esc_attr( $args['label_for'] ); ?>]" 
		value="<?php echo isset( $options[ $args['label_for'] ] ) ? $options[ $args['label_for'] ] : '' ; ?>" />
		<?php
	}

	/**
	 * Callback for options field
	 * 
	 * @since 1.0.0
	 * 
	 * @param Array arguements defined in add_settings_field()
	 */
	public function ad_zcrm_redirect_field_cb( $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = get_option( 'ad_options' );
		// output the field
		?>
		<input type="text" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="ad_options[<?php echo esc_attr( $args['label_for'] ); ?>]" 
		value="<?php echo isset( $options[ $args['label_for'] ] ) ? $options[ $args['label_for'] ] : '' ; ?>" />
		<?php
	}

	/**
	 * Callback for options field
	 * 
	 * @since 1.0.0
	 * 
	 * @param Array arguements defined in add_settings_field()
	 */
	public function ad_zcrm_temp_token_cb( $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = get_option( 'ad_options' );
		// output the field
		?>
		<input type="text" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="ad_options[<?php echo esc_attr( $args['label_for'] ); ?>]" 
		value="<?php echo isset( $options[ $args['label_for'] ] ) ? $options[ $args['label_for'] ] : '' ; ?>" />
		<button id="generate_token"><?php echo __('Generate Access Tokens', 'adept-drivers'); ?></button>
		<div class="zcrm_token_status"></div>
		<?php
	}

	/**
	 * Callback for options field
	 * 
	 * @since 1.0.0
	 * 
	 * @param Array arguements defined in add_settings_field()
	 */
	public function ad_moodle_api_token_cb( $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = get_option( 'ad_options' );
		// output the field
		?>
		<input type="text" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="ad_options[<?php echo esc_attr( $args['label_for'] ); ?>]" 
		value="<?php echo isset( $options[ $args['label_for'] ] ) ? $options[ $args['label_for'] ] : '' ; ?>" />
		<?php
	}

	/**
	 * Callback for options field
	 * 
	 * @since 1.0.0
	 * 
	 * @param Array arguements defined in add_settings_field()
	 */
	public function ad_moodle_company_id_cb( $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = get_option( 'ad_options' );
		// output the field
		?>
		<input type="text" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="ad_options[<?php echo esc_attr( $args['label_for'] ); ?>]" 
		value="<?php echo isset( $options[ $args['label_for'] ] ) ? $options[ $args['label_for'] ] : '' ; ?>" />
		<?php
	}

	/**
	 * Callback for options field
	 * 
	 * @since 1.0.0
	 * 
	 * @param Array arguements defined in add_settings_field()
	 */
	public function ad_google_api_key_cb( $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = get_option( 'ad_options' );
		// output the field
		?>
		<input type="text" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="ad_options[<?php echo esc_attr( $args['label_for'] ); ?>]" 
		value="<?php echo isset( $options[ $args['label_for'] ] ) ? $options[ $args['label_for'] ] : '' ; ?>" />
		<?php
	}

    /**
	 * Register the admin menu page
	 * 
	 * @since	1.0.0
	 */
	public function add_admin_menu() {
		$hook =  add_menu_page( __('Adept Drivers', 'adept-drivers'), __('Adept Drivers', 'adept-drivers'), 'manage_options', 'adept-drivers-plugin', [$this, 'plugin_main_page'], plugin_dir_url( __FILE__ ) . '/img/steering-wheel.svg' );
        add_submenu_page( 'adept-drivers-plugin', __('Instructors', 'adept-drivers'), __('Instructors', 'adept-drivers'), 'manage_options', 'adept-drivers-plugin-instructors', [new Adept_Drivers_Instructors, 'render_page'] );
        add_submenu_page( 'adept-drivers-plugin', __('Bookings', 'adept-drivers'), __('Bookings', 'adept-drivers'), 'manage_options', 'adept-drivers-plugin-bookings', [new Adept_Drivers_Public_Booking, 'render_page'] );
        add_submenu_page( 'adept-drivers-plugin', __('Students', 'adept-drivers'), __('Students', 'adept-drivers'), 'manage_options', 'adept-drivers-plugin-students', [new Adept_Drivers_Students, 'render_students_page'] );
        add_submenu_page( 'adept-drivers-plugin', __('Settings', 'adept-drivers'), __('Settings', 'adept-drivers'), 'manage_options', 'adept-drivers-plugin-settings', [$this, 'plugin_settings_page'] );
        add_submenu_page( 'adept-drivers-plugin', __('Query', 'adept-drivers'), __('Query', 'adept-drivers'), 'manage_options', 'adept-drivers-plugin-test', [$this, 'plugin_tests_page'] );

        // add_submenu_page('wp-quotes-plugin', __('Forms', 'wpquotes'), __('Forms', 'wpquotes'), 'manage_options', 'wp-quotes-plugin-forms');

		// add_submenu_page( null, 'New WPQ Form1', 'New WPQ Form1', 'manage-options', 'wpq-new-form1', 'wpq_admin_page_new_form');
    }
    /**
     * Render Main Plugin page
     *
     * @return void
     */
    public function plugin_main_page(){
        include 'partials/adept-drivers-admin-dashboard.php';
    }
    
    /**
     * Render Plugin Settings Page
     *
     * @return void
     */
    public function plugin_settings_page(){
        include 'partials/adept-drivers-admin-settings.php';
    }

    public function plugin_tests_page(){
        include 'partials/adept-drivers-admin-tests.php';
    }

    /**
	 * Function to run all admin hooks
	 * 
	 * @since 1.0.0
	 */
	public function run_all(){
	}
}
