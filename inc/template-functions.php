<?php
/**
 * Template Functions
 *
 * @package Kavipushp_Bridals
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Fallback Menu for Primary Navigation
 */
function kavipushp_fallback_menu() {
    ?>
    <ul class="nav-menu">
        <li><a href="<?php echo esc_url(home_url('/')); ?>"><?php _e('Home', 'kavipushp-bridals'); ?></a></li>
        <li><a href="<?php echo esc_url(get_post_type_archive_link('bridal_set')); ?>"><?php _e('Collection', 'kavipushp-bridals'); ?></a></li>
        <li><a href="#"><?php _e('About Us', 'kavipushp-bridals'); ?></a></li>
        <li><a href="#"><?php _e('Contact', 'kavipushp-bridals'); ?></a></li>
    </ul>
    <?php
}

/**
 * Fallback Menu for Footer
 */
function kavipushp_footer_fallback_menu() {
    ?>
    <ul class="footer-menu">
        <li><a href="<?php echo esc_url(home_url('/')); ?>"><?php _e('Home', 'kavipushp-bridals'); ?></a></li>
        <li><a href="<?php echo esc_url(get_post_type_archive_link('bridal_set')); ?>"><?php _e('Browse Sets', 'kavipushp-bridals'); ?></a></li>
        <li><a href="#"><?php _e('How It Works', 'kavipushp-bridals'); ?></a></li>
        <li><a href="#"><?php _e('About Us', 'kavipushp-bridals'); ?></a></li>
        <li><a href="#"><?php _e('Contact Us', 'kavipushp-bridals'); ?></a></li>
        <li><a href="#"><?php _e('FAQs', 'kavipushp-bridals'); ?></a></li>
    </ul>
    <?php
}

/**
 * Get Bridal Set Price
 */
function kavipushp_get_set_price($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }

    $price = get_post_meta($post_id, '_rental_price', true);
    return $price ? floatval($price) : 0;
}

/**
 * Get Bridal Set Deposit Amount
 */
function kavipushp_get_deposit_amount($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }

    $deposit = get_post_meta($post_id, '_deposit_amount', true);
    return $deposit ? floatval($deposit) : 0;
}

/**
 * Check if Set is Available
 */
function kavipushp_is_set_available($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }

    $availability = get_post_meta($post_id, '_availability', true);
    return $availability === 'available';
}

/**
 * Get Availability Status Label
 */
function kavipushp_get_availability_label($status) {
    $labels = array(
        'available'   => __('Available', 'kavipushp-bridals'),
        'rented'      => __('Currently Rented', 'kavipushp-bridals'),
        'maintenance' => __('Under Maintenance', 'kavipushp-bridals'),
        'unavailable' => __('Unavailable', 'kavipushp-bridals'),
    );

    return isset($labels[$status]) ? $labels[$status] : $status;
}

/**
 * Get Booking Status Label
 */
function kavipushp_get_booking_status_label($status) {
    $labels = array(
        'pending'   => __('Pending', 'kavipushp-bridals'),
        'confirmed' => __('Confirmed', 'kavipushp-bridals'),
        'picked_up' => __('Picked Up', 'kavipushp-bridals'),
        'returned'  => __('Returned', 'kavipushp-bridals'),
        'completed' => __('Completed', 'kavipushp-bridals'),
        'cancelled' => __('Cancelled', 'kavipushp-bridals'),
    );

    return isset($labels[$status]) ? $labels[$status] : $status;
}

/**
 * Format Price
 */
function kavipushp_format_price($price, $currency = '') {
    if (!$currency) {
        $currency = get_theme_mod('currency_symbol', '₹');
    }

    return $currency . number_format($price, 0);
}

/**
 * Calculate Rental Total
 */
function kavipushp_calculate_rental_total($set_id, $pickup_date, $return_date) {
    $rental_price = kavipushp_get_set_price($set_id);
    $deposit = kavipushp_get_deposit_amount($set_id);

    $pickup = strtotime($pickup_date);
    $return = strtotime($return_date);

    $days = ceil(($return - $pickup) / (60 * 60 * 24)) + 1;
    $rental_total = $days * $rental_price;

    return array(
        'days'         => $days,
        'rental_price' => $rental_price,
        'rental_total' => $rental_total,
        'deposit'      => $deposit,
        'grand_total'  => $rental_total + $deposit,
    );
}

