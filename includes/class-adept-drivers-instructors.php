<?php
require plugin_dir_path( __DIR__ ) . '/vendor/autoload.php';

/**
 * Class for Tookan API Handler
 * 
 * @package Adept_Drivers
 * @subpackage Adept_Drivers/includes
 * @author Samer Alotaibi <sam@samiscoding.com>
 */

 class Adept_Drivers_Instructors{

    /**
     * DB instance
     * 
     * @access private
     */
    private $db;

    /**
     * Logger
     * 
     * @access private
     */
    private $logger;

    /**
     * The list of keys to display
     * 
     * @access public
     */
    public $Mustache;

    /**
     * List of updated/inserted agents
     * 
     * @access private
     */
    private $ids;


    public function __construct(){
        global $wpdb;
        $this->db = $wpdb;
        $this->logger = new Adept_Drivers_Logger('INSTRUCTORS');
        $this->Mustache = new Mustache_Engine(array(
            'entity_flags' => ENT_QUOTES,
            'loader' => new Mustache_Loader_FilesystemLoader(plugin_dir_path( __DIR__ ).'/admin/templates')
        ));
    }

    /**
     * Insert Instructor into DB if not exist
     * 
     * @param Array $data Instructor's Data
     * 
     * @return Bool
     */
    public function insert_update_instructor( $data ){
        $tablename = $this->db->prefix . 'ad_instructors';
        $instructor_id = $data['instructor_id'];
        $find_sql = "SELECT id FROM $tablename WHERE instructor_id = $instructor_id";
        $agent_exist = $this->db->get_row($find_sql, 'ARRAY_A');
        if($agent_exist){
            $op = $this->db->update($tablename, $data, array('instructor_id' => $instructor_id));
        }else{
            $op = $this->db->insert($tablename, $data);
        }
        return !$op ? false : true;
    }

    /**
     * Delete non-existing agent
     * 
     * @param Array $updated_ids
     * 
     * @return Bool
     */
    public function delete_agents($updated_ids){
        $tablename = $this->db->prefix . 'ad_instructors';
        $sql = "SELECT instructor_id FROM $tablename";
        $ids = $this->db->get_results($sql, 'ARRAY_N');
        foreach ($ids as $value) {
            if(!in_array(intval($value[0]), $updated_ids)){
                $this->db->delete($tablename, array('instructor_id' => $value[0]));
            }
        }
        return true;

    }

    /**
     * Get All Instructors
     * 
     * @return Array $instructors
     */
    public function get_all_instructors($type = null){
        
        $tablename = $this->db->prefix . 'ad_instructors';

        $sql = "SELECT * FROM $tablename";
        if($type){
            $sql = "SELECT instructor_id FROM $tablename WHERE type = '$type'";
        }
        $instructors = $this->db->get_results($sql, 'ARRAY_A');
        $this->logger->Log_Information(array($instructors, $type), __FUNCTION__);

        return $instructors;
    }

    /**
     * Get Agent By ID
     * 
     * @param Int $agent_id
     * 
     * @return Array $agent
     */
    public function get_agent_details( $agent_id ){
        $agent_id = intval($agent_id);
        $tablename = $this->db->prefix . 'ad_instructors';

        $sql = "SELECT * FROM $tablename WHERE instructor_id = $agent_id";

        $agent = $this->db->get_row($sql, 'ARRAY_A');

        return $agent;
    }

    /**
     * Get nearest Agent
     * 
     * @param Array $coordinates
     * 
     * @return Int $agent_id
     */
    public function get_nearest_instructor( $coordinates ){
        $min = 1000;
        $agents = $this->get_all_instructors();
        foreach ($agents as $key => $agent) {
            $distance = $this->get_distance($coordinates['lat'], $coordinates['long'], $agent['latitude'], $agent['longitude'] );
            $this->logger->Log_Information(array('distance' => $distance, 'coords' => $coordinates, '$agent' => $agent), __FUNCTION__);
            if($distance < $min){
                $min = $distance;
                $agent_id = $agent['instructor_id'];
            }

        }
        // if($agent_id)
        return $agent_id ?? false;
    }

    /**
     * Get Bookings for instructor
     * 
     * @param Int $ID instructor id
     * 
     * @return mix Array or False
     */

    public function get_instructor_bookings( $ID ){
        $tablename = $this->db->prefix . 'ad_bookings';
        $ID = intval($ID);
        $sql = "SELECT * FROM $tablename WHERE instructor = $ID";
        $bookings = $this->db->get_results($sql, 'ARRAY_A');
        return $bookings ? $bookings : false;
    }

    /**
     * Count Instructor Bookings
     * 
     * @param Int $ID agent id
     * 
     * @return Int $count
     */
    public function count_agent_bookings( $ID ){
        $tablename = $this->db->prefix . 'ad_bookings';
        $ID = intval($ID);
        $sql = "SELECT count(*) FROM $tablename WHERE instructor = $ID";
        $this->logger->Log_information($ID, __FUNCTION__ . ' SQL');
        $count = $this->db->get_var($sql);
        $this->logger->Log_Information($count, __FUNCTION__);
        return $count;
    }

    /**
     * Helper function to get distance
     * 
     * @param Float $latitude1
     * @param Float $longitude1
     * @param Float $latitude2
     * @param Float $longitude2
     * 
     * @return Int $d distance in KM
     */
    public function get_distance($latitude1, $longitude1, $latitude2, $longitude2) {  
        $earth_radius = 6371;
      
        $dLat = deg2rad($latitude2 - $latitude1);  
        $dLon = deg2rad($longitude2 - $longitude1);  
      
        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * sin($dLon/2) * sin($dLon/2);  
        $c = 2 * asin(sqrt($a));  
        $d = $earth_radius * $c;  
      
        return $d;  
    }

    /**
     * Get list of agents names/ids
     * 
     * @return Array $agents
     */
    public function get_agents_names_id(){
        $tablename = $this->db->prefix . 'ad_instructors';

        $sql = "SELECT instructor_id, inst_name FROM $tablename";

        $agents = $this->db->get_results($sql, 'ARRAY_A');
        $this->logger->Log_Information($agents, __FUNCTION__);
        wp_send_json(array(
            'success' => true,
            'data' => $agents
        ), 200);


    }

    /**
     * Update user Agent
     * 
     * @return WP_Json_Response
     */
    public function update_student_agent(){
        $id = $_REQUEST['uid'];
        $agentID = $_REQUEST['agentID'];

        $student_agents = maybe_unserialize(get_user_meta( $id, 'ad_student_instructor', true ));
        if(empty($student_agents)){
            $student_agents = array($agentID);
        }else{
            array_unshift($student_agents, $agentID);
        }
        $this->logger->Log_Information(array('agent' => $agentID, 'studentmeta' => $student_agents), __FUNCTION__);

        $update = update_user_meta( $id, 'ad_student_instructor',  $student_agents);

        wp_send_json(array(
            'success' => true,
            'message' => $update,
            'data' => $student_agents
        ), 200);
    }

    /**
     * Render Instructors Page
     */
    public function render_page(){
        $agents = array();
        foreach ($this->get_all_instructors() as $key => $agent) {
            $agent['count'] = $this->count_agent_bookings($agent['instructor_id']);
            $agent['bookings'] = $agent['count'] > 0 ? $this->get_instructor_bookings($agent['instructor_id']) : false;
            $aget['has_bookings'] = $agent['count'] > 0 ? true : false;
            $agent['name'] = $this->get_agent_details($agent['instructor_id'])['inst_name'];
            $agents[] = $agent;
        }
        $this->logger->Log_Information($agents, __FUNCTION__);
        $tpl = $this->Mustache->loadTemplate('instructors-table');
		echo $tpl->render(array('agents' => $agents));
    }

 }