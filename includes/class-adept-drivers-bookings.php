<?php
/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Adept_Drivers
 * @subpackage Adept_Drivers/public/partials
 * @author     Samer Alotaibi <sam@samiscoding.com>
 */

 class Adept_Drivers_Public_Booking {

    /**
     * Holds current user ID
     * 
     * @access private
     */
    private $userID;

    /**
     * DB
     * 
     * @access private
     */
    private $db;

    /**
     * Loger
     * 
     */
    public $logger;

    /**
     * tablename
     * 
     * @access private
     */
    private $tablename;

    /**
     * The list of keys to display
     * 
     * @access public
     */
    public $Mustache;


    /**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      int    $id       User ID
	 */
    public function __construct( ){
        global $wpdb;
        $this->db = $wpdb;
        // $this->userID = $id;
        $this->logger = new Adept_Drivers_Logger('Bookings');
        $this->tablename = $this->db->prefix . ADEPT_DRIVERS_DBTABLE;
        $this->Mustache = new Mustache_Engine(array(
            'entity_flags' => ENT_QUOTES,
            'loader' => new Mustache_Loader_FilesystemLoader(plugin_dir_path( __DIR__ ).'/admin/templates')
        ));

    }

    /**
     * Get Student Bookings
     * 
     * @param $id student id
     * 
     * @return Mix Bookings | false
     */
    public function get_student_bookings($id){
        global $wpbd;
		$sql = "SELECT * FROM $this->tablename WHERE student_id = $id";
        $bookings = $this->db->get_results($sql, "ARRAY_A");
        $this->logger->Log_Information($bookings, __FUNCTION__);
        return empty($bookings) ? false : $bookings;  
        
    }

    /**
     * Get Student Bookings
     * 
     * @param $id student id
     * 
     * @return Mix Bookings | false
     */
    public function get_student_bookings_count($id){
        global $wpbd;
		$sql = "SELECT COUNT(id) FROM $this->tablename WHERE student_id = $id";
        $bookings = $this->db->get_var($sql);
        $this->logger->Log_Information($bookings, __FUNCTION__);
        return $bookings;  
        
    }

    /**
     * Add Student Booking
     * 
     * @param Datetime $bookingDate
     * 
     * @param Int $id student ID
     * 
     * @return Bool
     */
    public function add_student_bookings( $bookingDate, $id, $exam = false ){
        // $date = new DateTime($bookingDate);
        // $booking = $date->format('Y-m-d H:i:s');
        $exam = $exam == 'true' ? true : false;
        $dates = explode(' to ', $bookingDate);
        $from_date = $dates[0];
        $to_date = $dates[1];
        if($exam){
            $instructors = new Adept_Drivers_Instructors();
            $agents = $instructors->get_all_instructors('freelance');
            $this->logger->Log_Information(array($agents, gettype($exam)), 'add_student_bookings--Freelancer');
        }else{
            $agents = $this->get_student_agents($id);
        }
        // $this->logger->Log_Information($agents, 'add_student_bookings');
        if(!$agents){
            $this->logger->Log_Error($agents, 'add_student_bookings--');
            return false;
        }else{
            // $this->logger->Log_Information($id, 'get student');
            $user = get_user_by('ID', $id);
            if($user){
                $TOOKAN = new Adept_Drivers_Tookan();
                $task = $TOOKAN->create_task($user, $from_date, $to_date, $agents, $exam);
                    $this->logger->Log_Information($task, 'add_student_bookings--task');
                    return $task;
            }
        }
    }

    /**
     * Get student's agents
     * 
     * @param Int $id Student ID
     * @return Array $agents | False
     */
    public function get_student_agents($id){
        $TOOKAN = new Adept_Drivers_Tookan();
        $student_tookanID = get_user_meta( $id, 'ad_student_tookan_id', true );
        $student_agents = get_user_meta( $id, 'ad_student_instructor', true );
        $this->logger->Log_Information(gettype($student_agents), 'READ student agents');
        if(is_array($student_agents)){
            if(!empty($student_agents)){
                return maybe_unserialize($student_agents);
            }else{
                $student_agents = $TOOKAN->get_agents_near_customer( $student_tookanID );
                $this->logger->Log_Information($student_agents, 'get student agents');

                if( !empty($student_agents) ){
                    $agentID = [$student_agents];
                    $result = update_user_meta( $id, 'ad_student_instructor', $agentID);
                    $this->logger->Log_Information($result, '$result');
                    return $agentID;
                }else{
                    //Try locally
                    $instructor_ins = new Adept_Drivers_Instructors();
                    $student_coordinate = get_user_meta( $id, 'coordinates', true);
                    $agent_id = $instructor_ins->get_nearest_instructor( $student_coordinate );
                    if($agent_id){
                        $result = update_user_meta( $id, 'ad_student_instructor', array($agent_id));
                        return $agent_id;
                    }else{
                        return false;
                    }
                }
            }
        }
    }

    /**
     * Save student booking to DB
     * 
     * @param Array $bookingdata
     * 
     * @return Bool
     */
    public function save_student_booking($bookingdata, $id){
        $dates = explode(' to ', $bookingdata['booking_date']);
        $from_date = $dates[0];
        $to_date = $dates[1];
        $from_booking_date = strtotime($from_date);
        $from_booking_date = date('Y-m-d H:i:s', $from_booking_date);
        $to_booking_date = strtotime($to_date);
        $to_booking_date = date('Y-m-d H:i:s', $to_booking_date);
        $data = array(
            'student_id' => $bookingdata['student_id'],
            'tookan_id' => $bookingdata['tookan_id'],
            'booking_date' => $from_booking_date,
            'booking_end' => $to_booking_date,
            'instructor' => $bookingdata['agent_id'],
            'job_id' => $bookingdata['job_id'],
            'tracking_url' => $bookingdata['tracking_url'],
            'status' => 1
        );
        // $this->logger->Log_Information(array('string' => $bookingdata, 'dates' => $dates), 'check data');
        
        $insert = $this->db->insert($this->tablename, $data);
        if($insert){
            $this->logger->Log_Information($insert, __FUNCTION__);
            return $insert;
        }else{
            $this->logger->Log_Error($insert, __FUNCTION__);
            return false;
        }
    }

    /**
     * Delete Student bookig
     * 
     * @param Int $bookig_id
     * 
     * @return Bool
     */
    public function delete_booking( $booking_id, $id ){
        $id = intval($booking_id);
        $this->logger->Log_Information($booking_id, __FUNCTION__);
        $delete = $this->db->delete($this->tablename, array('job_id' => $id));
        if($delete){
            $TOOKAN = new Adept_Drivers_Tookan();
            $result = $TOOKAN->delete_task( $id );
            //Delete at tookan
            return $result;
        }else{
            return false;
        }
    }

    /**
     * Get all bookings
     * 
     * @param
     * 
     * @return Array $bookings
     */
    public function get_all_bookings(){

        $sql = "SELECT * FROM $this->tablename";
        $results = $this->db->get_results($sql, "ARRAY_A");

        $this->logger->Log_Information($results, __FUNCTION__);
        return $results;
    }

    /**
     * Render Bookings Page
     */
    public function render_page(){
        $render_obj = array();
        $bookings = $this->get_all_bookings();
        foreach ($bookings as $booking) {
            $agentID = $booking['instructor'];
            $studentID = $booking['student_id'];
            $studentName = get_user_by( 'ID', $studentID )->display_name;
            $instructor_ins = new Adept_Drivers_Instructors();
            $agent = $instructor_ins->get_agent_details($agentID);
            $booking['instructor_name'] = $agent['inst_name'];
            $booking['status'] = $booking['status'] == 1 ? 'Pending' : 'Complete';
            $booking['cancel'] = $booking['status'] == 'Pending' ? true : false;
            $this->logger->Log_Information($agent, __FUNCTION__);
            $this->logger->Log_Information($booking, __FUNCTION__ . 'booking');
            $render_obj[] = $booking;
        }
        $tpl = $this->Mustache->loadTemplate('bookings-table');
		echo $tpl->render(array('bookings' => $render_obj));
    }

    /**
     * Check booking Status
     * 
     */
    public function cron_check_booking_status(){
        //TODO::
    }

 }