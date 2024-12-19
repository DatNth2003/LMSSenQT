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
        'show_in_menu' => false,
        'menu_position' => 5,
        'menu_icon' => 'dashicons-welcome-learn-more',
    );

    register_post_type( 'senqt_course', $args );

    // Đăng ký taxonomy cho danh mục khóa học
    $taxonomy_labels = array(
        'name'              => __( 'Danh mục khóa học', 'senqt-lms' ),
        'singular_name'     => __( 'Danh mục khóa học', 'senqt-lms' ),
        'search_items'      => __( 'Tìm danh mục', 'senqt-lms' ),
        'all_items'         => __( 'Tất cả danh mục', 'senqt-lms' ),
        'parent_item'       => __( 'Danh mục cha', 'senqt-lms' ),
        'parent_item_colon' => __( 'Danh mục cha:', 'senqt-lms' ),
        'edit_item'         => __( 'Sửa danh mục', 'senqt-lms' ),
        'update_item'       => __( 'Cập nhật danh mục', 'senqt-lms' ),
        'add_new_item'      => __( 'Thêm danh mục mới', 'senqt-lms' ),
        'new_item_name'     => __( 'Tên danh mục mới', 'senqt-lms' ),
        'menu_name'         => __( 'Danh mục khóa học', 'senqt-lms' ),
    );

    $taxonomy_args = array(
        'hierarchical'      => true,
        'labels'           => $taxonomy_labels,
        'show_ui'          => true,
        'show_admin_column' => true,
        'query_var'        => true,
        'rewrite'          => array( 'slug' => 'danh-muc-khoa-hoc' ),
        'show_in_rest'     => true,
    );

    register_taxonomy( 'senqt_course_category', array( 'senqt_course' ), $taxonomy_args );
}
add_action( 'init', 'senqt_register_course_post_type' );

// Thêm Metabox vào Dashboard cho CPT 'senqt_course'
function senqt_course_add_meta_boxes() {
    add_meta_box(
        'senqt_course_meta_box',
        __( 'Thông tin khóa học', 'senqt-lms' ),
        'senqt_course_meta_box_callback',
        'senqt_course',
        'normal',
        'default'
    );
}
add_action( 'add_meta_boxes', 'senqt_course_add_meta_boxes' );

// Callback cho Metabox
function senqt_course_meta_box_callback( $post ) {
    wp_nonce_field( 'senqt_course_meta_box', 'senqt_course_meta_box_nonce' );

    // Lấy giá trị đã lưu trước đó (nếu có)
    $course_video = get_post_meta( $post->ID, 'senqt_course_video', true );

    ?>
    <p>
        <label for="senqt_course_video"><?php _e( 'Video giới thiệu:', 'senqt-lms' ); ?></label><br>
        <input type="text" id="senqt_course_video" name="senqt_course_video" value="<?php echo esc_attr( $course_video ); ?>" size="50" placeholder="URL video (YouTube, Vimeo, v.v.)">
    </p>
    <?php
}

