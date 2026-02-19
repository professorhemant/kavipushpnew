<?php
/**
 * Sample Data Generator
 * Run this once to populate the site with sample bridal sets
 *
 * @package Kavipushp_Bridals
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add Admin Menu for Sample Data Import
 */
function kavipushp_sample_data_menu() {
    add_submenu_page(
        'edit.php?post_type=bridal_set',
        __('Import Sample Data', 'kavipushp-bridals'),
        __('Import Samples', 'kavipushp-bridals'),
        'manage_options',
        'kavipushp-sample-data',
        'kavipushp_sample_data_page'
    );
}
add_action('admin_menu', 'kavipushp_sample_data_menu');

/**
 * Sample Data Page
 */
function kavipushp_sample_data_page() {
    ?>
    <div class="wrap">
        <h1><?php _e('Import Sample Data', 'kavipushp-bridals'); ?></h1>
        <p><?php _e('Click the button below to generate sample bridal sets and categories for testing.', 'kavipushp-bridals'); ?></p>

        <?php
        if (isset($_POST['generate_samples']) && check_admin_referer('generate_samples')) {
            kavipushp_generate_sample_data();
            echo '<div class="notice notice-success"><p>' . __('Sample data generated successfully!', 'kavipushp-bridals') . '</p></div>';
        }
        ?>

        <form method="post">
            <?php wp_nonce_field('generate_samples'); ?>
            <p>
                <label for="num_sets"><?php _e('Number of sets to generate:', 'kavipushp-bridals'); ?></label>
                <input type="number" name="num_sets" id="num_sets" value="50" min="1" max="400" style="width: 80px;">
            </p>
            <p>
                <input type="submit" name="generate_samples" class="button button-primary" value="<?php esc_attr_e('Generate Sample Data', 'kavipushp-bridals'); ?>">
            </p>
        </form>

        <hr>
        <h2><?php _e('Delete All Sample Data', 'kavipushp-bridals'); ?></h2>
        <p style="color: #dc3545;"><?php _e('Warning: This will delete ALL bridal sets and bookings!', 'kavipushp-bridals'); ?></p>
        <?php
        if (isset($_POST['delete_samples']) && check_admin_referer('delete_samples')) {
            kavipushp_delete_sample_data();
            echo '<div class="notice notice-warning"><p>' . __('All sample data deleted!', 'kavipushp-bridals') . '</p></div>';
        }
        ?>
        <form method="post">
            <?php wp_nonce_field('delete_samples'); ?>
            <input type="submit" name="delete_samples" class="button button-secondary" value="<?php esc_attr_e('Delete All Data', 'kavipushp-bridals'); ?>" onclick="return confirm('<?php esc_attr_e('Are you sure? This cannot be undone!', 'kavipushp-bridals'); ?>');">
        </form>
    </div>
    <?php
}

/**
 * Generate Sample Data
 */
