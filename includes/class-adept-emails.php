<?php
require plugin_dir_path( __DIR__ ) . '/vendor/autoload.php';

/**
 * The file that contains email templates
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
class Adept_Drivers_Emails {

	/**
	 * Logger
	 */
	public $logger;

	public function __construct() {
		$this->logger = new Adept_Drivers_Logger('EMAILER');
	}

	/**
	 * Email for successful Registration
	 * 
	 * @param Array $data
	 * 
	 * @return String $body
	 */
	public static function successful_registration_with_agent($data) {

		$body = "<h5>Dear Student Name,<h5>

		<p>Thank you for registering with Drive Ontario!</p>
		
		<p>We are delighted that you have chosen us to help you with your journey to obtain your Ontario driver’s license.</p>
		
		<p>Your username and password is below:</p>
		
		<p>Username: $data[username]</p>
		<p>Password: $data[password]</p>
		
		<p>To login visit our website or click <a href='" . get_permalink( wc_get_page_id( 'myaccount' ) ) . "'>here</a>. to start booking you in car lessons.</p>
		
		<p>Your Driving Instructor is $data[instructor] </p>
		<p>Best Regards,</p>
		<p>Drive Ontario</p>";

		
		// {}, 
		// Vehicle Make, Model, Colour, License Plate
		
		// (this data, pull from tookan agent profile)
		
		// If you have an questions email register@driveontario.ca or 905-272-3511
		
		

		// '
		return $body;

	}

	/**
	 * Student with missing agent (agent deleted)
	 * 
	 * @param Array $data
	 * 
	 * @return String $body
	 */
	public static function student_agent_deleted($data){
		$body = "<h5>Warning!<h5>

		<p>Student no longer has Agent -- Agent deleted</p>
		
		<p>The student below is no longer assigned to any Instructors on Tookan. They will not be able to book lessons until the issue is resolved</p>
		
		<p>Student and Agent Information:</p>
		
		<p>Student Name: $data[student_name]</p>
		<p>Student ID: $data[student_id]</p>
		<p>Agent Name: $data[agent_name]</p>
		<p>Agent ID: $data[agent_id]</p>

		<p>Best Regards,</p>
		<p>Drive Ontario</p>";

		return $body;
	}

	function log_mailer_errors( $wp_error ){
		$this->logger->Log_Error($wp_error->get_error_message(), '-- Failed Email');
	  }

}
