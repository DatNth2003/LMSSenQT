<?php
// Kiểm tra WooCommerce
if (!class_exists('WooCommerce')) {
    return;
}

// Include WooCommerce abstract classes
if (!class_exists('WC_Payment_Gateway')) {
    include_once WC_ABSPATH . 'includes/abstracts/abstract-wc-payment-gateway.php';
}

class SenQT_LMS_Payment {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        // Kiểm tra WooCommerce đã được kích hoạt
        if ($this->check_woocommerce()) {
            $this->init();
        }
    }

    private function check_woocommerce() {
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', function() {
                ?>
                <div class="notice notice-error">
                    <p><?php _e('SenQT LMS yêu cầu cài đặt và kích hoạt plugin WooCommerce.', 'senqt-lms'); ?></p>
                </div>
                <?php
            });
            return false;
        }
        return true;
    }

    public function init() {
        add_action('init', array($this, 'register_post_type'));
        add_action('wp_ajax_process_offline_payment', array($this, 'process_offline_payment'));
        add_action('wp_ajax_nopriv_process_offline_payment', array($this, 'process_offline_payment'));
        
        // Thêm phương thức thanh toán offline vào WooCommerce
        add_filter('woocommerce_payment_gateways', array($this, 'add_offline_gateway'));
        
        // Hook cho email và trạng thái đơn hàng
        add_action('woocommerce_order_status_changed', array($this, 'handle_order_status_change'), 10, 4);
        add_action('woocommerce_email_order_details', array($this, 'add_bank_details_to_email'), 10, 4);
        
        // Hook cho mã giảm giá
        add_filter('woocommerce_coupon_is_valid', array($this, 'validate_course_coupon'), 10, 2);
        add_action('woocommerce_applied_coupon', array($this, 'handle_course_coupon'));
    }

    public function add_offline_gateway($gateways) {
        $gateways[] = 'SenQT_LMS_WC_Offline_Gateway';
        return $gateways;
    }

    public function handle_order_status_change($order_id, $old_status, $new_status, $order) {
        if ($new_status == 'completed') {
            // Kích hoạt khóa học cho học viên
            $course_id = $order->get_meta('_senqt_course_id');
            $user_id = $order->get_customer_id();
            
            if ($course_id && $user_id) {
                // Thêm logic kích hoạt khóa học ở đây
                update_user_meta($user_id, '_enrolled_course_' . $course_id, true);
                
                // Gửi email thông báo kích hoạt khóa học
                $this->send_course_activation_email($order, $course_id);
            }
        }
    }

    public function send_course_activation_email($order, $course_id) {
        $course = get_post($course_id);
        $user_email = $order->get_billing_email();
        $user_name = $order->get_billing_first_name();
        
        $subject = sprintf(__('Khóa học %s đã được kích hoạt!', 'senqt-lms'), $course->post_title);
        
        $message = sprintf(
            __('Xin chào %s,

Cảm ơn bạn đã đăng ký khóa học tại Sen Quốc Tế. Khóa học của bạn đã được kích hoạt:

Tên khóa học: %s
Mã đơn hàng: %s

Bạn có thể truy cập khóa học ngay bây giờ tại:
%s

Nếu cần hỗ trợ, vui lòng liên hệ:
- Email: Senquocte@gmail.com
- Hotline: 096 677 76 28

Trân trọng,
Sen Quốc Tế', 'senqt-lms'),
            $user_name,
            $course->post_title,
            $order->get_order_number(),
            get_permalink($course_id)
        );
        
        wp_mail($user_email, $subject, $message);
    }

    public function add_bank_details_to_email($order, $sent_to_admin, $plain_text, $email) {
        if ($order->get_payment_method() != 'offline_payment') {
            return;
        }
        
        echo '<h2>' . __('Thông tin chuyển khoản', 'senqt-lms') . '</h2>';
        echo '<p>' . __('Vui lòng chuyển khoản theo thông tin sau:', 'senqt-lms') . '</p>';
        echo '<ul>';
        echo '<li>' . __('Ngân hàng: VCB', 'senqt-lms') . '</li>';
        echo '<li>' . __('Số tài khoản: 1234567890', 'senqt-lms') . '</li>';
        echo '<li>' . __('Chủ tài khoản: CÔNG TY CỔ PHẦN SEN QUỐC TẾ', 'senqt-lms') . '</li>';
        echo '<li>' . sprintf(__('Nội dung: [%s] Thanh toan khoa hoc', 'senqt-lms'), $order->get_order_number()) . '</li>';
        echo '</ul>';
    }

    public function validate_course_coupon($valid, $coupon) {
        if (!$valid) {
            return $valid;
        }
        
        // Kiểm tra xem mã giảm giá có áp dụng cho khóa học không
        $course_ids = $coupon->get_meta('_course_ids');
        if (empty($course_ids)) {
            return $valid;
        }
        
        // Kiểm tra giỏ hàng
        $cart = WC()->cart;
        if (!$cart) {
            return false;
        }
        
        $valid = false;
        foreach ($cart->get_cart() as $cart_item) {
            $product_id = $cart_item['product_id'];
            $course_id = get_post_meta($product_id, '_course_id', true);
            
            if ($course_id && in_array($course_id, $course_ids)) {
                $valid = true;
                break;
            }
        }
        
        return $valid;
    }

    public function handle_course_coupon($coupon_code) {
        $coupon = new WC_Coupon($coupon_code);
        
        // Lưu thông tin sử dụng mã giảm giá
        $usage_count = $coupon->get_usage_count();
        $coupon->set_usage_count($usage_count + 1);
        $coupon->save();
    }

    public function process_offline_payment() {
        check_ajax_referer('senqt_offline_payment', 'payment_nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(array(
                'message' => __('Vui lòng đăng nhập để thực hiện thanh toán.', 'senqt-lms')
            ));
        }

        $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
        $coupon_code = isset($_POST['coupon_code']) ? sanitize_text_field($_POST['coupon_code']) : '';
        
        if (!$course_id) {
            wp_send_json_error(array(
                'message' => __('Khóa học không hợp lệ.', 'senqt-lms')
            ));
        }

        // Tạo đơn hàng WooCommerce
        $product_id = $this->get_or_create_wc_product($course_id);
        
        $order = wc_create_order();
        
        // Thêm sản phẩm vào đơn hàng
        $order->add_product(wc_get_product($product_id), 1);
        
        // Áp dụng mã giảm giá nếu có
        if (!empty($coupon_code)) {
            $coupon = new WC_Coupon($coupon_code);
            if ($coupon->is_valid()) {
                $order->apply_coupon($coupon_code);
            }
        }
        
        // Cập nhật thông tin thanh toán
        $order->set_payment_method('offline_payment');
        $order->set_billing_first_name('Công ty Cổ phần');
        $order->set_billing_last_name('Sen Quốc Tế');
        $order->set_billing_company('Công ty Cổ phần Sen Quốc Tế');
        $order->set_billing_address_1('281 Điện Biên Phủ');
        $order->set_billing_city('Nha Trang');
        $order->set_billing_state('Khánh Hòa');
        $order->set_billing_postcode('');
        $order->set_billing_country('VN');
        $order->set_billing_email('Senquocte@gmail.com');
        $order->set_billing_phone('096 677 76 28');
        
        // Cập nhật meta data
        $order->update_meta_data('_senqt_course_id', $course_id);
        
        // Lưu đơn hàng
        $order->calculate_totals();
        $order->save();
        
        // Cập nhật trạng thái
        $order->update_status('on-hold', __('Đơn hàng chờ thanh toán chuyển khoản.', 'senqt-lms'));
        
        // Gửi email xác nhận đơn hàng
        WC()->mailer()->customer_invoice($order);
        
        wp_send_json_success(array(
            'message' => __('Đơn hàng đã được tạo thành công!', 'senqt-lms'),
            'order_id' => $order->get_id()
        ));
    }

    private function get_or_create_wc_product($course_id) {
        $course = get_post($course_id);
        $sku = 'COURSE-' . $course_id;
        
        // Tìm sản phẩm theo SKU
        $product_id = wc_get_product_id_by_sku($sku);
        
        if (!$product_id) {
            // Tạo sản phẩm mới
            $product = new WC_Product_Simple();
            $product->set_name($course->post_title);
            $product->set_status('publish');
            $product->set_catalog_visibility('hidden');
            $product->set_price(SenQT_LMS_Course::get_course_price($course_id));
            $product->set_regular_price(SenQT_LMS_Course::get_course_price($course_id));
            $product->set_sku($sku);
            $product->save();
            
            $product_id = $product->get_id();
            
            // Lưu liên kết với khóa học
            update_post_meta($product_id, '_course_id', $course_id);
        }
        
        return $product_id;
    }
}

