// Đăng ký Shortcode để hiển thị form thanh toán offline và danh sách khóa học
function senqt_lms_offline_payment_shortcode( $atts ) {
    if ( is_admin() ) {
        return ''; // Không hiển thị khi ở trang quản trị
    }

    ob_start();

    // Lấy danh sách các khóa học (post type 'course')
    $args = array(
        'post_type' => 'course',
        'posts_per_page' => -1, // Lấy tất cả khóa học
    );
    $query = new WP_Query( $args );

    if ( $query->have_posts() ) {
        // Hiển thị danh sách khóa học
        echo '<form method="post">';
        echo '<h3>Chọn khóa học bạn muốn thanh toán:</h3>';
        echo '<select name="course_id" required>';
        while ( $query->have_posts() ) {
            $query->the_post();
            echo '<option value="' . get_the_ID() . '">' . get_the_title() . '</option>';
        }
        echo '</select>';

        // Thêm các trường thông tin thanh toán
        echo '<h3>Thông tin thanh toán</h3>';
        echo '<label for="payment_method">Phương thức thanh toán:</label>';
        echo '<select name="payment_method" required>';
        echo '<option value="bank_transfer">Chuyển khoản ngân hàng</option>';
        echo '<option value="cash">Tiền mặt tại trung tâm</option>';
        echo '</select><br>';

        echo '<label for="amount">Số tiền:</label>';
        echo '<input type="text" name="amount" required><br>';

        echo '<label for="payment_info">Thông tin thanh toán:</label>';
        echo '<textarea name="payment_info" rows="5" required></textarea><br>';

        echo '<input type="submit" name="submit_payment" value="Gửi yêu cầu thanh toán">';
        echo '</form>';
    } else {
        echo '<p>Không có khóa học nào.</p>';
    }

    wp_reset_postdata();

    // Xử lý thanh toán nếu form được gửi
    if ( isset( $_POST['submit_payment'] ) ) {
        senqt_lms_handle_offline_payment( $_POST );
    }

    return ob_get_clean();
}
add_shortcode( 'senqt_lms_offline_payment', 'senqt_lms_offline_payment_shortcode' );
