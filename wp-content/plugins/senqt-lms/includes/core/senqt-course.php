<?php
class SenQT_LMS_Course {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->init();
    }

    private function init() {
        add_action('init', array($this, 'register_post_type'));
        add_action('init', array($this, 'register_taxonomies'));
        add_action('add_meta_boxes', array($this, 'add_course_meta_boxes'));
        add_action('save_post', array($this, 'save_course_meta'));
    }

    public function register_post_type() {
        $labels = array(
            'name'               => __('Khóa học', 'senqt-lms'),
            'singular_name'      => __('Khóa học', 'senqt-lms'),
            'add_new'           => __('Thêm mới', 'senqt-lms'),
            'add_new_item'      => __('Thêm khóa học mới', 'senqt-lms'),
            'edit_item'         => __('Chỉnh sửa khóa học', 'senqt-lms'),
            'new_item'          => __('Khóa học mới', 'senqt-lms'),
            'view_item'         => __('Xem khóa học', 'senqt-lms'),
            'search_items'      => __('Tìm kiếm khóa học', 'senqt-lms'),
            'not_found'         => __('Không tìm thấy khóa học', 'senqt-lms'),
            'not_found_in_trash'=> __('Không tìm thấy khóa học trong thùng rác', 'senqt-lms'),
            'menu_name'         => __('Khóa học', 'senqt-lms'),
        );

        $args = array(
            'labels'              => $labels,
            'public'              => true,
            'publicly_queryable'  => true,
            'show_ui'             => true,
            'show_in_menu'        => false,
            'query_var'           => true,
            'rewrite'             => array('slug' => 'khoa-hoc'),
            'capability_type'     => 'post',
            'has_archive'         => true,
            'hierarchical'        => false,
            'menu_position'       => 5,
            'supports'            => array('title', 'editor', 'thumbnail', 'excerpt'),
            'show_in_rest'        => true,
        );

        register_post_type('senqt_course', $args);
    }

    public function register_taxonomies() {
        $labels = array(
            'name'              => __('Danh mục khóa học', 'senqt-lms'),
            'singular_name'     => __('Danh mục khóa học', 'senqt-lms'),
            'search_items'      => __('Tìm danh mục', 'senqt-lms'),
            'all_items'         => __('Tất cả danh mục', 'senqt-lms'),
            'parent_item'       => __('Danh mục cha', 'senqt-lms'),
            'parent_item_colon' => __('Danh mục cha:', 'senqt-lms'),
            'edit_item'         => __('Sửa danh mục', 'senqt-lms'),
            'update_item'       => __('Cập nhật danh mục', 'senqt-lms'),
            'add_new_item'      => __('Thêm danh mục mới', 'senqt-lms'),
            'new_item_name'     => __('Tên danh mục mới', 'senqt-lms'),
            'menu_name'         => __('Danh mục khóa học', 'senqt-lms'),
        );

        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'danh-muc-khoa-hoc'),
            'show_in_rest'      => true,
        );

        register_taxonomy('senqt_course_category', array('senqt_course'), $args);
    }

    public function add_course_meta_boxes() {
        add_meta_box(
            'senqt_course_details',
            __('Thông tin khóa học', 'senqt-lms'),
            array($this, 'render_course_meta_box'),
            'senqt_course',
            'normal',
            'high'
        );
    }

    public function render_course_meta_box($post) {
        // Add nonce for security
        wp_nonce_field('senqt_course_meta_box', 'senqt_course_meta_box_nonce');

        // Get saved values
        $price = get_post_meta($post->ID, '_course_price', true);
        $video_url = get_post_meta($post->ID, '_course_video', true);
        $duration = get_post_meta($post->ID, '_course_duration', true);
        ?>
        <div class="senqt-course-meta">
            <p>
                <label for="course_price"><?php _e('Giá khóa học:', 'senqt-lms'); ?></label>
                <input type="number" id="course_price" name="course_price" value="<?php echo esc_attr($price); ?>" />
            </p>
            <p>
                <label for="course_video"><?php _e('URL Video giới thiệu:', 'senqt-lms'); ?></label>
                <input type="url" id="course_video" name="course_video" value="<?php echo esc_url($video_url); ?>" style="width: 100%;" />
            </p>
            <p>
                <label for="course_duration"><?php _e('Thời lượng khóa học:', 'senqt-lms'); ?></label>
                <input type="text" id="course_duration" name="course_duration" value="<?php echo esc_attr($duration); ?>" />
            </p>
        </div>
        <?php
    }

    public function save_course_meta($post_id) {
        // Check if our nonce is set
        if (!isset($_POST['senqt_course_meta_box_nonce'])) {
            return;
        }

        // Verify that the nonce is valid
        if (!wp_verify_nonce($_POST['senqt_course_meta_box_nonce'], 'senqt_course_meta_box')) {
            return;
        }

        // If this is an autosave, our form has not been submitted, so we don't want to do anything
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check the user's permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save course price
        if (isset($_POST['course_price'])) {
            update_post_meta($post_id, '_course_price', sanitize_text_field($_POST['course_price']));
        }

        // Save video URL
        if (isset($_POST['course_video'])) {
            update_post_meta($post_id, '_course_video', esc_url_raw($_POST['course_video']));
        }

        // Save duration
        if (isset($_POST['course_duration'])) {
            update_post_meta($post_id, '_course_duration', sanitize_text_field($_POST['course_duration']));
        }
    }

    // Helper functions
    public static function get_course_price($course_id) {
        return get_post_meta($course_id, '_course_price', true);
    }

    public static function get_course_video($course_id) {
        return get_post_meta($course_id, '_course_video', true);
    }

    public static function get_course_duration($course_id) {
        return get_post_meta($course_id, '_course_duration', true);
    }
}

// Initialize the class
SenQT_LMS_Course::get_instance();
