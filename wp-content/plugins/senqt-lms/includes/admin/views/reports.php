<?php
if (!defined('ABSPATH')) exit;

// Lấy thống kê doanh thu
global $wpdb;
$payment_table = $wpdb->prefix . 'senqt_offline_payments';

// Thống kê doanh thu theo tháng hiện tại
$current_month = date('Y-m');
$monthly_revenue = $wpdb->get_var($wpdb->prepare(
    "SELECT SUM(amount) FROM $payment_table 
    WHERE status = 'completed' 
    AND DATE_FORMAT(payment_date, '%Y-%m') = %s",
    $current_month
));

// Tổng doanh thu
$total_revenue = $wpdb->get_var(
    "SELECT SUM(amount) FROM $payment_table WHERE status = 'completed'"
);

// Thống kê học viên
$total_students = $wpdb->get_var(
    "SELECT COUNT(DISTINCT user_id) FROM $payment_table WHERE status = 'completed'"
);

// Thống kê khóa học
$total_courses = wp_count_posts('senqt_course');
$published_courses = $total_courses->publish;

// Thống kê đơn hàng
$pending_orders = $wpdb->get_var(
    "SELECT COUNT(*) FROM $payment_table WHERE status = 'pending'"
);
$completed_orders = $wpdb->get_var(
    "SELECT COUNT(*) FROM $payment_table WHERE status = 'completed'"
);
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <!-- Thống kê tổng quan -->
    <div class="senqt-stats-grid">
        <div class="stats-box">
            <h3><?php _e('Doanh thu tháng này', 'senqt-lms'); ?></h3>
            <p class="stats-number"><?php echo number_format($monthly_revenue, 0, ',', '.'); ?> VNĐ</p>
        </div>

        <div class="stats-box">
            <h3><?php _e('Tổng doanh thu', 'senqt-lms'); ?></h3>
            <p class="stats-number"><?php echo number_format($total_revenue, 0, ',', '.'); ?> VNĐ</p>
        </div>

        <div class="stats-box">
            <h3><?php _e('Tổng số học viên', 'senqt-lms'); ?></h3>
            <p class="stats-number"><?php echo number_format($total_students); ?></p>
        </div>

        <div class="stats-box">
            <h3><?php _e('Khóa học đã xuất bản', 'senqt-lms'); ?></h3>
            <p class="stats-number"><?php echo number_format($published_courses); ?></p>
        </div>
    </div>

    <!-- Thống kê đơn hàng -->
    <div class="senqt-order-stats">
        <h2><?php _e('Thống kê đơn hàng', 'senqt-lms'); ?></h2>
        <div class="order-stats-grid">
            <div class="stats-box">
                <h3><?php _e('Đơn hàng chờ xử lý', 'senqt-lms'); ?></h3>
                <p class="stats-number"><?php echo number_format($pending_orders); ?></p>
            </div>
            <div class="stats-box">
                <h3><?php _e('Đơn hàng hoàn thành', 'senqt-lms'); ?></h3>
                <p class="stats-number"><?php echo number_format($completed_orders); ?></p>
            </div>
        </div>
    </div>

    <!-- Biểu đồ doanh thu -->
    <div class="senqt-revenue-chart">
        <h2><?php _e('Biểu đồ doanh thu', 'senqt-lms'); ?></h2>
        <canvas id="revenueChart"></canvas>
    </div>

    <style>
        .senqt-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }

        .order-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }

        .stats-box {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .stats-box h3 {
            margin: 0 0 10px 0;
            color: #23282d;
            font-size: 16px;
        }

        .stats-number {
            font-size: 24px;
            font-weight: bold;
            margin: 0;
            color: #0073aa;
        }

        .senqt-revenue-chart {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin: 20px 0;
        }

        .senqt-order-stats {
            margin: 20px 0;
        }
    </style>

    <?php
    // Lấy dữ liệu doanh thu 6 tháng gần nhất
    $revenue_data = $wpdb->get_results(
        "SELECT DATE_FORMAT(payment_date, '%Y-%m') as month,
        SUM(amount) as revenue
        FROM $payment_table
        WHERE status = 'completed'
        GROUP BY month
        ORDER BY month DESC
        LIMIT 6"
    );

    $months = array();
    $revenues = array();
    foreach (array_reverse($revenue_data) as $data) {
        $months[] = date('m/Y', strtotime($data->month));
        $revenues[] = $data->revenue;
    }
    ?>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var ctx = document.getElementById('revenueChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($months); ?>,
                datasets: [{
                    label: 'Doanh thu (VNĐ)',
                    data: <?php echo json_encode($revenues); ?>,
                    borderColor: '#0073aa',
                    backgroundColor: 'rgba(0, 115, 170, 0.1)',
                    borderWidth: 2,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('vi-VN').format(value) + ' VNĐ';
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return new Intl.NumberFormat('vi-VN').format(context.raw) + ' VNĐ';
                            }
                        }
                    }
                }
            }
        });
    });
    </script>
</div>
