<?php
/**
 * Kavipushp Bridals Theme Functions
 *
 * @package Kavipushp_Bridals
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Theme Constants
define('KAVIPUSHP_VERSION', '1.0.0');
define('KAVIPUSHP_DIR', get_template_directory());
define('KAVIPUSHP_URI', get_template_directory_uri());

/**
 * Theme Setup
 */
function kavipushp_setup() {
    // Add theme support
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo', array(
        'height'      => 100,
        'width'       => 300,
        'flex-height' => true,
        'flex-width'  => true,
    ));
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
    ));
    add_theme_support('woocommerce');
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');

    // Register navigation menus
    register_nav_menus(array(
        'primary'   => __('Primary Menu', 'kavipushp-bridals'),
        'footer'    => __('Footer Menu', 'kavipushp-bridals'),
    ));

    // Add image sizes
    add_image_size('product-thumb', 400, 500, true);
    add_image_size('product-large', 800, 1000, true);
    add_image_size('category-thumb', 400, 400, true);
    add_image_size('gallery-thumb', 300, 300, true);
}
add_action('after_setup_theme', 'kavipushp_setup');

/**
 * Enqueue Scripts and Styles
 */
function kavipushp_scripts() {
    // Google Fonts
    wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap', array(), null);

    // Font Awesome
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css', array(), '6.5.1');

    // Theme Styles
    wp_enqueue_style('kavipushp-style', get_stylesheet_uri(), array(), KAVIPUSHP_VERSION);
    wp_enqueue_style('kavipushp-custom', KAVIPUSHP_URI . '/assets/css/custom.css', array(), KAVIPUSHP_VERSION);

    // Theme Scripts
    wp_enqueue_script('kavipushp-main', KAVIPUSHP_URI . '/assets/js/main.js', array('jquery'), KAVIPUSHP_VERSION, true);

    // Localize script for AJAX
    wp_localize_script('kavipushp-main', 'kavipushp_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('kavipushp_nonce'),
    ));
}
add_action('wp_enqueue_scripts', 'kavipushp_scripts');

/**
 * Register Custom Post Type: Bridal Sets
 */
function kavipushp_register_bridal_sets() {
    $labels = array(
        'name'                  => _x('Bridal Sets', 'Post Type General Name', 'kavipushp-bridals'),
        'singular_name'         => _x('Bridal Set', 'Post Type Singular Name', 'kavipushp-bridals'),
        'menu_name'             => __('Bridal Sets', 'kavipushp-bridals'),
        'name_admin_bar'        => __('Bridal Set', 'kavipushp-bridals'),
        'archives'              => __('Set Archives', 'kavipushp-bridals'),
        'attributes'            => __('Set Attributes', 'kavipushp-bridals'),
        'parent_item_colon'     => __('Parent Set:', 'kavipushp-bridals'),
        'all_items'             => __('All Bridal Sets', 'kavipushp-bridals'),
        'add_new_item'          => __('Add New Bridal Set', 'kavipushp-bridals'),
        'add_new'               => __('Add New', 'kavipushp-bridals'),
        'new_item'              => __('New Bridal Set', 'kavipushp-bridals'),
        'edit_item'             => __('Edit Bridal Set', 'kavipushp-bridals'),
        'update_item'           => __('Update Bridal Set', 'kavipushp-bridals'),
        'view_item'             => __('View Bridal Set', 'kavipushp-bridals'),
        'view_items'            => __('View Bridal Sets', 'kavipushp-bridals'),
        'search_items'          => __('Search Bridal Set', 'kavipushp-bridals'),
        'not_found'             => __('Not found', 'kavipushp-bridals'),
        'not_found_in_trash'    => __('Not found in Trash', 'kavipushp-bridals'),
        'featured_image'        => __('Featured Image', 'kavipushp-bridals'),
        'set_featured_image'    => __('Set featured image', 'kavipushp-bridals'),
        'remove_featured_image' => __('Remove featured image', 'kavipushp-bridals'),
        'use_featured_image'    => __('Use as featured image', 'kavipushp-bridals'),
        'insert_into_item'      => __('Insert into set', 'kavipushp-bridals'),
        'uploaded_to_this_item' => __('Uploaded to this set', 'kavipushp-bridals'),
        'items_list'            => __('Sets list', 'kavipushp-bridals'),
        'items_list_navigation' => __('Sets list navigation', 'kavipushp-bridals'),
        'filter_items_list'     => __('Filter sets list', 'kavipushp-bridals'),
    );

    $args = array(
        'label'                 => __('Bridal Set', 'kavipushp-bridals'),
        'description'           => __('Bridal Jewelry Sets for Rental', 'kavipushp-bridals'),
        'labels'                => $labels,
        'supports'              => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
        'taxonomies'            => array('bridal_category', 'bridal_tag'),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 5,
        'menu_icon'             => 'dashicons-heart',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'show_in_rest'          => true,
    );

    register_post_type('bridal_set', $args);
}
add_action('init', 'kavipushp_register_bridal_sets');

/**
 * Register Custom Taxonomy: Bridal Categories
 */
function kavipushp_register_bridal_taxonomy() {
    // Category Taxonomy
    $category_labels = array(
        'name'                       => _x('Bridal Categories', 'Taxonomy General Name', 'kavipushp-bridals'),
        'singular_name'              => _x('Bridal Category', 'Taxonomy Singular Name', 'kavipushp-bridals'),
        'menu_name'                  => __('Categories', 'kavipushp-bridals'),
        'all_items'                  => __('All Categories', 'kavipushp-bridals'),
        'parent_item'                => __('Parent Category', 'kavipushp-bridals'),
        'parent_item_colon'          => __('Parent Category:', 'kavipushp-bridals'),
        'new_item_name'              => __('New Category Name', 'kavipushp-bridals'),
        'add_new_item'               => __('Add New Category', 'kavipushp-bridals'),
        'edit_item'                  => __('Edit Category', 'kavipushp-bridals'),
        'update_item'                => __('Update Category', 'kavipushp-bridals'),
        'view_item'                  => __('View Category', 'kavipushp-bridals'),
        'separate_items_with_commas' => __('Separate categories with commas', 'kavipushp-bridals'),
        'add_or_remove_items'        => __('Add or remove categories', 'kavipushp-bridals'),
        'choose_from_most_used'      => __('Choose from the most used', 'kavipushp-bridals'),
        'popular_items'              => __('Popular Categories', 'kavipushp-bridals'),
        'search_items'               => __('Search Categories', 'kavipushp-bridals'),
        'not_found'                  => __('Not Found', 'kavipushp-bridals'),
        'no_terms'                   => __('No categories', 'kavipushp-bridals'),
        'items_list'                 => __('Categories list', 'kavipushp-bridals'),
        'items_list_navigation'      => __('Categories list navigation', 'kavipushp-bridals'),
    );

    $category_args = array(
        'labels'                     => $category_labels,
        'hierarchical'               => true,
        'public'                     => true,
        'show_ui'                    => true,
        'show_admin_column'          => true,
        'show_in_nav_menus'          => true,
        'show_tagcloud'              => true,
        'show_in_rest'               => true,
    );

    register_taxonomy('bridal_category', array('bridal_set'), $category_args);

    // Tag Taxonomy
    $tag_labels = array(
        'name'                       => _x('Bridal Tags', 'Taxonomy General Name', 'kavipushp-bridals'),
        'singular_name'              => _x('Bridal Tag', 'Taxonomy Singular Name', 'kavipushp-bridals'),
        'menu_name'                  => __('Tags', 'kavipushp-bridals'),
        'all_items'                  => __('All Tags', 'kavipushp-bridals'),
        'parent_item'                => null,
        'parent_item_colon'          => null,
        'new_item_name'              => __('New Tag Name', 'kavipushp-bridals'),
        'add_new_item'               => __('Add New Tag', 'kavipushp-bridals'),
        'edit_item'                  => __('Edit Tag', 'kavipushp-bridals'),
        'update_item'                => __('Update Tag', 'kavipushp-bridals'),
        'view_item'                  => __('View Tag', 'kavipushp-bridals'),
        'separate_items_with_commas' => __('Separate tags with commas', 'kavipushp-bridals'),
        'add_or_remove_items'        => __('Add or remove tags', 'kavipushp-bridals'),
        'choose_from_most_used'      => __('Choose from the most used', 'kavipushp-bridals'),
        'popular_items'              => __('Popular Tags', 'kavipushp-bridals'),
        'search_items'               => __('Search Tags', 'kavipushp-bridals'),
        'not_found'                  => __('Not Found', 'kavipushp-bridals'),
        'no_terms'                   => __('No tags', 'kavipushp-bridals'),
        'items_list'                 => __('Tags list', 'kavipushp-bridals'),
        'items_list_navigation'      => __('Tags list navigation', 'kavipushp-bridals'),
    );

    $tag_args = array(
        'labels'                     => $tag_labels,
        'hierarchical'               => false,
        'public'                     => true,
        'show_ui'                    => true,
        'show_admin_column'          => true,
        'show_in_nav_menus'          => true,
        'show_tagcloud'              => true,
        'show_in_rest'               => true,
    );

    register_taxonomy('bridal_tag', array('bridal_set'), $tag_args);
}
add_action('init', 'kavipushp_register_bridal_taxonomy');

