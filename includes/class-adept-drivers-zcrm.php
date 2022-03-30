<?php
require plugin_dir_path( __DIR__ ) . '/vendor/autoload.php';
use zcrmsdk\crm\setup\restclient\ZCRMRestClient;
use zcrmsdk\crm\crud\ZCRMRecord;
use zcrmsdk\oauth\ZohoOAuth;

/**
 * Class for Zoho CRM API Handler
 * 
 * @package Adept_Drivers
 * @subpackage Adept_Drivers/includes
 * @author Samer Alotaibi <sam@samiscoding.com>
 */
class Adept_Drivers_ZCRM {

    /**
     * Zoho CRM Client ID;
     */
    private $zcrm_id;

    /**
     * Zoho CRM Client Secret
     */
    private $zcrm_secret;

    /**
     * Zoho CRM Redirect URI
     */
    private $zcrm_redirect_uri;

    /**
     * Zoho CRM Redirect URI
     */
    private $zcrm_email;

    /**
     * Zoho Configuration
     */
    private $configuration;

    /**
     * Zoho Client Instance
     */
    private $zinst;

    /**
     * Zoho Temp Token
     */
    private $zcrm_temp_token;

    /**
     * Zoho CRM URI
     */
    private $zcrm_uri = 'https://accounts.zoho.com/oauth/v2/token';

    /**
     * Path to token storage
     */
    private $zcrm_token_storage;

    /**
     * Logger instance
     */
    public $logger;

    /**
     * Constructor function
     * 
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->zcrm_token_storage = plugin_dir_path( __DIR__ ) . '/TokenStorage';
        $this->run_all();
        $this->api_key = get_option('ad_options')['ad_tookan_api'];
        $this->zcrm_id = get_option('ad_options')['ad_zcrm_cid'];
        $this->zcrm_secret = get_option('ad_options')['ad_zcrm_csecret'];
        $this->zcrm_redirect_uri = get_option('ad_options')['ad_zcrm_redirect_uri'];
        $this->zcrm_email = get_option('ad_options')['ad_zcrm_email'];
        $this->zcrm_temp_token = get_option('ad_options')['ad_zcrm_temp_token'];
        // $this->init_zcrm_client();
        $this->configuration = array(
            "client_id"=>$this->zcrm_id,
            "client_secret"=> $this->zcrm_secret,
            "redirect_uri"=> $this->zcrm_redirect_uri,
            "currentUserEmail"=> get_option('ad_options')['ad_zcrm_email'],
            'token_persistence_path' => $this->zcrm_token_storage
        );
        $ver_array = array_filter($this->configuration);
        if(count($this->configuration) == count($ver_array)){
            ZCRMRestClient::initialize( $this->configuration );
            $this->zinst = ZCRMRestClient::getInstance();
        }

        
        // $this->generate_token_from_refresh();
        $this->logger = new Adept_Drivers_Logger('ZCRM');
        // $this->logger->Log_Information($this->zinst, __FUNCTION__);
    }

    /**
     * Update record
     * 
     * @param Array $student data
     * 
     * @return Mix
     */
    public function update_student_records( $student, $type ){
        if(is_array($student)){
            $moduleIns = $this->zinst->getModuleInstance("Contacts"); // to get the instance of the module
            $this->logger->Log_Information($student, __FUNCTION__);
            $inventoryRecords = array();
            /**
             * Following methods are being used only by same Inventory only  *
             */
            $record = ZCRMRecord::getInstance("Contacts", $student['student_zoho_id']); // to get the instance of the record
            $record->setFieldValue("First_Name", $student['fname']); // This function use to set FieldApiName and value similar to all other FieldApis and Custom field
            $record->setFieldValue("Last_Name", $student['lname']);
            $record->setFieldValue("Email", $student['email']);
            
            array_push($inventoryRecords, $record); // pushing the record to the array
            
            if($type !== 'details'){
                //add address fields
                $record->setFieldValue("Mailing_City", $student['city']); // This function use to set FieldApiName and value similar to all other FieldApis and Custom field
                $record->setFieldValue("Mailing_State", $student['state']);
                $record->setFieldValue("Mailing_Street", $student['address']);
                $record->setFieldValue("Mailing_Zip", $student['zipcode']);
                $record->setFieldValue("Mobile", $student['phone']);

            }
            
            $trigger=array();//triggers to include
            $responseIn = $moduleIns->updateRecords($inventoryRecords,$trigger); // updating the records.$trigger is optional , to update price book records$pricebookRecords can be used in the place of $inventoryRecords
            $success = '';
            foreach ($responseIn->getEntityResponses() as $responseIns) {
                $success = $responseIns->getMessage();
                $this->logger->Log_Information($responseIns->getMessage(), __FUNCTION__);
                // echo "HTTP Status Code:" . $responseIn->getHttpStatusCode(); // To get http response code
                // echo "Status:" . $responseIns->getStatus(); // To get response status
                // echo "Message:" . $responseIns->getMessage(); // To get response message
                // echo "Code:" . $responseIns->getCode(); // To get status code
                // echo "Details:" . json_encode($responseIns->getDetails());
            }
            return $success;
        }
        return false;
    }

