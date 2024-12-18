function senqt_lms_course_list_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'type'     => 'carousel', // Loại hiển thị: carousel hoặc danh sách
        'limit'    => 8,          // Số lượng khóa học tối đa
        'orderby'  => 'date',     // Sắp xếp theo ngày
        'order'    => 'DESC',     // Thứ tự giảm dần
    ), $atts, 'senqt_lms_course_list' );

    $args = array(
        'post_type'      => 'course',
        'posts_per_page' => $atts['limit'],
        'orderby'        => $atts['orderby'],
        'order'          => $atts['order'],
        'paged'          => ( $atts['type'] === 'list' ) ? max(1, get_query_var('paged')) : 1,
    );

    $query = new WP_Query( $args );
    $output = '';

    if ( $query->have_posts() ) {
        if ( $atts['type'] === 'carousel' ) {
            // Hiển thị dạng carousel
            $output .= '<div class="senqt-lms-carousel">';
            while ( $query->have_posts() ) {
                $query->the_post();
                $output .= '<div class="course-item">';
                $output .= '<a href="' . get_permalink() . '">';
                $output .= get_the_post_thumbnail( get_the_ID(), 'medium' );
                $output .= '<h3>' . get_the_title() . '</h3>';
                $output .= '</a>';
                $output .= '</div>';
            }
            $output .= '</div>';
        } else {
            // Hiển thị dạng danh sách với phân trang
            $output .= '<div class="senqt-lms-list">';
            while ( $query->have_posts() ) {
                $query->the_post();
                $output .= '<div class="course-item">';
                $output .= '<h3><a href="' . get_permalink() . '">' . get_the_title() . '</a></h3>';
                $output .= '<p>' . wp_trim_words( get_the_excerpt(), 15, '...' ) . '</p>';
                $output .= '</div>';
            }
            $output .= '</div>';

            // Phân trang
            $output .= '<div class="pagination">';
            $output .= paginate_links( array(
                'total'   => $query->max_num_pages,
                'current' => max(1, get_query_var('paged')),
            ) );
            $output .= '</div>';
        }
        wp_reset_postdata();
    } else {
        $output .= '<p>Không tìm thấy khóa học nào.</p>';
    }

    return $output;
}
add_shortcode( 'senqt_lms_course_list', 'senqt_lms_course_list_shortcode' );
