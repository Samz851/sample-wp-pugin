<?php
require plugin_dir_path( __DIR__ ) . '/vendor/autoload.php';

/**
 * Class for Tookan API Handler
 * 
 * @package Adept_Drivers
 * @subpackage Adept_Drivers/includes
 * @author Samer Alotaibi <sam@samiscoding.com>
 */
class Adept_Drivers_Tookan
{

    /**
     * The User's tookan api key
     *
     * @since 1.0.0
     * @access protected
     * @var string
     */
    protected $api_key;

    /**
     * Loger
     * 
     */
    public $logger;

    /**
     * All possible response codes
     * 
     * @since 1.0.0
     * @access protected
     * @var array
     */
    protected $responses = array (
        200 => 'SUCCESS',
        100 => 'PARAMETER_MISSING',
        101 => 'INVALID_KEY',
        200 => 'ACTION_COMPLETE',
        201 => 'SHOW_ERROR_MESSAGE',
        404 => 'ERROR_IN_EXECUTION'
    );

    /**
     * All Possible tasks status
     * 
     * @since 1.0.0
     * @access protected
     * @var array
     */
    protected $job_status = array(
        0 => 'Assigned',
        1 => 'Started',
        2 => 'Successful',
        3 => 'Failed',
        4 => 'InProgress',
        6 => 'Unassigned',
        7 => 'Accepted',
        8 => 'Decline',
        9 => 'Cancel',
        10 => 'Deleted'

    );

    /**
     * Main API URL
     * 
     * @since 1.0.0
     * @access protected
     * @var string
     */
    protected $api_url = 'https://api.tookanapp.com/v2/';

    /**
     * Constructor function
     * 
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->run_all();
        $this->api_key = get_option('ad_options')['ad_tookan_api'];
        $this->logger = new Adept_Drivers_Logger('TOOKAN');
    }

    /**
     * Return API Key
     *
     * @return string API Key
     */
    public function display_key(){
        $this->api_key = get_option('ad_options')['ad_tookan_api'];
        return $this->api_key;
    }

    /**
     * Create Task
     * 
     * @since 1.0.0
     * 
     */
    public function create_task($user, $pickupDatetime, $deliveryDatetime, $agents, $exam){
        $url = $this->api_url . 'create_task';
        $add_string = get_user_meta( $user->ID, 'billing_address_1', true ) . ', ' . get_user_meta( $user->ID, 'billing_city', true ) . ' ' . get_user_meta( $user->ID, 'billing_postcode', true ) . ', ' . get_user_meta( $user->ID, 'billing_state', true ) . ' Canada';
        $body = array(
            'customer_email' => $user->email,
            'customer_username' => $user->display_name,
            'customer_phone' => get_user_meta( $user->ID, 'billing_phone', true ),
            'customer_address'=> $add_string,
            'job_description'=> 'Lesson',
            'job_pickup_datetime'=> $pickupDatetime,
            'job_delivery_datetime'=> $deliveryDatetime,
            'has_pickup'=> '0',
            'has_delivery'=> '0',
            'layout_type'=> '2',
            'tracking_link'=> 1,
            'timezone'=> '-330',
            'api_key'=> $this->api_key,
            'auto_assignment'=> 1,
            'fleet_id'=> $agents[0],
            'ref_images'=> [
                'http=>//tookanapp.com/wp-content/uploads/2015/11/logo_dark.png',
                'http=>//tookanapp.com/wp-content/uploads/2015/11/logo_dark.png'
            ],
            'notify'=> 1,
            'tags'=> '',
            'geofence'=> 0
        );
        if($exam){
            $body['geofence'] = 1;
            unset($body['fleet_id']);
        }
        $response = wp_remote_post( $url, array(
            'method'      => 'POST',
            'timeout'     => 45,
            'redirection' => 5,
            'httpversion' => '1.0',
            'blocking'    => true,
            'headers'     => array('Content-Type'=> 'application/json'),
            'body'        => json_encode($body),
            'cookies'     => array()
            )
        );
        $this->logger->Log_Information(gettype($response['body']['data']), 'Add Task Response--TYPE');
        $this->logger->Log_Information(json_decode($response['body'], true), 'Add Task Response');
        if ( is_wp_error( $response )  ) {
            return array(
                "success" => false,
                "message" => $response
            );
        } else {
            $response_body = json_decode($response['body'], true);
            if(empty($response_body['data'])){
                return array(
                    "success" => false,
                    "message" => $response['message']
                );
            }else{
                return array(
                    "success" => true,
                    'agent_id' => $agents[0],
                    'job_id' => $response_body['data']['job_id'],
                    'tracking_url' => $response_body['data']['tracking_link']
                );
            }

        }

    }