    /**
     * Create ZCRM Webhook
     * 
     * @since 1.0.0
     */
    public function zcrm_resapi(){
        register_rest_route( 'adept-drivers/v1', 'noticifcations', array(
            'methods' => 'POST',
            'callback' => [$this, 'handle_zcrm_notifications'],
        ) );
    }

    /**
     * Handle ZCRM Webhook for new contacts
     *
     * @return void
     */
    public function handle_zcrm_notifications( $request ){
        $student_ins = new Adept_Drivers_Students();
        /** 
         * The post data fetched raw
         */
        $post_data = $request->get_body_params();

        /**
         * Post data mutated to array
         */
        $post_data = json_decode(json_encode($post_data), true);

        $this->logger->Log_Information($post_data, 'Webhook');

        /**
        * Convert address data
        */
        $user_address = array(
            'student_address_1' => 'billing_address_1',
            'student_city' => 'billing_city',
            'student_postal' => 'billing_postcode',
            'student_state' => 'billing_state',
            'student_phone' => 'billing_phone'
        );

        /**
         * Holds core data from zcrm
         */
        $zcrm_core = ['studentemail', 'student_name', 'student_registration'];

        if(is_array($post_data)){

            //Check if user exists
            $user_exists = get_user_by('email', $post_data['studentemail']);


            if($user_exists){
                $this->logger->Log_Information($post_data, 'Webhook-Post data');

                $user_id = $user_exists->ID;

                //update user
                $userdata = array(
                    'ID'                    => $user_id,
                    'user_login'            => explode('@',$post_data['studentemail'])[0],   //(string) The user's login username.
                    'user_nicename'         => str_replace('.','_',explode('@',$post_data['studentemail'])[0]),   //(string) The URL-friendly user name.
                    'user_email'            => $post_data['studentemail'],   //(string) The user email address.
                    'display_name'          => $post_data['student_name'],   //(string) The user's display name. Default is the user's username.
                    'first_name'            => explode(' ', $post_data['student_name'])[0],   //(string) The user's first name. For new users, will be used to build the first part of the user's display name if $display_name is not specified.
                    'last_name'             => explode(' ', $post_data['student_name'])[1],   //(string) The user's last name. For new users, will be used to build the second part of the user's display name if $display_name is not specified.
                    'user_registered'       => $post_data['student_registration'],   //(string) Date the user registered. Format is 'Y-m-d H:i:s'.
                    'show_admin_bar_front'  => 'false',   //(string|bool) Whether to display the Admin Bar for the user on the site's front end. Default true.
                    'role'                  => 'student',   //(string) User's role.
                );

                $user_metas = array();
                //Update user metas
                /**
                 * Add user meta -- only if not core
                 */
                foreach ( $post_data as $key=>$value ){
                    if(!in_array($key, $zcrm_core)){
                        $user_metas[$key] = $value; 
                        if(array_key_exists($key, $user_address)){
                            $user_metas[$user_address[$key]] = $value;
                        }else{
                            $user_metas[$key] = $value;
                        }
                    }
                }

                $data = array_merge($userdata, $user_metas);
                $update_student = $student_ins->update_user_student($data);

                if(!$update_student){
                    $this->logger->Log_Error('Failed to update user', __FUNCTION__);
                }


            }else{
                /**
                 * Generate password
                 */
                $pass = wp_generate_password( 12, true );

                /**
                 * Prep userdata
                 */
                $userdata = array(
                    'user_pass'             => $pass,   //(string) The plain-text user password.
                    'user_login'            => explode('@',$post_data['studentemail'])[0],   //(string) The user's login username.
                    'user_nicename'         => str_replace('.','_',explode('@',$post_data['studentemail'])[0]),   //(string) The URL-friendly user name.
                    'user_email'            => $post_data['studentemail'],   //(string) The user email address.
                    'display_name'          => $post_data['student_name'],   //(string) The user's display name. Default is the user's username.
                    'first_name'            => explode(' ', $post_data['student_name'])[0],   //(string) The user's first name. For new users, will be used to build the first part of the user's display name if $display_name is not specified.
                    'last_name'             => explode(' ', $post_data['student_name'])[1],   //(string) The user's last name. For new users, will be used to build the second part of the user's display name if $display_name is not specified.
                    'user_registered'       => $post_data['student_registration'],   //(string) Date the user registered. Format is 'Y-m-d H:i:s'.
                    'show_admin_bar_front'  => 'false',   //(string|bool) Whether to display the Admin Bar for the user on the site's front end. Default true.
                    'role'                  => 'student',   //(string) User's role.
                
                );

                $user_metas = array();

                // /**
                //  * create new user
                //  * 
                //  * @return Int|WP_error
                //  */
                // $new_user = wp_insert_user($userdata);

                foreach ( $post_data as $key=>$value ){
                    if(!in_array($key, $zcrm_core)){
                        if(array_key_exists($key, $user_address)){
                            $user_metas[$user_address[$key]] = $value;
                        }else{
                            $user_metas[$key] = $value;
                        }
                    }
                }

                $data = array_merge($userdata, $user_metas);
                $insert_student = $student_ins->create_user_student($data);

            }


            
        }
        $response = new WP_REST_Response( array(
            'success' => true,
            'message' => 'You\'ve reached the ZCRM endpoint'
        ) );
        $response->set_status( 200 );
        return $response;
    }

