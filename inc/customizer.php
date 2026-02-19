<?php
/**
 * Theme Customizer
 *
 * @package Kavipushp_Bridals
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register Customizer Settings
 */
function kavipushp_customize_register($wp_customize) {

    // ===== General Settings =====
    $wp_customize->add_section('kavipushp_general', array(
        'title'    => __('General Settings', 'kavipushp-bridals'),
        'priority' => 30,
    ));

    // Currency Symbol
    $wp_customize->add_setting('currency_symbol', array(
        'default'           => '₹',
        'sanitize_callback' => 'sanitize_text_field',
    ));

    $wp_customize->add_control('currency_symbol', array(
        'label'   => __('Currency Symbol', 'kavipushp-bridals'),
        'section' => 'kavipushp_general',
        'type'    => 'text',
    ));

    // ===== Hero Section =====
    $wp_customize->add_section('kavipushp_hero', array(
        'title'    => __('Hero Section', 'kavipushp-bridals'),
        'priority' => 31,
    ));

    // Hero Title
    $wp_customize->add_setting('hero_title', array(
        'default'           => 'Exquisite Bridal Jewelry',
        'sanitize_callback' => 'sanitize_text_field',
    ));

    $wp_customize->add_control('hero_title', array(
        'label'   => __('Hero Title', 'kavipushp-bridals'),
        'section' => 'kavipushp_hero',
        'type'    => 'text',
    ));

    // Hero Title Highlight
    $wp_customize->add_setting('hero_title_highlight', array(
        'default'           => 'For Rent',
        'sanitize_callback' => 'sanitize_text_field',
    ));

    $wp_customize->add_control('hero_title_highlight', array(
        'label'   => __('Hero Title Highlight', 'kavipushp-bridals'),
        'section' => 'kavipushp_hero',
        'type'    => 'text',
    ));

    // Hero Subtitle
    $wp_customize->add_setting('hero_subtitle', array(
        'default'           => 'Make your special day unforgettable with our stunning collection of premium bridal jewelry sets available for rent.',
        'sanitize_callback' => 'sanitize_textarea_field',
    ));

    $wp_customize->add_control('hero_subtitle', array(
        'label'   => __('Hero Subtitle', 'kavipushp-bridals'),
        'section' => 'kavipushp_hero',
        'type'    => 'textarea',
    ));

    // Hero Background Image
    $wp_customize->add_setting('hero_background', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ));

    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'hero_background', array(
        'label'   => __('Hero Background Image', 'kavipushp-bridals'),
        'section' => 'kavipushp_hero',
    )));

    // ===== Contact Information =====
    $wp_customize->add_section('kavipushp_contact', array(
        'title'    => __('Contact Information', 'kavipushp-bridals'),
        'priority' => 32,
    ));

    // Phone
    $wp_customize->add_setting('contact_phone', array(
        'default'           => '+91 98765 43210',
        'sanitize_callback' => 'sanitize_text_field',
    ));

    $wp_customize->add_control('contact_phone', array(
        'label'   => __('Phone Number', 'kavipushp-bridals'),
        'section' => 'kavipushp_contact',
        'type'    => 'text',
    ));

    // Email
    $wp_customize->add_setting('contact_email', array(
        'default'           => 'info@kavipushp.com',
        'sanitize_callback' => 'sanitize_email',
    ));

    $wp_customize->add_control('contact_email', array(
        'label'   => __('Email Address', 'kavipushp-bridals'),
        'section' => 'kavipushp_contact',
        'type'    => 'email',
    ));

    // Address
    $wp_customize->add_setting('contact_address', array(
        'default'           => "123 Jewelry Lane\nMumbai, Maharashtra 400001",
        'sanitize_callback' => 'sanitize_textarea_field',
    ));

    $wp_customize->add_control('contact_address', array(
        'label'   => __('Address', 'kavipushp-bridals'),
        'section' => 'kavipushp_contact',
        'type'    => 'textarea',
    ));

    // Business Hours
    $wp_customize->add_setting('business_hours', array(
        'default'           => 'Mon - Sat: 10:00 AM - 8:00 PM',
        'sanitize_callback' => 'sanitize_text_field',
    ));

    $wp_customize->add_control('business_hours', array(
        'label'   => __('Business Hours', 'kavipushp-bridals'),
        'section' => 'kavipushp_contact',
        'type'    => 'text',
    ));

    // ===== Social Media =====
    $wp_customize->add_section('kavipushp_social', array(
        'title'    => __('Social Media', 'kavipushp-bridals'),
        'priority' => 33,
    ));

    // Facebook
    $wp_customize->add_setting('social_facebook', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ));

    $wp_customize->add_control('social_facebook', array(
        'label'   => __('Facebook URL', 'kavipushp-bridals'),
        'section' => 'kavipushp_social',
        'type'    => 'url',
    ));

    // Instagram
    $wp_customize->add_setting('social_instagram', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ));

    $wp_customize->add_control('social_instagram', array(
        'label'   => __('Instagram URL', 'kavipushp-bridals'),
        'section' => 'kavipushp_social',
        'type'    => 'url',
    ));

    // Twitter
    $wp_customize->add_setting('social_twitter', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ));

    $wp_customize->add_control('social_twitter', array(
        'label'   => __('Twitter URL', 'kavipushp-bridals'),
        'section' => 'kavipushp_social',
        'type'    => 'url',
    ));

    // YouTube
    $wp_customize->add_setting('social_youtube', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ));

    $wp_customize->add_control('social_youtube', array(
        'label'   => __('YouTube URL', 'kavipushp-bridals'),
        'section' => 'kavipushp_social',
        'type'    => 'url',
    ));

    // WhatsApp
    $wp_customize->add_setting('social_whatsapp', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ));

    $wp_customize->add_control('social_whatsapp', array(
        'label'       => __('WhatsApp Number', 'kavipushp-bridals'),
        'description' => __('Enter number with country code (e.g., 919876543210)', 'kavipushp-bridals'),
        'section'     => 'kavipushp_social',
        'type'        => 'text',
    ));

    // ===== Footer Settings =====
    $wp_customize->add_section('kavipushp_footer', array(
        'title'    => __('Footer Settings', 'kavipushp-bridals'),
        'priority' => 34,
    ));

    // Footer About Text
    $wp_customize->add_setting('footer_about', array(
        'default'           => 'We offer the finest collection of bridal jewelry sets for rent. Make your special day even more memorable with our exquisite pieces.',
        'sanitize_callback' => 'sanitize_textarea_field',
    ));

    $wp_customize->add_control('footer_about', array(
        'label'   => __('Footer About Text', 'kavipushp-bridals'),
        'section' => 'kavipushp_footer',
        'type'    => 'textarea',
    ));

    // ===== Colors =====
    $wp_customize->add_setting('primary_color', array(
        'default'           => '#c9a86c',
        'sanitize_callback' => 'sanitize_hex_color',
    ));

    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'primary_color', array(
        'label'   => __('Primary Color', 'kavipushp-bridals'),
        'section' => 'colors',
    )));

    $wp_customize->add_setting('secondary_color', array(
        'default'           => '#2c1810',
        'sanitize_callback' => 'sanitize_hex_color',
    ));

    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'secondary_color', array(
        'label'   => __('Secondary Color', 'kavipushp-bridals'),
        'section' => 'colors',
    )));
}
add_action('customize_register', 'kavipushp_customize_register');

