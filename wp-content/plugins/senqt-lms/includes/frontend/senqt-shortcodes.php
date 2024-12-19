<?php
class SenQT_LMS_Shortcodes {
    public function __construct() {
        add_shortcode('senqt_lms_course_list', array($this, 'render_course_list'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
    }

    public function enqueue_assets() {
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
            SENQT_LMS_PLUGIN_URL . 'assets/css/style.css',
            array(),
            SENQT_LMS_VERSION
        );
        
        wp_enqueue_script(
            'senqt-lms-script',
            SENQT_LMS_PLUGIN_URL . 'assets/js/script.js',
            array('jquery', 'owl-carousel'),
            SENQT_LMS_VERSION,
            true
        );
    }

    public function render_course_list($atts) {
        $atts = shortcode_atts(array(
            'type' => 'carousel',
            'limit' => 8,
            'orderby' => 'date',
            'order' => 'DESC',
            'category' => '',
        ), $atts);

        $args = array(
            'post_type' => 'senqt_course',
            'posts_per_page' => $atts['limit'],
            'orderby' => $atts['orderby'],
            'order' => $atts['order'],
        );

        if (!empty($atts['category'])) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'senqt_course_category',
                    'field' => 'slug',
                    'terms' => explode(',', $atts['category']),
                ),
            );
        }

        $query = new WP_Query($args);
        
        if (!$query->have_posts()) {
            return '<p class="no-courses">' . __('Không tìm thấy khóa học nào.', 'senqt-lms') . '</p>';
        }

        ob_start();

        if ($atts['type'] === 'carousel') {
            echo '<div class="senqt-lms-carousel owl-carousel owl-theme">';
        } else {
            echo '<div class="course-list">';
        }

        while ($query->have_posts()) {
            $query->the_post();
            $course_id = get_the_ID();
            ?>
            <div class="course-item">
                <a href="<?php the_permalink(); ?>">
                    <?php if (has_post_thumbnail()): ?>
                        <?php the_post_thumbnail('medium'); ?>
                    <?php else: ?>
                        <img src="<?php echo SENQT_LMS_PLUGIN_URL; ?>assets/images/default-course.jpg" alt="<?php the_title_attribute(); ?>">
                    <?php endif; ?>
                    
                    <h3><?php the_title(); ?></h3>
                    
                    <?php if ($price = SenQT_LMS_Course::get_course_price($course_id)): ?>
                        <div class="course-price">
                            <?php echo number_format($price, 0, ',', '.'); ?> VNĐ
                        </div>
                    <?php endif; ?>

                    <?php if ($duration = SenQT_LMS_Course::get_course_duration($course_id)): ?>
                        <div class="course-duration">
                            <i class="dashicons dashicons-clock"></i> <?php echo esc_html($duration); ?>
                        </div>
                    <?php endif; ?>

                    <div class="course-excerpt">
                        <?php echo wp_trim_words(get_the_excerpt(), 15); ?>
                    </div>
                </a>
            </div>
            <?php
        }

        echo '</div>';

        wp_reset_postdata();

        return ob_get_clean();
    }
}
