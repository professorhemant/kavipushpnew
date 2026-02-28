<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="site-header">
    <!-- Top Bar -->
    <div class="header-top">
        <div class="container">
            <div class="header-top-content">
                <div class="header-contact">
                    <a href="tel:<?php echo esc_attr(get_theme_mod('contact_phone', '+91 98765 43210')); ?>">
                        <i class="fas fa-phone-alt"></i> <?php echo esc_html(get_theme_mod('contact_phone', '+91 98765 43210')); ?>
                    </a>
                    <a href="mailto:<?php echo esc_attr(get_theme_mod('contact_email', 'info@kavipushp.com')); ?>">
                        <i class="fas fa-envelope"></i> <?php echo esc_html(get_theme_mod('contact_email', 'info@kavipushp.com')); ?>
                    </a>
                </div>
                <div class="header-social">
                    <?php if (get_theme_mod('social_facebook')): ?>
                        <a href="<?php echo esc_url(get_theme_mod('social_facebook')); ?>" target="_blank"><i class="fab fa-facebook-f"></i></a>
                    <?php endif; ?>
                    <?php if (get_theme_mod('social_instagram')): ?>
                        <a href="<?php echo esc_url(get_theme_mod('social_instagram')); ?>" target="_blank"><i class="fab fa-instagram"></i></a>
                    <?php endif; ?>
                    <?php if (get_theme_mod('social_whatsapp')): ?>
                        <a href="https://wa.me/<?php echo esc_attr(get_theme_mod('social_whatsapp')); ?>" target="_blank"><i class="fab fa-whatsapp"></i></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Header -->
    <div class="header-main">
        <div class="container">
            <div class="header-content">
                <!-- Logo -->
                <div class="site-logo">
                    <?php if (has_custom_logo()): ?>
                        <?php the_custom_logo(); ?>
                    <?php else: ?>
                        <a href="<?php echo esc_url(home_url('/')); ?>">
                            <h1>Kavipushp <span>Bridals</span></h1>
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Navigation -->
                <nav class="main-navigation">
                    <?php
                    wp_nav_menu(array(
                        'theme_location' => 'primary',
                        'menu_class'     => 'nav-menu',
                        'container'      => false,
                        'fallback_cb'    => 'kavipushp_fallback_menu',
                    ));
                    ?>
                </nav>

                <!-- Header Actions -->
                <div class="header-actions">
                    <!-- Search -->
                    <div class="header-search">
                        <form action="<?php echo esc_url(home_url('/')); ?>" method="get">
                            <input type="text" name="s" placeholder="<?php esc_attr_e('Search...', 'kavipushp-bridals'); ?>" value="<?php echo get_search_query(); ?>">
                            <input type="hidden" name="post_type" value="bridal_set">
                            <button type="submit"><i class="fas fa-search"></i></button>
                        </form>
                    </div>

                    <!-- Account -->
                    <div class="header-account">
                        <?php if (is_user_logged_in()): ?>
                            <a href="<?php echo esc_url(get_permalink(get_option('woocommerce_myaccount_page_id'))); ?>">
                                <i class="fas fa-user"></i>
                                <span><?php _e('My Account', 'kavipushp-bridals'); ?></span>
                            </a>
                        <?php else: ?>
                            <a href="<?php echo esc_url(wp_login_url()); ?>">
                                <i class="fas fa-user"></i>
                                <span><?php _e('Login', 'kavipushp-bridals'); ?></span>
                            </a>
                        <?php endif; ?>
                    </div>

                    <!-- Cart (if WooCommerce active) -->
                    <?php if (class_exists('WooCommerce')): ?>
                    <div class="header-cart">
                        <a href="<?php echo esc_url(wc_get_cart_url()); ?>">
                            <i class="fas fa-shopping-bag"></i>
                            <span class="cart-count"><?php echo WC()->cart->get_cart_contents_count(); ?></span>
                        </a>
                    </div>
                    <?php endif; ?>

                    <!-- Mobile Menu Toggle -->
                    <button class="menu-toggle" aria-label="<?php esc_attr_e('Toggle Menu', 'kavipushp-bridals'); ?>">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</header>

<main class="site-main">