/**
 * Register Custom Post Type: Bookings/Rentals
 */
function kavipushp_register_bookings() {
    $labels = array(
        'name'                  => _x('Bookings', 'Post Type General Name', 'kavipushp-bridals'),
        'singular_name'         => _x('Booking', 'Post Type Singular Name', 'kavipushp-bridals'),
        'menu_name'             => __('Bookings', 'kavipushp-bridals'),
        'name_admin_bar'        => __('Booking', 'kavipushp-bridals'),
        'archives'              => __('Booking Archives', 'kavipushp-bridals'),
        'all_items'             => __('All Bookings', 'kavipushp-bridals'),
        'add_new_item'          => __('Add New Booking', 'kavipushp-bridals'),
        'add_new'               => __('Add New', 'kavipushp-bridals'),
        'new_item'              => __('New Booking', 'kavipushp-bridals'),
        'edit_item'             => __('Edit Booking', 'kavipushp-bridals'),
        'update_item'           => __('Update Booking', 'kavipushp-bridals'),
        'view_item'             => __('View Booking', 'kavipushp-bridals'),
        'search_items'          => __('Search Booking', 'kavipushp-bridals'),
        'not_found'             => __('Not found', 'kavipushp-bridals'),
        'not_found_in_trash'    => __('Not found in Trash', 'kavipushp-bridals'),
    );

    $args = array(
        'label'                 => __('Booking', 'kavipushp-bridals'),
        'description'           => __('Customer Bookings', 'kavipushp-bridals'),
        'labels'                => $labels,
        'supports'              => array('title', 'custom-fields'),
        'hierarchical'          => false,
        'public'                => false,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 6,
        'menu_icon'             => 'dashicons-calendar-alt',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => false,
        'can_export'            => true,
        'has_archive'           => false,
        'exclude_from_search'   => true,
        'publicly_queryable'    => false,
        'capability_type'       => 'post',
        'show_in_rest'          => true,
    );

    register_post_type('booking', $args);
}
add_action('init', 'kavipushp_register_bookings');

/**
 * Add Meta Boxes for Bridal Sets
 */
