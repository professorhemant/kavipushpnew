<?php
/**
 * Page Template
 *
 * @package Kavipushp_Bridals
 */

get_header();
?>

<section class="page-section" style="padding: 50px 0;">
    <div class="container">
        <?php while (have_posts()): the_post(); ?>
            <article id="page-<?php the_ID(); ?>" <?php post_class(); ?>>
                <header class="page-header" style="margin-bottom: 40px;">
                    <h1 style="font-size: 42px;"><?php the_title(); ?></h1>
                </header>

                <?php if (has_post_thumbnail()): ?>
                    <div class="page-featured-image" style="margin-bottom: 30px;">
                        <?php the_post_thumbnail('large', array('style' => 'width: 100%; border-radius: 15px;')); ?>
                    </div>
                <?php endif; ?>

                <div class="page-content" style="font-size: 17px; line-height: 1.8;">
                    <?php the_content(); ?>
                </div>
            </article>
        <?php endwhile; ?>
    </div>
</section>

<?php get_footer(); ?>