function kavipushp_generate_sample_data() {
    $num_sets = isset($_POST['num_sets']) ? intval($_POST['num_sets']) : 50;
    $num_sets = min(400, max(1, $num_sets));

    // Create Categories
    $categories = array(
        'Kundan Sets' => 'Exquisite Kundan jewelry sets featuring traditional craftsmanship',
        'Polki Sets' => 'Stunning uncut diamond (Polki) bridal sets',
        'Meenakari Sets' => 'Colorful enamel work jewelry with intricate designs',
        'Temple Jewelry' => 'Traditional South Indian temple-inspired designs',
        'Diamond Sets' => 'Elegant diamond-studded bridal jewelry',
        'Gold Plated Sets' => 'Beautiful gold-plated jewelry at affordable prices',
        'Pearl Sets' => 'Graceful pearl-embellished bridal sets',
        'Antique Sets' => 'Vintage-style antique finish jewelry',
        'Jadau Sets' => 'Traditional Rajasthani Jadau jewelry',
        'Contemporary Sets' => 'Modern fusion designs for contemporary brides',
    );

    $category_ids = array();
    foreach ($categories as $name => $description) {
        $term = wp_insert_term($name, 'bridal_category', array('description' => $description));
        if (!is_wp_error($term)) {
            $category_ids[] = $term['term_id'];
        } else {
            $existing = get_term_by('name', $name, 'bridal_category');
            if ($existing) {
                $category_ids[] = $existing->term_id;
            }
        }
    }

    // Sample set names
    $prefixes = array('Royal', 'Maharani', 'Rani', 'Princess', 'Divine', 'Elegant', 'Graceful', 'Bridal', 'Traditional', 'Classic', 'Luxe', 'Heritage', 'Vintage', 'Grand', 'Exquisite');
    $suffixes = array('Collection', 'Series', 'Set', 'Ensemble', 'Suite', 'Design', 'Creation', 'Edition');

    // Materials
    $materials = array('Gold Plated', 'Kundan', 'Polki', 'Meenakari', 'Pearl', 'Diamond', 'Antique Gold', 'Rose Gold', 'Jadau');

    // Includes options
    $includes_options = array(
        "Necklace\nEarrings\nMaang Tikka",
        "Necklace\nEarrings\nMaang Tikka\nBangles (Set of 4)",
        "Choker Necklace\nLong Haar\nEarrings\nMaang Tikka\nHath Phool",
        "Necklace\nEarrings\nNose Ring\nMaang Tikka",
        "Complete Bridal Set:\nNecklace\nEarrings\nMaang Tikka\nBangles\nHath Phool\nNose Ring",
        "Layered Necklace\nJhumka Earrings\nMaang Tikka\nBajuband",
    );

    // Generate sets
    for ($i = 1; $i <= $num_sets; $i++) {
        $set_code = 'KP' . str_pad($i, 3, '0', STR_PAD_LEFT);
        $prefix = $prefixes[array_rand($prefixes)];
        $suffix = $suffixes[array_rand($suffixes)];
        $title = $prefix . ' ' . $suffix . ' ' . $set_code;

        $rental_price = rand(15, 150) * 100; // 1500 to 15000
        $deposit = $rental_price * rand(2, 5);
        $weight = rand(50, 500);
        $material = $materials[array_rand($materials)];
        $includes = $includes_options[array_rand($includes_options)];

        $content = "This stunning $prefix bridal set is perfect for your special day. Crafted with exquisite $material work, this set showcases traditional Indian artistry at its finest.\n\n";
        $content .= "Each piece is carefully inspected and maintained to ensure you look absolutely radiant on your wedding day. The intricate detailing and premium finish make this set a favorite among our brides.\n\n";
        $content .= "Perfect for: Wedding ceremonies, Reception, Sangeet, Engagement";

        // Create post
        $post_id = wp_insert_post(array(
            'post_type'    => 'bridal_set',
            'post_title'   => $title,
            'post_content' => $content,
            'post_status'  => 'publish',
            'post_excerpt' => "Beautiful $prefix bridal jewelry set featuring $material work. Perfect for making your special day memorable.",
        ));

        if (!is_wp_error($post_id)) {
            // Add meta
            update_post_meta($post_id, '_set_id', $set_code);
            update_post_meta($post_id, '_rental_price', $rental_price);
            update_post_meta($post_id, '_deposit_amount', $deposit);
            update_post_meta($post_id, '_availability', 'available');
            update_post_meta($post_id, '_set_includes', $includes);
            update_post_meta($post_id, '_weight', $weight);
            update_post_meta($post_id, '_material', $material);

            // Assign random category
            if (!empty($category_ids)) {
                wp_set_object_terms($post_id, $category_ids[array_rand($category_ids)], 'bridal_category');
            }
        }
    }
}

/**
 * Delete Sample Data
 */
function kavipushp_delete_sample_data() {
    // Delete all bridal sets
    $sets = get_posts(array(
        'post_type'      => 'bridal_set',
        'posts_per_page' => -1,
        'fields'         => 'ids',
    ));

    foreach ($sets as $post_id) {
        wp_delete_post($post_id, true);
    }

    // Delete all bookings
    $bookings = get_posts(array(
        'post_type'      => 'booking',
        'posts_per_page' => -1,
        'fields'         => 'ids',
    ));

    foreach ($bookings as $post_id) {
        wp_delete_post($post_id, true);
    }

    // Delete categories
    $terms = get_terms(array(
        'taxonomy'   => 'bridal_category',
        'hide_empty' => false,
    ));

    foreach ($terms as $term) {
        wp_delete_term($term->term_id, 'bridal_category');
    }
}