    /**
     * Autoassign Task
     * 
     * @since 1.0.0
     */
    public function autoassign_task(){
        $task = $_REQUEST['booking_id'];
        $url = $this->api_url . 're_autoassign_task';
        $body = array(
            'api_key' => $this->api_key,
            'job_id' => $task
        );
        $response = wp_remote_post( $url, array(
            'method'      => 'POST',
            'timeout'     => 45,
            'redirection' => 5,
            'httpversion' => '1.0',
            'blocking'    => true,
            'headers'     => array('Content-Type'=> 'application/json'),
            'body'        => json_encode($body),
            'cookies'     => array()
            )
        );

        $this->logger->Log_Information($response, __FUNCTION__);
        
    }

    /**
     * Get All Agents Available
     * 
     * @since 1.0.0
     */
    public function get_all_agents(){
        $agents = array();
        // Get first the Captive Agents
        $captive = $this->get_all_captive_agents();
        $this->logger->Log_Information($captive, 'THIS IS CAPTIVE INFO');
        if($captive['success']){
            $agents = array_map(function($a){$a['type'] = 'captive'; return $a;}, $captive['data']);
        }else{
            wp_send_json($captive, 400);
        }

        //Get Freelancer Agents
        $freelance = $this->get_all_freelancer_agents();
        if($freelance['success']){
            $freelance['data'] = array_map(function($a){$a['type'] = 'freelance'; return $a;}, $freelance['data']);
            $agents = array_merge($agents, $freelance['data']);
        }else{
            wp_send_json($freelance, 400); 
        }

        //Save agents in DB
        $instructor = new Adept_Drivers_Instructors();
        $ids = array();
        $this->logger->Log_Information($agents, __FUNCTION__);
        foreach ($agents as $agent) {
            $agent_details = $this->get_agent_details($agent['fleet_id']);
            if(!$agent_details){
                return $this->logger->Log_Error($agent, '--ERROR--' . __FUNCTION__);
            }
            $updated = $instructor->insert_update_instructor(array(
                'instructor_id' => $agent['fleet_id'],
                'inst_name' => $agent['name'],
                'latitude' => $agent_details['home_latitude'],
                'longitude' => $agent_details['home_longitude'],
                'type' => $agent['type']
            ));
                array_push($ids, $agent['fleet_id']);
        }
        // Delete non existing agents
        $instructor->delete_agents($ids);
        wp_send_json(array(
            "success" => true
        ));


    }

    /**
     * Assing task to agent
     * 
     * @since 1.0.0
     */
    public function assign_task_to_agent($agentID = 0, $taskID = 0){
        $url = $this->api_url . 'assign_task';
        $taskID = 154551638;
        $agentID = 581960;
        $teamID = 354771;

        $body = array(
            'api_key'=> $this->api_key,
            'job_id'=> $taskID,
            'fleet_id'=> $agentID,
            'team_id'=> $teamID,
            'job_status'=> 6
        );

        $response = wp_remote_post( $url, array(
            'method'      => 'POST',
            'timeout'     => 45,
            'redirection' => 5,
            'httpversion' => '1.0',
            'blocking'    => true,
            'headers'     => array('Content-Type'=> 'application/json'),
            'body'        => json_encode($body),
            'cookies'     => array()
            )
        );
        if ( is_wp_error( $response ) ) {
            $this->logger->Log_Error($response, 'TOOKAN-ERROR');
        } else {
            $this->logger->Log_Information($response, 'TOOKAN-INFO');
        }
    }

