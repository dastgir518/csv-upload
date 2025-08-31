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

    // ✅ Check if required PHP functions exist
    $required_functions = array("fopen", "fgetcsv", "fclose", "json_encode");
    foreach($required_functions as $func){
        if(!function_exists($func)){
            echo json_encode(array(
                "status" => 0,
                "message" => "Missing PHP function: " . $func,
            ));
            exit;
        }
    }

    // ✅ Check if $wpdb is available
    global $wpdb;
    if(!isset($wpdb) || !method_exists($wpdb, 'insert')){
        echo json_encode(array(
            "status" => 0,
            "message" => "Database handler (wpdb) or insert() method missing",
        ));
        exit;
    }

    // ✅ File upload check
    if(!isset($_FILES['csv_file']) || empty($_FILES['csv_file']['tmp_name'])){
        echo json_encode(array(
            "status" => 0,
            "message" => "CSV file not uploaded",
        ));
        exit;
    }

    $filename = $_FILES['csv_file']['tmp_name'];

    // ✅ Try opening file
    $handle = fopen($filename , "r");
    if(!$handle){
        echo json_encode(array(
            "status" => 0,
            "message" => "Unable to open CSV file",
        ));
        exit;
    }

    // ✅ Process file
    $last_insert = false;
    while(($data = fgetcsv($handle , 1000 , ",")) !== false){
        $name   = isset($data[0]) ? $data[0] : '';
        $rollno = isset($data[1]) ? $data[1] : '';
        $class  = isset($data[2]) ? $data[2] : '';
        $grade  = isset($data[3]) ? $data[3] : '';

        $table_name = $wpdb->prefix . "student_data";

        $last_insert = $wpdb->insert(
            $table_name,
            array(
                "name"   => $name,
                "rollno" => $rollno,
                "class"  => $class,
                "grade"  => $grade
            ),
            array("%s","%s","%s","%s")
        );
    }

    fclose($handle);

    echo json_encode(array(
        "status" => $last_insert ? 1 : 0,
        "message" => $last_insert ? "success" : "error",
    ));
    exit;
}


