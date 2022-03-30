<?php
require plugin_dir_path( __DIR__ ) . '/vendor/autoload.php';

/**
 * Class for LMS API Handler
 * 
 * @package Adept_Drivers
 * @subpackage Adept_Drivers/includes
 * @author Samer Alotaibi <sam@samiscoding.com>
 */

 class Adept_Drivers_LMS {


    /**
     * LMS API KEY
     */
    private $token;

    /**
     * Role IDs
     */
    private $roles = array(
        10 => 'company manager',
        5 => 'student',
        7 => 'user',
        11 => 'company department manager'

    );

    /**
     * Domain
     */
    private $domain = 'https://lms.learndrive.ca';

    /**
     * Rest Format
     */
    private $format = 'json';

    /**
     * user pass
     */
    private $pass;

    /**
     * company ID
     */
    private $company_id;

    /**
     * Moodle Instance
     */
    private $MoodleRest;

    /**
     * Student Role ID
     */
    private $student_role_id = 5;

    /**
     * Course ID
     */
    private $course_id = 5;

    /**
     * Logger
     */
    public $logger;
    
    /**
    * Constructor function
    * 
    * @since 1.0.0
    */
    public function __construct()
    {
        $this->token = get_option('ad_options')['ad_moodle_api_token'];
        $this->integrated = empty($this->token) ? false : true;
        $this->company_id = get_option('ad_options')['ad_moodle_company_id'];
        $this->logger = new Adept_Drivers_Logger('LMS');
        $this->run_all();
    }

    /**
     * Initiate user in LMS
     * 
     * @since 1.0.0
     * 
     * @param Array $user
     * 
     * @return Array $user | False 
     */
    private function initiate_user($user){
        $function = 'core_user_create_users';
        $url = $this->domain . '/webservice/rest/server.php'. '?wstoken=' . $this->token . '&wsfunction='.$function . '&moodlewsrestformat=' . $this->format;
        $user_defaults = array(                
                "username" => "string",
                "auth" => "manual",
                "password" => wp_generate_password(),
                "firstname" => "string",
                "lastname" => "string",
                "email" => "string",
                "phone1" => "string" 
        );

        // if($user = ''){
        //     $user = $user_defaults;
        // }
        
        $user_array = array_merge($user_defaults, $user);

        $params = array('users' => array($user_array));
        $this->MoodleRest = new MoodleRest($this->domain . '/webservice/rest/server.php', $this->token);
        $this->MoodleRest->request($function, array('users'=>[$user_array]), MoodleRest::METHOD_POST);
        $result = $this->MoodleRest->getData();
        if(isset($result['errorcode'])){
            wp_mail( get_option( 'admin_email' ), 'LMS-FAILED REGISTRATION', 'Failed to register student ' . $user['firstname'] . ' ' . $user['lastname'] . ' With error message: ' . $result['debuginfo']);
            $this->logger->Log_Error($result, 'Initiate_user');
            $this->logger->Log_Error($this->MoodleRest->getUrl(), 'Initiate_user-URL');
            $result = false;
        }
        return $result;
    }

    /**
     * Confirm User in LMS
     * 
     * @param INT $userID
     * 
     * @return Array $user | False
     */
    private function create_user($userID){

        $assign_to_company_fn = 'block_iomad_company_admin_assign_users';

        $this->MoodleRest->request($assign_to_company_fn, array('users' => [array('userid' => $userID, 'companyid' => $this->company_id)]), MoodleRest::METHOD_POST);
        $result = $this->MoodleRest->getData();
        if(isset($result['errorcode'])){
            wp_mail( get_option( 'admin_email' ), 'LMS-FAILED REGISTRATION', 'Failed to register student ' . $user['firstname'] . ' ' . $user['lastname'] . ' With error message: ' . $result['debuginfo']);
            $this->logger->Log_Error($result['debuginfo'], 'create_user');
            $result = false;
        }
        return $result;
    }

    /**
     * enrol user in LMS
     * 
     * @param INT $userID;
     * 
     * @return Array $user | False
     */
    private function enrol_user($userID){

        $enrol_to_course_fn = 'block_iomad_company_admin_enrol_users';

        $this->MoodleRest->request($enrol_to_course_fn, array('enrolments' => [array('roleid' => $this->student_role_id, 'userid' => $userID, 'courseid' => $this->course_id)]), MoodleRest::METHOD_POST);
        $result = $this->MoodleRest->getData();
        if(isset($result['errorcode'])){
            wp_mail( get_option( 'admin_email' ), 'LMS-FAILED REGISTRATION', 'Failed to register student ' . $user['firstname'] . ' ' . $user['lastname'] . ' With error message: ' . $result['debuginfo']);
            $this->logger->Log_Error($result['debuginfo'], 'enrol_user');
            $result = false;
        }
        return $result;        
    }

    /**
     * Proccess User
     * 
     * @param Array $user
     * 
     * @return Bool True | False
     */
    public function process_user($user){
        $this->logger->Log_Information($user, "process_user");
        // First initiate user in lms
        if($this->integrated){
            $initiate =  $this->initiate_user($user);
            if($initiate){
             $this->logger->Log_Information($initiate, "process_user-INIT");
                $create = $this->create_user($initiate[0]['id']);
                $this->logger->Log_Information($create, "process_user-CREATE");
     
                if($create){
                    $enrol = $this->enrol_user($initiate[0]['id']);
                    $this->logger->Log_Information($enrol, "process_user-CREATE");
     
                     if($enrol) {
                         //Send Email
                         $this->logger->Log_Error($user, "email user");
                         return $initiate;
                     }else{
                         $this->logger->Log_Error($enrol, "process_user-Enrol");
                     }
                }else{
                     $this->logger->Log_Error($create, "process_user-CREATE");
                }
            }else{
                 $this->logger->Log_Error($initiate, "process_user-INIT");
            }
        }
       
       return false;
    }

    /**
     * Test Mail
     */
    public function test_mail(){
                            //Send Email
            $message = "Welcome to Adept Drivers <br> These are your credentials for the website and the LMS <br> Username: $user[username], password: $user[password]";
            $email = wp_mail('sam.otb@hotmail.ca', 'Successful Registration', $message);
                $this->logger->Log_Error($email, "email user");
                $response = new WP_REST_Response( array(
                    'success' => true,
                    'message' => 'Check your email'
                ) );
                $response->set_status( 200 );
            return $response;
    }

    /**
     * Get Student Progress
     * 
     * @param Int $id student id
     * 
     * @return Array $student_progress
     */
    public function get_student_progress($id){
        if($this->integrated){
            $progress_fn = 'gradereport_overview_get_course_grades';
            $this->MoodleRest = new MoodleRest($this->domain . '/webservice/rest/server.php', $this->token);
    
            $this->MoodleRest->request($progress_fn, array('userid' => $id), MoodleRest::METHOD_POST);
            $result = $this->MoodleRest->getData();
            if(isset($result['errorcode'])){
                $this->logger->Log_Error($result, __FUNCTION__);
                $result = false;
            }else{
                $this->logger->Log_Information($result, __FUNCTION__);
            }
        }else{
            $result = false;
        }
        

        return $result;  
    }

    /**
     * Update student data
     * 
     * @param Array $user
     * 
     * @return Mix $user | False
     */
    public function update_user($user){

        if($this->integrated){
            $update_user_fn = 'core_user_update_users';
            $this->MoodleRest = new MoodleRest($this->domain . '/webservice/rest/server.php', $this->token);
            $this->MoodleRest->request($update_user_fn, array('users' => array($user)), MoodleRest::METHOD_POST);
            $result = $this->MoodleRest->getData();
            if(isset($result['errorcode'])){
                wp_mail( get_option( 'admin_email' ), 'LMS-FAILED REGISTRATION', 'Failed to update student ' . $user['firstname'] . ' ' . $user['lastname'] . ' With error message: ' . $result['debuginfo']);
                $this->logger->Log_Error($result['debuginfo'], __FUNCTION__);
                $result = false;
            }
            $this->logger->Log_Information($result, __FUNCTION__);
        }else{
            $result = false;
        }
        
        return $result;
    }

    /**
     * Function to run all admin hooks
     * 
     * @since 1.0.0
     */
    public function run_all(){
        add_action( 'wp_ajax_ad_create_lms_user', array($this, 'test_mail'));
    }
 }