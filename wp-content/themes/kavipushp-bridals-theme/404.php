<?php
/**
 * 404 Page Template
 *
 * @package Kavipushp_Bridals
 */

get_header();
?>

<section class="error-404-section" style="min-height: 60vh; display: flex; align-items: center; text-align: center; padding: 80px 0;">
    <div class="container">
        <div class="error-content" style="max-width: 600px; margin: 0 auto;">
            <div style="font-size: 150px; font-weight: 700; color: var(--primary-color); line-height: 1; font-family: var(--font-primary);">404</div>
            <h1 style="font-size: 36px; margin: 20px 0;"><?php _e('Page Not Found', 'kavipushp-bridals'); ?></h1>
            <p style="font-size: 18px; color: var(--text-light); margin-bottom: 30px;">
                <?php _e('Oops! The page you are looking for might have been removed or is temporarily unavailable.', 'kavipushp-bridals'); ?>
            </p>

            <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="btn btn-primary">
                    <i class="fas fa-home"></i> <?php _e('Go Home', 'kavipushp-bridals'); ?>
                </a>
                <a href="<?php echo esc_url(get_post_type_archive_link('bridal_set')); ?>" class="btn btn-outline">
                    <i class="fas fa-gem"></i> <?php _e('Browse Collection', 'kavipushp-bridals'); ?>
                </a>
            </div>

            <div style="margin-top: 50px;">
                <h3 style="margin-bottom: 20px;"><?php _e('Or search for what you need:', 'kavipushp-bridals'); ?></h3>
                <form action="<?php echo esc_url(home_url('/')); ?>" method="get" style="display: flex; gap: 10px; max-width: 400px; margin: 0 auto;">
                    <input type="text" name="s" placeholder="<?php esc_attr_e('Search...', 'kavipushp-bridals'); ?>" style="flex: 1; padding: 15px 20px; border: 1px solid var(--border-color); border-radius: 30px;">
                    <input type="hidden" name="post_type" value="bridal_set">
                    <button type="submit" class="btn btn-primary" style="padding: 15px 25px;">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php get_footer(); ?>
