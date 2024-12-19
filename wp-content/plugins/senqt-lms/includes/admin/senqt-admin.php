<?php
class SenQT_LMS_Admin {
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }

    public function add_admin_menu() {
        add_menu_page(
            __('Quản lý Khóa học', 'senqt-lms'),
            __('Quản lý Khóa học', 'senqt-lms'),
            'manage_options',
            'senqt_course_management',
            array($this, 'render_course_management_page'),
            'dashicons-welcome-learn-more',
            6
        );

        // Submenu cho khóa học
        add_submenu_page(
            'senqt_course_management',
            __('Tất cả khóa học', 'senqt-lms'),
            __('Tất cả khóa học', 'senqt-lms'),
            'manage_options',
            'edit.php?post_type=senqt_course',
            null
        );

        // Submenu cho danh mục
        add_submenu_page(
            'senqt_course_management',
            __('Danh mục khóa học', 'senqt-lms'),
            __('Danh mục khóa học', 'senqt-lms'),
            'manage_options',
            'edit-tags.php?taxonomy=senqt_course_category&post_type=senqt_course',
            null
        );

        // Submenu cho thanh toán offline
        add_submenu_page(
            'senqt_course_management',
            __('Thanh toán Offline', 'senqt-lms'),
            __('Thanh toán Offline', 'senqt-lms'),
            'manage_options',
            'senqt_offline_payments',
            array($this, 'render_offline_payments_page')
        );

        // Submenu cho báo cáo thống kê
        add_submenu_page(
            'senqt_course_management',
            __('Báo cáo & Thống kê', 'senqt-lms'),
            __('Báo cáo & Thống kê', 'senqt-lms'),
            'manage_options',
            'senqt_reports',
            array($this, 'render_reports_page')
        );
    }

    public function enqueue_admin_assets() {
        wp_enqueue_style(
            'senqt-lms-admin',
            SENQT_LMS_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            SENQT_LMS_VERSION
        );

        wp_enqueue_script(
            'senqt-lms-admin',
            SENQT_LMS_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            SENQT_LMS_VERSION,
            true
        );
    }

    public function render_course_management_page() {
        include SENQT_LMS_PLUGIN_DIR . 'includes/admin/views/course-management.php';
    }

    public function render_offline_payments_page() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'senqt_offline_payments';
        
        // Handle status updates
        if (isset($_POST['payment_id']) && isset($_POST['new_status'])) {
            $payment_id = intval($_POST['payment_id']);
            $new_status = sanitize_text_field($_POST['new_status']);
            
            $wpdb->update(
                $table_name,
                array('status' => $new_status),
                array('id' => $payment_id),
                array('%s'),
                array('%d')
            );
        }

        // Get all payments
        $payments = $wpdb->get_results("SELECT * FROM $table_name ORDER BY payment_date DESC");
        
        // Include view
        include SENQT_LMS_PLUGIN_DIR . 'includes/admin/views/offline-payments.php';
    }

    public function render_reports_page() {
        include SENQT_LMS_PLUGIN_DIR . 'includes/admin/views/reports.php';
    }
}