function kavipushp_add_bridal_set_meta_boxes() {
    add_meta_box(
        'bridal_set_details',
        __('Set Details', 'kavipushp-bridals'),
        'kavipushp_bridal_set_details_callback',
        'bridal_set',
        'normal',
        'high'
    );

    add_meta_box(
        'bridal_set_gallery',
        __('Image Gallery', 'kavipushp-bridals'),
        'kavipushp_bridal_set_gallery_callback',
        'bridal_set',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'kavipushp_add_bridal_set_meta_boxes');

/**
 * Bridal Set Details Meta Box Callback
 */
function kavipushp_bridal_set_details_callback($post) {
    wp_nonce_field('kavipushp_save_bridal_set', 'kavipushp_bridal_set_nonce');

    $rental_price = get_post_meta($post->ID, '_rental_price', true);
    $deposit_amount = get_post_meta($post->ID, '_deposit_amount', true);
    $set_id = get_post_meta($post->ID, '_set_id', true);
    $availability = get_post_meta($post->ID, '_availability', true);
    $includes = get_post_meta($post->ID, '_set_includes', true);
    $weight = get_post_meta($post->ID, '_weight', true);
    $material = get_post_meta($post->ID, '_material', true);
    ?>
    <table class="form-table">
        <tr>
            <th><label for="set_id"><?php _e('Set ID/Code', 'kavipushp-bridals'); ?></label></th>
            <td>
                <input type="text" id="set_id" name="set_id" value="<?php echo esc_attr($set_id); ?>" class="regular-text" />
                <p class="description"><?php _e('Unique identifier for the set (e.g., KP001)', 'kavipushp-bridals'); ?></p>
            </td>
        </tr>
        <tr>
            <th><label for="rental_price"><?php _e('Rental Price (per day)', 'kavipushp-bridals'); ?></label></th>
            <td>
                <input type="number" id="rental_price" name="rental_price" value="<?php echo esc_attr($rental_price); ?>" class="regular-text" step="0.01" min="0" />
                <p class="description"><?php _e('Daily rental price in your currency', 'kavipushp-bridals'); ?></p>
            </td>
        </tr>
        <tr>
            <th><label for="deposit_amount"><?php _e('Security Deposit', 'kavipushp-bridals'); ?></label></th>
            <td>
                <input type="number" id="deposit_amount" name="deposit_amount" value="<?php echo esc_attr($deposit_amount); ?>" class="regular-text" step="0.01" min="0" />
                <p class="description"><?php _e('Refundable security deposit amount', 'kavipushp-bridals'); ?></p>
            </td>
        </tr>
        <tr>
            <th><label for="availability"><?php _e('Availability Status', 'kavipushp-bridals'); ?></label></th>
            <td>
                <select id="availability" name="availability">
                    <option value="available" <?php selected($availability, 'available'); ?>><?php _e('Available', 'kavipushp-bridals'); ?></option>
                    <option value="rented" <?php selected($availability, 'rented'); ?>><?php _e('Currently Rented', 'kavipushp-bridals'); ?></option>
                    <option value="maintenance" <?php selected($availability, 'maintenance'); ?>><?php _e('Under Maintenance', 'kavipushp-bridals'); ?></option>
                    <option value="unavailable" <?php selected($availability, 'unavailable'); ?>><?php _e('Unavailable', 'kavipushp-bridals'); ?></option>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="set_includes"><?php _e('Set Includes', 'kavipushp-bridals'); ?></label></th>
            <td>
                <textarea id="set_includes" name="set_includes" rows="4" class="large-text"><?php echo esc_textarea($includes); ?></textarea>
                <p class="description"><?php _e('List items included in the set (e.g., Necklace, Earrings, Maang Tikka, Bangles)', 'kavipushp-bridals'); ?></p>
            </td>
        </tr>
        <tr>
            <th><label for="weight"><?php _e('Weight (grams)', 'kavipushp-bridals'); ?></label></th>
            <td>
                <input type="text" id="weight" name="weight" value="<?php echo esc_attr($weight); ?>" class="regular-text" />
            </td>
        </tr>
        <tr>
            <th><label for="material"><?php _e('Material', 'kavipushp-bridals'); ?></label></th>
            <td>
                <input type="text" id="material" name="material" value="<?php echo esc_attr($material); ?>" class="regular-text" />
                <p class="description"><?php _e('e.g., Gold Plated, Kundan, Polki, Meenakari', 'kavipushp-bridals'); ?></p>
            </td>
        </tr>
    </table>
    <?php
}

/**
 * Bridal Set Gallery Meta Box Callback
 */
function kavipushp_bridal_set_gallery_callback($post) {
    $gallery_images = get_post_meta($post->ID, '_gallery_images', true);
    ?>
    <div id="bridal-set-gallery">
        <div id="gallery-images" class="gallery-images-container">
            <?php
            if (!empty($gallery_images)) {
                $image_ids = explode(',', $gallery_images);
                foreach ($image_ids as $image_id) {
                    $image_url = wp_get_attachment_image_url($image_id, 'thumbnail');
                    if ($image_url) {
                        echo '<div class="gallery-image" data-id="' . esc_attr($image_id) . '">';
                        echo '<img src="' . esc_url($image_url) . '" alt="" />';
                        echo '<button type="button" class="remove-image">&times;</button>';
                        echo '</div>';
                    }
                }
            }
            ?>
        </div>
        <input type="hidden" id="gallery_images" name="gallery_images" value="<?php echo esc_attr($gallery_images); ?>" />
        <button type="button" class="button" id="add-gallery-images"><?php _e('Add Images', 'kavipushp-bridals'); ?></button>
    </div>
    <style>
        .gallery-images-container { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 15px; }
        .gallery-image { position: relative; width: 100px; height: 100px; }
        .gallery-image img { width: 100%; height: 100%; object-fit: cover; border-radius: 5px; }
        .gallery-image .remove-image { position: absolute; top: -5px; right: -5px; width: 20px; height: 20px; border-radius: 50%; background: #dc3545; color: #fff; border: none; cursor: pointer; font-size: 14px; line-height: 1; }
    </style>
    <script>
    jQuery(document).ready(function($) {
        var frame;
        $('#add-gallery-images').on('click', function(e) {
            e.preventDefault();
            if (frame) { frame.open(); return; }
            frame = wp.media({
                title: '<?php _e("Select Gallery Images", "kavipushp-bridals"); ?>',
                button: { text: '<?php _e("Add to Gallery", "kavipushp-bridals"); ?>' },
                multiple: true
            });
            frame.on('select', function() {
                var attachments = frame.state().get('selection').map(function(attachment) {
                    attachment = attachment.toJSON();
                    return attachment;
                });
                var ids = $('#gallery_images').val() ? $('#gallery_images').val().split(',') : [];
                attachments.forEach(function(attachment) {
                    if (ids.indexOf(attachment.id.toString()) === -1) {
                        ids.push(attachment.id);
                        var thumb = attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;
                        $('#gallery-images').append('<div class="gallery-image" data-id="' + attachment.id + '"><img src="' + thumb + '" alt="" /><button type="button" class="remove-image">&times;</button></div>');
                    }
                });
                $('#gallery_images').val(ids.join(','));
            });
            frame.open();
        });
        $(document).on('click', '.remove-image', function() {
            var $parent = $(this).parent();
            var id = $parent.data('id').toString();
            var ids = $('#gallery_images').val().split(',').filter(function(i) { return i !== id; });
            $('#gallery_images').val(ids.join(','));
            $parent.remove();
        });
    });
    </script>
    <?php
}

/**
 * Save Bridal Set Meta Box Data
 */
function kavipushp_save_bridal_set_meta($post_id) {
    if (!isset($_POST['kavipushp_bridal_set_nonce']) || !wp_verify_nonce($_POST['kavipushp_bridal_set_nonce'], 'kavipushp_save_bridal_set')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    $fields = array(
        'set_id'         => '_set_id',
        'rental_price'   => '_rental_price',
        'deposit_amount' => '_deposit_amount',
        'availability'   => '_availability',
        'set_includes'   => '_set_includes',
        'weight'         => '_weight',
        'material'       => '_material',
        'gallery_images' => '_gallery_images',
    );

    foreach ($fields as $field => $meta_key) {
        if (isset($_POST[$field])) {
            update_post_meta($post_id, $meta_key, sanitize_text_field($_POST[$field]));
        }
    }
}
add_action('save_post_bridal_set', 'kavipushp_save_bridal_set_meta');

/**
 * Add Meta Boxes for Bookings
 */
function kavipushp_add_booking_meta_boxes() {
    add_meta_box(
        'booking_details',
        __('Booking Details', 'kavipushp-bridals'),
        'kavipushp_booking_details_callback',
        'booking',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'kavipushp_add_booking_meta_boxes');

/**
 * Booking Details Meta Box Callback
 */
function kavipushp_booking_details_callback($post) {
    wp_nonce_field('kavipushp_save_booking', 'kavipushp_booking_nonce');

    $customer_id = get_post_meta($post->ID, '_customer_id', true);
    $customer_name = get_post_meta($post->ID, '_customer_name', true);
    $customer_email = get_post_meta($post->ID, '_customer_email', true);
    $customer_phone = get_post_meta($post->ID, '_customer_phone', true);
    $customer_address = get_post_meta($post->ID, '_customer_address', true);
    $bridal_set_id = get_post_meta($post->ID, '_bridal_set_id', true);
    $function_date = get_post_meta($post->ID, '_function_date', true);
    $pickup_date = get_post_meta($post->ID, '_pickup_date', true);
    $return_date = get_post_meta($post->ID, '_return_date', true);
    $booking_status = get_post_meta($post->ID, '_booking_status', true);
    $total_amount = get_post_meta($post->ID, '_total_amount', true);
    $deposit_paid = get_post_meta($post->ID, '_deposit_paid', true);
    $notes = get_post_meta($post->ID, '_booking_notes', true);

    // Get all customers from database
    global $wpdb;
    $customers_table = $wpdb->prefix . 'kavipushp_customers';
    $customers = array();
    if ($wpdb->get_var("SHOW TABLES LIKE '$customers_table'") == $customers_table) {
        $customers = $wpdb->get_results("SELECT * FROM $customers_table ORDER BY full_name ASC");
    }
    ?>
    <style>
        .kp-booking-form .form-table th { width: 150px; }
        .kp-customer-select-wrap { display: flex; gap: 10px; align-items: center; }
        .kp-customer-select-wrap select { min-width: 300px; }
        .kp-auto-filled { background-color: #f0f7ff !important; }
        .kp-section-divider { background: #f0f0f0; padding: 10px 15px; margin: 10px 0; font-weight: 600; }
    </style>

    <div class="kp-booking-form">
    <table class="form-table">
        <tr>
            <th colspan="2"><h3 style="margin:0; padding: 10px 0; border-bottom: 2px solid #c9a86c;"><?php _e('Select Customer', 'kavipushp-bridals'); ?></h3></th>
        </tr>
        <tr>
            <th><label for="select_customer"><?php _e('Choose Customer', 'kavipushp-bridals'); ?></label></th>
            <td>
                <div class="kp-customer-select-wrap">
                    <select id="select_customer" name="select_customer" onchange="fillCustomerData(this.value)">
                        <option value=""><?php _e('-- Select a Customer --', 'kavipushp-bridals'); ?></option>
                        <?php foreach ($customers as $customer): ?>
                            <option value="<?php echo esc_attr($customer->id); ?>"
                                data-name="<?php echo esc_attr($customer->full_name); ?>"
                                data-email="<?php echo esc_attr($customer->email); ?>"
                                data-phone="<?php echo esc_attr($customer->phone); ?>"
                                data-address="<?php echo esc_attr($customer->address); ?>"
                                data-function-date="<?php echo esc_attr($customer->function_date); ?>"
                                data-return-date="<?php echo esc_attr($customer->return_date); ?>"
                                <?php selected($customer_id, $customer->id); ?>>
                                <?php echo esc_html($customer->full_name . ' - ' . $customer->phone); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <a href="<?php echo admin_url('admin.php?page=kavipushp-customers&action=add'); ?>" class="button" target="_blank">
                        <?php _e('+ Add New Customer', 'kavipushp-bridals'); ?>
                    </a>
                </div>
                <p class="description"><?php _e('Select a customer to auto-fill their information, or enter manually below.', 'kavipushp-bridals'); ?></p>
            </td>
        </tr>
        <tr>
            <th colspan="2"><h3 style="margin:0; padding: 10px 0; border-bottom: 2px solid #c9a86c;"><?php _e('Customer Information', 'kavipushp-bridals'); ?></h3></th>
        </tr>
        <tr>
            <th><label for="customer_name"><?php _e('Customer Name', 'kavipushp-bridals'); ?> *</label></th>
            <td>
                <input type="hidden" id="customer_id" name="customer_id" value="<?php echo esc_attr($customer_id); ?>" />
                <input type="text" id="customer_name" name="customer_name" value="<?php echo esc_attr($customer_name); ?>" class="regular-text" required />
            </td>
        </tr>
        <tr>
            <th><label for="customer_email"><?php _e('Email', 'kavipushp-bridals'); ?></label></th>
            <td><input type="email" id="customer_email" name="customer_email" value="<?php echo esc_attr($customer_email); ?>" class="regular-text" /></td>
        </tr>
        <tr>
            <th><label for="customer_phone"><?php _e('Phone', 'kavipushp-bridals'); ?> *</label></th>
            <td><input type="tel" id="customer_phone" name="customer_phone" value="<?php echo esc_attr($customer_phone); ?>" class="regular-text" required /></td>
        </tr>
        <tr>
            <th><label for="customer_address"><?php _e('Address', 'kavipushp-bridals'); ?></label></th>
            <td><textarea id="customer_address" name="customer_address" rows="2" class="large-text"><?php echo esc_textarea($customer_address); ?></textarea></td>
        </tr>
        <tr>
            <th colspan="2"><h3 style="margin:0; padding: 10px 0; border-bottom: 2px solid #c9a86c;"><?php _e('Rental Details', 'kavipushp-bridals'); ?></h3></th>
        </tr>
        <tr>
            <th><label for="bridal_set_id"><?php _e('Bridal Set', 'kavipushp-bridals'); ?> *</label></th>
            <td>
                <select id="bridal_set_id" name="bridal_set_id" required>
                    <option value=""><?php _e('-- Select a Set --', 'kavipushp-bridals'); ?></option>
                    <?php
                    $sets = get_posts(array('post_type' => 'bridal_set', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC'));
                    foreach ($sets as $set) {
                        $set_code = get_post_meta($set->ID, '_set_id', true);
                        $rental_price = get_post_meta($set->ID, '_rental_price', true);
                        $display = $set->post_title . ($set_code ? ' (' . $set_code . ')' : '') . ' - ₹' . number_format($rental_price) . '/day';
                        echo '<option value="' . esc_attr($set->ID) . '" ' . selected($bridal_set_id, $set->ID, false) . '>' . esc_html($display) . '</option>';
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="function_date"><?php _e('Function Date', 'kavipushp-bridals'); ?> *</label></th>
            <td>
                <input type="date" id="function_date" name="function_date" value="<?php echo esc_attr($function_date); ?>" onchange="calculateDates()" required />
                <p class="description"><?php _e('Return date will be auto-calculated as Function Date + 2 days', 'kavipushp-bridals'); ?></p>
            </td>
        </tr>
        <tr>
            <th><label for="return_date"><?php _e('Return Date', 'kavipushp-bridals'); ?></label></th>
            <td>
                <input type="date" id="return_date" name="return_date" value="<?php echo esc_attr($return_date); ?>" readonly style="background: #f5f5f5;" />
                <span style="color: #666; margin-left: 10px;">(Function Date + 2 days)</span>
            </td>
        </tr>
        <tr>
            <th><label for="pickup_date"><?php _e('Pickup Date', 'kavipushp-bridals'); ?></label></th>
            <td><input type="date" id="pickup_date" name="pickup_date" value="<?php echo esc_attr($pickup_date); ?>" /></td>
        </tr>
        <tr>
            <th><label for="booking_status"><?php _e('Booking Status', 'kavipushp-bridals'); ?></label></th>
            <td>
                <select id="booking_status" name="booking_status">
                    <option value="pending" <?php selected($booking_status, 'pending'); ?>><?php _e('Pending', 'kavipushp-bridals'); ?></option>
                    <option value="confirmed" <?php selected($booking_status, 'confirmed'); ?>><?php _e('Confirmed', 'kavipushp-bridals'); ?></option>
                    <option value="picked_up" <?php selected($booking_status, 'picked_up'); ?>><?php _e('Picked Up', 'kavipushp-bridals'); ?></option>
                    <option value="returned" <?php selected($booking_status, 'returned'); ?>><?php _e('Returned', 'kavipushp-bridals'); ?></option>
                    <option value="completed" <?php selected($booking_status, 'completed'); ?>><?php _e('Completed', 'kavipushp-bridals'); ?></option>
                    <option value="cancelled" <?php selected($booking_status, 'cancelled'); ?>><?php _e('Cancelled', 'kavipushp-bridals'); ?></option>
                </select>
            </td>
        </tr>
        <tr>
            <th colspan="2"><h3 style="margin:0; padding: 10px 0; border-bottom: 2px solid #c9a86c;"><?php _e('Payment Information', 'kavipushp-bridals'); ?></h3></th>
        </tr>
        <tr>
            <th><label for="total_amount"><?php _e('Total Amount (₹)', 'kavipushp-bridals'); ?></label></th>
            <td><input type="number" id="total_amount" name="total_amount" value="<?php echo esc_attr($total_amount); ?>" step="0.01" min="0" /></td>
        </tr>
        <tr>
            <th><label for="deposit_paid"><?php _e('Deposit Paid', 'kavipushp-bridals'); ?></label></th>
            <td>
                <select id="deposit_paid" name="deposit_paid">
                    <option value="no" <?php selected($deposit_paid, 'no'); ?>><?php _e('No', 'kavipushp-bridals'); ?></option>
                    <option value="yes" <?php selected($deposit_paid, 'yes'); ?>><?php _e('Yes', 'kavipushp-bridals'); ?></option>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="booking_notes"><?php _e('Notes', 'kavipushp-bridals'); ?></label></th>
            <td><textarea id="booking_notes" name="booking_notes" rows="3" class="large-text"><?php echo esc_textarea($notes); ?></textarea></td>
        </tr>
    </table>
    </div>

    <script>
    function fillCustomerData(customerId) {
        if (!customerId) {
            // Clear fields if no customer selected
            document.getElementById('customer_id').value = '';
            document.getElementById('customer_name').value = '';
            document.getElementById('customer_email').value = '';
            document.getElementById('customer_phone').value = '';
            document.getElementById('customer_address').value = '';
            document.getElementById('function_date').value = '';
            document.getElementById('return_date').value = '';

            // Remove highlight
            ['customer_name', 'customer_email', 'customer_phone', 'customer_address', 'function_date', 'return_date'].forEach(function(id) {
                document.getElementById(id).classList.remove('kp-auto-filled');
            });
            return;
        }

        var select = document.getElementById('select_customer');
        var option = select.options[select.selectedIndex];

        // Fill customer data
        document.getElementById('customer_id').value = customerId;
        document.getElementById('customer_name').value = option.dataset.name || '';
        document.getElementById('customer_email').value = option.dataset.email || '';
        document.getElementById('customer_phone').value = option.dataset.phone || '';
        document.getElementById('customer_address').value = option.dataset.address || '';

        // Fill function date and calculate return date
        if (option.dataset.functionDate) {
            document.getElementById('function_date').value = option.dataset.functionDate;
            calculateDates();
        }

        // Highlight auto-filled fields
        ['customer_name', 'customer_email', 'customer_phone', 'customer_address', 'function_date', 'return_date'].forEach(function(id) {
            document.getElementById(id).classList.add('kp-auto-filled');
        });
    }

    function calculateDates() {
        var functionDate = document.getElementById('function_date').value;
        if (functionDate) {
            var date = new Date(functionDate);
            date.setDate(date.getDate() + 2);
            var returnDate = date.toISOString().split('T')[0];
            document.getElementById('return_date').value = returnDate;
        }
    }

    // Calculate on page load if function date exists
    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('function_date').value) {
            calculateDates();
        }
    });
    </script>
    <?php
}

/**
 * Save Booking Meta Box Data
 */
function kavipushp_save_booking_meta($post_id) {
    if (!isset($_POST['kavipushp_booking_nonce']) || !wp_verify_nonce($_POST['kavipushp_booking_nonce'], 'kavipushp_save_booking')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    $fields = array(
        'customer_id'      => '_customer_id',
        'customer_name'    => '_customer_name',
        'customer_email'   => '_customer_email',
        'customer_phone'   => '_customer_phone',
        'customer_address' => '_customer_address',
        'bridal_set_id'    => '_bridal_set_id',
        'function_date'    => '_function_date',
        'pickup_date'      => '_pickup_date',
        'return_date'      => '_return_date',
        'booking_status'   => '_booking_status',
        'total_amount'     => '_total_amount',
        'deposit_paid'     => '_deposit_paid',
        'booking_notes'    => '_booking_notes',
    );

    foreach ($fields as $field => $meta_key) {
        if (isset($_POST[$field])) {
            if ($field === 'customer_email') {
                update_post_meta($post_id, $meta_key, sanitize_email($_POST[$field]));
            } elseif ($field === 'customer_address' || $field === 'booking_notes') {
                update_post_meta($post_id, $meta_key, sanitize_textarea_field($_POST[$field]));
            } else {
                update_post_meta($post_id, $meta_key, sanitize_text_field($_POST[$field]));
            }
        }
    }
}
add_action('save_post_booking', 'kavipushp_save_booking_meta');

/**
 * Register Widgets
 */
function kavipushp_widgets_init() {
    register_sidebar(array(
        'name'          => __('Shop Sidebar', 'kavipushp-bridals'),
        'id'            => 'shop-sidebar',
        'description'   => __('Widgets for the shop page sidebar', 'kavipushp-bridals'),
        'before_widget' => '<div id="%1$s" class="widget filter-section %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="widget-title">',
        'after_title'   => '</h4>',
    ));

    register_sidebar(array(
        'name'          => __('Footer Widget 1', 'kavipushp-bridals'),
        'id'            => 'footer-1',
        'description'   => __('First footer widget area', 'kavipushp-bridals'),
        'before_widget' => '<div id="%1$s" class="footer-widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4>',
        'after_title'   => '</h4>',
    ));

    register_sidebar(array(
        'name'          => __('Footer Widget 2', 'kavipushp-bridals'),
        'id'            => 'footer-2',
        'description'   => __('Second footer widget area', 'kavipushp-bridals'),
        'before_widget' => '<div id="%1$s" class="footer-widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4>',
        'after_title'   => '</h4>',
    ));

    register_sidebar(array(
        'name'          => __('Footer Widget 3', 'kavipushp-bridals'),
        'id'            => 'footer-3',
        'description'   => __('Third footer widget area', 'kavipushp-bridals'),
        'before_widget' => '<div id="%1$s" class="footer-widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4>',
        'after_title'   => '</h4>',
    ));
}
add_action('widgets_init', 'kavipushp_widgets_init');

/**
 * AJAX: Check Availability
 */
function kavipushp_check_availability() {
    check_ajax_referer('kavipushp_nonce', 'nonce');

    $set_id = isset($_POST['set_id']) ? intval($_POST['set_id']) : 0;
    $pickup_date = isset($_POST['pickup_date']) ? sanitize_text_field($_POST['pickup_date']) : '';
    $return_date = isset($_POST['return_date']) ? sanitize_text_field($_POST['return_date']) : '';

    if (!$set_id || !$pickup_date || !$return_date) {
        wp_send_json_error(array('message' => __('Invalid data provided', 'kavipushp-bridals')));
    }

    // Check if set is available
    $availability = get_post_meta($set_id, '_availability', true);
    if ($availability !== 'available') {
        wp_send_json_error(array('message' => __('This set is currently not available for rental', 'kavipushp-bridals')));
    }

    // Check for conflicting bookings
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
    $is_available = true;

    foreach ($bookings as $booking) {
        $booked_pickup = get_post_meta($booking->ID, '_pickup_date', true);
        $booked_return = get_post_meta($booking->ID, '_return_date', true);

        // Check for date overlap
        if (($pickup_date <= $booked_return && $return_date >= $booked_pickup)) {
            $is_available = false;
            break;
        }
    }

    if ($is_available) {
        // Calculate price
        $rental_price = get_post_meta($set_id, '_rental_price', true);
        $deposit = get_post_meta($set_id, '_deposit_amount', true);
        $days = (strtotime($return_date) - strtotime($pickup_date)) / (60 * 60 * 24) + 1;
        $total = $days * floatval($rental_price);

        wp_send_json_success(array(
            'available'     => true,
            'days'          => $days,
            'rental_price'  => $rental_price,
            'total'         => $total,
            'deposit'       => $deposit,
            'grand_total'   => $total + floatval($deposit),
        ));
    } else {
        wp_send_json_error(array('message' => __('This set is not available for the selected dates', 'kavipushp-bridals')));
    }
}
add_action('wp_ajax_kavipushp_check_availability', 'kavipushp_check_availability');
add_action('wp_ajax_nopriv_kavipushp_check_availability', 'kavipushp_check_availability');

/**
 * AJAX: Create Booking
 */
function kavipushp_create_booking() {
    check_ajax_referer('kavipushp_nonce', 'nonce');

    $required_fields = array('set_id', 'pickup_date', 'return_date', 'customer_name', 'customer_email', 'customer_phone');

    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            wp_send_json_error(array('message' => __('Please fill in all required fields', 'kavipushp-bridals')));
        }
    }

    $set_id = intval($_POST['set_id']);
    $set = get_post($set_id);

    if (!$set) {
        wp_send_json_error(array('message' => __('Invalid bridal set', 'kavipushp-bridals')));
    }

    // Create booking
    $booking_id = wp_insert_post(array(
        'post_type'   => 'booking',
        'post_title'  => sanitize_text_field($_POST['customer_name']) . ' - ' . $set->post_title,
        'post_status' => 'publish',
    ));

    if (is_wp_error($booking_id)) {
        wp_send_json_error(array('message' => __('Failed to create booking', 'kavipushp-bridals')));
    }

    // Calculate totals
    $pickup_date = sanitize_text_field($_POST['pickup_date']);
    $return_date = sanitize_text_field($_POST['return_date']);
    $rental_price = get_post_meta($set_id, '_rental_price', true);
    $deposit = get_post_meta($set_id, '_deposit_amount', true);
    $days = (strtotime($return_date) - strtotime($pickup_date)) / (60 * 60 * 24) + 1;
    $total = $days * floatval($rental_price) + floatval($deposit);

    // Save booking meta
    update_post_meta($booking_id, '_customer_name', sanitize_text_field($_POST['customer_name']));
    update_post_meta($booking_id, '_customer_email', sanitize_email($_POST['customer_email']));
    update_post_meta($booking_id, '_customer_phone', sanitize_text_field($_POST['customer_phone']));
    update_post_meta($booking_id, '_customer_address', sanitize_textarea_field($_POST['customer_address'] ?? ''));
    update_post_meta($booking_id, '_bridal_set_id', $set_id);
    update_post_meta($booking_id, '_pickup_date', $pickup_date);
    update_post_meta($booking_id, '_return_date', $return_date);
    update_post_meta($booking_id, '_booking_status', 'pending');
    update_post_meta($booking_id, '_total_amount', $total);
    update_post_meta($booking_id, '_deposit_paid', 'no');

    // Send confirmation email
    kavipushp_send_booking_email($booking_id);

    wp_send_json_success(array(
        'message'    => __('Booking created successfully! We will contact you soon to confirm.', 'kavipushp-bridals'),
        'booking_id' => $booking_id,
    ));
}
add_action('wp_ajax_kavipushp_create_booking', 'kavipushp_create_booking');
add_action('wp_ajax_nopriv_kavipushp_create_booking', 'kavipushp_create_booking');

/**
 * Send Booking Confirmation Email
 */
function kavipushp_send_booking_email($booking_id) {
    $customer_email = get_post_meta($booking_id, '_customer_email', true);
    $customer_name = get_post_meta($booking_id, '_customer_name', true);
    $set_id = get_post_meta($booking_id, '_bridal_set_id', true);
    $set = get_post($set_id);
    $pickup_date = get_post_meta($booking_id, '_pickup_date', true);
    $return_date = get_post_meta($booking_id, '_return_date', true);
    $total = get_post_meta($booking_id, '_total_amount', true);

    $subject = __('Booking Confirmation - Kavipushp Bridals', 'kavipushp-bridals');

    $message = sprintf(
        __("Dear %s,\n\nThank you for your booking request!\n\nBooking Details:\n- Set: %s\n- Pickup Date: %s\n- Return Date: %s\n- Total Amount: %s\n\nWe will contact you shortly to confirm your booking.\n\nBest regards,\nKavipushp Bridals", 'kavipushp-bridals'),
        $customer_name,
        $set ? $set->post_title : '',
        date_i18n(get_option('date_format'), strtotime($pickup_date)),
        date_i18n(get_option('date_format'), strtotime($return_date)),
        number_format($total, 2)
    );

    $headers = array('Content-Type: text/plain; charset=UTF-8');

    wp_mail($customer_email, $subject, $message, $headers);

    // Also notify admin
    $admin_email = get_option('admin_email');
    $admin_subject = __('New Booking Request - Kavipushp Bridals', 'kavipushp-bridals');
    wp_mail($admin_email, $admin_subject, $message, $headers);
}

/**
 * Add Booking Status Column to Admin
 */
function kavipushp_booking_columns($columns) {
    $new_columns = array();
    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;
        if ($key === 'title') {
            $new_columns['bridal_set'] = __('Bridal Set', 'kavipushp-bridals');
            $new_columns['dates'] = __('Rental Dates', 'kavipushp-bridals');
            $new_columns['status'] = __('Status', 'kavipushp-bridals');
            $new_columns['total'] = __('Total', 'kavipushp-bridals');
        }
    }
    return $new_columns;
}
add_filter('manage_booking_posts_columns', 'kavipushp_booking_columns');

/**
 * Populate Booking Columns
 */
function kavipushp_booking_column_content($column, $post_id) {
    switch ($column) {
        case 'bridal_set':
            $set_id = get_post_meta($post_id, '_bridal_set_id', true);
            if ($set_id) {
                $set = get_post($set_id);
                echo $set ? esc_html($set->post_title) : '-';
            }
            break;
        case 'dates':
            $pickup = get_post_meta($post_id, '_pickup_date', true);
            $return = get_post_meta($post_id, '_return_date', true);
            if ($pickup && $return) {
                echo date_i18n('M j', strtotime($pickup)) . ' - ' . date_i18n('M j, Y', strtotime($return));
            }
            break;
        case 'status':
            $status = get_post_meta($post_id, '_booking_status', true);
            $statuses = array(
                'pending'   => array('label' => __('Pending', 'kavipushp-bridals'), 'color' => '#ffc107'),
                'confirmed' => array('label' => __('Confirmed', 'kavipushp-bridals'), 'color' => '#28a745'),
                'picked_up' => array('label' => __('Picked Up', 'kavipushp-bridals'), 'color' => '#17a2b8'),
                'returned'  => array('label' => __('Returned', 'kavipushp-bridals'), 'color' => '#6c757d'),
                'completed' => array('label' => __('Completed', 'kavipushp-bridals'), 'color' => '#c9a86c'),
                'cancelled' => array('label' => __('Cancelled', 'kavipushp-bridals'), 'color' => '#dc3545'),
            );
            if (isset($statuses[$status])) {
                echo '<span style="background:' . $statuses[$status]['color'] . ';color:#fff;padding:3px 10px;border-radius:3px;font-size:12px;">' . $statuses[$status]['label'] . '</span>';
            }
            break;
        case 'total':
            $total = get_post_meta($post_id, '_total_amount', true);
            echo $total ? number_format($total, 2) : '-';
            break;
    }
}
add_action('manage_booking_posts_custom_column', 'kavipushp_booking_column_content', 10, 2);

/**
 * Add Dashboard Widgets
 */
function kavipushp_dashboard_widgets() {
    wp_add_dashboard_widget(
        'kavipushp_bookings_widget',
        __('Recent Bookings', 'kavipushp-bridals'),
        'kavipushp_bookings_widget_callback'
    );

    wp_add_dashboard_widget(
        'kavipushp_stats_widget',
        __('Rental Statistics', 'kavipushp-bridals'),
        'kavipushp_stats_widget_callback'
    );
}
add_action('wp_dashboard_setup', 'kavipushp_dashboard_widgets');

/**
 * Recent Bookings Widget
 */
function kavipushp_bookings_widget_callback() {
    $bookings = get_posts(array(
        'post_type'      => 'booking',
        'posts_per_page' => 5,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ));

    if (empty($bookings)) {
        echo '<p>' . __('No bookings yet.', 'kavipushp-bridals') . '</p>';
        return;
    }

    echo '<table style="width:100%;border-collapse:collapse;">';
    echo '<thead><tr style="text-align:left;border-bottom:1px solid #ddd;">';
    echo '<th style="padding:8px;">' . __('Customer', 'kavipushp-bridals') . '</th>';
    echo '<th style="padding:8px;">' . __('Set', 'kavipushp-bridals') . '</th>';
    echo '<th style="padding:8px;">' . __('Status', 'kavipushp-bridals') . '</th>';
    echo '</tr></thead><tbody>';

    foreach ($bookings as $booking) {
        $customer = get_post_meta($booking->ID, '_customer_name', true);
        $set_id = get_post_meta($booking->ID, '_bridal_set_id', true);
        $set = $set_id ? get_post($set_id) : null;
        $status = get_post_meta($booking->ID, '_booking_status', true);

        echo '<tr style="border-bottom:1px solid #eee;">';
        echo '<td style="padding:8px;">' . esc_html($customer) . '</td>';
        echo '<td style="padding:8px;">' . ($set ? esc_html($set->post_title) : '-') . '</td>';
        echo '<td style="padding:8px;">' . esc_html(ucfirst($status)) . '</td>';
        echo '</tr>';
    }

    echo '</tbody></table>';
    echo '<p><a href="' . admin_url('edit.php?post_type=booking') . '">' . __('View all bookings', 'kavipushp-bridals') . '</a></p>';
}

/**
 * Statistics Widget
 */
function kavipushp_stats_widget_callback() {
    // Total sets
    $total_sets = wp_count_posts('bridal_set')->publish;

    // Total bookings
    $total_bookings = wp_count_posts('booking')->publish;

    // Pending bookings
    $pending = get_posts(array(
        'post_type'      => 'booking',
        'posts_per_page' => -1,
        'meta_query'     => array(
            array(
                'key'   => '_booking_status',
                'value' => 'pending',
            ),
        ),
    ));

    // This month's revenue
    $month_start = date('Y-m-01');
    $month_end = date('Y-m-t');
    $month_bookings = get_posts(array(
        'post_type'      => 'booking',
        'posts_per_page' => -1,
        'date_query'     => array(
            'after'     => $month_start,
            'before'    => $month_end,
            'inclusive' => true,
        ),
        'meta_query'     => array(
            array(
                'key'     => '_booking_status',
                'value'   => array('confirmed', 'completed', 'picked_up', 'returned'),
                'compare' => 'IN',
            ),
        ),
    ));

    $month_revenue = 0;
    foreach ($month_bookings as $booking) {
        $month_revenue += floatval(get_post_meta($booking->ID, '_total_amount', true));
    }

    echo '<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">';

    echo '<div style="background:#f0f0f0;padding:15px;border-radius:5px;text-align:center;">';
    echo '<div style="font-size:24px;font-weight:bold;color:#c9a86c;">' . $total_sets . '</div>';
    echo '<div>' . __('Bridal Sets', 'kavipushp-bridals') . '</div>';
    echo '</div>';

    echo '<div style="background:#f0f0f0;padding:15px;border-radius:5px;text-align:center;">';
    echo '<div style="font-size:24px;font-weight:bold;color:#c9a86c;">' . $total_bookings . '</div>';
    echo '<div>' . __('Total Bookings', 'kavipushp-bridals') . '</div>';
    echo '</div>';

    echo '<div style="background:#f0f0f0;padding:15px;border-radius:5px;text-align:center;">';
    echo '<div style="font-size:24px;font-weight:bold;color:#ffc107;">' . count($pending) . '</div>';
    echo '<div>' . __('Pending', 'kavipushp-bridals') . '</div>';
    echo '</div>';

    echo '<div style="background:#f0f0f0;padding:15px;border-radius:5px;text-align:center;">';
    echo '<div style="font-size:24px;font-weight:bold;color:#28a745;">' . number_format($month_revenue, 2) . '</div>';
    echo '<div>' . __('This Month', 'kavipushp-bridals') . '</div>';
    echo '</div>';

    echo '</div>';
}

/**
 * Shortcodes
 */

// Featured Sets Shortcode
function kavipushp_featured_sets_shortcode($atts) {
    $atts = shortcode_atts(array(
        'count' => 8,
        'category' => '',
    ), $atts);

    $args = array(
        'post_type'      => 'bridal_set',
        'posts_per_page' => intval($atts['count']),
        'orderby'        => 'date',
        'order'          => 'DESC',
    );

    if (!empty($atts['category'])) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'bridal_category',
                'field'    => 'slug',
                'terms'    => $atts['category'],
            ),
        );
    }

    $sets = get_posts($args);

    if (empty($sets)) {
        return '<p>' . __('No bridal sets found.', 'kavipushp-bridals') . '</p>';
    }

    ob_start();
    echo '<div class="products-grid">';
    foreach ($sets as $set) {
        $rental_price = get_post_meta($set->ID, '_rental_price', true);
        $availability = get_post_meta($set->ID, '_availability', true);
        $thumb = get_the_post_thumbnail_url($set->ID, 'product-thumb');
        $categories = get_the_terms($set->ID, 'bridal_category');
        ?>
        <div class="product-card">
            <div class="product-image">
                <?php if ($thumb): ?>
                    <img src="<?php echo esc_url($thumb); ?>" alt="<?php echo esc_attr($set->post_title); ?>">
                <?php else: ?>
                    <img src="<?php echo KAVIPUSHP_URI; ?>/assets/images/placeholder.jpg" alt="<?php echo esc_attr($set->post_title); ?>">
                <?php endif; ?>
                <?php if ($availability === 'available'): ?>
                    <span class="product-badge"><?php _e('Available', 'kavipushp-bridals'); ?></span>
                <?php endif; ?>
                <div class="product-actions">
                    <button class="quick-view" data-id="<?php echo $set->ID; ?>"><i class="fas fa-eye"></i></button>
                    <button class="add-to-wishlist"><i class="fas fa-heart"></i></button>
                </div>
            </div>
            <div class="product-info">
                <?php if ($categories): ?>
                    <span class="product-category"><?php echo esc_html($categories[0]->name); ?></span>
                <?php endif; ?>
                <h3><a href="<?php echo get_permalink($set->ID); ?>"><?php echo esc_html($set->post_title); ?></a></h3>
                <div class="product-meta">
                    <span class="product-price"><?php echo number_format($rental_price, 0); ?> <span>/day</span></span>
                </div>
            </div>
        </div>
        <?php
    }
    echo '</div>';
    return ob_get_clean();
}
add_shortcode('featured_sets', 'kavipushp_featured_sets_shortcode');

// Categories Shortcode
function kavipushp_categories_shortcode($atts) {
    $atts = shortcode_atts(array(
        'count' => 6,
    ), $atts);

    $categories = get_terms(array(
        'taxonomy'   => 'bridal_category',
        'hide_empty' => false,
        'number'     => intval($atts['count']),
    ));

    if (empty($categories) || is_wp_error($categories)) {
        return '<p>' . __('No categories found.', 'kavipushp-bridals') . '</p>';
    }

    ob_start();
    echo '<div class="categories-grid">';
    foreach ($categories as $category) {
        $image_id = get_term_meta($category->term_id, 'category_image', true);
        $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'category-thumb') : KAVIPUSHP_URI . '/assets/images/category-placeholder.jpg';
        ?>
        <a href="<?php echo get_term_link($category); ?>" class="category-card">
            <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($category->name); ?>">
            <div class="category-overlay">
                <h3><?php echo esc_html($category->name); ?></h3>
                <span><?php echo $category->count; ?> <?php _e('Sets', 'kavipushp-bridals'); ?></span>
            </div>
        </a>
        <?php
    }
    echo '</div>';
    return ob_get_clean();
}
add_shortcode('bridal_categories', 'kavipushp_categories_shortcode');

