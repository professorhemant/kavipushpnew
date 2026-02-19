<?php
/**
 * The main template file
 *
 * @package Kavipushp_Bridals
 */

get_header();
?>

<?php if (is_front_page() && !is_home()): ?>
    <?php get_template_part('template-parts/home', 'hero'); ?>
    <?php get_template_part('template-parts/home', 'categories'); ?>
    <?php get_template_part('template-parts/home', 'featured'); ?>
    <?php get_template_part('template-parts/home', 'how-it-works'); ?>
    <?php get_template_part('template-parts/home', 'features'); ?>
    <?php get_template_part('template-parts/home', 'testimonials'); ?>
    <?php get_template_part('template-parts/home', 'cta'); ?>
<?php else: ?>

<div class="container">
    <div class="content-area" style="padding: 50px 0;">
        <?php if (have_posts()): ?>
            <div class="posts-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 30px;">
                <?php while (have_posts()): the_post(); ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class('post-card'); ?>>
                        <?php if (has_post_thumbnail()): ?>
                            <div class="post-thumbnail">
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_post_thumbnail('medium_large'); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                        <div class="post-content" style="padding: 20px;">
                            <h2 class="post-title" style="font-size: 22px; margin-bottom: 10px;">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h2>
                            <div class="post-excerpt">
                                <?php the_excerpt(); ?>
                            </div>
                            <a href="<?php the_permalink(); ?>" class="btn btn-outline" style="margin-top: 15px;">
                                <?php _e('Read More', 'kavipushp-bridals'); ?>
                            </a>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>

            <?php the_posts_pagination(array(
                'mid_size'  => 2,
                'prev_text' => '<i class="fas fa-chevron-left"></i>',
                'next_text' => '<i class="fas fa-chevron-right"></i>',
            )); ?>

        <?php else: ?>
            <p><?php _e('No posts found.', 'kavipushp-bridals'); ?></p>
        <?php endif; ?>
    </div>
</div>

<?php endif; ?>

<?php get_footer(); ?>
