<?php
if (!defined('ABSPATH')) exit;
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="senqt-admin-overview">
        <!-- Thống kê tổng quan -->
        <div class="senqt-stats-box">
            <h3><?php _e('Thống kê', 'senqt-lms'); ?></h3>
            <?php
            $total_courses = wp_count_posts('senqt_course');
            $published_courses = $total_courses->publish;
            ?>
            <p><?php printf(__('Tổng số khóa học: %d', 'senqt-lms'), $published_courses); ?></p>
        </div>

        <!-- Các liên kết nhanh -->
        <div class="senqt-quick-links">
            <h3><?php _e('Liên kết nhanh', 'senqt-lms'); ?></h3>
            <a href="<?php echo admin_url('post-new.php?post_type=senqt_course'); ?>" class="button button-primary">
                <?php _e('Thêm khóa học mới', 'senqt-lms'); ?>
            </a>
            <a href="<?php echo admin_url('edit.php?post_type=senqt_course'); ?>" class="button">
                <?php _e('Quản lý khóa học', 'senqt-lms'); ?>
            </a>
            <a href="<?php echo admin_url('edit-tags.php?taxonomy=senqt_course_category&post_type=senqt_course'); ?>" class="button">
                <?php _e('Quản lý danh mục', 'senqt-lms'); ?>
            </a>
        </div>
    </div>

    <style>
        .senqt-admin-overview {
            margin-top: 20px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        .senqt-stats-box, .senqt-quick-links {
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .senqt-quick-links .button {
            margin: 5px;
            display: inline-block;
        }
    </style>
</div>
