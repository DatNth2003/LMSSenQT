<?php
if (!defined('ABSPATH')) exit;
?>

<div class="wrap">
    <h1><?php _e('Quản lý Thanh toán Offline', 'senqt-lms'); ?></h1>
    
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php _e('ID', 'senqt-lms'); ?></th>
                <th><?php _e('Học viên', 'senqt-lms'); ?></th>
                <th><?php _e('Khóa học', 'senqt-lms'); ?></th>
                <th><?php _e('Số tiền', 'senqt-lms'); ?></th>
                <th><?php _e('Trạng thái', 'senqt-lms'); ?></th>
                <th><?php _e('Ngày thanh toán', 'senqt-lms'); ?></th>
                <th><?php _e('Ghi chú', 'senqt-lms'); ?></th>
                <th><?php _e('Thao tác', 'senqt-lms'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ($payments): ?>
                <?php foreach ($payments as $payment): ?>
                    <tr>
                        <td><?php echo esc_html($payment->id); ?></td>
                        <td>
                            <?php 
                            $user = get_user_by('id', $payment->user_id);
                            echo esc_html($user ? $user->display_name : __('Unknown', 'senqt-lms'));
                            ?>
                        </td>
                        <td>
                            <?php 
                            $course = get_post($payment->course_id);
                            echo esc_html($course ? $course->post_title : __('Unknown', 'senqt-lms'));
                            ?>
                        </td>
                        <td><?php echo number_format($payment->amount, 0, ',', '.') . ' VNĐ'; ?></td>
                        <td>
                            <span class="payment-status status-<?php echo esc_attr($payment->status); ?>">
                                <?php echo esc_html(ucfirst($payment->status)); ?>
                            </span>
                        </td>
                        <td><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($payment->payment_date))); ?></td>
                        <td><?php echo esc_html($payment->notes); ?></td>
                        <td>
                            <form method="post" style="display: inline-block;">
                                <input type="hidden" name="payment_id" value="<?php echo esc_attr($payment->id); ?>">
                                <select name="new_status">
                                    <option value="pending" <?php selected($payment->status, 'pending'); ?>><?php _e('Chờ xử lý', 'senqt-lms'); ?></option>
                                    <option value="completed" <?php selected($payment->status, 'completed'); ?>><?php _e('Hoàn thành', 'senqt-lms'); ?></option>
                                    <option value="cancelled" <?php selected($payment->status, 'cancelled'); ?>><?php _e('Đã hủy', 'senqt-lms'); ?></option>
                                </select>
                                <button type="submit" class="button button-small">
                                    <?php _e('Cập nhật', 'senqt-lms'); ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8"><?php _e('Không có thanh toán nào.', 'senqt-lms'); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<style>
.payment-status {
    padding: 3px 8px;
    border-radius: 3px;
    font-weight: bold;
}
.status-pending {
    background: #f0f0f0;
    color: #777;
}
.status-completed {
    background: #dff0d8;
    color: #3c763d;
}
.status-cancelled {
    background: #f2dede;
    color: #a94442;
}
</style>