// Gateway class cho thanh toán offline
class SenQT_LMS_WC_Offline_Gateway extends WC_Payment_Gateway {
    public function __construct() {
        $this->id = 'offline_payment';
        $this->title = __('Chuyển khoản ngân hàng', 'senqt-lms');
        $this->description = $this->get_payment_description();
        $this->method_title = __('Chuyển khoản ngân hàng', 'senqt-lms');
        $this->method_description = __('Thanh toán bằng chuyển khoản ngân hàng.', 'senqt-lms');
        $this->has_fields = false;
        
        $this->init_form_fields();
        $this->init_settings();
    }
    
    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title' => __('Bật/Tắt', 'senqt-lms'),
                'type' => 'checkbox',
                'label' => __('Bật thanh toán chuyển khoản', 'senqt-lms'),
                'default' => 'yes'
            )
        );
    }
    
    private function get_payment_description() {
        return __('
            Thông tin chuyển khoản:
            - Ngân hàng: VCB
            - Số tài khoản: 1234567890
            - Chủ tài khoản: CÔNG TY CỔ PHẦN SEN QUỐC TẾ
            
            Nội dung chuyển khoản: [Mã đơn hàng] [Tên của bạn]
            
            Đơn hàng sẽ được xử lý sau khi chúng tôi nhận được thanh toán (trong vòng 24h).
        ', 'senqt-lms');
    }
}

// Initialize the class
SenQT_LMS_Payment::get_instance();