/**
 * Get User Bookings
 */
function kavipushp_get_user_bookings($user_email = '', $status = '') {
    if (!$user_email) {
        $current_user = wp_get_current_user();
        $user_email = $current_user->user_email;
    }

    $args = array(
        'post_type'      => 'booking',
        'posts_per_page' => -1,
        'meta_query'     => array(
            array(
                'key'   => '_customer_email',
                'value' => $user_email,
            ),
        ),
        'orderby'        => 'date',
        'order'          => 'DESC',
    );

    if ($status) {
        $args['meta_query'][] = array(
            'key'   => '_booking_status',
            'value' => $status,
        );
    }

    return get_posts($args);
}

/**
 * Check if Date is Available for Set
 */
function kavipushp_check_date_availability($set_id, $pickup_date, $return_date) {
    $args = array(
        'post_type'      => 'booking',
        'posts_per_page' => -1,
        'meta_query'     => array(
            'relation' => 'AND',
            array(
                'key'   => '_bridal_set_id',
                'value' => $set_id,
            ),
            array(
                'key'     => '_booking_status',
                'value'   => array('pending', 'confirmed', 'picked_up'),
                'compare' => 'IN',
            ),
        ),
    );

    $bookings = get_posts($args);

    foreach ($bookings as $booking) {
        $booked_pickup = get_post_meta($booking->ID, '_pickup_date', true);
        $booked_return = get_post_meta($booking->ID, '_return_date', true);

        // Check for date overlap
        if ($pickup_date <= $booked_return && $return_date >= $booked_pickup) {
            return false;
        }
    }

    return true;
}

/**
 * Get Booked Dates for Set
 */
function kavipushp_get_booked_dates($set_id) {
    $args = array(
        'post_type'      => 'booking',
        'posts_per_page' => -1,
        'meta_query'     => array(
            'relation' => 'AND',
            array(
                'key'   => '_bridal_set_id',
                'value' => $set_id,
            ),
            array(
                'key'     => '_booking_status',
                'value'   => array('pending', 'confirmed', 'picked_up'),
                'compare' => 'IN',
            ),
        ),
    );

    $bookings = get_posts($args);
    $booked_dates = array();

    foreach ($bookings as $booking) {
        $pickup = get_post_meta($booking->ID, '_pickup_date', true);
        $return = get_post_meta($booking->ID, '_return_date', true);

        $booked_dates[] = array(
            'start' => $pickup,
            'end'   => $return,
        );
    }

    return $booked_dates;
}

/**
 * Quick View AJAX Handler
 */