// Booking Form Shortcode
function kavipushp_booking_form_shortcode($atts) {
    $atts = shortcode_atts(array(
        'set_id' => 0,
    ), $atts);

    ob_start();
    include KAVIPUSHP_DIR . '/template-parts/booking-form.php';
    return ob_get_clean();
}
add_shortcode('booking_form', 'kavipushp_booking_form_shortcode');

/**
 * Add category image field
 */
function kavipushp_category_add_image_field() {
    ?>
    <div class="form-field">
        <label for="category_image"><?php _e('Category Image', 'kavipushp-bridals'); ?></label>
        <input type="hidden" id="category_image" name="category_image" value="" />
        <div id="category-image-preview"></div>
        <button type="button" class="button" id="upload-category-image"><?php _e('Upload Image', 'kavipushp-bridals'); ?></button>
        <button type="button" class="button" id="remove-category-image" style="display:none;"><?php _e('Remove Image', 'kavipushp-bridals'); ?></button>
    </div>
    <?php
}
add_action('bridal_category_add_form_fields', 'kavipushp_category_add_image_field');

function kavipushp_category_edit_image_field($term) {
    $image_id = get_term_meta($term->term_id, 'category_image', true);
    $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'thumbnail') : '';
    ?>
    <tr class="form-field">
        <th><label for="category_image"><?php _e('Category Image', 'kavipushp-bridals'); ?></label></th>
        <td>
            <input type="hidden" id="category_image" name="category_image" value="<?php echo esc_attr($image_id); ?>" />
            <div id="category-image-preview">
                <?php if ($image_url): ?>
                    <img src="<?php echo esc_url($image_url); ?>" style="max-width:200px;">
                <?php endif; ?>
            </div>
            <button type="button" class="button" id="upload-category-image"><?php _e('Upload Image', 'kavipushp-bridals'); ?></button>
            <button type="button" class="button" id="remove-category-image" <?php echo $image_id ? '' : 'style="display:none;"'; ?>><?php _e('Remove Image', 'kavipushp-bridals'); ?></button>
        </td>
    </tr>
    <?php
}
add_action('bridal_category_edit_form_fields', 'kavipushp_category_edit_image_field');