    /**
     * Get Agents near customer
     * 
     * @since 1.0.0
     * 
     * @param INT $customer_id
     * 
     * @return Array agents
     */
    public function get_agents_near_customer( $customer_id = '' ){
        $url = $this->api_url . 'get_fleets_near_customer';

        $body = array(
            'api_key'=> $this->api_key,
            'customer_id'=> empty($customer_id) ? 28598175 : $customer_id,
            'radius_in_metres'=> 50000
        );

        $response = wp_remote_post( $url, array(
            'method'      => 'POST',
            'timeout'     => 45,
            'redirection' => 5,
            'httpversion' => '1.0',
            'blocking'    => true,
            'headers'     => array('Content-Type'=> 'application/json'),
            'body'        => json_encode($body),
            'cookies'     => array()
            )
        );


        if ( is_wp_error( $response ) ) {
            $this->logger->Log_Error($response['body'], 'Agent by proximity');
            return false;
        } else {
            
            $response_arr = json_decode( $response['body'], true);
            $this->logger->Log_Information($response_arr, 'Agent by promixity');
            $this->logger->Log_Information(array_keys($response_arr['data'][0]), 'Type of data');

            return $response_arr['data'][0]['fleet_id'];
        }
    }

    /**
     * Add Student as a customer
     * 
     * @since 1.0.0
     * 
     * @param Array $customer
     * 
     * @return mix customer ID | false
     */
    public function add_customer( $customer ){
        $url = $this->api_url . 'customer/add';

        $body = array(
            'api_key'=> $this->api_key,
            'user_type'=> 0,
            'name'=> $customer['name'],
            'phone' => $customer['phone'],
            'email' => $customer['email'],
            'address' => $customer['address'],
            'latitude' => $customer['latitude'],
            'longitude' => $customer['longitude']
        );

        $response = wp_remote_post( $url, array(
            'method'      => 'POST',
            'timeout'     => 45,
            'redirection' => 5,
            'httpversion' => '1.0',
            'blocking'    => true,
            'headers'     => array('Content-Type'=> 'application/json'),
            'body'        => json_encode($body),
            'cookies'     => array()
            )
        );


        if ( is_wp_error( $response ) ) {
            $this->logger->Log_Error($response['body'], 'Adding Customer');
            return false;
        } else {
            // wp_send_json(array(
            //     "success" => true,
            //     "message" => $response['body']
            // ));
            $this->logger->Log_Information($response['body'], 'Adding Customer');
            $this->logger->Log_Type(json_encode($response['body']), 'Adding Customer');

            return json_decode($response['body'], true);
        }
    }

    /**
     * Get Agent Data
     * 
     * @param Int $agentID
     * 
     * @return Array $agent
     */
    public function get_agent_details( $agentID ){
        $url = $this->api_url . 'view_fleet_profile';

        $body = array(
            'api_key'=> $this->api_key,
            'fleet_id' => $agentID,
            'include_home_address' => 1

        );
        $this->logger->Log_Information(array('ID' => $agentID, 'body' => $body), __FUNCTION__);
        $response = wp_remote_post( $url, array(
            'method'      => 'POST',
            'timeout'     => 45,
            'redirection' => 5,
            'httpversion' => '1.0',
            'blocking'    => true,
            'headers'     => array('Content-Type'=> 'application/json'),
            'body'        => json_encode($body),
            'cookies'     => array()
            )
        );


        if ( is_wp_error( $response ) ) {
            $this->logger->Log_Error($response['body'], __FUNCTION__);
            return false;
        } else {
            // wp_send_json(array(
            //     "success" => true,
            //     "message" => $response['body']
            // ));
            $this->logger->Log_Information($response['body'], __FUNCTION__);
            $this->logger->Log_Type(json_encode($response['body']), __FUNCTION__);
            $response_body = json_decode($response['body'], true);
            return $response_body['data']['fleet_details'][0];
        }
    }


