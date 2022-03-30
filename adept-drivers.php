<?php
require plugin_dir_path( __FILE__ ) . '/vendor/autoload.php';

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://samiscoding.com
 * @since             1.0.0
 * @package           Adept_Drivers
 *
 * @wordpress-plugin
 * Plugin Name:       Adept Drivers
 * Plugin URI:        https://adeptdrivers.com
 * Description:       Adept Drivers API Solution
 * Version:           1.0.0
 * Author:            Samer Alotaibi
 * Author URI:        https://samiscoding.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       adept-drivers
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'ADEPT_DRIVERS_VERSION', '1.0.1' );

/**
 * Database table for the plugin
 * 
 */
define('ADEPT_DRIVERS_DBTABLE', 'ad_bookings');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-adept-drivers-activator.php
 */
function activate_adept_drivers() {
	Adept_Drivers_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-adept-drivers-deactivator.php
 */
function deactivate_adept_drivers() {
	Adept_Drivers_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_adept_drivers' );
register_deactivation_hook( __FILE__, 'deactivate_adept_drivers' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_adept_drivers() {

	$plugin = new Adept_Drivers();
	$plugin->run();

}
run_adept_drivers();
