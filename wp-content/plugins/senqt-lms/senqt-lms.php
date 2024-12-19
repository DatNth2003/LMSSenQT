<?php
/**
 * Plugin Name: SenQTLMS
 * Plugin URI: #
 * Description: Plugin quản lý khóa học cho công ty Sen Quốc Tế.
 * Version: 1.0
 * Author: DatNth
 * Author URI: #
 * License: GPL2
 * 
 * Requires at least: 5.0
 * Requires PHP: 7.2
 * WC requires at least: 3.0
 * WC tested up to: 8.4
 */

// Ngăn chặn truy cập trực tiếp
if (!defined('ABSPATH')) exit;

// Định nghĩa hằng số
define('SENQT_LMS_VERSION', '1.0');
define('SENQT_LMS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SENQT_LMS_PLUGIN_URL', plugin_dir_url(__FILE__));

// Kiểm tra WooCommerce
function senqt_lms_check_woocommerce() {
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', function() {
            ?>
            <div class="notice notice-error">
                <p><?php _e('SenQT LMS yêu cầu cài đặt và kích hoạt plugin WooCommerce. Vui lòng cài đặt WooCommerce trước khi sử dụng plugin này.', 'senqt-lms'); ?></p>
            </div>
            <?php
        });
        return false;
    }
    return true;
}

// Autoload classes
spl_autoload_register(function ($class_name) {
    if (strpos($class_name, 'SenQT_LMS') !== false) {
        $classes_dir = realpath(plugin_dir_path(__FILE__)) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR;
        $class_file = str_replace('_', DIRECTORY_SEPARATOR, $class_name) . '.php';
        $file = $classes_dir . $class_file;
        if (file_exists($file)) {
            require_once $file;
        }
    }
});

// Load core files
function senqt_lms_load_files() {
    if (!senqt_lms_check_woocommerce()) {
        return;
    }

    require_once SENQT_LMS_PLUGIN_DIR . 'includes/core/senqt-course.php';
    require_once SENQT_LMS_PLUGIN_DIR . 'includes/admin/senqt-admin.php';
    require_once SENQT_LMS_PLUGIN_DIR . 'includes/payment/senqt-payment.php';
}

// Initialize plugin
function senqt_lms_init() {
    if (!senqt_lms_check_woocommerce()) {
        return;
    }

    // Initialize admin
    if (is_admin()) {
        new SenQT_LMS_Admin();
    }

    // Initialize payment
    SenQT_LMS_Payment::get_instance();

    // Initialize course
    SenQT_LMS_Course::get_instance();
}

// Hook vào sau khi WooCommerce đã được load
add_action('plugins_loaded', 'senqt_lms_load_files', 20);
add_action('init', 'senqt_lms_init', 20);

// Activation hook
register_activation_hook(__FILE__, 'senqt_lms_activate');
function senqt_lms_activate() {
    if (!senqt_lms_check_woocommerce()) {
        wp_die(__('SenQT LMS yêu cầu cài đặt và kích hoạt plugin WooCommerce. Vui lòng cài đặt WooCommerce trước khi kích hoạt plugin này.', 'senqt-lms'));
    }

    // Tạo các bảng cần thiết
    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();

    // Table for offline payments
    $table_name = $wpdb->prefix . 'senqt_offline_payments';
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        course_id bigint(20) NOT NULL,
        amount decimal(10,2) NOT NULL,
        status varchar(20) NOT NULL DEFAULT 'pending',
        payment_date datetime DEFAULT CURRENT_TIMESTAMP,
        notes text,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    dbDelta($sql);

    // Flush rewrite rules
    flush_rewrite_rules();
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'senqt_lms_deactivate');
function senqt_lms_deactivate() {
    flush_rewrite_rules();
}
