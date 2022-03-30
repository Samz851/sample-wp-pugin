<?php
require plugin_dir_path( __DIR__ ) . '../../vendor/autoload.php';

/**
 * My Account Dashboard
 *
 * Shows the first intro screen on the account dashboard.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/dashboard.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 4.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$allowed_html = array(
	'a' => array(
		'href' => array(),
	),
);
$student_inst = new Adept_Drivers_Students();
$student = $student_inst->get_student_dashboard_obj(get_current_user_id());
?>


<div class="ad-student-dashboard row">
	<div class="ad-student-dasboard-panel row">
		<div class="ad-dash-unit col-md-6">
			<div class="ad-dash-unit-student-core">
				<table>
					<tr>
						<td><?php echo $student['first_name'] . ' ' . $student['last_name'] ;?></td>
						<td><?php echo $current_user->user_email;?></td>
					</tr>
					<tr>
						<td>Total Bookings: <?php echo $student['total_bookings'];?></td>
						<td>Remaining: <?php echo $student['student_car_sessions_count']; ?></td>
					</tr>
				</table>
			</div>
		</div>
		<div class="ad-dash-unit col-md-6">
			<div class="ad-dash-unit-student-core">
				<table>
					<tr>
						<td>License: <?php echo !empty($student['student_license']) ? $student['student_license'] : 'N/A' ;?></td>
						<td>Issued: <?php echo !empty($student['student_lcissue']) ? $student['student_lcissue'] : 'N/A' ;?></td>
					</tr>
					<tr>
						<td>G2 Elgibility: <?php echo !empty($student['student_g2el']) ? $student['student_g2el'] : 'N/A';?></td>
						<td>Remaining: <?php echo !empty($student['exam_date']) ? $student['exam_date'] : 'N/A'; ?></td>
					</tr>
				</table>
			</div>
		</div>
		<div class="ad-dash-unit col-md-6">
			<div class="ad-dash-unit-student-core">
				<table>
					<tr>
						<td>Course Progress: </td>
						<td><?php echo $student['student_progress'] !== '-' ? $student['student_progress'] : 'N/A'; ?></td>
					</tr>
					<tr>
						<td>Login </td>
						<td><?php 
							if($student['has_LMS'] == 'yes') : ?>
							<a href="https://adeptdrivers.learndrive.ca">LMS</a>
							<?php endif; ?>
						</td>
					</tr>
				</table>
			</div>
		</div>
		<div class="ad-dash-unit col-md-6">
			<div class="ad-dash-unit-student-core">
				<table>
					<tr>
						<td><?php echo $student['billing_address_1'] ;?></td>
						<td><?php echo $student['billing_city'];?></td>
					</tr>
					<tr>
						<td><?php echo $student['billing_postcode'];?></td>
						<td><?php echo $student['billing_state']; ?></td>
					</tr>
					<tr>
						<td><?php echo preg_replace( '/([0-9]{3})([0-9]{3})([0-9]{4})/', '($1) $2-$3', $student['billing_phone'] );?></td>
						<td><?php echo $student['student_dob']; ?></td>
					</tr>
					<tr>
						<td colspan="2"><a href="/my-account/edit-address/billing/" id="edit-address">Edit</a></td>
					</tr>
				</table>
			</div>
		</div>
	</div>
</div>

<?php
	/**
	 * My Account dashboard.
	 *
	 * @since 2.6.0
	 */
	do_action( 'woocommerce_account_dashboard' );

	/**
	 * Deprecated woocommerce_before_my_account action.
	 *
	 * @deprecated 2.6.0
	 */
	do_action( 'woocommerce_before_my_account' );

	/**
	 * Deprecated woocommerce_after_my_account action.
	 *
	 * @deprecated 2.6.0
	 */
	do_action( 'woocommerce_after_my_account' );

/* Omit closing PHP tag at the end of PHP files to avoid "headers already sent" issues. */
