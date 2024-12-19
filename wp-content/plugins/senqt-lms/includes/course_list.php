<?php
// Shortcode hiển thị danh sách khóa học
function senqt_lms_course_list_shortcode( $atts ) {
    // Đảm bảo assets được load
    senqt_enqueue_course_assets();

    $atts = shortcode_atts( array(
        'type'     => 'carousel',
        'limit'    => 8,
        'orderby'  => 'date',
        'order'    => 'DESC'
    ), $atts );

    $args = array(
        'post_type'      => 'senqt_course',
        'posts_per_page' => $atts['limit'],
        'orderby'        => $atts['orderby'],
        'order'          => $atts['order'],
        'paged'          => ( $atts['type'] === 'list' ) ? max( 1, get_query_var('paged') ) : 1,
    );

    $query = new WP_Query( $args );
    $output = '';

    if ( $query->have_posts() ) {
        if ( $atts['type'] === 'carousel' ) {
            $output .= '<div class="senqt-lms-carousel owl-carousel owl-theme">';
            while ( $query->have_posts() ) {
                $query->the_post();
                $output .= '<div class="course-item">';
                $output .= '<a href="' . esc_url( get_permalink() ) . '">';
                if (has_post_thumbnail()) {
                    $output .= get_the_post_thumbnail( get_the_ID(), 'medium', array( 'alt' => esc_attr( get_the_title() ) ) );
                } else {
                    // Ảnh mặc định nếu không có ảnh đại diện
                    $output .= '<img src="' . esc_url(SENQT_LMS_PLUGIN_URL . 'assets/images/default-course.jpg') . '" alt="' . esc_attr( get_the_title() ) . '">';
                }
                $output .= '<h3>' . esc_html( get_the_title() ) . '</h3>';
                $video_url = get_post_meta( get_the_ID(), 'senqt_course_video', true );
                if ( !empty( $video_url ) ) {
                    $output .= '<div class="course-video">' . esc_url( $video_url ) . '</div>';
                }
                $output .= '</a>';
                $output .= '</div>';
            }
            $output .= '</div>';
        } else {
            $output .= '<div class="course-list">';
            while ( $query->have_posts() ) {
                $query->the_post();
                $output .= '<div class="course-item">';
                if (has_post_thumbnail()) {
                    $output .= '<div class="course-thumbnail">';
                    $output .= get_the_post_thumbnail( get_the_ID(), 'medium', array( 'alt' => esc_attr( get_the_title() ) ) );
                    $output .= '</div>';
                }
                $output .= '<div class="course-content">';
                $output .= '<h3><a href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a></h3>';
                $output .= '<div class="course-excerpt">' . wp_kses_post( wp_trim_words( get_the_excerpt(), 15, '...' ) ) . '</div>';
                $video_url = get_post_meta( get_the_ID(), 'senqt_course_video', true );
                if ( !empty( $video_url ) ) {
                    $output .= '<div class="course-video">' . esc_url( $video_url ) . '</div>';
                }
                $output .= '</div>';
                $output .= '</div>';
            }
            $output .= '</div>';

            if ($query->max_num_pages > 1) {
                $output .= '<div class="course-pagination">';
                $output .= paginate_links( array(
                    'total'      => $query->max_num_pages,
                    'current'    => max( 1, get_query_var('paged') ),
                    'prev_text'  => __('&laquo; Trước', 'senqt-lms'),
                    'next_text'  => __('Tiếp &raquo;', 'senqt-lms'),
                ) );
                $output .= '</div>';
            }
        }
        wp_reset_postdata();
    } else {
        $output .= '<p class="no-courses">' . __( 'Không tìm thấy khóa học nào.', 'senqt-lms' ) . '</p>';
    }

    return apply_filters( 'senqt_lms_course_list_output', $output, $atts );
}
add_shortcode( 'senqt_lms_course_list', 'senqt_lms_course_list_shortcode' );

// Đăng ký assets cho carousel và danh sách khóa học
function senqt_enqueue_course_assets() {
    // jQuery
    wp_enqueue_script('jquery');
    
    // Owl Carousel CSS
    wp_enqueue_style(
        'owl-carousel',
        'https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css',
        array(),
        '2.3.4'
    );
    wp_enqueue_style(
        'owl-carousel-theme',
        'https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css',
        array(),
        '2.3.4'
    );

    // Owl Carousel JS
    wp_enqueue_script(
        'owl-carousel',
        'https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js',
        array('jquery'),
        '2.3.4',
        true
    );

    // Custom CSS và JS
    wp_enqueue_style(
        'senqt-lms-style',
        SENQT_LMS_PLUGIN_URL . 'assets/style.css',
        array(),
        filemtime(SENQT_LMS_PLUGIN_PATH . 'assets/style.css')
    );
    
    wp_enqueue_script(
        'senqt-lms-script',
        SENQT_LMS_PLUGIN_URL . 'assets/script.js',
        array('jquery', 'owl-carousel'),
        filemtime(SENQT_LMS_PLUGIN_PATH . 'assets/script.js'),
        true
    );
}