function kavipushp_save_category_image($term_id) {
    if (isset($_POST['category_image'])) {
        update_term_meta($term_id, 'category_image', intval($_POST['category_image']));
    }
}
add_action('created_bridal_category', 'kavipushp_save_category_image');
add_action('edited_bridal_category', 'kavipushp_save_category_image');

/**
 * Admin Scripts for Category Image
 */
function kavipushp_admin_scripts($hook) {
    if ($hook === 'edit-tags.php' || $hook === 'term.php') {
        wp_enqueue_media();
        wp_enqueue_script('kavipushp-admin', KAVIPUSHP_URI . '/assets/js/admin.js', array('jquery'), KAVIPUSHP_VERSION, true);
    }
}
add_action('admin_enqueue_scripts', 'kavipushp_admin_scripts');

/**
 * Include template files
 */
require_once KAVIPUSHP_DIR . '/inc/template-functions.php';
require_once KAVIPUSHP_DIR . '/inc/customizer.php';
require_once KAVIPUSHP_DIR . '/inc/sample-data.php';
require_once KAVIPUSHP_DIR . '/inc/admin-dashboard.php';
require_once KAVIPUSHP_DIR . '/inc/admin-pages.php';

/**
 * AJAX: Get Set Deposit
 */
function kavipushp_get_set_deposit() {
    check_ajax_referer('kavipushp_nonce', 'nonce');

    $set_id = isset($_POST['set_id']) ? intval($_POST['set_id']) : 0;

    if (!$set_id) {
        wp_send_json_error();
    }

    $deposit = get_post_meta($set_id, '_deposit_amount', true);

    wp_send_json_success(array('deposit' => $deposit));
}
add_action('wp_ajax_kavipushp_get_set_deposit', 'kavipushp_get_set_deposit');
add_action('wp_ajax_nopriv_kavipushp_get_set_deposit', 'kavipushp_get_set_deposit');