    /**
     * Ajax to display key
     *
     * @return void
     */
    public function ajax_ad_display_key(){
        wp_send_json(array(
            "success"=> true,
            'message' => $this->display_key(),
        ), 200);
    }

    /**
     * Delete Task
     * 
     * @param Int $job_id
     * 
     * @return Bool
     */
    public function delete_task( $job_id ){
        $url = $this->api_url . 'delete_task';

        $body = array(
            'api_key'=> $this->api_key,
            'job_id' => $job_id

        );
        $response = wp_remote_post( $url, array(
            'method'      => 'POST',
            'timeout'     => 45,
            'redirection' => 5,
            'httpversion' => '1.0',
            'blocking'    => true,
            'headers'     => array('Content-Type'=> 'application/json'),
            'body'        => json_encode($body),
            'cookies'     => array()
            )
        );


        if ( is_wp_error( $response ) ) {
            return false;
            $this->logger->Log_Error($response['body'], __FUNCTION__);
        } else {
            $this->logger->Log_Information($response['body'], __FUNCTION__);
            //Process response schedule to return raw timetable data
            return true;
        }
    }

    /**
     * Get Agent Schedule
     * 
     * @param Int $agentID
     * @param String $dateFrom
     * @param String $dateTo
     * 
     * @return Array $schedule
     */
    public function get_agent_schedule( $agentID, $dateFrom, $dateTo ){
        $url = $this->api_url . 'get_fleets_monthly_availability';


        $body = array(
            'api_key' => $this->api_key,
            'fleet_id' => $agentID,
            'start_date' => $dateFrom,
            'end_date' => $dateTo

        );

        $response = wp_remote_post( $url, array(
            'method'      => 'POST',
            'timeout'     => 45,
            'redirection' => 5,
            'httpversion' => '1.0',
            'blocking'    => true,
            'headers'     => array('Content-Type'=> 'application/json'),
            'body'        => json_encode($body),
            'cookies'     => array() 
        ));
        if ( is_wp_error( $response ) ) {
            return false;
        } else {
            $response_body = json_decode($response['body'], true);
            $this->logger->Log_Information($response_body['data']['detailed_slots'], __FUNCTION__);
            $filtered = $this->filter_agent_schedule($response_body['data']['detailed_slots']);
            // $this->logger->Log_Information($response_body, __FUNCTION__);
            // foreach ($response_body['data']['fleet'] as $key => $value) {
            //     # code...
            // }
            return $filtered;
        }
    }

