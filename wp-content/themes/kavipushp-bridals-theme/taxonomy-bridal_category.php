<?php
/**
 * Taxonomy Archive: Bridal Category
 *
 * @package Kavipushp_Bridals
 */

get_header();

$term = get_queried_object();
$term_image_id = get_term_meta($term->term_id, 'category_image', true);
$term_image = $term_image_id ? wp_get_attachment_image_url($term_image_id, 'large') : '';
?>

<!-- Category Header -->
<section class="category-header" style="background: linear-gradient(rgba(44, 24, 16, 0.8), rgba(44, 24, 16, 0.8))<?php echo $term_image ? ', url(' . esc_url($term_image) . ')' : ''; ?>; background-size: cover; background-position: center; padding: 80px 0; text-align: center; color: #fff;">
    <div class="container">
        <h1 style="color: #fff; font-size: 48px; margin-bottom: 15px;"><?php single_term_title(); ?></h1>
        <?php if ($term->description): ?>
            <p style="max-width: 600px; margin: 0 auto; opacity: 0.9;"><?php echo esc_html($term->description); ?></p>
        <?php endif; ?>
        <p style="margin-top: 15px; opacity: 0.8;">
            <?php printf(_n('%s Bridal Set', '%s Bridal Sets', $term->count, 'kavipushp-bridals'), $term->count); ?>
        </p>
    </div>
</section>

<section class="shop-page" style="padding-top: 50px;">
    <div class="container">
        <!-- Breadcrumb -->
        <div class="breadcrumb" style="margin-bottom: 30px; font-size: 14px;">
            <a href="<?php echo esc_url(home_url('/')); ?>"><?php _e('Home', 'kavipushp-bridals'); ?></a>
            <span style="margin: 0 10px;">/</span>
            <a href="<?php echo esc_url(get_post_type_archive_link('bridal_set')); ?>"><?php _e('Collection', 'kavipushp-bridals'); ?></a>
            <span style="margin: 0 10px;">/</span>
            <span><?php single_term_title(); ?></span>
        </div>

        <?php if (have_posts()): ?>
            <!-- Toolbar -->
            <div class="shop-toolbar">
                <div class="results-count">
                    <?php
                    global $wp_query;
                    printf(_n('%s Set Found', '%s Sets Found', $wp_query->found_posts, 'kavipushp-bridals'), $wp_query->found_posts);
                    ?>
                </div>
            </div>

            <div class="products-grid">
                <?php while (have_posts()): the_post();
                    $rental_price = get_post_meta(get_the_ID(), '_rental_price', true);
                    $availability = get_post_meta(get_the_ID(), '_availability', true);
                    $set_id_code = get_post_meta(get_the_ID(), '_set_id', true);
                ?>
                    <div class="product-card">
                        <div class="product-image">
                            <?php if (has_post_thumbnail()): ?>
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_post_thumbnail('product-thumb'); ?>
                                </a>
                            <?php else: ?>
                                <a href="<?php the_permalink(); ?>">
                                    <img src="<?php echo KAVIPUSHP_URI; ?>/assets/images/placeholder.jpg" alt="<?php the_title_attribute(); ?>">
                                </a>
                            <?php endif; ?>

                            <?php if ($availability === 'available'): ?>
                                <span class="product-badge"><?php _e('Available', 'kavipushp-bridals'); ?></span>
                            <?php elseif ($availability === 'rented'): ?>
                                <span class="product-badge trending"><?php _e('Rented', 'kavipushp-bridals'); ?></span>
                            <?php endif; ?>

                            <div class="product-actions">
                                <button class="quick-view" data-id="<?php the_ID(); ?>"><i class="fas fa-eye"></i></button>
                                <button class="add-to-wishlist" data-id="<?php the_ID(); ?>"><i class="fas fa-heart"></i></button>
                            </div>
                        </div>
                        <div class="product-info">
                            <span class="product-category"><?php echo esc_html($term->name); ?></span>
                            <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                            <?php if ($set_id_code): ?>
                                <small style="color: #999;">ID: <?php echo esc_html($set_id_code); ?></small>
                            <?php endif; ?>
                            <div class="product-meta">
                                <span class="product-price">
                                    <?php echo number_format($rental_price, 0); ?> <span>/day</span>
                                </span>
                                <a href="<?php the_permalink(); ?>" class="btn btn-outline" style="padding: 8px 15px; font-size: 12px;">
                                    <?php _e('View Details', 'kavipushp-bridals'); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <!-- Pagination -->
            <div class="pagination">
                <?php
                echo paginate_links(array(
                    'prev_text' => '<i class="fas fa-chevron-left"></i>',
                    'next_text' => '<i class="fas fa-chevron-right"></i>',
                ));
                ?>
            </div>

        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-box-open"></i>
                <h3><?php _e('No Sets Found', 'kavipushp-bridals'); ?></h3>
                <p><?php _e('There are no bridal sets in this category yet.', 'kavipushp-bridals'); ?></p>
                <a href="<?php echo esc_url(get_post_type_archive_link('bridal_set')); ?>" class="btn btn-primary">
                    <?php _e('Browse All Sets', 'kavipushp-bridals'); ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php get_footer(); ?>