/**
 * AJAX: Get Wishlist Items
 */
function kavipushp_get_wishlist_items() {
    check_ajax_referer('kavipushp_nonce', 'nonce');

    $ids = isset($_POST['ids']) ? array_map('intval', $_POST['ids']) : array();

    if (empty($ids)) {
        wp_send_json_error();
    }

    $sets = get_posts(array(
        'post_type'      => 'bridal_set',
        'post__in'       => $ids,
        'posts_per_page' => -1,
    ));

    ob_start();
    foreach ($sets as $set) {
        $rental_price = get_post_meta($set->ID, '_rental_price', true);
        $availability = get_post_meta($set->ID, '_availability', true);
        $thumb = get_the_post_thumbnail_url($set->ID, 'product-thumb');
        $categories = get_the_terms($set->ID, 'bridal_category');
        ?>
        <div class="product-card">
            <div class="product-image">
                <?php if ($thumb): ?>
                    <img src="<?php echo esc_url($thumb); ?>" alt="<?php echo esc_attr($set->post_title); ?>">
                <?php else: ?>
                    <img src="<?php echo KAVIPUSHP_URI; ?>/assets/images/placeholder.jpg" alt="<?php echo esc_attr($set->post_title); ?>">
                <?php endif; ?>
                <?php if ($availability === 'available'): ?>
                    <span class="product-badge"><?php _e('Available', 'kavipushp-bridals'); ?></span>
                <?php endif; ?>
            </div>
            <div class="product-info">
                <?php if ($categories && !is_wp_error($categories)): ?>
                    <span class="product-category"><?php echo esc_html($categories[0]->name); ?></span>
                <?php endif; ?>
                <h3><a href="<?php echo get_permalink($set->ID); ?>"><?php echo esc_html($set->post_title); ?></a></h3>
                <div class="product-meta">
                    <span class="product-price"><?php echo number_format($rental_price, 0); ?> <span>/day</span></span>
                    <button class="add-to-wishlist active" data-id="<?php echo $set->ID; ?>">
                        <i class="fas fa-heart"></i>
                    </button>
                </div>
            </div>
        </div>
        <?php
    }
    $html = ob_get_clean();

    wp_send_json_success(array('html' => $html));
}
add_action('wp_ajax_kavipushp_get_wishlist_items', 'kavipushp_get_wishlist_items');
add_action('wp_ajax_nopriv_kavipushp_get_wishlist_items', 'kavipushp_get_wishlist_items');
