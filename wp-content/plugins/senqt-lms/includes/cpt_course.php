<?php
// Đăng ký loại bài viết tùy chỉnh (Custom Post Type) cho khóa học
function senqt_register_course_post_type() {
    $labels = array(
        'name' => __( 'Khóa học', 'senqt-lms' ),
        'singular_name' => __( 'Khóa học', 'senqt-lms' ),
        'add_new' => __( 'Thêm mới', 'senqt-lms' ),
        'add_new_item' => __( 'Thêm khóa học mới', 'senqt-lms' ),
        'edit_item' => __( 'Chỉnh sửa khóa học', 'senqt-lms' ),
        'new_item' => __( 'Khóa học mới', 'senqt-lms' ),
        'view_item' => __( 'Xem khóa học', 'senqt-lms' ),
        'search_items' => __( 'Tìm kiếm khóa học', 'senqt-lms' ),
        'not_found' => __( 'Không tìm thấy khóa học', 'senqt-lms' ),
        'not_found_in_trash' => __( 'Không tìm thấy khóa học trong thùng rác', 'senqt-lms' ),
    );

    $args = array(
        'labels' => $labels,
        'has_archive' => true,
        'public' => true,
        'hierarchical' => false,
        'supports' => array(
            'title', 
            'editor', 
            'excerpt', 
            'custom-fields', 
            'thumbnail', 
            'page-attributes'
        ),
        'rewrite' => array( 'slug' => 'khoa-hoc' ),
        'show_in_rest' => true,
        'menu_position' => 5,
        'menu_icon' => 'dashicons-welcome-learn-more',
    );

    register_post_type( 'senqt_course', $args );
}
add_action( 'init', 'senqt_register_course_post_type' );

// Đăng ký taxonomy (Phân loại khóa học)
function senqt_register_category_taxonomy() {
    $labels = array(
        'name' => __( 'Các danh mục', 'senqt-lms' ),
        'singular_name' => __( 'Danh mục', 'senqt-lms' ),
        'search_items' => __( 'Tìm kiếm danh mục', 'senqt-lms' ),
        'all_items' => __( 'Tất cả danh mục', 'senqt-lms' ),
        'edit_item' => __( 'Chỉnh sửa danh mục', 'senqt-lms' ),
        'update_item' => __( 'Cập nhật danh mục', 'senqt-lms' ),
        'add_new_item' => __( 'Thêm danh mục mới', 'senqt-lms' ),
        'new_item_name' => __( 'Tên danh mục mới', 'senqt-lms' ),
        'menu_name' => __( 'Danh mục', 'senqt-lms' ),
    );

    $args = array(
        'labels' => $labels,
        'hierarchical' => true,
        'show_in_rest' => true,
        'rewrite' => array( 'slug' => 'danh-muc-khoa-hoc' ),
    );

    // Liên kết taxonomy với CPT 'senqt_course'
    register_taxonomy( 'senqt_course_category', array( 'senqt_course' ), $args );
}
add_action( 'init', 'senqt_register_category_taxonomy' );

