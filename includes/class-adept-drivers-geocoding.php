<?php
/**
 * Class for Google Geocoding API
 * 
 * @package Adept_Drivers
 * @subpackage Adept_Drivers/includes
 * @author Samer Alotaibi <sam@samiscoding.com>
 */

class Adept_Drivers_Geocoding{

    /**
     * Google API Key
     * @access private
     */
    private $google_key;

    /**
     * User Address
     */
    private $address;

    /**
    * Constructor function
    * 
    * @since 1.0.0
    */
    public function __construct($address)
    {
        $this->address = $address;
        $this->google_key = get_option('ad_options')['ad_google_api_key'];
    }


    /**
     * geocode the user address
     * 
     * @return Array $geolocation
     */
    public function geocode(){
 
        // url encode the address
        $address = urlencode($this->address);
         
        // google map geocode api url
        $url = "https://maps.googleapis.com/maps/api/geocode/json?address={$address}&key={$this->google_key}";
     
        // get the json response
        $resp_json = file_get_contents($url);
         
        // decode the json
        $resp = json_decode($resp_json, true);
        
        $logger = new Adept_Drivers_Logger('GEOCODING');
        $logger->Log_Information($resp, 'USER COORDINATES');
        // response status will be 'OK', if able to geocode given address 
        if($resp['status']=='OK'){
     
            // get the important data
            $lati = isset($resp['results'][0]['geometry']['location']['lat']) ? $resp['results'][0]['geometry']['location']['lat'] : "";
            $longi = isset($resp['results'][0]['geometry']['location']['lng']) ? $resp['results'][0]['geometry']['location']['lng'] : "";
            $formatted_address = isset($resp['results'][0]['formatted_address']) ? $resp['results'][0]['formatted_address'] : "";
             
            // verify if data is complete
            if($lati && $longi && $formatted_address){
             
                // put the data in the array
                $data_arr = array();            
                 
                array_push(
                    $data_arr, 
                        $lati, 
                        $longi, 
                        $formatted_address
                    );
                 
                return $data_arr;
                 
            }else{
                return false;
            }
             
        }
     
        else{
            echo "<strong>ERROR: {$resp['status']}</strong>";
            return false;
        }
    }
}

