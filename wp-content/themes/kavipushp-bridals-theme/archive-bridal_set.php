<?php
/**
 * Archive Template for Bridal Sets (Shop Page)
 *
 * @package Kavipushp_Bridals
 */

get_header();

// Get filter parameters
$selected_category = isset($_GET['category']) ? sanitize_text_field($_GET['category']) : '';
$min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : 0;
$max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : 0;
$availability = isset($_GET['availability']) ? sanitize_text_field($_GET['availability']) : '';
$sort = isset($_GET['sort']) ? sanitize_text_field($_GET['sort']) : 'date';
?>

<section class="shop-page">
    <div class="container">
        <!-- Page Header -->
        <div class="page-header" style="text-align: center; margin-bottom: 40px;">
            <h1><?php _e('Our Bridal Collection', 'kavipushp-bridals'); ?></h1>
            <p><?php _e('Discover our exquisite range of bridal jewelry sets available for rent', 'kavipushp-bridals'); ?></p>
        </div>

        <div class="shop-layout">
            <!-- Sidebar / Filters -->
            <aside class="shop-sidebar">
                <form method="get" action="<?php echo esc_url(get_post_type_archive_link('bridal_set')); ?>" id="filter-form">

                    <!-- Categories Filter -->
                    <div class="filter-section">
                        <h4><?php _e('Categories', 'kavipushp-bridals'); ?></h4>
                        <div class="filter-options">
                            <?php
                            $categories = get_terms(array(
                                'taxonomy'   => 'bridal_category',
                                'hide_empty' => true,
                            ));
                            if (!empty($categories) && !is_wp_error($categories)):
                                foreach ($categories as $category):
                            ?>
                                <label>
                                    <input type="radio" name="category" value="<?php echo esc_attr($category->slug); ?>" <?php checked($selected_category, $category->slug); ?>>
                                    <?php echo esc_html($category->name); ?> (<?php echo $category->count; ?>)
                                </label>
                            <?php
                                endforeach;
                            endif;
                            ?>
                            <label>
                                <input type="radio" name="category" value="" <?php checked($selected_category, ''); ?>>
                                <?php _e('All Categories', 'kavipushp-bridals'); ?>
                            </label>
                        </div>
                    </div>

                    <!-- Price Range Filter -->
                    <div class="filter-section">
                        <h4><?php _e('Price Range (per day)', 'kavipushp-bridals'); ?></h4>
                        <div class="price-range">
                            <div class="form-row" style="display: flex; gap: 10px;">
                                <input type="number" name="min_price" placeholder="<?php esc_attr_e('Min', 'kavipushp-bridals'); ?>" value="<?php echo $min_price ? $min_price : ''; ?>" style="width: 50%;">
                                <input type="number" name="max_price" placeholder="<?php esc_attr_e('Max', 'kavipushp-bridals'); ?>" value="<?php echo $max_price ? $max_price : ''; ?>" style="width: 50%;">
                            </div>
                        </div>
                    </div>

                    <!-- Availability Filter -->
                    <div class="filter-section">
                        <h4><?php _e('Availability', 'kavipushp-bridals'); ?></h4>
                        <div class="filter-options">
                            <label>
                                <input type="radio" name="availability" value="available" <?php checked($availability, 'available'); ?>>
                                <?php _e('Available Now', 'kavipushp-bridals'); ?>
                            </label>
                            <label>
                                <input type="radio" name="availability" value="" <?php checked($availability, ''); ?>>
                                <?php _e('Show All', 'kavipushp-bridals'); ?>
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;"><?php _e('Apply Filters', 'kavipushp-bridals'); ?></button>
                    <a href="<?php echo esc_url(get_post_type_archive_link('bridal_set')); ?>" class="btn btn-outline" style="width: 100%; margin-top: 10px; text-align: center;"><?php _e('Clear All', 'kavipushp-bridals'); ?></a>
                </form>
            </aside>

            <!-- Products Grid -->
            <div class="shop-content">
                <!-- Toolbar -->
                <div class="shop-toolbar">
                    <div class="results-count">
                        <?php
                        global $wp_query;
                        $total = $wp_query->found_posts;
                        printf(_n('%s Set Found', '%s Sets Found', $total, 'kavipushp-bridals'), $total);
                        ?>
                    </div>
                    <div class="shop-sort">
                        <label for="sort"><?php _e('Sort by:', 'kavipushp-bridals'); ?></label>
                        <select name="sort" id="sort" onchange="this.form.submit()" form="filter-form">
                            <option value="date" <?php selected($sort, 'date'); ?>><?php _e('Newest First', 'kavipushp-bridals'); ?></option>
                            <option value="title" <?php selected($sort, 'title'); ?>><?php _e('Name (A-Z)', 'kavipushp-bridals'); ?></option>
                            <option value="price_low" <?php selected($sort, 'price_low'); ?>><?php _e('Price: Low to High', 'kavipushp-bridals'); ?></option>
                            <option value="price_high" <?php selected($sort, 'price_high'); ?>><?php _e('Price: High to Low', 'kavipushp-bridals'); ?></option>
                        </select>
                    </div>
                </div>

                <?php
                // Build query
                $paged = get_query_var('paged') ? get_query_var('paged') : 1;
                $args = array(
                    'post_type'      => 'bridal_set',
                    'posts_per_page' => 12,
                    'paged'          => $paged,
                );

                // Category filter
                if ($selected_category) {
                    $args['tax_query'] = array(
                        array(
                            'taxonomy' => 'bridal_category',
                            'field'    => 'slug',
                            'terms'    => $selected_category,
                        ),
                    );
                }

                // Meta queries for price and availability
                $meta_query = array();

                if ($min_price) {
                    $meta_query[] = array(
                        'key'     => '_rental_price',
                        'value'   => $min_price,
                        'compare' => '>=',
                        'type'    => 'NUMERIC',
                    );
                }

                if ($max_price) {
                    $meta_query[] = array(
                        'key'     => '_rental_price',
                        'value'   => $max_price,
                        'compare' => '<=',
                        'type'    => 'NUMERIC',
                    );
                }

                if ($availability) {
                    $meta_query[] = array(
                        'key'   => '_availability',
                        'value' => $availability,
                    );
                }

                if (!empty($meta_query)) {
                    $args['meta_query'] = $meta_query;
                }

                // Sorting
                switch ($sort) {
                    case 'title':
                        $args['orderby'] = 'title';
                        $args['order'] = 'ASC';
                        break;
                    case 'price_low':
                        $args['meta_key'] = '_rental_price';
                        $args['orderby'] = 'meta_value_num';
                        $args['order'] = 'ASC';
                        break;
                    case 'price_high':
                        $args['meta_key'] = '_rental_price';
                        $args['orderby'] = 'meta_value_num';
                        $args['order'] = 'DESC';
                        break;
                    default:
                        $args['orderby'] = 'date';
                        $args['order'] = 'DESC';
                }

                $sets_query = new WP_Query($args);
                ?>

                <?php if ($sets_query->have_posts()): ?>
                    <div class="products-grid-new">
                        <?php while ($sets_query->have_posts()): $sets_query->the_post();
                            $rental_price = get_post_meta(get_the_ID(), '_rental_price', true);
                            $availability_status = get_post_meta(get_the_ID(), '_availability', true);
                            $set_id_code = get_post_meta(get_the_ID(), '_set_id', true);
                            $categories = get_the_terms(get_the_ID(), 'bridal_category');
                            $category_name = ($categories && !is_wp_error($categories)) ? $categories[0]->name : 'Bridal Set';
                        ?>
                            <div class="kp-product-card">
                                <div class="kp-product-top">
                                    <span class="kp-product-title-small"><?php the_title(); ?></span>
                                    <?php if ($availability_status === 'available'): ?>
                                        <span class="kp-availability-badge available"><?php _e('Available', 'kavipushp-bridals'); ?></span>
                                    <?php elseif ($availability_status === 'rented'): ?>
                                        <span class="kp-availability-badge rented"><?php _e('Rented', 'kavipushp-bridals'); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="kp-product-body">
                                    <span class="kp-product-category"><?php echo esc_html(strtoupper($category_name)); ?></span>
                                    <h3 class="kp-product-name"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                    <p class="kp-product-id">ID: <?php echo esc_html($set_id_code ?: 'KP' . get_the_ID()); ?></p>
                                </div>
                                <div class="kp-product-footer">
                                    <span class="kp-product-price"><?php echo number_format($rental_price, 0); ?> <span class="kp-per-day">/day</span></span>
                                    <a href="<?php the_permalink(); ?>" class="kp-view-btn"><?php _e('VIEW DETAILS', 'kavipushp-bridals'); ?></a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>

                    <!-- Pagination -->
                    <div class="pagination">
                        <?php
                        echo paginate_links(array(
                            'total'     => $sets_query->max_num_pages,
                            'current'   => $paged,
                            'prev_text' => '<i class="fas fa-chevron-left"></i>',
                            'next_text' => '<i class="fas fa-chevron-right"></i>',
                        ));
                        ?>
                    </div>

                    <?php wp_reset_postdata(); ?>
                <?php else: ?>
                    <div class="no-products" style="text-align: center; padding: 60px 20px;">
                        <i class="fas fa-search" style="font-size: 60px; color: #ddd; margin-bottom: 20px;"></i>
                        <h3><?php _e('No Bridal Sets Found', 'kavipushp-bridals'); ?></h3>
                        <p><?php _e('Try adjusting your filters or browse all our collections.', 'kavipushp-bridals'); ?></p>
                        <a href="<?php echo esc_url(get_post_type_archive_link('bridal_set')); ?>" class="btn btn-primary" style="margin-top: 20px;">
                            <?php _e('View All Sets', 'kavipushp-bridals'); ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php get_footer(); ?>
