jQuery(document).ready(function($) {
    // Kiểm tra xem có carousel nào không
    if ($('.senqt-lms-carousel').length > 0) {
        $('.senqt-lms-carousel').owlCarousel({
            items: 4,
            loop: true,
            margin: 10,
            nav: true,
            dots: true,
            autoplay: true,
            autoplayTimeout: 3000,
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
