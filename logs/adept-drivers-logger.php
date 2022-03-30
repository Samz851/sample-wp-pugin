<?php
/**
 * The Logger class of the plugin.
 *
 * @link       https://samiscoding.com
 * @since      1.0.0
 *
 * @package    Adept_Drivers
 * @subpackage Adept_Drivers/logs
 */

 class Adept_Drivers_Logger{
     /**
      * Document root
      */
      public $DOCUMENT_ROOT;

      /**
       * Logs path
       */
      public $logs_path;

      /**
       * Filename
       */
      public $filename;

      /**
       * Log types
       */
      public $log_type;

      public function __construct($loc){
        $this->DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];
        if (!file_exists($this->DOCUMENT_ROOT. '/wp-content/plugins/adept-drivers/logs')) {
            mkdir($this->DOCUMENT_ROOT. '/wp-content/plugins/adept-drivers/logs', 0777, true);
        }
        $this->logs_path = $this->DOCUMENT_ROOT .  '/wp-content/plugins/adept-drivers/logs/' . $loc . '.log';

      }

      public function Log_Error($message, $type){
        $f = fopen( $this->logs_path, 'a');
        $date = new DateTime();
        fwrite($f, date_format($date, 'Y-m-d H:i:s') . '--- ERROR --- ' . json_encode($message) . ' Type: ' . $type . PHP_EOL);
        fclose($f);
      }

      public function Log_Information($message, $type){
        $f = fopen( $this->logs_path, 'a');
        $date = new DateTime();
        fwrite($f, date_format($date, 'Y-m-d H:i:s') . '--- INFO --- ' . json_encode($message) . ' Type: ' . $type . PHP_EOL);
        fclose($f);
      }

      public function Log_Type($obj, $type){
        $f = fopen( $this->logs_path, 'a');
        $date = new DateTime();
        fwrite($f, date_format($date, 'Y-m-d H:i:s') . '--- TYPE --- ' . gettype($obj) . ' Type: ' . $type . PHP_EOL);
        fclose($f);
      }
 }