    /**
     * Helper function to sort out the agent's timing
     * 
     * @param Array $schedule
     * 
     * @return Array $filtered Schedule
     */
    public function filter_agent_schedule( $schedule ){
        // $this->logger->Log_Information(array('type' => gettype($schedule), '$schedule' => $schedule), __FUNCTION__);
        $result = array();
        if(is_array($schedule)){
            foreach ($schedule as $date) {
                $result[$date['date']] = array();
                $available = false;
                $hour = array();
                foreach ($date['slots'] as $slot) {
                    # code...
                    
                    // $this->logger->Log_Information($slot, '--Inside foreach');
                    
                    if($slot['available_status'] == 0){
                        $hour = explode(':', $slot['slot_timming'])[0];
                        $minutes = explode(':', $slot['slot_timming'])[1];
                        $result[$date['date']][$hour][] = $minutes;
                        // if($minutes == '15' || $minutes == '00'){
                        //     $result[$date['date']][$hour][0][] = $minutes;
                        // }else{
                        //     $result[$date['date']][$hour][1][] = $minutes;
                        // }
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Check Booking Status and update
     * @return Void
     */
    public function update_booking_status(){
        $bookings = $this->get_unacknowledged_tasks();
        $this->logger->Log_Information($bookings, __FUNCTION__);
    }

    /**
     * Get Unacknowledged Tasks
     * @return Array $bookings Job Ids
     */
    public function get_unacknowledged_tasks(){
        $bookings = array(); // Get only bookings for exams 
        $sql = "SELECT * FROM $this->tablename WHERE acknowledgment IS NULL AND instructor = 0";
        $results = $this->db->get_results($sql, "ARRAY_A");
        foreach ($results as $key => $value) {
            array_push($bookings, $value);
        }
        return $bookings;
    }

    /**
     * Update customer
     * 
     * @param Array $user
     * @since 1.0.0
     */
    public function update_customer( $user ){
        $url = $this->api_url . 'customer/edit';
        $body = array(
            'api_key'=> $this->api_key,
            'user_type'=> 0,
            'customer_id'=> $user['student_tookan_id'],
            'name' => $user['fname'] . ' ' . $user['lname'],
            'phone' => $user['phone'],
            'email' => $user['email'],
            'address' => $user['full_address'],
            'latitude' => $user['latitude'],
            'longitude' => $user['longitude']
        );

        $response = wp_remote_post( $url, array(
            'method'      => 'POST',
            'timeout'     => 45,
            'redirection' => 5,
            'httpversion' => '1.0',
            'blocking'    => true,
            'headers'     => array('Content-Type'=> 'application/json'),
            'body'        => json_encode($body),
            'cookies'     => array()
            )
        );
        if ( is_wp_error( $response ) ) {
            return false;
        } else {
            return true;
        }
        $this->logger->Log_Information($response, __FUNCTION__);


    }

    /**
     * Get All Captive agents
     * 
     * @since 1.0.0
     */
    public function get_all_captive_agents(){
        $url = $this->api_url . 'get_all_fleets';
        $body = array(
            'api_key'=> $this->api_key,
            'status'=> 0,
            'fleet_type'=> 1
        );

        $response = wp_remote_post( $url, array(
            'method'      => 'POST',
            'timeout'     => 45,
            'redirection' => 5,
            'httpversion' => '1.0',
            'blocking'    => true,
            'headers'     => array('Content-Type'=> 'application/json'),
            'body'        => json_encode($body),
            'cookies'     => array()
            )
        );

        if(is_wp_error( $response )){
            return array('success' => false, 'message' => 'Failed to fetch Captive Agents', 'error' => $response->get_error_message());
        }else{
            $result = json_decode($response['body'], true);
            return array('success' => true, 'data' => $result['data']);
        }
    }

    /**
     * Get All Freelancer agents
     * 
     * @since 1.0.0
     */
    public function get_all_freelancer_agents(){
        $url = $this->api_url . 'get_all_fleets';
        $body = array(
            'api_key'=> $this->api_key,
            'status'=> 0,
            'fleet_type'=> 2
        );

        $response = wp_remote_post( $url, array(
            'method'      => 'POST',
            'timeout'     => 45,
            'redirection' => 5,
            'httpversion' => '1.0',
            'blocking'    => true,
            'headers'     => array('Content-Type'=> 'application/json'),
            'body'        => json_encode($body),
            'cookies'     => array()
            )
        );

        if(is_wp_error( $response )){
            return array('success' => false, 'message' => 'Failed to fetch Freelance Agents', 'error' => $response->get_error_message());
        }else{
            $result = json_decode($response['body'], true);
            return array('success' => true, 'data' => $result['data']);
        }
    }

    /**
	 * Function to run all admin hooks
	 * 
	 * @since 1.0.0
	 */
	public function run_all(){
        add_action( 'wp_ajax_ad_get_tookan_key', array($this, 'ajax_ad_display_key'));
        add_action( 'wp_ajax_ad_create_tookan_task', array($this, 'create_task'));
        add_action( 'wp_ajax_ad_get_agents', array($this, 'get_all_agents'));
        add_action ( 'wp_ajax_ad_assign_task_to_agent', array($this, 'assign_task_to_agent'));
        add_action( 'wp_ajax_ad_autoassign_booking', array($this, 'autoassign_task'));
	}
    
}