// Lưu dữ liệu Metabox khi lưu bài viết
function senqt_save_course_meta_box( $post_id ) {
    // Kiểm tra nonce
    if ( ! isset( $_POST['senqt_course_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['senqt_course_meta_box_nonce'], 'senqt_course_meta_box' ) ) {
        return;
    }

    // Kiểm tra quyền người dùng
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    // Lưu dữ liệu video
    if ( isset( $_POST['senqt_course_video'] ) ) {
        $course_video = sanitize_text_field( $_POST['senqt_course_video'] );
        update_post_meta( $post_id, 'senqt_course_video', $course_video );
    }
}
add_action( 'save_post', 'senqt_save_course_meta_box' );

// Tùy chỉnh cột hiển thị trên Dashboard cho CPT 'senqt_course'
function senqt_course_custom_columns( $columns ) {
    $columns['course_video'] = __( 'Video giới thiệu', 'senqt-lms' );
    return $columns;
}
add_filter( 'manage_senqt_course_posts_columns', 'senqt_course_custom_columns' );

function senqt_course_custom_columns_content( $column, $post_id ) {
    if ( $column === 'course_video' ) {
        $video = get_post_meta( $post_id, 'senqt_course_video', true );
        echo esc_html( $video );
    }
}
add_action( 'manage_senqt_course_posts_custom_column', 'senqt_course_custom_columns_content', 10, 2 );

// Thêm menu vào Dashboard
function senqt_course_dashboard_menu() {
    add_menu_page(
        __( 'Quản lý Khóa học', 'senqt-lms' ),
        __( 'Quản lý Khóa học', 'senqt-lms' ),
        'manage_options',
        'senqt_course_management',
        'senqt_course_management_page',
        'dashicons-welcome-learn-more',
        6
    );

    // Submenu "Danh mục khóa học"
    add_submenu_page(
        'senqt_course_management',
        __( 'Danh mục khóa học', 'senqt-lms' ),
        __( 'Danh mục khóa học', 'senqt-lms' ),
        'manage_options',
        'edit-tags.php?taxonomy=senqt_course_category&post_type=senqt_course',
        null
    );
}
add_action( 'admin_menu', 'senqt_course_dashboard_menu' );

function senqt_course_management_page() {
    global $wpdb;

    // Xử lý thêm/sửa/xóa
    if ( isset( $_POST['senqt_course_action'] ) ) {
        if ( $_POST['senqt_course_action'] == 'add' || $_POST['senqt_course_action'] == 'edit' ) {
            $post_data = array(
                'post_title' => sanitize_text_field( $_POST['course_title'] ),
                'post_content' => wp_kses_post( $_POST['course_content'] ), // Cho phép HTML an toàn
                'post_type' => 'senqt_course',
                'post_status' => 'publish'
            );

            if ( $_POST['senqt_course_action'] == 'edit' ) {
                $post_data['ID'] = intval( $_POST['course_id'] );
                $post_id = wp_update_post( $post_data );
            } else {
                $post_id = wp_insert_post( $post_data );
            }

            if ( $post_id && !is_wp_error( $post_id ) ) {
                // Lưu video URL
                update_post_meta( $post_id, 'senqt_course_video', sanitize_text_field( $_POST['course_video'] ) );
                
                // Xử lý featured image nếu có
                if ( isset( $_POST['_thumbnail_id'] ) ) {
                    set_post_thumbnail( $post_id, intval( $_POST['_thumbnail_id'] ) );
                }

                echo '<div class="updated"><p>' . 
                    ($_POST['senqt_course_action'] == 'add' ? 
                        __('Khóa học đã được thêm thành công!', 'senqt-lms') : 
                        __('Khóa học đã được cập nhật!', 'senqt-lms')) . 
                    '</p></div>';
            }
        } elseif ( $_POST['senqt_course_action'] == 'delete' ) {
            wp_delete_post( intval( $_POST['course_id'] ), true );
            echo '<div class="updated"><p>' . __('Khóa học đã bị xóa!', 'senqt-lms') . '</p></div>';
        }
    }

    // Lấy khóa học cần sửa (nếu có)
    $editing_course = null;
    if ( isset( $_GET['edit'] ) ) {
        $editing_course = get_post( intval( $_GET['edit'] ) );
    }

    // Lấy danh sách khóa học
    $courses = get_posts( array(
        'post_type' => 'senqt_course',
        'post_status' => 'publish',
        'numberposts' => -1,
    ) );

    ?>
    <div class="wrap">
        <h1><?php _e( 'Quản lý Khóa học', 'senqt-lms' ); ?></h1>

        <!-- Form Thêm/Sửa Khóa học -->
        <div class="postbox">
            <h2 class="hndle"><?php echo $editing_course ? __('Sửa khóa học', 'senqt-lms') : __('Thêm khóa học mới', 'senqt-lms'); ?></h2>
            <div class="inside">
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="senqt_course_action" value="<?php echo $editing_course ? 'edit' : 'add'; ?>">
                    <?php if ( $editing_course ) : ?>
                        <input type="hidden" name="course_id" value="<?php echo $editing_course->ID; ?>">
                    <?php endif; ?>

                    <table class="form-table">
                        <tr>
                            <th><label for="course_title"><?php _e( 'Tên khóa học', 'senqt-lms' ); ?></label></th>
                            <td>
                                <input type="text" id="course_title" name="course_title" class="regular-text" 
                                    value="<?php echo $editing_course ? esc_attr($editing_course->post_title) : ''; ?>" required>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="course_content"><?php _e( 'Nội dung khóa học', 'senqt-lms' ); ?></label></th>
                            <td>
                                <?php 
                                wp_editor( 
                                    $editing_course ? $editing_course->post_content : '', 
                                    'course_content',
                                    array(
                                        'media_buttons' => true,
                                        'textarea_name' => 'course_content',
                                        'textarea_rows' => 10,
                                        'teeny' => false
                                    )
                                ); 
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="course_video"><?php _e( 'Video giới thiệu', 'senqt-lms' ); ?></label></th>
                            <td>
                                <input type="text" id="course_video" name="course_video" class="regular-text"
                                    value="<?php echo $editing_course ? esc_attr(get_post_meta($editing_course->ID, 'senqt_course_video', true)) : ''; ?>">
                            </td>
                        </tr>
                        <tr>
                            <th><label><?php _e( 'Ảnh đại diện', 'senqt-lms' ); ?></label></th>
                            <td>
                                <?php
                                if ($editing_course) {
                                    $thumbnail_id = get_post_thumbnail_id($editing_course->ID);
                                    if ($thumbnail_id) {
                                        echo wp_get_attachment_image($thumbnail_id, 'thumbnail');
                                    }
                                }
                                ?>
                                <div id="featured-image-container">
                                    <input type="hidden" name="_thumbnail_id" id="_thumbnail_id" 
                                        value="<?php echo $editing_course ? get_post_thumbnail_id($editing_course->ID) : ''; ?>">
                                    <button type="button" class="button" id="upload_image_button">
                                        <?php _e('Chọn ảnh đại diện', 'senqt-lms'); ?>
                                    </button>
                                    <button type="button" class="button" id="remove_image_button" style="<?php echo !$editing_course || !has_post_thumbnail($editing_course->ID) ? 'display:none;' : ''; ?>">
                                        <?php _e('Xóa ảnh', 'senqt-lms'); ?>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </table>
                    <p class="submit">
                        <button type="submit" class="button button-primary">
                            <?php echo $editing_course ? __('Cập nhật khóa học', 'senqt-lms') : __('Thêm khóa học', 'senqt-lms'); ?>
                        </button>
                        <?php if ($editing_course) : ?>
                            <a href="<?php echo admin_url('admin.php?page=senqt_course_management'); ?>" class="button">
                                <?php _e('Hủy', 'senqt-lms'); ?>
                            </a>
                        <?php endif; ?>
                    </p>
                </form>
            </div>
        </div>

        <!-- Danh sách Khóa học -->
        <div class="postbox">
            <h2 class="hndle"><?php _e( 'Danh sách Khóa học', 'senqt-lms' ); ?></h2>
            <div class="inside">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th width="50"><?php _e( 'ID', 'senqt-lms' ); ?></th>
                            <th width="60"><?php _e( 'Ảnh', 'senqt-lms' ); ?></th>
                            <th><?php _e( 'Tên khóa học', 'senqt-lms' ); ?></th>
                            <th><?php _e( 'Video giới thiệu', 'senqt-lms' ); ?></th>
                            <th width="150"><?php _e( 'Hành động', 'senqt-lms' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ( $courses ) : ?>
                            <?php foreach ( $courses as $course ) : ?>
                                <tr>
                                    <td><?php echo $course->ID; ?></td>
                                    <td>
                                        <?php 
                                        if (has_post_thumbnail($course->ID)) {
                                            echo get_the_post_thumbnail($course->ID, array(50, 50));
                                        }
                                        ?>
                                    </td>
                                    <td><?php echo esc_html( $course->post_title ); ?></td>
                                    <td><?php echo esc_html( get_post_meta( $course->ID, 'senqt_course_video', true ) ); ?></td>
                                    <td>
                                        <a href="<?php echo add_query_arg('edit', $course->ID); ?>" class="button">
                                            <?php _e( 'Sửa', 'senqt-lms' ); ?>
                                        </a>
                                        <form method="post" style="display:inline;">
                                            <input type="hidden" name="senqt_course_action" value="delete">
                                            <input type="hidden" name="course_id" value="<?php echo $course->ID; ?>">
                                            <button type="submit" class="button" onclick="return confirm('<?php _e('Bạn có chắc chắn muốn xóa khóa học này?', 'senqt-lms'); ?>');">
                                                <?php _e( 'Xóa', 'senqt-lms' ); ?>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="5"><?php _e( 'Không có khóa học nào.', 'senqt-lms' ); ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($) {
        // Xử lý upload ảnh đại diện
        var mediaUploader;
        $('#upload_image_button').click(function(e) {
            e.preventDefault();
            if (mediaUploader) {
                mediaUploader.open();
                return;
            }
            mediaUploader = wp.media({
                title: '<?php _e('Chọn ảnh đại diện', 'senqt-lms'); ?>',
                button: {
                    text: '<?php _e('Sử dụng ảnh này', 'senqt-lms'); ?>'
                },
                multiple: false
            });

            mediaUploader.on('select', function() {
                var attachment = mediaUploader.state().get('selection').first().toJSON();
                $('#_thumbnail_id').val(attachment.id);
                $('#featured-image-container').prepend('<img src="' + attachment.url + '" style="max-width:150px;height:auto;margin-bottom:10px;display:block;">');
                $('#remove_image_button').show();
            });
            mediaUploader.open();
        });

        // Xử lý xóa ảnh đại diện
        $('#remove_image_button').click(function() {
            $('#_thumbnail_id').val('');
            $('#featured-image-container img').remove();
            $(this).hide();
        });
    });
    </script>
    <?php
}
