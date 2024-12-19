<?php
class SenQT_LMS_Payment {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        $this->init();
    }

    private function init() {
        add_action('init', array($this, 'register_post_type'));
        add_shortcode('senqt_offline_payment', array($this, 'render_payment_form'));
        add_action('wp_ajax_process_offline_payment', array($this, 'process_offline_payment'));
        add_action('wp_ajax_nopriv_process_offline_payment', array($this, 'process_offline_payment'));
    }

    public function register_post_type() {
        $labels = array(
            'name'               => __('Thanh toán Offline', 'senqt-lms'),
            'singular_name'      => __('Thanh toán Offline', 'senqt-lms'),
            'add_new'           => __('Thêm mới', 'senqt-lms'),
            'add_new_item'      => __('Thêm thanh toán mới', 'senqt-lms'),
            'edit_item'         => __('Chỉnh sửa thanh toán', 'senqt-lms'),
            'new_item'          => __('Thanh toán mới', 'senqt-lms'),
            'view_item'         => __('Xem thanh toán', 'senqt-lms'),
            'search_items'      => __('Tìm kiếm thanh toán', 'senqt-lms'),
            'not_found'         => __('Không tìm thấy thanh toán', 'senqt-lms'),
            'not_found_in_trash'=> __('Không tìm thấy thanh toán trong thùng rác', 'senqt-lms'),
            'menu_name'         => __('Thanh toán Offline', 'senqt-lms'),
        );

        $args = array(
            'labels'              => $labels,
            'public'              => false,
            'publicly_queryable'  => false,
            'show_ui'             => true,
            'show_in_menu'        => false,
            'query_var'           => true,
            'capability_type'     => 'post',
            'has_archive'         => false,
            'hierarchical'        => false,
            'supports'            => array('title', 'editor'),
        );

        register_post_type('senqt_offline_payment', $args);
    }

    public function render_payment_form($atts) {
        if (!is_user_logged_in()) {
            return '<p>' . __('Vui lòng đăng nhập để thực hiện thanh toán.', 'senqt-lms') . '</p>';
        }

        $course_id = isset($atts['course_id']) ? intval($atts['course_id']) : get_the_ID();
        $course = get_post($course_id);

        if (!$course || $course->post_type !== 'senqt_course') {
            return '<p>' . __('Khóa học không tồn tại.', 'senqt-lms') . '</p>';
        }

        $price = SenQT_LMS_Course::get_course_price($course_id);
        
        ob_start();
        ?>
        <div class="senqt-payment-form">
            <h3><?php _e('Thông tin thanh toán', 'senqt-lms'); ?></h3>
            <form id="offline-payment-form" method="post">
                <?php wp_nonce_field('senqt_offline_payment', 'payment_nonce'); ?>
                <input type="hidden" name="course_id" value="<?php echo esc_attr($course_id); ?>">
                
                <div class="form-row">
                    <label><?php _e('Khóa học:', 'senqt-lms'); ?></label>
                    <strong><?php echo esc_html($course->post_title); ?></strong>
                </div>

                <div class="form-row">
                    <label><?php _e('Giá:', 'senqt-lms'); ?></label>
                    <strong><?php echo number_format($price, 0, ',', '.'); ?> VNĐ</strong>
                </div>

                <div class="form-row">
                    <label for="payment_notes"><?php _e('Ghi chú:', 'senqt-lms'); ?></label>
                    <textarea name="payment_notes" id="payment_notes" rows="4"></textarea>
                </div>

                <div class="form-row">
                    <button type="submit" class="button button-primary">
                        <?php _e('Gửi yêu cầu thanh toán', 'senqt-lms'); ?>
                    </button>
                </div>
            </form>

            <div class="payment-instructions">
                <h4><?php _e('Hướng dẫn thanh toán:', 'senqt-lms'); ?></h4>
                <ol>
                    <li><?php _e('Chuyển khoản theo thông tin:', 'senqt-lms'); ?>
                        <ul>
                            <li><?php _e('Ngân hàng: VCB', 'senqt-lms'); ?></li>
                            <li><?php _e('Số tài khoản: 1234567890', 'senqt-lms'); ?></li>
                            <li><?php _e('Chủ tài khoản: CÔNG TY CỔ PHẦN SEN QUỐC TẾ', 'senqt-lms'); ?></li>
                        </ul>
                    </li>
                    <li><?php _e('Nội dung chuyển khoản: [Tên của bạn] thanh toan khoa hoc [Tên khóa học]', 'senqt-lms'); ?></li>
                    <li><?php _e('Sau khi chuyển khoản, vui lòng điền ghi chú với thông tin chuyển khoản của bạn.', 'senqt-lms'); ?></li>
                    <li><?php _e('Chúng tôi sẽ xác nhận và kích hoạt khóa học cho bạn trong vòng 24h.', 'senqt-lms'); ?></li>
                </ol>
            </div>
        </div>

        <style>
        .senqt-payment-form {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .form-row {
            margin-bottom: 15px;
        }
        .form-row label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-row textarea {
            width: 100%;
        }
        .payment-instructions {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
        .payment-instructions ul {
            margin-left: 20px;
        }
        </style>

        <script>
        jQuery(document).ready(function($) {
            $('#offline-payment-form').on('submit', function(e) {
                e.preventDefault();
                
                var form = $(this);
                var submitButton = form.find('button[type="submit"]');
                
                submitButton.prop('disabled', true);
                
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'process_offline_payment',
                        course_id: form.find('input[name="course_id"]').val(),
                        payment_notes: form.find('textarea[name="payment_notes"]').val(),
                        payment_nonce: form.find('input[name="payment_nonce"]').val()
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('<?php _e('Yêu cầu thanh toán đã được gửi thành công!', 'senqt-lms'); ?>');
                            window.location.reload();
                        } else {
                            alert(response.data.message || '<?php _e('Có lỗi xảy ra. Vui lòng thử lại.', 'senqt-lms'); ?>');
                        }
                    },
                    error: function() {
                        alert('<?php _e('Có lỗi xảy ra. Vui lòng thử lại.', 'senqt-lms'); ?>');
                    },
                    complete: function() {
                        submitButton.prop('disabled', false);
                    }
                });
            });
        });
        </script>
        <?php
        return ob_get_clean();
    }

    public function process_offline_payment() {
        check_ajax_referer('senqt_offline_payment', 'payment_nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(array(
                'message' => __('Vui lòng đăng nhập để thực hiện thanh toán.', 'senqt-lms')
            ));
        }

        $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
        $notes = isset($_POST['payment_notes']) ? sanitize_textarea_field($_POST['payment_notes']) : '';

        if (!$course_id) {
            wp_send_json_error(array(
                'message' => __('Khóa học không hợp lệ.', 'senqt-lms')
            ));
        }

        $price = SenQT_LMS_Course::get_course_price($course_id);
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'senqt_offline_payments';
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'user_id' => get_current_user_id(),
                'course_id' => $course_id,
                'amount' => $price,
                'status' => 'pending',
                'notes' => $notes,
            ),
            array(
                '%d',
                '%d',
                '%f',
                '%s',
                '%s'
            )
        );

        if ($result) {
            // Gửi email thông báo cho admin
            $admin_email = get_option('admin_email');
            $subject = sprintf(__('Yêu cầu thanh toán mới cho khóa học: %s', 'senqt-lms'), get_the_title($course_id));
            $message = sprintf(
                __('Có yêu cầu thanh toán mới:\n\nKhóa học: %s\nHọc viên: %s\nSố tiền: %s VNĐ\nGhi chú: %s', 'senqt-lms'),
                get_the_title($course_id),
                wp_get_current_user()->display_name,
                number_format($price, 0, ',', '.'),
                $notes
            );
            
            wp_mail($admin_email, $subject, $message);

            wp_send_json_success(array(
                'message' => __('Yêu cầu thanh toán đã được gửi thành công!', 'senqt-lms')
            ));
        } else {
            wp_send_json_error(array(
                'message' => __('Không thể tạo yêu cầu thanh toán. Vui lòng thử lại.', 'senqt-lms')
            ));
        }
    }
}

// Initialize the class
SenQT_LMS_Payment::get_instance();
