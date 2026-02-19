<?php
/**
 * Front Page Template - Dashboard Style
 *
 * @package Kavipushp_Bridals
 */

get_header();

// Get statistics
$total_sets = wp_count_posts('bridal_set')->publish;
$total_bookings = wp_count_posts('booking')->publish;

// Get pending bookings
$pending_bookings = get_posts(array(
    'post_type' => 'booking',
    'posts_per_page' => -1,
    'meta_query' => array(
        array(
            'key' => '_booking_status',
            'value' => 'pending',
        ),
    ),
));
$pending_count = count($pending_bookings);

// Get active bookings (picked up)
$active_bookings = get_posts(array(
    'post_type' => 'booking',
    'posts_per_page' => -1,
    'meta_query' => array(
        array(
            'key' => '_booking_status',
            'value' => 'picked_up',
        ),
    ),
));
$active_count = count($active_bookings);

// Get total customers
global $wpdb;
$customers_table = $wpdb->prefix . 'kavipushp_customers';
$total_customers = 0;
if ($wpdb->get_var("SHOW TABLES LIKE '$customers_table'") == $customers_table) {
    $total_customers = $wpdb->get_var("SELECT COUNT(*) FROM $customers_table");
}

// Get categories
$categories = get_terms(array(
    'taxonomy' => 'bridal_category',
    'hide_empty' => false,
));
$total_categories = !is_wp_error($categories) ? count($categories) : 0;

// Calculate this month's revenue
$month_start = date('Y-m-01');
$month_bookings = get_posts(array(
    'post_type' => 'booking',
    'posts_per_page' => -1,
    'date_query' => array(
        'after' => $month_start,
        'inclusive' => true,
    ),
    'meta_query' => array(
        array(
            'key' => '_booking_status',
            'value' => array('confirmed', 'completed', 'picked_up', 'returned'),
            'compare' => 'IN',
        ),
    ),
));
$month_revenue = 0;
foreach ($month_bookings as $booking) {
    $month_revenue += floatval(get_post_meta($booking->ID, '_total_amount', true));
}
?>