/**
 * Output Custom CSS from Customizer
 */
function kavipushp_customizer_css() {
    $primary_color = get_theme_mod('primary_color', '#c9a86c');
    $secondary_color = get_theme_mod('secondary_color', '#2c1810');
    $hero_bg = get_theme_mod('hero_background', '');

    $css = '';

    if ($primary_color !== '#c9a86c' || $secondary_color !== '#2c1810') {
        $css .= ":root {";
        if ($primary_color !== '#c9a86c') {
            $css .= "--primary-color: {$primary_color};";
        }
        if ($secondary_color !== '#2c1810') {
            $css .= "--secondary-color: {$secondary_color};";
        }
        $css .= "}";
    }

    if ($hero_bg) {
        $css .= ".hero-section { background-image: linear-gradient(rgba(44, 24, 16, 0.7), rgba(44, 24, 16, 0.7)), url({$hero_bg}); }";
    }

    if ($css) {
        wp_add_inline_style('kavipushp-style', $css);
    }
}
add_action('wp_enqueue_scripts', 'kavipushp_customizer_css', 20);

/**
 * Customizer Preview JS
 */
function kavipushp_customize_preview_js() {
    wp_enqueue_script('kavipushp-customizer', KAVIPUSHP_URI . '/assets/js/customizer.js', array('customize-preview'), KAVIPUSHP_VERSION, true);
}
add_action('customize_preview_init', 'kavipushp_customize_preview_js');