function kavipushp_quick_view_handler() {
    check_ajax_referer('kavipushp_nonce', 'nonce');

    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;

    if (!$product_id) {
        wp_send_json_error(array('message' => __('Invalid product', 'kavipushp-bridals')));
    }

    $post = get_post($product_id);

    if (!$post || $post->post_type !== 'bridal_set') {
        wp_send_json_error(array('message' => __('Product not found', 'kavipushp-bridals')));
    }

    $rental_price = get_post_meta($product_id, '_rental_price', true);
    $deposit = get_post_meta($product_id, '_deposit_amount', true);
    $availability = get_post_meta($product_id, '_availability', true);
    $includes = get_post_meta($product_id, '_set_includes', true);
    $thumb = get_the_post_thumbnail_url($product_id, 'product-large');

    ob_start();
    ?>
    <div class="quick-view-content" style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
        <div class="quick-view-image">
            <?php if ($thumb): ?>
                <img src="<?php echo esc_url($thumb); ?>" alt="<?php echo esc_attr($post->post_title); ?>" style="width: 100%; border-radius: 10px;">
            <?php endif; ?>
        </div>
        <div class="quick-view-details">
            <h2><?php echo esc_html($post->post_title); ?></h2>
            <div class="product-price" style="font-size: 28px; color: var(--primary-color); margin: 15px 0;">
                <?php echo kavipushp_format_price($rental_price); ?> <span style="font-size: 14px; color: #999;">/day</span>
            </div>

            <?php if ($availability === 'available'): ?>
                <span class="status-badge available"><i class="fas fa-check-circle"></i> <?php _e('Available', 'kavipushp-bridals'); ?></span>
            <?php else: ?>
                <span class="status-badge unavailable"><i class="fas fa-times-circle"></i> <?php echo kavipushp_get_availability_label($availability); ?></span>
            <?php endif; ?>

            <div class="quick-view-excerpt" style="margin: 20px 0; color: #666;">
                <?php echo wp_trim_words($post->post_content, 30); ?>
            </div>

            <?php if ($includes): ?>
            <div style="background: #f9f9f9; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <strong><?php _e('Includes:', 'kavipushp-bridals'); ?></strong><br>
                <?php echo nl2br(esc_html($includes)); ?>
            </div>
            <?php endif; ?>

            <?php if ($deposit): ?>
            <p><strong><?php _e('Security Deposit:', 'kavipushp-bridals'); ?></strong> <?php echo kavipushp_format_price($deposit); ?></p>
            <?php endif; ?>

            <a href="<?php echo get_permalink($product_id); ?>" class="btn btn-primary" style="margin-top: 15px;">
                <?php _e('View Details & Book', 'kavipushp-bridals'); ?>
            </a>
        </div>
    </div>
    <?php
    $html = ob_get_clean();

    wp_send_json_success(array('html' => $html));
}
add_action('wp_ajax_kavipushp_quick_view', 'kavipushp_quick_view_handler');
add_action('wp_ajax_nopriv_kavipushp_quick_view', 'kavipushp_quick_view_handler');

/**
 * Search Suggestions AJAX Handler
 */
function kavipushp_search_suggestions_handler() {
    check_ajax_referer('kavipushp_nonce', 'nonce');

    $query = isset($_POST['query']) ? sanitize_text_field($_POST['query']) : '';

    if (strlen($query) < 3) {
        wp_send_json_error();
    }

    $args = array(
        'post_type'      => 'bridal_set',
        'posts_per_page' => 5,
        's'              => $query,
    );

    $posts = get_posts($args);
    $suggestions = array();

    foreach ($posts as $post) {
        $suggestions[] = array(
            'id'    => $post->ID,
            'title' => $post->post_title,
            'url'   => get_permalink($post->ID),
            'image' => get_the_post_thumbnail_url($post->ID, 'thumbnail') ?: KAVIPUSHP_URI . '/assets/images/placeholder.jpg',
        );
    }

    wp_send_json_success(array('suggestions' => $suggestions));
}
add_action('wp_ajax_kavipushp_search_suggestions', 'kavipushp_search_suggestions_handler');
add_action('wp_ajax_nopriv_kavipushp_search_suggestions', 'kavipushp_search_suggestions_handler');

/**
 * Add Body Classes
 */
function kavipushp_body_classes($classes) {
    if (is_front_page()) {
        $classes[] = 'home-page';
    }

    if (is_post_type_archive('bridal_set')) {
        $classes[] = 'shop-page';
    }

    if (is_singular('bridal_set')) {
        $classes[] = 'single-product-page';
    }

    return $classes;
}
add_filter('body_class', 'kavipushp_body_classes');

/**
 * Modify Archive Title
 */
function kavipushp_archive_title($title) {
    if (is_post_type_archive('bridal_set')) {
        return __('Our Bridal Collection', 'kavipushp-bridals');
    }

    if (is_tax('bridal_category')) {
        return single_term_title('', false);
    }

    return $title;
}
add_filter('get_the_archive_title', 'kavipushp_archive_title');

/**
 * Custom Excerpt Length
 */
function kavipushp_excerpt_length($length) {
    return 20;
}
add_filter('excerpt_length', 'kavipushp_excerpt_length');

/**
 * Custom Excerpt More
 */
function kavipushp_excerpt_more($more) {
    return '...';
}
add_filter('excerpt_more', 'kavipushp_excerpt_more');