<div class="kp-frontend-dashboard">
    <!-- Dashboard Header -->
    <section class="kp-dash-header">
        <div class="container">
            <div class="kp-dash-welcome">
                <h1><?php _e('Kavipushp Jewels Rental', 'kavipushp-bridals'); ?></h1>
                <p><?php _e('Premium Bridal Jewelry Rental Management System', 'kavipushp-bridals'); ?></p>
            </div>
            <div class="kp-dash-actions">
                <?php if (is_user_logged_in() && current_user_can('manage_options')): ?>
                    <a href="<?php echo admin_url('admin.php?page=kavipushp-dashboard'); ?>" class="kp-dash-btn primary">
                        <i class="fas fa-cog"></i> <?php _e('Admin Panel', 'kavipushp-bridals'); ?>
                    </a>
                <?php else: ?>
                    <a href="<?php echo wp_login_url(home_url()); ?>" class="kp-dash-btn primary">
                        <i class="fas fa-sign-in-alt"></i> <?php _e('Login', 'kavipushp-bridals'); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Stats Cards -->
    <section class="kp-dash-stats">
        <div class="container">
            <div class="kp-stats-grid">
                <div class="kp-stat-card">
                    <div class="kp-stat-icon blue">
                        <i class="fas fa-gem"></i>
                    </div>
                    <div class="kp-stat-info">
                        <h3><?php echo number_format($total_sets); ?></h3>
                        <p><?php _e('Total Jewelry Sets', 'kavipushp-bridals'); ?></p>
                    </div>
                </div>
                <div class="kp-stat-card">
                    <div class="kp-stat-icon green">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="kp-stat-info">
                        <h3><?php echo number_format($total_bookings); ?></h3>
                        <p><?php _e('Total Bookings', 'kavipushp-bridals'); ?></p>
                    </div>
                </div>
                <div class="kp-stat-card">
                    <div class="kp-stat-icon orange">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="kp-stat-info">
                        <h3><?php echo number_format($pending_count); ?></h3>
                        <p><?php _e('Pending Bookings', 'kavipushp-bridals'); ?></p>
                    </div>
                </div>
                <div class="kp-stat-card">
                    <div class="kp-stat-icon purple">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="kp-stat-info">
                        <h3><?php echo number_format($total_customers); ?></h3>
                        <p><?php _e('Customers', 'kavipushp-bridals'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Quick Actions / Navigation Cards -->
    <section class="kp-dash-nav">
        <div class="container">
            <h2 class="kp-section-title"><?php _e('Quick Access', 'kavipushp-bridals'); ?></h2>
            <div class="kp-nav-grid">
                <!-- Customers -->
                <a href="<?php echo admin_url('admin.php?page=kavipushp-customers'); ?>" class="kp-nav-card">
                    <div class="kp-nav-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3><?php _e('Customers', 'kavipushp-bridals'); ?></h3>
                    <p><?php _e('Manage customer database and contact information', 'kavipushp-bridals'); ?></p>
                    <span class="kp-nav-arrow"><i class="fas fa-arrow-right"></i></span>
                </a>

                <!-- Jewelry Inventory -->
                <a href="<?php echo admin_url('admin.php?page=kavipushp-inventory'); ?>" class="kp-nav-card">
                    <div class="kp-nav-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <i class="fas fa-gem"></i>
                    </div>
                    <h3><?php _e('Jewelry Inventory', 'kavipushp-bridals'); ?></h3>
                    <p><?php _e('View and manage all bridal jewelry sets', 'kavipushp-bridals'); ?></p>
                    <span class="kp-nav-arrow"><i class="fas fa-arrow-right"></i></span>
                </a>

                <!-- Bookings -->
                <a href="<?php echo admin_url('admin.php?page=kavipushp-bookings'); ?>" class="kp-nav-card">
                    <div class="kp-nav-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h3><?php _e('Bookings', 'kavipushp-bridals'); ?></h3>
                    <p><?php _e('Manage rental bookings and schedules', 'kavipushp-bridals'); ?></p>
                    <span class="kp-nav-arrow"><i class="fas fa-arrow-right"></i></span>
                </a>

                <!-- Invoices -->
                <a href="<?php echo admin_url('admin.php?page=kavipushp-invoices'); ?>" class="kp-nav-card">
                    <div class="kp-nav-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                    <h3><?php _e('Invoices', 'kavipushp-bridals'); ?></h3>
                    <p><?php _e('Generate and print rental invoices', 'kavipushp-bridals'); ?></p>
                    <span class="kp-nav-arrow"><i class="fas fa-arrow-right"></i></span>
                </a>

                <!-- Browse Collection -->
                <a href="<?php echo get_post_type_archive_link('bridal_set'); ?>" class="kp-nav-card">
                    <div class="kp-nav-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                        <i class="fas fa-th-large"></i>
                    </div>
                    <h3><?php _e('Browse Collection', 'kavipushp-bridals'); ?></h3>
                    <p><?php _e('View all bridal sets with filters', 'kavipushp-bridals'); ?></p>
                    <span class="kp-nav-arrow"><i class="fas fa-arrow-right"></i></span>
                </a>

                <!-- Settings -->
                <a href="<?php echo admin_url('admin.php?page=kavipushp-settings'); ?>" class="kp-nav-card">
                    <div class="kp-nav-icon" style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);">
                        <i class="fas fa-cog"></i>
                    </div>
                    <h3><?php _e('Settings', 'kavipushp-bridals'); ?></h3>
                    <p><?php _e('Configure system settings and preferences', 'kavipushp-bridals'); ?></p>
                    <span class="kp-nav-arrow"><i class="fas fa-arrow-right"></i></span>
                </a>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="kp-dash-categories">
        <div class="container">
            <h2 class="kp-section-title"><?php _e('Jewelry Categories', 'kavipushp-bridals'); ?></h2>
            <div class="kp-categories-grid">
                <?php
                if (!empty($categories) && !is_wp_error($categories)):
                    foreach ($categories as $category):
                        $count = $category->count;
                ?>
                    <a href="<?php echo get_term_link($category); ?>" class="kp-category-card">
                        <div class="kp-cat-icon">
                            <i class="fas fa-crown"></i>
                        </div>
                        <h4><?php echo esc_html($category->name); ?></h4>
                        <span class="kp-cat-count"><?php echo $count; ?> <?php _e('Sets', 'kavipushp-bridals'); ?></span>
                    </a>
                <?php
                    endforeach;
                endif;
                ?>
            </div>
        </div>
    </section>

    <!-- Recent Bookings -->
    <section class="kp-dash-recent">
        <div class="container">
            <div class="kp-recent-header">
                <h2 class="kp-section-title"><?php _e('Recent Bookings', 'kavipushp-bridals'); ?></h2>
                <a href="<?php echo admin_url('admin.php?page=kavipushp-bookings'); ?>" class="kp-view-all"><?php _e('View All', 'kavipushp-bridals'); ?> <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="kp-recent-table">
                <table>
                    <thead>
                        <tr>
                            <th><?php _e('Customer', 'kavipushp-bridals'); ?></th>
                            <th><?php _e('Set', 'kavipushp-bridals'); ?></th>
                            <th><?php _e('Dates', 'kavipushp-bridals'); ?></th>
                            <th><?php _e('Status', 'kavipushp-bridals'); ?></th>
                            <th><?php _e('Amount', 'kavipushp-bridals'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $recent_bookings = get_posts(array(
                            'post_type' => 'booking',
                            'posts_per_page' => 5,
                            'orderby' => 'date',
                            'order' => 'DESC',
                        ));

                        if (!empty($recent_bookings)):
                            foreach ($recent_bookings as $booking):
                                $customer_name = get_post_meta($booking->ID, '_customer_name', true);
                                $set_id = get_post_meta($booking->ID, '_bridal_set_id', true);
                                $set = $set_id ? get_post($set_id) : null;
                                $pickup = get_post_meta($booking->ID, '_pickup_date', true);
                                $return = get_post_meta($booking->ID, '_return_date', true);
                                $status = get_post_meta($booking->ID, '_booking_status', true);
                                $total = get_post_meta($booking->ID, '_total_amount', true);

                                $status_class = '';
                                $status_label = ucfirst($status);
                                switch($status) {
                                    case 'pending': $status_class = 'pending'; break;
                                    case 'confirmed': $status_class = 'confirmed'; break;
                                    case 'picked_up': $status_class = 'active'; $status_label = 'Active'; break;
                                    case 'returned': $status_class = 'returned'; break;
                                    case 'completed': $status_class = 'completed'; break;
                                    case 'cancelled': $status_class = 'cancelled'; break;
                                }
                        ?>
                            <tr>
                                <td><strong><?php echo esc_html($customer_name); ?></strong></td>
                                <td><?php echo $set ? esc_html($set->post_title) : '-'; ?></td>
                                <td><?php echo $pickup ? date('d M', strtotime($pickup)) . ' - ' . date('d M', strtotime($return)) : '-'; ?></td>
                                <td><span class="kp-status-badge <?php echo $status_class; ?>"><?php echo $status_label; ?></span></td>
                                <td><strong><?php echo $total ? '₹' . number_format($total) : '-'; ?></strong></td>
                            </tr>
                        <?php
                            endforeach;
                        else:
                        ?>
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 30px; color: #999;">
                                    <?php _e('No bookings yet', 'kavipushp-bridals'); ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- Footer Info -->
    <section class="kp-dash-footer-info">
        <div class="container">
            <div class="kp-info-grid">
                <div class="kp-info-card">
                    <i class="fas fa-phone"></i>
                    <h4><?php _e('Contact', 'kavipushp-bridals'); ?></h4>
                    <p>+91 98765 43210</p>
                </div>
                <div class="kp-info-card">
                    <i class="fas fa-map-marker-alt"></i>
                    <h4><?php _e('Location', 'kavipushp-bridals'); ?></h4>
                    <p>Mumbai, India</p>
                </div>
                <div class="kp-info-card">
                    <i class="fas fa-clock"></i>
                    <h4><?php _e('Working Hours', 'kavipushp-bridals'); ?></h4>
                    <p>10 AM - 8 PM</p>
                </div>
                <div class="kp-info-card">
                    <i class="fas fa-envelope"></i>
                    <h4><?php _e('Email', 'kavipushp-bridals'); ?></h4>
                    <p>info@kavipushp.com</p>
                </div>
            </div>
        </div>
    </section>
</div>

<?php get_footer(); ?>
