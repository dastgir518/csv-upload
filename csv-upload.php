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


//create tables
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