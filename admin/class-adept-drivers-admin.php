<?php
require plugin_dir_path( __DIR__ ) . '/vendor/autoload.php';

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Adept_Drivers
 * @subpackage Adept_Drivers/admin
 * @author     Samer Alotaibi <sam@samiscoding.com>
 */
class Adept_Drivers_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * User password
	 * @access private
	 * @var string
	 */
	private $pass;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->ad_set_user_roles();
		$this->run_all();

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Adept_Drivers_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Adept_Drivers_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		if($_GET['page'] == 'adept-drivers-plugin-students'){
			wp_enqueue_style( 'bootstrap-datepicker-css' , plugin_dir_url(__FILE__) . 'css/bootstrap-datepicker.min.css', array(), $this->version, 'all');
			wp_enqueue_style( 'bootstrap-css' , plugin_dir_url(__FILE__) . 'css/bootstrap.min.css', array(), $this->version, 'all');
		}
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/adept-drivers-admin.css', array(), $this->version, 'all' );
		wp_enqueue_style( 'ad-fa', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css', array(), $this->version, 'all');


	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Adept_Drivers_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Adept_Drivers_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( 'moments-js' , plugin_dir_url(__FILE__) . 'js/moments.js', array(), $this->version, true);
		wp_enqueue_script( 'bootstrap-js' , plugin_dir_url(__FILE__) . 'js/bootstrap.min.js', array('moments-js'), $this->version, true);
		wp_enqueue_script ( 'bootstrap-datepicker-js' , plugin_dir_url(__FILE__) . 'js/bootstrap-datepicker.min.js', array('bootstrap-js'), $this->version, true);
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/adept-drivers-admin.js', array( 'jquery', 'bootstrap-datepicker-js' ), $this->version, false );
		if($_GET['post_type'] == 'product'){
			wp_enqueue_script( 'ad-z-sync', plugin_dir_url( __FILE__ ) . 'js/adept-drivers-sync.js', array( 'jquery'), $this->version, false );
		}


	}

	/**
	 * Prep User Roles and Caps.
	 *
	 * @since    1.0.0
	 */
	private function ad_set_user_roles(){

		/**
		 * These functions remove default user roles
		 */
		remove_role( 'subscriber' );
		remove_role( 'editor' );
		remove_role( 'contributor' );
		remove_role( 'author' );
		remove_role( 'shop_manager' );
		// remove_role( 'customer' );

		/**
		 * These functions add new user roles
		 */
		add_role( 'instructor' , __( 'Instructor', 'adept-drivers' ),
			array()
		 );
		add_role( 'student' , __( 'Student', 'adept-drivers' ),
			array(
				'read' => true,
			)
		);
	}

	/**
	 * Add custom fields to each product
	 * 
	 * @since 1.0.0
	 */
	function ad_wc_product_custom_fields(){
        global $woocommerce, $post;
        echo '<div class="product_custom_field">';

        woocommerce_wp_text_input(
            array(
                'id' => 'in_car_sessions',
                'placeholder' => 'In Car Sessions',
                'label' => __('In Car Sessions', 'adept-drivers'),
				'class' => 'ad-prod-meta',
				'type'  => 'number',
            )
		);
		woocommerce_wp_checkbox( 
			array( 
				'id'            => 'includes_bde', 
				'wrapper_class' => 'ad-prod-meta', 
				'label'         => __('Includes LMS Course', 'adept-adapters' ), 
				'description'   => __( 'Includes an LMS course with this product', 'adept-drivers' ) 
				)
			);
		woocommerce_wp_text_input( 
			array( 
				'id'            => 'lesson_duration', 
				'wrapper_class' => 'ad-prod-meta', 
				'placeholder' => 'Intervals of 30 mins',
				'label'         => __('In Car Session Duration (30min Intervals)', 'adept-adapters' ), 
				'description'   => __( 'How long is each lesson in x 30min.', 'adept-drivers' ),
				'type'  => 'number',
				'custom_attributes' => array(
					'min'	=> '0',
					'max' => 6
				)
				)
			);
        echo '</div>';
	}
	
	/**
	 * Save custom fields of products
	 * 
	 * @since 1.0.0
	 */
	function ad_wc_product_custom_fields_save( $post_id ){
		$fields = $_POST;
		// $metas = ['lab_report', 'faq', 'why_buy', 'suggested_use', 'ingredients', 'product_facts', 'amount_cbd', 'total_cbd', 'size_volume'];
		// $wysiwyg_keys = ['faq', 'ingredients', 'why_buy'];
		foreach ($fields as $key => $value) {
			if( $key == 'in_car_sessions' || $key == 'includes_bde' || $key == 'lesson_duration'){
				update_post_meta($post_id, $key, $value);
			}
		}
	}

	/**
	 * add registration fields
	 * 
	 * @since 1.0.0
	 */
	function ad_extra_register_fields() {?>
		<p class="form-row form-row-wide">
		<label for="reg_billing_phone"><?php _e( 'Phone', 'woocommerce' ); ?></label>
		<input type="text" class="input-text" name="billing_phone" id="reg_billing_phone" value="<?php esc_attr_e( $_POST['billing_phone'] ); ?>" />
		</p>
		<p class="form-row form-row-first">
		<label for="reg_billing_first_name"><?php _e( 'First name', 'adept-drivers' ); ?><span class="required">*</span></label>
		<input type="text" class="input-text" name="billing_first_name" id="reg_billing_first_name" value="<?php if ( ! empty( $_POST['billing_first_name'] ) ) esc_attr_e( $_POST['billing_first_name'] ); ?>" />
		</p>
		<p class="form-row form-row-last">
		<label for="reg_billing_last_name"><?php _e( 'Last name', 'adept-drivers' ); ?><span class="required">*</span></label>
		<input type="text" class="input-text" name="billing_last_name" id="reg_billing_last_name" value="<?php if ( ! empty( $_POST['billing_last_name'] ) ) esc_attr_e( $_POST['billing_last_name'] ); ?>" />
		</p>
		<p class="form-row form-row-wide">
		<label for="student_dob"><?php _e( 'Date of Birth', 'adept-drivers' ); ?><span class="required">*</span></label>
		<input type="date" class="input-text" name="student_dob" id="student_dob" value="<?php if ( ! empty( $_POST['student_dob'] ) ) esc_attr_e( $_POST['student_dob'] ); ?>" />
		</p>
		<p class="form-row form-row-first">
		<label for="reg_billing_address_1"><?php _e( 'Address', 'adept-drivers' ); ?><span class="required">*</span></label>
		<input type="text" class="input-text" name="billing_address_1" id="reg_billing_address_1" value="<?php if ( ! empty( $_POST['billing_address_1'] ) ) esc_attr_e( $_POST['billing_address_1'] ); ?>" />
		</p>
		<p class="form-row form-row-last">
		<label for="reg_billing_city"><?php _e( 'City', 'adept-drivers' ); ?><span class="required">*</span></label>
		<input type="text" class="input-text" name="billing_city" id="reg_billing_city" value="<?php if ( ! empty( $_POST['billing_city'] ) ) esc_attr_e( $_POST['billing_city'] ); ?>" />
		</p>
		<p class="form-row form-row-first">
		<label for="reg_billing_postcode"><?php _e( 'Postal Code', 'adept-drivers' ); ?><span class="required">*</span></label>
		<input type="text" class="input-text" name="billing_postcode" id="reg_billing_postcode" value="<?php if ( ! empty( $_POST['billing_postcode'] ) ) esc_attr_e( $_POST['billing_address_1'] ); ?>" />
		</p>
		<p class="form-row form-row-last">
		<label for="reg_billing_state"><?php _e( 'Province', 'adept-drivers' ); ?><span class="required">*</span></label>
		<input type="text" class="input-text" name="billing_state" id="reg_billing_state" value="<?php if ( ! empty( $_POST['billing_state'] ) ) esc_attr_e( $_POST['billing_state'] ); ?>" />
		</p>
		<p class="form-row form-row-first">
		<label for="student_license"><?php _e( 'License #', 'adept-drivers' ); ?><span class="required">*</span></label>
		<input type="text" class="input-text" name="student_license" id="student_license" value="<?php if ( ! empty( $_POST['student_license'] ) ) esc_attr_e( $_POST['student_license'] ); ?>" />
		</p>
		<p class="form-row form-row-last">
		<label for="student_lcissue"><?php _e( 'License Issued', 'adept-drivers' ); ?><span class="required">*</span></label>
		<input type="date" class="input-text" name="student_lcissue" id="student_lcissue" value="<?php if ( ! empty( $_POST['student_lcissue'] ) ) esc_attr_e( $_POST['student_lcissue'] ); ?>" />
		</p>
		<div class="clear"></div>
		<?php
	  }

	  /**
	   * Validation for Registration extra field
	   * 
	   * @param String $username
	   * 
	   * @param String $email
	   * 
	   * @param Object $validation_errors
	   * 
	   * @return Object $validation_errors
	   */
		public function ad_validate_extra_register_fields( $username, $email, $validation_errors ) {
			if ( isset( $_POST['student_dob'] ) && empty( $_POST['student_dob'] ) ) {
				$validation_errors->add( 'student_dob', __( '<strong>Error</strong>: Date of Birth is required!', 'adept-drivers' ) );
			}
			if ( isset( $_POST['billing_address_1'] ) && empty( $_POST['billing_address_1'] ) ) {
				$validation_errors->add( 'billing_address_1', __( '<strong>Error</strong>: Address is required!.', 'adept-drivers' ) );
			}
			if ( isset( $_POST['billing_city'] ) && empty( $_POST['billing_city'] ) ) {
				$validation_errors->add( 'billing_city', __( '<strong>Error</strong>: City is required!.', 'adept-drivers' ) );
			}
			if ( isset( $_POST['billing_postcode'] ) && empty( $_POST['billing_postcode'] ) ) {
				$validation_errors->add( 'billing_postcode', __( '<strong>Error</strong>: Postal Code is required!.', 'adept-drivers' ) );
			}
			if ( isset( $_POST['billing_state'] ) && empty( $_POST['billing_state'] ) ) {
				$validation_errors->add( 'billing_state', __( '<strong>Error</strong>: Province is required!.', 'adept-drivers' ) );
			}
			$this->pass = $_POST['password'];
			return $validation_errors;
		}
	  
	  /**
	   * Deactivate all newly registered users
	   * 
	   * @param int $user_id
	   * 
	   * @since 1.0.0
	   */
	  function inactive_user_registration( $user_id ){
		if( !empty($_POST) ){
			add_user_meta( $user_id, 'ad_is_active', false, true);

			//get user coordinates
			//First get metas to format address string
			$user_metas = get_user_meta($user_id);
			//then get address string
			$add_string = $user_metas['billing_address_1'] . ', ' . $user_metas['billing_city'] . ' ' . $user_metas['billing_postal'] . ', ' . $user_metas['billing_state'] . ' Canada';
			//then initialize geocoder
			$geocorder = new Adept_Drivers_Geocoding($add_string);
			//then get coordinates
			$coordinates = $geocorder->geocode();
			//Finally save coordinates
			add_user_meta( $user_id, 'coordinates', array('lat' => $coordinates[0], 'long' => $coordinates[1]), true);

		}
	  }

	  /**
	   * adjust query to skip inactive users
	   * 
	   * @param WP_User_Query $args
	   * 
	   * @since 1.0.0
	   */
	  function skip_inactive_user_query( $args ){
		  $args['meta_query'] = array(
			'relation' => 'OR',
			array(
				'key'     => 'ad_is_active',
                'value'   => true,
			),
			array(
				'key'     => 'wp_user_level',
				'value' => 10
			)
			
		  );

		  return $args;
	  }

	/**
	 * Activate users after purchase and process user record
	 * 
	 * @param int $order_id
	 * @since 1.0.0
	 */
	function activate_user_after_purchase( $order_id ){
		$register_lms = false;
		$booking_lessons = 0;
		$order = wc_get_order( $order_id );
		$userID = $order->get_user_id();
		$order->add_order_note( $userID );
		$order->add_order_note(__('This is a test note', 'adept-drivers'));
		$user_metas = get_user_meta( $userID );
		if( $userID ){
			$user = get_userdata($userID);
			// $pass = wp_generate_password( $length = 12, $include_standard_special_chars = false );
			$user_data = array(                
				"username" => $user->user_nicename,
				"password" => $this->pass,
				"firstname" => $user_metas['first_name'],
				"lastname" => $user_metas['last_name'],
				"email" => $user->user_meail,
				"phone1" => $user_metas['billing_phone'],     
			);

			update_user_meta( $userID, 'ad_is_active', true);
			// $order = wc_get_order( $order_id );
			$items = $order->get_items();
			foreach ( $items as $item ) {
				// $product_name = $item->get_name();
				$product_id = $item->get_product_id();
				if(get_post_meta( $product_id, 'includes_bde', true ) == 'yes'){
					$register_lms = true;
				}
				$bookings = get_post_meta( $product_id, 'in_car_sessions', true );
				if($bookings && $bookings > 0){
					$booking_lessons = $bookings;
				}
				$products = unserialize(get_user_meta($userID, 'student_products', true));
				if($products){
					$products[] = $product_id;
				}else{
					$products = array($product_id);
				}
				update_user_meta( $userID, 'student_product', $products );
			}
			if($register_lms){
				$LMS = new Adept_Drivers_LMS();
				$LMS_user = $LMS->process_user($user_data);
				add_user_meta($userID, 'lmsid', $LMS_user, true);

			}
			if($bookings > 0){
				//Obj for tookan
				$customer_data = array(
					'name' => $user->display_name,
					'phone' => $user_metas['billing_phone'],
					'email' => $user->user_email,
					'address' => $user_metas['billing_address_1'] . ', ' . $user_metas['billing_city'] . ' ' . $user_metas['billing_postal'] . ', ' . $user_metas['billing_state'] . ' Canada',
					'latitude' => maybe_unserialize($user_metas['coordinates']['lat']),
					'longitude' => maybe_unserialize($user_metas['coordinates']['long'])
				);
				$TOKAAN = new Adept_Drivers_Tookan();
				$customer = $TOKAAN->add_customer($customer_data);
				if($customer['data']['customer_id']){
                    add_user_meta( $userID, 'ad_student_tookan_id', $customer['data']['customer_id'], true);
                    $agentID = array();
                    // Get agents near this customer
                    $agent = $TOKAAN->get_agents_near_customer( $customer['data']['customer_id'] );
                    if($agent) {
                        array_push($agentID, $agent[0]['fleet_id']);
                    }else{
                        //Search locally
                        $instructor_ins = new Adept_Drivers_Instructors();
                        $agent_id = $instructor_ins->get_nearest_instructor(array('lat' => maybe_unserialize($user_metas['coordinates']['lat']), 'long' => maybe_unserialize($user_metas['coordinates']['long'])));
                        if($agent_id) array_push($agentID, $agent_id);
                    }
                    add_user_meta( $userID, 'ad_student_instructor', $agentID, true);
                }
			}

		}
	}

	/**
	 * Hide Admin pages from users
	 * 
	 */
	function ad_remove_menu_pages() {

		global $user_ID;
		
		if ( $user_ID != 1 ) {
			remove_menu_page('edit.php'); // Posts
			remove_menu_page('upload.php'); // Media
			remove_menu_page('link-manager.php'); // Links
			remove_menu_page('edit-comments.php'); // Comments
			remove_menu_page('edit.php?post_type=page'); // Pages
			remove_menu_page('plugins.php'); // Plugins
			remove_menu_page('themes.php'); // Appearance
			remove_menu_page('tools.php'); // Tools
			remove_menu_page('options-general.php'); // Settings
			remove_menu_page('edit.php?post_type=cpt_courses');
			remove_menu_page( 'edit.php?post_type=cpt_layouts' );
			remove_menu_page( 'edit.php?post_type=cpt_services' );
			remove_menu_page( 'edit.php?post_type=cpt_team' );
			remove_menu_page( 'edit.php?post_type=cpt_testimonials' );
			remove_menu_page( 'admin.php?page=wpcf7' );
			remove_menu_page( 'edit.php?post_type=eb_course' );
			remove_menu_page( 'admin.php?page=wc-admin&path=/analytics/overview' );
			remove_menu_page( 'admin.php?page=wc-admin&path=/marketing' );
			remove_menu_page( 'admin.php?page=vc-general' );
			remove_menu_page( 'admin.php?page=wp-mail-smtp' );
			remove_menu_page( 'admin.php?page=revslider' );
			remove_menu_page( 'admin.php?page=essential-grid' );
			remove_menu_page( 'admin.php?page=themepunch-google-fonts' );
			remove_menu_page( 'essential-grid' );
		}
	}

	/**
	 * Add Endpoint for User Dashboard
	 * 
	 */
	function ad_booking_endpoint() {
		add_rewrite_endpoint( 'lessons-booking', EP_ROOT | EP_PAGES );
	}
	
	/**
	 * Add endpoint to Dashboard menue
	 * 
	 * @param Array $items menu items
	 * 
	 * @return Array $items
	 */
	function ad_booking_menu_items( $items ) {
		unset($items['downloads']);

		$items = array_slice($items, 0, 2) + array('lessons-booking' => __( 'Booking', 'adept-drivers' )) + array_slice( $items, 2, NULL, true );


		return $items;
	}

	/**
	 * Add the endpoint to the quary variables
	 * 
	 * @param Array $vars
	 * 
	 * @return Array $items
	 */
	function ad_booking_query_vars( $vars ) {
		$vars[] = 'lessons-booking';
	
		return $vars;
	}
	
	/**
	 * Function to run all admin hooks
	 * 
	 * @since 1.0.0
	 */
	public function run_all(){
		require_once plugin_dir_path( __FILE__ ) . '/class-adept-drivers-pages.php';
		$pages = new Adept_Drivers_Pages();
		$ad_tookan = new Adept_Drivers_Tookan;
		$ad_tookan->run_all();
		$pages->run_all();

		$studentsClass = new Adept_Drivers_Students();
		$studentsClass->run_all();

	}


}