    /**
     * Generate access token from refresh token
     * 
     * @since 1.0.0
     */
    private function generate_token_from_refresh(){

        $strarray = unserialize(file_get_contents($this->zcrm_token_storage . '/zcrm_oauthtokens.txt'));
        // $userIdentifier = $this->zcrm_email; 
        // $oAuthClient = ZohoOAuth::getClientInstance(); 
        // $oAuthClient->generateAccessTokenFromRefreshToken($refreshToken,$userIdentifier);
        // var_dump($strarray);
    }

    /**
     * Initialize ZCRM SDK
     * 
     * @since 1.0.0
     */
    private function init_zcrm_client () {
        $this->configuration = array(
            "client_id"=>$this->zcrm_id,
            "client_secret"=> $this->zcrm_secret,
            "redirect_uri"=> $this->zcrm_redirect_uri,
            "currentUserEmail"=> $this->zcrm_email,
            'token_persistence_path' => $this->zcrm_token_storage
        );
        ZCRMRestClient::initialize($this->configuration);
        $oAuthClient = ZohoOAuth::getClientInstance();
        $oAuthTokens = $oAuthClient->generateAccessToken($this->zcrm_temp_token);
    }

    /**
     * Push student record to CRM
     * 
     * @param Array $record
     * 
     * @return void
     */
    public function push_student_record( $record ){


        $userdata = array(
            'Email'                     => $record['user_email'],
            'Last_Name'                 => $record['last_name'],
            'First_Name'                => $record['first_name'],
            'Mailing Street'            => $record['billing_address_1'],
            'Mailing_City'              => $record['billing_city'],
            'Mailing Zip'               => $record['billing_postcode'],
            'Mailing State'             => $record['billing_state'],
            'Phone'                     => $record['billing_phone'],
            'G2_Eligibility_Date'       => $record['student_g2el'],
            'Conditions_Eye_Glasses'    => $record['student_cond']
        );
        $moduleIns = $this->zinst->getModuleInstance("Students");
        $records = array();
        $record = ZCRMRecord::getInstance("Students",null);
        $this->logger->Log_Information($record, __FUNCTION__);
        array_push($records, $record);
        $responseIn = $moduleIns->createRecords($records);
        if($responseIn->getEntityResponses()[0]->getStatus() == 'success'){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Push Products records on Zoho
     * 
     * @param Array $record
     * 
     * @return Bool
     */
    public function push_product_record( $product ){
        $this->logger->Log_Information($product, __FUNCTION__);
        $productdata = array(
            'Product_Name'                     => $product->get_name(),
            'Unit_Price'                     => $product->get_price(),
            'In_Car_Sessions'           => $product->get_meta('in_car_sessions') ?? 0,
            'Has_LMS'                   => $product->get_meta('includes_bde') ? true : false,
            'Session_Duration_30_min_Intervals' => $product->get_meta('lesson_duration') ?? 0,
        );
        if(is_array($productdata)){
            $moduleIns = $this->zinst->getModuleInstance("Products"); // to get the instance of the module
            $this->logger->Log_Information($productdata, __FUNCTION__);
            $inventoryRecords = array();
            /**
             * Following methods are being used only by same Inventory only  *
             */

            $record = ZCRMRecord::getInstance("Products", null); // to get the instance of the record
            foreach ($productdata as $key => $value) {
                $record->setFieldValue($key, $value); // This function use to set FieldApiName and value similar to all other FieldApis and Custom field
                # code...
            }
            
            array_push($inventoryRecords, $record); // pushing the record to the array
            
            
            $trigger=array();//triggers to include
            $responseIn = $moduleIns->createRecords($inventoryRecords,$trigger); // updating the records.$trigger is optional , to update price book records$pricebookRecords can be used in the place of $inventoryRecords
            $success = '';
            foreach ($responseIn->getEntityResponses() as $responseIns) {
                $success = $responseIns->getMessage();
                $this->logger->Log_Information($responseIns->getMessage(), __FUNCTION__);
                // echo "HTTP Status Code:" . $responseIn->getHttpStatusCode(); // To get http response code
                // echo "Status:" . $responseIns->getStatus(); // To get response status
                // echo "Message:" . $responseIns->getMessage(); // To get response message
                // echo "Code:" . $responseIns->getCode(); // To get status code
                // echo "Details:" . json_encode($responseIns->getDetails());
            }
            return $success;
        }
        return false;

    }

    /**
     * On product creation or update
     * 
     * @param String $new_status
     * @param String $old_status
     * @param WP_Post $post
     * 
     * @return Boolean
     */
    public function product_sync( $post_id = 0){
        $post_id = $post_id == 0 ? $_REQUEST['id'] : $post_id;
            $product = wc_get_product($post_id);
            $this->logger->Log_Error($product, __FUNCTION__);
            if($product){
                $sync = $this->push_product_record($product);
            }else{
                $sync = false;
            }
            if($sync){
                add_post_meta( $post_id, 'zsynced', true, true );
                wp_send_json(array(
                    "success" => true
                ));
            }else{
                wp_send_json(array(
                    "success" => false
                ));
            }
    }

    /**
     * Hook to add sync option to products
     * 
     * @param Array $columns
     * 
     * @return Array $columns
     */
    public function product_sync_column( $columns ){
        $columns['zsync'] = __('Zoho Sync', 'adept-drivers');
        return $columns;
    }

    /**
     * Hook to populate sync action in Products list
     * 
     * @param String $output
     * 
     * @param String $column_name
     * 
     * @param Int $id
     */
    public function product_sync_action( $column_name){
        global $post;
        if($column_name == 'zsync'){
            if(!get_post_meta($post->ID, 'zsynced')){
                $output = "<a href='#' class='ad-sync-zoho' id='ad-sync-zoho' data-id='$post->ID'>" . __('Sync Now', 'adept-drivers') . "</a>";
            }else{
                $output = "<span class='dashicons dashicons-yes-alt'></span>";
            }
        }
        echo $output;
    }

    /**
     * 
     */

    /**
     * TEST_ GEt all Modules
     * 
     * 
     * @since 1.0.0
     */
    public function get_zcrm_api_test( ){
        $moduleIns = ZCRMRestClient::getInstance()->getModuleInstance("Contacts"); // To get module instance
        $response = $moduleIns->getAllFields(); // to get the field
        $fields = $response->getData(); // to get the array of ZCRMField instances
        $result = array();
        foreach($fields as $field){
            $result[] = $field->getApiName();
        }
          $this->logger->Log_Information($result, __FUNCTION__);
    }

    /**
     * Ajax to call ZCRM with modules info
     */
    public function ad_zcrm_get_modules(){

        $results = $this->get_zcrm_api_test();

        wp_send_json(array(
            "success" => true,
            "message" => json_decode($results)
        ));
    }

    public function ajax_generate_token(){
        try{
            // $this->init_zcrm_client();
            wp_send_json(array(
                "success" => true,
                "message" => "Successfully Generated Tokens"
            ));
        }catch(Exception $e){
            wp_send_json(array(
                "success" => false,
                "message" => $e->getMessage()
            ));
        }
    }

    /**
	 * Function to run all admin hooks
	 * 
	 * @since 1.0.0
	 */
	public function run_all(){

        add_action( 'wp_ajax_ad_zcrm_get_modules', array($this, 'get_zcrm_api_test'));
        add_action( 'wp_ajax_generate_zcrm_token', array($this, 'ajax_generate_token'));

	}
}
?>