<?php
/**
 * Plugin Name: SenQTLMS
 * Plugin URI: #
 * Description: Plugin quản lý khóa học cho công ty Sen Quốc Tế.
 * Version: 1.0
 * Author: DatNth
 * Author URI: #
 * License: GPL2
 */

// Ngăn chặn truy cập trực tiếp
if ( !defined( 'ABSPATH' ) ) exit;

// Định nghĩa đường dẫn và URL của plugin
define( 'SENQT_LMS_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'SENQT_LMS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'SENQT_LMS_VERSION', '1.0' );

// Include các file cần thiết
include_once( SENQT_LMS_PLUGIN_PATH . 'includes/cpt_course.php' );
include_once( SENQT_LMS_PLUGIN_PATH . 'includes/course_list.php' );

// Tải style hoặc script
function senqt_lms_enqueue_assets() {
    wp_enqueue_style( 'senqt-lms-style', SENQT_LMS_PLUGIN_URL . 'assets/style.css', array(), SENQT_LMS_VERSION );
    wp_enqueue_script( 'senqt-lms-script', SENQT_LMS_PLUGIN_URL . 'assets/script.js', array('jquery'), SENQT_LMS_VERSION, true );
}
add_action( 'wp_enqueue_scripts', 'senqt_lms_enqueue_assets' );
