// Cập nhật trạng thái đơn hàng khi thanh toán đã được xác nhận
function senqt_lms_update_order_status( $order_id, $status ) {
    if ( 'offline_payment' === get_post_type( $order_id ) ) {
        // Cập nhật trạng thái của đơn hàng
        wp_update_post( array(
            'ID'          => $order_id,
            'post_status' => $status,
        ) );

        // Gửi email cho khách hàng
        $customer_email = get_post_meta( $order_id, '_customer_email', true );
        $subject = 'Xác nhận thanh toán';
        $message = 'Chúng tôi đã nhận được thanh toán của bạn. Trạng thái đơn hàng đã được cập nhật.';
        
        wp_mail( $customer_email, $subject, $message );
    }
}

// Ví dụ sử dụng hàm để cập nhật trạng thái và gửi email khi thanh toán đã xác nhận
senqt_lms_update_order_status( $order_id, 'completed' );
