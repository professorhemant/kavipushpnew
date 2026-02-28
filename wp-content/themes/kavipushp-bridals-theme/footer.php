</main><!-- .site-main -->

<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <!-- About -->
            <div class="footer-about">
                <h3>Kavipushp <span>Bridals</span></h3>
                <p><?php echo esc_html(get_theme_mod('footer_about', 'We offer the finest collection of bridal jewelry sets for rent. Make your special day even more memorable with our exquisite pieces.')); ?></p>
                <div class="footer-social">
                    <?php if (get_theme_mod('social_facebook')): ?>
                        <a href="<?php echo esc_url(get_theme_mod('social_facebook')); ?>" target="_blank"><i class="fab fa-facebook-f"></i></a>
                    <?php endif; ?>
                    <?php if (get_theme_mod('social_instagram')): ?>
                        <a href="<?php echo esc_url(get_theme_mod('social_instagram')); ?>" target="_blank"><i class="fab fa-instagram"></i></a>
                    <?php endif; ?>
                    <?php if (get_theme_mod('social_twitter')): ?>
                        <a href="<?php echo esc_url(get_theme_mod('social_twitter')); ?>" target="_blank"><i class="fab fa-twitter"></i></a>
                    <?php endif; ?>
                    <?php if (get_theme_mod('social_youtube')): ?>
                        <a href="<?php echo esc_url(get_theme_mod('social_youtube')); ?>" target="_blank"><i class="fab fa-youtube"></i></a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="footer-widget">
                <h4><?php _e('Quick Links', 'kavipushp-bridals'); ?></h4>
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'footer',
                    'menu_class'     => 'footer-menu',
                    'container'      => false,
                    'depth'          => 1,
                    'fallback_cb'    => 'kavipushp_footer_fallback_menu',
                ));
                ?>
            </div>

            <!-- Categories -->
            <div class="footer-widget">
                <h4><?php _e('Categories', 'kavipushp-bridals'); ?></h4>
                <ul>
                    <?php
                    $categories = get_terms(array(
                        'taxonomy'   => 'bridal_category',
                        'hide_empty' => false,
                        'number'     => 6,
                    ));
                    if (!empty($categories) && !is_wp_error($categories)):
                        foreach ($categories as $category):
                    ?>
                        <li><a href="<?php echo esc_url(get_term_link($category)); ?>"><?php echo esc_html($category->name); ?></a></li>
                    <?php
                        endforeach;
                    endif;
                    ?>
                </ul>
            </div>

            <!-- Contact Info -->
            <div class="footer-widget">
                <h4><?php _e('Contact Us', 'kavipushp-bridals'); ?></h4>
                <ul class="footer-contact">
                    <li>
                        <i class="fas fa-map-marker-alt"></i>
                        <span><?php echo nl2br(esc_html(get_theme_mod('contact_address', "123 Jewelry Lane\nMumbai, Maharashtra 400001"))); ?></span>
                    </li>
                    <li>
                        <i class="fas fa-phone-alt"></i>
                        <span><?php echo esc_html(get_theme_mod('contact_phone', '+91 98765 43210')); ?></span>
                    </li>
                    <li>
                        <i class="fas fa-envelope"></i>
                        <span><?php echo esc_html(get_theme_mod('contact_email', 'info@kavipushp.com')); ?></span>
                    </li>
                    <li>
                        <i class="fas fa-clock"></i>
                        <span><?php echo esc_html(get_theme_mod('business_hours', 'Mon - Sat: 10:00 AM - 8:00 PM')); ?></span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. <?php _e('All Rights Reserved.', 'kavipushp-bridals'); ?></p>
            <div class="footer-bottom-links">
                <a href="<?php echo esc_url(get_privacy_policy_url()); ?>"><?php _e('Privacy Policy', 'kavipushp-bridals'); ?></a>
                <a href="#"><?php _e('Terms & Conditions', 'kavipushp-bridals'); ?></a>
                <a href="#"><?php _e('Rental Policy', 'kavipushp-bridals'); ?></a>
            </div>
        </div>
    </div>
</footer>

<!-- WhatsApp Float Button -->
<?php if (get_theme_mod('social_whatsapp')): ?>
<a href="https://wa.me/<?php echo esc_attr(get_theme_mod('social_whatsapp')); ?>" class="whatsapp-float" target="_blank">
    <i class="fab fa-whatsapp"></i>
</a>
<style>
.whatsapp-float {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 60px;
    height: 60px;
    background: #25D366;
    color: #fff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 30px;
    box-shadow: 0 4px 15px rgba(37, 211, 102, 0.4);
    z-index: 999;
    transition: all 0.3s ease;
}
.whatsapp-float:hover {
    transform: scale(1.1);
    color: #fff;
}
</style>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
