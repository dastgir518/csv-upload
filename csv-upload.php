<?php
/**
 * Plugin Name: CSV Uploader
 * Description: this plugin will allow user to upload data via csv
 * Author: Ghulam Dastgir
 * Version: 1.0
 * Author URI: https://example.com
 * Plugin URI: https:///example.com
 */

define('CSVUP_PLUGIN_DIR' , plugin_dir_path(__FILE__));

add_shortcode("csvup_upload_data_form" , "csvup_upload_form");
function csvup_upload_form(){
    ob_start();
    include_once CSVUP_PLUGIN_DIR . "/include/csv-upload-form.php";
    $form = ob_get_contents();
    ob_clean();
    return $form;
}


//create tables for database
register_activation_hook(__FILE__ , 'csvup_activation_callback');
function csvup_activation_callback(){
    global $wpdb;
    $prefix = $wpdb->prefix;
    $table_name = $prefix . "student_data";
    $collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE `".$table_name."` 
    (`id` INT NOT NULL AUTO_INCREMENT , `name` VARCHAR(50) NULL , 
    `rollno` VARCHAR(10) NULL , `class` VARCHAR(10) NULL , 
    `grade` VARCHAR(10) NULL , PRIMARY KEY (`id`)) 
    ".$collate."";
    
    include_once ABSPATH . "/wp-admin/includes/upgrade.php";
    dbDelta($sql);
}

//enque js
add_action('wp_enqueue_scripts' , 'csvup_scripts');
function csvup_scripts(){
    wp_enqueue_script('csvup-script' , plugin_dir_url(__FILE__) . '/assets/script.js' , array('jquery'));
    wp_localize_script('csvup-script' , 'csvup_object' , array(
        'ajaxurl' => admin_url('admin-ajax.php')
    ));
}

add_action("wp_ajax_csv_data_submit" ,  "csv_ajax_handler");
add_action("wp_ajax_nopriv_csv_data_submit" ,  "csv_ajax_handler");
function csv_ajax_handler(){

  echo json_encode(array(
    "status" => 1,
    "message" => "success",
  ));
  exit;
}