// Hiển thị form CRUD trên frontend
function senqt_course_crud_form() {
    if (isset($_POST['senqt_save_course'])) {
        senqt_save_course();
    }

    $course = null;
    if (isset($_GET['edit_course'])) {
        $course_id = intval($_GET['edit_course']);
        $course = get_post($course_id);
    }

    if (isset($_GET['delete_course'])) {
        $course_id = intval($_GET['delete_course']);
        wp_delete_post($course_id, true);
        echo '<p>Khóa học đã được xóa.</p>';
    }
    ?>
    <style>
        form { max-width: 600px; margin: auto; }
        table { width: 100%; border-collapse: collapse; margin: 20px auto; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; }
        button { background-color: #0073aa; color: white; border: none; padding: 10px 15px; cursor: pointer; }
        button:hover { background-color: #005880; }
    </style>
    <h2><?php echo isset($course) ? 'Chỉnh sửa Khóa học' : 'Thêm Khóa học'; ?></h2>
    <form method="post" enctype="multipart/form-data">
        <p>
            <label for="course_title">Tiêu đề khóa học:</label><br>
            <input type="text" id="course_title" name="course_title" value="<?php echo isset($course) ? esc_attr($course->post_title) : ''; ?>" required>
        </p>
        <p>
            <label for="course_content">Nội dung khóa học:</label><br>
            <textarea id="course_content" name="course_content" rows="5" required><?php echo isset($course) ? esc_textarea($course->post_content) : ''; ?></textarea>
        </p>
        <p>
            <label for="course_category">Danh mục khóa học:</label><br>
            <select id="course_category" name="course_category">
                <?php
                $categories = get_terms(array(
                    'taxonomy' => 'senqt_course_category',
                    'hide_empty' => false,
                ));
                foreach ($categories as $category) {
                    $selected = isset($course) && has_term($category->term_id, 'senqt_course_category', $course->ID) ? 'selected' : '';
                    echo "<option value='{$category->term_id}' {$selected}>{$category->name}</option>";
                }
                ?>
            </select>
        </p>
        <p>
            <label for="course_image">Ảnh đại diện:</label><br>
            <input type="file" id="course_image" name="course_image">
        </p>
        <p>
            <label for="course_video">Video giới thiệu:</label><br>
            <input type="file" id="course_video" name="course_video">
        </p>
        <?php if (isset($course)): ?>
            <input type="hidden" name="course_id" value="<?php echo $course->ID; ?>">
        <?php endif; ?>
        <p>
            <button type="submit" name="senqt_save_course">Lưu Khóa học</button>
        </p>
    </form>

    <h2>Danh sách Khóa học</h2>
    <table>
        <thead>
            <tr>
                <th>Tiêu đề</th>
                <th>Danh mục</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $courses = new WP_Query(array(
                'post_type' => 'senqt_course',
                'posts_per_page' => -1,
            ));
            if ($courses->have_posts()):
                while ($courses->have_posts()): $courses->the_post();
                    $categories = get_the_terms(get_the_ID(), 'senqt_course_category');
                    $category_names = $categories ? implode(', ', wp_list_pluck($categories, 'name')) : 'Chưa phân loại';
                    ?>
                    <tr>
                        <td><?php the_title(); ?></td>
                        <td><?php echo esc_html($category_names); ?></td>
                        <td>
                            <a href="?edit_course=<?php the_ID(); ?>">Sửa</a> |
                            <a href="?delete_course=<?php the_ID(); ?>" onclick="return confirm('Bạn có chắc chắn muốn xóa?');">Xóa</a>
                        </td>
                    </tr>
                <?php endwhile; wp_reset_postdata(); else: ?>
                <tr>
                    <td colspan="3">Chưa có khóa học nào.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <?php
}
add_shortcode('senqt_course_crud', 'senqt_course_crud_form');

// Cập nhật logic xử lý lưu khóa học (Create/Update)
function senqt_save_course() {
    $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
    $course_title = sanitize_text_field($_POST['course_title']);
    $course_content = sanitize_textarea_field($_POST['course_content']);
    $course_category = intval($_POST['course_category']);

    $post_data = array(
        'post_title'   => $course_title,
        'post_content' => $course_content,
        'post_type'    => 'senqt_course',
        'post_status'  => 'publish',
    );

    if ($course_id > 0) {
        $post_data['ID'] = $course_id;
        $course_id = wp_update_post($post_data);
    } else {
        $course_id = wp_insert_post($post_data);
    }

    wp_set_object_terms($course_id, $course_category, 'senqt_course_category');

    // Xử lý upload ảnh
    if (!empty($_FILES['course_image']['name'])) {
        $image_id = media_handle_upload('course_image', $course_id);
        if (!is_wp_error($image_id)) {
            set_post_thumbnail($course_id, $image_id);
        }
    }

    // Xử lý upload video
    if (!empty($_FILES['course_video']['name'])) {
        $video_id = media_handle_upload('course_video', $course_id);
        if (!is_wp_error($video_id)) {
            update_post_meta($course_id, 'senqt_course_video', $video_id);
        }
    }

    echo '<p>Khóa học đã được lưu.</p>';
}