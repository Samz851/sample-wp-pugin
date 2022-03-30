<?php

/**
 * Fired during plugin activation
 *
 * @link       https://samiscoding.com
 * @since      1.0.0
 *
 * @package    Adept_Drivers
 * @subpackage Adept_Drivers/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Adept_Drivers
 * @subpackage Adept_Drivers/includes
 * @author     Samer Alotaibi <sam@samiscoding.com>
 */
class Adept_Drivers_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		global $wpdb;
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		$charset_collate = $wpdb->get_charset_collate();
		$table_name = $wpdb->prefix . 'ad_bookings';
		// $sql = "DROP TABLE IF EXISTS $table_name";
		// dbDelta( $sql );
		$foreign_table = $wpdb->prefix . 'users';
		$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			student_id 	bigint(20) NOT NULL,
			tookan_id bigint(12) NOT NULL,
			booking_date datetime NULL,
			booking_end datetime NULL,
			instructor bigint(64) NULL,
			job_id bigint(12) NULL,
			tracking_url VARCHAR(255) NULL,
			acknowledgment BOOLEAN NULL,
			status BOOLEAN,
			PRIMARY KEY (id)
		) $charset_collate;";
			dbDelta( $sql );

		$table_name = $wpdb->prefix . 'ad_instructors';
		// $sql = "DROP TABLE IF EXISTS $table_name";
		// dbDelta( $sql );
		$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			instructor_id 	bigint(20) NOT NULL,
			inst_name varchar(255) NOT NULL,
			latitude decimal(11,7) NOT NULL,
			longitude decimal(11,7) NOT NULL,
			booking_ids varchar(255) NULL,
			type varchar(25) NOT NULL,
			PRIMARY KEY (id)
		) $charset_collate;";
			dbDelta( $sql );
	}

}
