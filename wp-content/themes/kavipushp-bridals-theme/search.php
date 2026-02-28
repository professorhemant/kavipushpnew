<?php
/**
 * Search Results Template
 *
 * @package Kavipushp_Bridals
 */

get_header();
?>

<section class="search-results-section" style="padding: 50px 0;">
    <div class="container">
        <header class="search-header" style="text-align: center; margin-bottom: 50px;">
            <h1><?php _e('Search Results', 'kavipushp-bridals'); ?></h1>
            <p style="color: var(--text-light);">
                <?php
                global $wp_query;
                printf(
                    _n('%d result found for "%s"', '%d results found for "%s"', $wp_query->found_posts, 'kavipushp-bridals'),
                    $wp_query->found_posts,
                    get_search_query()
                );
                ?>
            </p>
        </header>

        <!-- Search Form -->
        <div style="max-width: 500px; margin: 0 auto 50px;">
            <form action="<?php echo esc_url(home_url('/')); ?>" method="get" style="display: flex; gap: 10px;">
                <input type="text" name="s" value="<?php echo get_search_query(); ?>" placeholder="<?php esc_attr_e('Search bridal sets...', 'kavipushp-bridals'); ?>" style="flex: 1; padding: 15px 20px; border: 1px solid var(--border-color); border-radius: 30px;">
                <input type="hidden" name="post_type" value="bridal_set">
                <button type="submit" class="btn btn-primary" style="padding: 15px 30px;">
                    <i class="fas fa-search"></i> <?php _e('Search', 'kavipushp-bridals'); ?>
                </button>
            </form>
        </div>

        <?php if (have_posts()): ?>
            <div class="products-grid">
                <?php while (have_posts()): the_post();
                    if (get_post_type() === 'bridal_set'):
                        $rental_price = get_post_meta(get_the_ID(), '_rental_price', true);
                        $availability = get_post_meta(get_the_ID(), '_availability', true);
                        $categories = get_the_terms(get_the_ID(), 'bridal_category');
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
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <?php if ($categories && !is_wp_error($categories)): ?>
                                <span class="product-category"><?php echo esc_html($categories[0]->name); ?></span>
                            <?php endif; ?>
                            <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                            <div class="product-meta">
                                <span class="product-price">
                                    <?php echo number_format($rental_price, 0); ?> <span>/day</span>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Regular post/page result -->
                    <div class="post-card" style="background: #fff; border-radius: 10px; overflow: hidden; box-shadow: var(--shadow);">
                        <?php if (has_post_thumbnail()): ?>
                            <a href="<?php the_permalink(); ?>">
                                <?php the_post_thumbnail('medium', array('style' => 'width: 100%; height: 200px; object-fit: cover;')); ?>
                            </a>
                        <?php endif; ?>
                        <div style="padding: 20px;">
                            <span style="color: var(--primary-color); font-size: 12px; text-transform: uppercase;"><?php echo get_post_type_object(get_post_type())->labels->singular_name; ?></span>
                            <h3 style="font-size: 20px; margin: 10px 0;"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                            <p style="color: var(--text-light);"><?php echo wp_trim_words(get_the_excerpt(), 15); ?></p>
                        </div>
                    </div>
                <?php endif;
                endwhile; ?>
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
                <i class="fas fa-search"></i>
                <h3><?php _e('No Results Found', 'kavipushp-bridals'); ?></h3>
                <p><?php _e('Sorry, we couldn\'t find what you\'re looking for. Try different keywords or browse our collection.', 'kavipushp-bridals'); ?></p>
                <a href="<?php echo esc_url(get_post_type_archive_link('bridal_set')); ?>" class="btn btn-primary">
                    <?php _e('Browse All Sets', 'kavipushp-bridals'); ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php get_footer(); ?>
