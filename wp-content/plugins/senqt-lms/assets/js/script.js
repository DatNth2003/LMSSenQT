jQuery(document).ready(function($) {
    // Initialize Owl Carousel
    if ($('.senqt-lms-carousel').length > 0) {
        $('.senqt-lms-carousel').owlCarousel({
            items: 4,
            loop: true,
            margin: 20,
            nav: true,
            dots: true,
            autoplay: true,
            autoplayTimeout: 5000,
            autoplayHoverPause: true,
            responsive: {
                0: {
                    items: 1
                },
                576: {
                    items: 2
                },
                768: {
                    items: 3
                },
                992: {
                    items: 4
                }
            },
            navText: [
                '<i class="dashicons dashicons-arrow-left-alt2"></i>',
                '<i class="dashicons dashicons-arrow-right-alt2"></i>'
            ]
        });
    }
});
