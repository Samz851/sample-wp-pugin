<?php
require plugin_dir_path( __DIR__ ) . '/vendor/autoload.php';

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://samiscoding.com
 * @since      1.0.0
 *
 * @package    Adept_Drivers
 * @subpackage Adept_Drivers/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Adept_Drivers
 * @subpackage Adept_Drivers/includes
 * @author     Samer Alotaibi <sam@samiscoding.com>
 */
class Adept_Drivers {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Adept_Drivers_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'ADEPT_DRIVERS_VERSION' ) ) {
			$this->version = ADEPT_DRIVERS_VERSION;
		} else {
			$this->version = '1.0.1';
		}
		$this->plugin_name = 'adept-drivers';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Adept_Drivers_Loader. Orchestrates the hooks of the plugin.
	 * - Adept_Drivers_i18n. Defines internationalization functionality.
	 * - Adept_Drivers_Admin. Defines all hooks for the admin area.
	 * - Adept_Drivers_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-adept-drivers-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-adept-drivers-public.php';

		$this->loader = new Adept_Drivers_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Adept_Drivers_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Adept_Drivers_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Adept_Drivers_Admin( $this->get_plugin_name(), $this->get_version() );
		require_once plugin_dir_path( __FILE__  ) . '/class-adept-drivers-zcrm.php';
		$ad_zcrm = new Adept_Drivers_ZCRM();
		require_once plugin_dir_path(__FILE__) . '../admin/class-adept-drivers-pages.php';
		$plugin_pages = new Adept_Drivers_Pages();
		require_once plugin_dir_path( __FILE__ ) . '/class-adept-drivers-lms.php';
		$ad_lms = new Adept_Drivers_LMS();
		$emailer = new Adept_Drivers_Emails();
		$instructors = new Adept_Drivers_Instructors();
		$booking = 'booking';
		// add_action( 'rest_api_init', array($ad_zcrm, 'zcrm_resapi'));
		$this->loader->add_action( 'init', $plugin_admin, 'ad_booking_endpoint');
		$this->loader->add_filter( 'woocommerce_account_menu_items', $plugin_admin, 'ad_booking_menu_items');
		$this->loader->add_filter( 'query_vars', $plugin_admin, 'ad_booking_query_vars');
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'rest_api_init', $ad_zcrm, 'zcrm_resapi');
		$this->loader->add_action( 'admin_menu', $plugin_pages, 'add_admin_menu');
		$this->loader->add_action( 'admin_init', $plugin_pages, 'register_wpq_settings');
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'ad_remove_menu_pages');
		$this->loader->add_action( 'woocommerce_product_options_general_product_data', $plugin_admin, 'ad_wc_product_custom_fields');
		$this->loader->add_action( 'woocommerce_process_product_meta', $plugin_admin, 'ad_wc_product_custom_fields_save');
		$this->loader->add_action( 'woocommerce_register_form_start', $plugin_admin, 'ad_extra_register_fields');
		$this->loader->add_action( 'user_register', $plugin_admin, 'inactive_user_registration');
		$this->loader->add_action( 'woocommerce_register_post', $plugin_admin, 'ad_validate_extra_register_fields');
		$this->loader->add_action( 'woocommerce_payment_complete', $plugin_admin, 'activate_user_after_purchase');
		$this->loader->add_action( 'woocommerce_order_status_completed', $plugin_admin, 'activate_user_after_purchase');
		$this->loader->add_filter( 'users_list_table_query_args', $plugin_admin, 'skip_inactive_user_query');
		$this->loader->add_action( 'wp_mail_failed', $emailer, 'log_mailer_errors',10, 1 );
		$this->loader->add_action( 'wp_ajax_ad_get_all_agents_name_id', $instructors, 'get_agents_names_id');
		$this->loader->add_action( 'wp_ajax_ad_update_student_agent', $instructors, 'update_student_agent');
		$this->loader->add_filter( 'manage_edit-product_columns', $ad_zcrm, 'product_sync_column', 10, 3 );
		$this->loader->add_action( 'manage_posts_custom_column', $ad_zcrm, 'product_sync_action', 10, 3);
		$this->loader->add_action( 'wp_ajax_ad_sync_z_product', $ad_zcrm, 'product_sync');
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Adept_Drivers_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_filter( 'woocommerce_locate_template', $plugin_public, 'ad_override_wc_template', 1, 3);
		$this->loader->add_action( 'template_redirect', $plugin_public, 'ad_redirect_pre_checkout');
		$this->loader->add_action( 'woocommerce_account_lessons-booking_endpoint', $plugin_public, 'booking_page_cb');
		// $this->loader->add_action( 'woocommerce_save_account_details', $plugin_public, 'user_edited_profile');
		$this->loader->add_action( 'woocommerce_customer_save_address', $plugin_public, 'user_edited_profile', 10, 2);
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Adept_Drivers_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
