<?php
/**
 * Custom Admin Dashboard for Kavipushp Jewels Rental
 *
 * @package Kavipushp_Bridals
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add Custom Admin Menu Page
 */
function kavipushp_admin_menu() {
    // Main menu page
    add_menu_page(
        __('Kavipushp Rentals', 'kavipushp-bridals'),
        __('Kavipushp', 'kavipushp-bridals'),
        'manage_options',
        'kavipushp-dashboard',
        'kavipushp_render_dashboard',
        'dashicons-diamond',
        2
    );

    // Submenu pages
    add_submenu_page(
        'kavipushp-dashboard',
        __('Dashboard', 'kavipushp-bridals'),
        __('Dashboard', 'kavipushp-bridals'),
        'manage_options',
        'kavipushp-dashboard',
        'kavipushp_render_dashboard'
    );

    add_submenu_page(
        'kavipushp-dashboard',
        __('Customers', 'kavipushp-bridals'),
        __('Customers', 'kavipushp-bridals'),
        'manage_options',
        'kavipushp-customers',
        'kavipushp_render_customers_enhanced'
    );

    add_submenu_page(
        'kavipushp-dashboard',
        __('Jewelry Inventory', 'kavipushp-bridals'),
        __('Jewelry Inventory', 'kavipushp-bridals'),
        'manage_options',
        'kavipushp-inventory',
        'kavipushp_render_inventory'
    );

    add_submenu_page(
        'kavipushp-dashboard',
        __('Availability', 'kavipushp-bridals'),
        __('Availability of Sets', 'kavipushp-bridals'),
        'manage_options',
        'kavipushp-availability',
        'kavipushp_render_availability'
    );

    add_submenu_page(
        'kavipushp-dashboard',
        __('Bookings', 'kavipushp-bridals'),
        __('Bookings', 'kavipushp-bridals'),
        'manage_options',
        'kavipushp-bookings',
        'kavipushp_render_bookings_management'
    );

    add_submenu_page(
        'kavipushp-dashboard',
        __('Invoices', 'kavipushp-bridals'),
        __('Invoices', 'kavipushp-bridals'),
        'manage_options',
        'kavipushp-invoices',
        'kavipushp_render_invoices_enhanced'
    );

    add_submenu_page(
        'kavipushp-dashboard',
        __('Settings', 'kavipushp-bridals'),
        __('Settings', 'kavipushp-bridals'),
        'manage_options',
        'kavipushp-settings',
        'kavipushp_render_settings_enhanced'
    );
}
add_action('admin_menu', 'kavipushp_admin_menu');

/**
 * Enqueue Admin Styles
 */
function kavipushp_admin_dashboard_styles($hook) {
    if (strpos($hook, 'kavipushp') === false) {
        return;
    }

    wp_enqueue_style('kavipushp-admin-dashboard', KAVIPUSHP_URI . '/assets/css/admin-dashboard.css', array(), KAVIPUSHP_VERSION);
    wp_enqueue_script('kavipushp-admin-dashboard', KAVIPUSHP_URI . '/assets/js/admin-dashboard.js', array('jquery'), KAVIPUSHP_VERSION, true);

    wp_localize_script('kavipushp-admin-dashboard', 'kavipushp_admin', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('kavipushp_admin_nonce'),
    ));
}
add_action('admin_enqueue_scripts', 'kavipushp_admin_dashboard_styles');

/**
 * Render Dashboard Page
 */
function kavipushp_render_dashboard() {
    $current_user = wp_get_current_user();

    // Get statistics
    $total_customers = kavipushp_get_total_customers();
    $active_bookings = kavipushp_get_active_bookings_count();
    $inventory_items = wp_count_posts('bridal_set')->publish;

    // Get recent bookings
    $recent_bookings = kavipushp_get_recent_bookings(10);
    ?>
    <div class="kavipushp-admin-wrap">
        <!-- Header -->
        <div class="kp-admin-header">
            <div class="kp-user-info">
                <i class="dashicons dashicons-admin-users"></i>
                <span><?php echo esc_html($current_user->user_email); ?></span>
            </div>
            <a href="<?php echo wp_logout_url(admin_url()); ?>" class="kp-logout-btn">
                <i class="dashicons dashicons-exit"></i> <?php _e('Logout', 'kavipushp-bridals'); ?>
            </a>
        </div>

        <!-- Page Title -->
        <div class="kp-page-title">
            <h1><?php _e('Dashboard', 'kavipushp-bridals'); ?></h1>
            <p><?php _e('Welcome to Kavipushp Jewels Rental Management', 'kavipushp-bridals'); ?></p>
        </div>

        <!-- Stats Cards -->
        <div class="kp-stats-grid">
            <div class="kp-stat-card">
                <div class="kp-stat-content">
                    <span class="kp-stat-label"><?php _e('Total Customers', 'kavipushp-bridals'); ?></span>
                    <span class="kp-stat-value"><?php echo number_format($total_customers); ?></span>
                </div>
                <div class="kp-stat-icon blue">
                    <i class="dashicons dashicons-groups"></i>
                </div>
            </div>

            <div class="kp-stat-card">
                <div class="kp-stat-content">
                    <span class="kp-stat-label"><?php _e('Active Bookings', 'kavipushp-bridals'); ?></span>
                    <span class="kp-stat-value"><?php echo number_format($active_bookings); ?></span>
                </div>
                <div class="kp-stat-icon orange">
                    <i class="dashicons dashicons-calendar-alt"></i>
                </div>
            </div>

            <div class="kp-stat-card">
                <div class="kp-stat-content">
                    <span class="kp-stat-label"><?php _e('Inventory Items', 'kavipushp-bridals'); ?></span>
                    <span class="kp-stat-value"><?php echo number_format($inventory_items); ?></span>
                </div>
                <div class="kp-stat-icon purple">
                    <i class="dashicons dashicons-archive"></i>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="kp-dashboard-grid">
            <!-- Booked Sets Section -->
            <div class="kp-card kp-booked-sets">
                <div class="kp-card-header">
                    <h2><i class="dashicons dashicons-calendar"></i> <?php _e('Booked Sets', 'kavipushp-bridals'); ?></h2>
                </div>
                <div class="kp-card-body">
                    <!-- Filters -->
                    <div class="kp-filters">
                        <div class="kp-filter-group">
                            <label><?php _e('Search Customer', 'kavipushp-bridals'); ?></label>
                            <input type="text" id="search-customer" placeholder="<?php esc_attr_e('Search by customer name...', 'kavipushp-bridals'); ?>">
                        </div>
                        <div class="kp-filter-group">
                            <label><?php _e('Date From', 'kavipushp-bridals'); ?></label>
                            <input type="date" id="date-from">
                        </div>
                        <div class="kp-filter-group">
                            <label><?php _e('Date To', 'kavipushp-bridals'); ?></label>
                            <input type="date" id="date-to">
                        </div>
                    </div>

                    <!-- Bookings Table -->
                    <div class="kp-table-info">
                        <?php printf(__('Showing %d of %d bookings', 'kavipushp-bridals'), count($recent_bookings), kavipushp_get_total_bookings_count()); ?>
                    </div>

                    <table class="kp-table" id="bookings-table">
                        <thead>
                            <tr>
                                <th><?php _e('Customer Name', 'kavipushp-bridals'); ?></th>
                                <th><?php _e('Booking Date', 'kavipushp-bridals'); ?></th>
                                <th><?php _e('Function Date', 'kavipushp-bridals'); ?></th>
                                <th><?php _e('Return Date', 'kavipushp-bridals'); ?></th>
                                <th><?php _e('Status', 'kavipushp-bridals'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recent_bookings)): ?>
                                <?php foreach ($recent_bookings as $booking):
                                    $customer_name = get_post_meta($booking->ID, '_customer_name', true);
                                    $booking_date = get_the_date('d/m/Y', $booking);
                                    $pickup_date = get_post_meta($booking->ID, '_pickup_date', true);
                                    $return_date = get_post_meta($booking->ID, '_return_date', true);
                                    $status = get_post_meta($booking->ID, '_booking_status', true);
                                ?>
                                <tr>
                                    <td><?php echo esc_html($customer_name); ?></td>
                                    <td><?php echo esc_html($booking_date); ?></td>
                                    <td><?php echo $pickup_date ? date('d/m/Y', strtotime($pickup_date)) : '-'; ?></td>
                                    <td><?php echo $return_date ? date('d/m/Y', strtotime($return_date)) : '-'; ?></td>
                                    <td>
                                        <span class="kp-status kp-status-<?php echo esc_attr($status); ?>">
                                            <?php echo esc_html(strtoupper($status)); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="kp-no-data"><?php _e('No bookings found', 'kavipushp-bridals'); ?></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Quick Actions Section -->
            <div class="kp-card kp-quick-actions">
                <div class="kp-card-header">
                    <h2><i class="dashicons dashicons-screenoptions"></i> <?php _e('Quick Actions', 'kavipushp-bridals'); ?></h2>
                </div>
                <div class="kp-card-body">
                    <a href="<?php echo admin_url('admin.php?page=kavipushp-customers&action=add'); ?>" class="kp-action-btn">
                        <div class="kp-action-icon yellow">
                            <i class="dashicons dashicons-admin-users"></i>
                        </div>
                        <div class="kp-action-text">
                            <strong><?php _e('Add New Customer', 'kavipushp-bridals'); ?></strong>
                            <span><?php _e('Register a new customer', 'kavipushp-bridals'); ?></span>
                        </div>
                    </a>

                    <a href="<?php echo admin_url('post-new.php?post_type=booking'); ?>" class="kp-action-btn">
                        <div class="kp-action-icon blue">
                            <i class="dashicons dashicons-calendar-alt"></i>
                        </div>
                        <div class="kp-action-text">
                            <strong><?php _e('Create Booking', 'kavipushp-bridals'); ?></strong>
                            <span><?php _e('Book jewelry for a customer', 'kavipushp-bridals'); ?></span>
                        </div>
                    </a>

                    <a href="<?php echo admin_url('edit.php?post_type=bridal_set'); ?>" class="kp-action-btn">
                        <div class="kp-action-icon purple">
                            <i class="dashicons dashicons-archive"></i>
                        </div>
                        <div class="kp-action-text">
                            <strong><?php _e('Manage Inventory', 'kavipushp-bridals'); ?></strong>
                            <span><?php _e('Update jewelry inventory', 'kavipushp-bridals'); ?></span>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Render Customers Page
 */
function kavipushp_render_customers() {
    $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';

    // Handle form submission
    if (isset($_POST['save_customer']) && check_admin_referer('save_customer')) {
        kavipushp_save_customer($_POST);
        echo '<div class="notice notice-success"><p>' . __('Customer saved successfully!', 'kavipushp-bridals') . '</p></div>';
    }

    ?>
    <div class="kavipushp-admin-wrap">
        <div class="kp-page-title">
            <h1><?php _e('Customers', 'kavipushp-bridals'); ?></h1>
            <p><?php _e('Manage your customers', 'kavipushp-bridals'); ?></p>
        </div>

        <?php if ($action === 'add' || $action === 'edit'): ?>
            <?php
            $customer_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            $customer = $customer_id ? kavipushp_get_customer($customer_id) : null;
            ?>
            <div class="kp-card">
                <div class="kp-card-header">
                    <h2><?php echo $customer ? __('Edit Customer', 'kavipushp-bridals') : __('Add New Customer', 'kavipushp-bridals'); ?></h2>
                </div>
                <div class="kp-card-body">
                    <form method="post" class="kp-form">
                        <?php wp_nonce_field('save_customer'); ?>
                        <input type="hidden" name="customer_id" value="<?php echo esc_attr($customer_id); ?>">

                        <div class="kp-form-row">
                            <div class="kp-form-group">
                                <label><?php _e('Full Name', 'kavipushp-bridals'); ?> *</label>
                                <input type="text" name="customer_name" value="<?php echo $customer ? esc_attr($customer['name']) : ''; ?>" required>
                            </div>
                            <div class="kp-form-group">
                                <label><?php _e('Phone Number', 'kavipushp-bridals'); ?> *</label>
                                <input type="tel" name="customer_phone" value="<?php echo $customer ? esc_attr($customer['phone']) : ''; ?>" required>
                            </div>
                        </div>

                        <div class="kp-form-row">
                            <div class="kp-form-group">
                                <label><?php _e('Email', 'kavipushp-bridals'); ?></label>
                                <input type="email" name="customer_email" value="<?php echo $customer ? esc_attr($customer['email']) : ''; ?>">
                            </div>
                            <div class="kp-form-group">
                                <label><?php _e('Alternate Phone', 'kavipushp-bridals'); ?></label>
                                <input type="tel" name="customer_alt_phone" value="<?php echo $customer ? esc_attr($customer['alt_phone']) : ''; ?>">
                            </div>
                        </div>

                        <div class="kp-form-group">
                            <label><?php _e('Address', 'kavipushp-bridals'); ?></label>
                            <textarea name="customer_address" rows="3"><?php echo $customer ? esc_textarea($customer['address']) : ''; ?></textarea>
                        </div>

                        <div class="kp-form-row">
                            <div class="kp-form-group">
                                <label><?php _e('ID Proof Type', 'kavipushp-bridals'); ?></label>
                                <select name="id_proof_type">
                                    <option value=""><?php _e('Select...', 'kavipushp-bridals'); ?></option>
                                    <option value="aadhar" <?php selected($customer ? $customer['id_proof_type'] : '', 'aadhar'); ?>><?php _e('Aadhar Card', 'kavipushp-bridals'); ?></option>
                                    <option value="pan" <?php selected($customer ? $customer['id_proof_type'] : '', 'pan'); ?>><?php _e('PAN Card', 'kavipushp-bridals'); ?></option>
                                    <option value="driving" <?php selected($customer ? $customer['id_proof_type'] : '', 'driving'); ?>><?php _e('Driving License', 'kavipushp-bridals'); ?></option>
                                    <option value="passport" <?php selected($customer ? $customer['id_proof_type'] : '', 'passport'); ?>><?php _e('Passport', 'kavipushp-bridals'); ?></option>
                                    <option value="voter" <?php selected($customer ? $customer['id_proof_type'] : '', 'voter'); ?>><?php _e('Voter ID', 'kavipushp-bridals'); ?></option>
                                </select>
                            </div>
                            <div class="kp-form-group">
                                <label><?php _e('ID Proof Number', 'kavipushp-bridals'); ?></label>
                                <input type="text" name="id_proof_number" value="<?php echo $customer ? esc_attr($customer['id_proof_number']) : ''; ?>">
                            </div>
                        </div>

                        <div class="kp-form-group">
                            <label><?php _e('Notes', 'kavipushp-bridals'); ?></label>
                            <textarea name="customer_notes" rows="2"><?php echo $customer ? esc_textarea($customer['notes']) : ''; ?></textarea>
                        </div>

                        <div class="kp-form-actions">
                            <button type="submit" name="save_customer" class="button button-primary"><?php _e('Save Customer', 'kavipushp-bridals'); ?></button>
                            <a href="<?php echo admin_url('admin.php?page=kavipushp-customers'); ?>" class="button"><?php _e('Cancel', 'kavipushp-bridals'); ?></a>
                        </div>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <!-- Customers List -->
            <div class="kp-card">
                <div class="kp-card-header">
                    <h2><?php _e('All Customers', 'kavipushp-bridals'); ?></h2>
                    <a href="<?php echo admin_url('admin.php?page=kavipushp-customers&action=add'); ?>" class="button button-primary">
                        <i class="dashicons dashicons-plus-alt2"></i> <?php _e('Add New', 'kavipushp-bridals'); ?>
                    </a>
                </div>
                <div class="kp-card-body">
                    <div class="kp-filters" style="margin-bottom: 20px;">
                        <input type="text" id="search-customers" placeholder="<?php esc_attr_e('Search customers...', 'kavipushp-bridals'); ?>" style="width: 300px;">
                    </div>

                    <table class="kp-table">
                        <thead>
                            <tr>
                                <th><?php _e('Name', 'kavipushp-bridals'); ?></th>
                                <th><?php _e('Phone', 'kavipushp-bridals'); ?></th>
                                <th><?php _e('Email', 'kavipushp-bridals'); ?></th>
                                <th><?php _e('Total Bookings', 'kavipushp-bridals'); ?></th>
                                <th><?php _e('Actions', 'kavipushp-bridals'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $customers = kavipushp_get_all_customers();
                            if (!empty($customers)):
                                foreach ($customers as $customer):
                            ?>
                            <tr>
                                <td><?php echo esc_html($customer['name']); ?></td>
                                <td><?php echo esc_html($customer['phone']); ?></td>
                                <td><?php echo esc_html($customer['email']); ?></td>
                                <td><?php echo esc_html($customer['booking_count']); ?></td>
                                <td>
                                    <a href="<?php echo admin_url('admin.php?page=kavipushp-customers&action=edit&id=' . $customer['id']); ?>" class="button button-small"><?php _e('Edit', 'kavipushp-bridals'); ?></a>
                                    <a href="<?php echo admin_url('admin.php?page=kavipushp-customers&action=view&id=' . $customer['id']); ?>" class="button button-small"><?php _e('View', 'kavipushp-bridals'); ?></a>
                                </td>
                            </tr>
                            <?php
                                endforeach;
                            else:
                            ?>
                            <tr>
                                <td colspan="5" class="kp-no-data"><?php _e('No customers found', 'kavipushp-bridals'); ?></td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Render Availability Page
 */
function kavipushp_render_availability() {
    ?>
    <div class="kavipushp-admin-wrap">
        <div class="kp-page-title">
            <h1><?php _e('Availability of Sets', 'kavipushp-bridals'); ?></h1>
            <p><?php _e('Check and manage jewelry set availability', 'kavipushp-bridals'); ?></p>
        </div>

        <div class="kp-card">
            <div class="kp-card-header">
                <h2><?php _e('Set Availability Calendar', 'kavipushp-bridals'); ?></h2>
            </div>
            <div class="kp-card-body">
                <div class="kp-filters" style="margin-bottom: 20px;">
                    <div class="kp-filter-group">
                        <label><?php _e('Select Set', 'kavipushp-bridals'); ?></label>
                        <select id="availability-set">
                            <option value=""><?php _e('All Sets', 'kavipushp-bridals'); ?></option>
                            <?php
                            $sets = get_posts(array('post_type' => 'bridal_set', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC'));
                            foreach ($sets as $set):
                                $set_code = get_post_meta($set->ID, '_set_id', true);
                            ?>
                            <option value="<?php echo $set->ID; ?>"><?php echo esc_html($set->post_title); ?> <?php echo $set_code ? '(' . $set_code . ')' : ''; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="kp-filter-group">
                        <label><?php _e('Month', 'kavipushp-bridals'); ?></label>
                        <input type="month" id="availability-month" value="<?php echo date('Y-m'); ?>">
                    </div>
                </div>

                <!-- Availability Status Legend -->
                <div class="kp-legend" style="margin-bottom: 20px;">
                    <span class="kp-legend-item"><span class="kp-dot available"></span> <?php _e('Available', 'kavipushp-bridals'); ?></span>
                    <span class="kp-legend-item"><span class="kp-dot booked"></span> <?php _e('Booked', 'kavipushp-bridals'); ?></span>
                    <span class="kp-legend-item"><span class="kp-dot maintenance"></span> <?php _e('Maintenance', 'kavipushp-bridals'); ?></span>
                </div>

                <!-- Sets Availability Table -->
                <table class="kp-table">
                    <thead>
                        <tr>
                            <th><?php _e('Set ID', 'kavipushp-bridals'); ?></th>
                            <th><?php _e('Set Name', 'kavipushp-bridals'); ?></th>
                            <th><?php _e('Category', 'kavipushp-bridals'); ?></th>
                            <th><?php _e('Status', 'kavipushp-bridals'); ?></th>
                            <th><?php _e('Next Available', 'kavipushp-bridals'); ?></th>
                            <th><?php _e('Actions', 'kavipushp-bridals'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($sets as $set):
                            $set_code = get_post_meta($set->ID, '_set_id', true);
                            $availability = get_post_meta($set->ID, '_availability', true);
                            $categories = get_the_terms($set->ID, 'bridal_category');
                            $next_available = kavipushp_get_next_available_date($set->ID);
                        ?>
                        <tr>
                            <td><?php echo esc_html($set_code ?: 'N/A'); ?></td>
                            <td><?php echo esc_html($set->post_title); ?></td>
                            <td><?php echo $categories && !is_wp_error($categories) ? esc_html($categories[0]->name) : '-'; ?></td>
                            <td>
                                <span class="kp-status kp-status-<?php echo esc_attr($availability); ?>">
                                    <?php echo esc_html(ucfirst($availability ?: 'available')); ?>
                                </span>
                            </td>
                            <td><?php echo $next_available ? date('d/m/Y', strtotime($next_available)) : __('Now', 'kavipushp-bridals'); ?></td>
                            <td>
                                <a href="<?php echo admin_url('post.php?post=' . $set->ID . '&action=edit'); ?>" class="button button-small"><?php _e('Edit', 'kavipushp-bridals'); ?></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Render Invoices Page
 */
function kavipushp_render_invoices() {
    ?>
    <div class="kavipushp-admin-wrap">
        <div class="kp-page-title">
            <h1><?php _e('Invoices', 'kavipushp-bridals'); ?></h1>
            <p><?php _e('Manage rental invoices', 'kavipushp-bridals'); ?></p>
        </div>

        <div class="kp-card">
            <div class="kp-card-header">
                <h2><?php _e('All Invoices', 'kavipushp-bridals'); ?></h2>
            </div>
            <div class="kp-card-body">
                <table class="kp-table">
                    <thead>
                        <tr>
                            <th><?php _e('Invoice #', 'kavipushp-bridals'); ?></th>
                            <th><?php _e('Customer', 'kavipushp-bridals'); ?></th>
                            <th><?php _e('Date', 'kavipushp-bridals'); ?></th>
                            <th><?php _e('Amount', 'kavipushp-bridals'); ?></th>
                            <th><?php _e('Status', 'kavipushp-bridals'); ?></th>
                            <th><?php _e('Actions', 'kavipushp-bridals'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $bookings = get_posts(array(
                            'post_type'      => 'booking',
                            'posts_per_page' => 20,
                            'meta_query'     => array(
                                array(
                                    'key'     => '_booking_status',
                                    'value'   => array('confirmed', 'completed', 'picked_up', 'returned'),
                                    'compare' => 'IN',
                                ),
                            ),
                        ));

                        if (!empty($bookings)):
                            foreach ($bookings as $booking):
                                $customer_name = get_post_meta($booking->ID, '_customer_name', true);
                                $total = get_post_meta($booking->ID, '_total_amount', true);
                                $status = get_post_meta($booking->ID, '_booking_status', true);
                        ?>
                        <tr>
                            <td>INV-<?php echo str_pad($booking->ID, 5, '0', STR_PAD_LEFT); ?></td>
                            <td><?php echo esc_html($customer_name); ?></td>
                            <td><?php echo get_the_date('d/m/Y', $booking); ?></td>
                            <td><?php echo number_format($total, 2); ?></td>
                            <td>
                                <span class="kp-status kp-status-<?php echo $status === 'completed' ? 'paid' : 'pending'; ?>">
                                    <?php echo $status === 'completed' ? __('PAID', 'kavipushp-bridals') : __('PENDING', 'kavipushp-bridals'); ?>
                                </span>
                            </td>
                            <td>
                                <a href="#" class="button button-small" onclick="kavipushpPrintInvoice(<?php echo $booking->ID; ?>)"><?php _e('Print', 'kavipushp-bridals'); ?></a>
                            </td>
                        </tr>
                        <?php
                            endforeach;
                        else:
                        ?>
                        <tr>
                            <td colspan="6" class="kp-no-data"><?php _e('No invoices found', 'kavipushp-bridals'); ?></td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Render Settings Page
 */
function kavipushp_render_settings() {
    // Save settings
    if (isset($_POST['save_settings']) && check_admin_referer('kavipushp_settings')) {
        update_option('kavipushp_business_name', sanitize_text_field($_POST['business_name']));
        update_option('kavipushp_business_phone', sanitize_text_field($_POST['business_phone']));
        update_option('kavipushp_business_email', sanitize_email($_POST['business_email']));
        update_option('kavipushp_business_address', sanitize_textarea_field($_POST['business_address']));
        update_option('kavipushp_gst_number', sanitize_text_field($_POST['gst_number']));
        update_option('kavipushp_terms', wp_kses_post($_POST['rental_terms']));

        echo '<div class="notice notice-success"><p>' . __('Settings saved successfully!', 'kavipushp-bridals') . '</p></div>';
    }
    ?>
    <div class="kavipushp-admin-wrap">
        <div class="kp-page-title">
            <h1><?php _e('Settings', 'kavipushp-bridals'); ?></h1>
            <p><?php _e('Configure your rental business settings', 'kavipushp-bridals'); ?></p>
        </div>

        <div class="kp-card">
            <div class="kp-card-header">
                <h2><?php _e('Business Information', 'kavipushp-bridals'); ?></h2>
            </div>
            <div class="kp-card-body">
                <form method="post" class="kp-form">
                    <?php wp_nonce_field('kavipushp_settings'); ?>

                    <div class="kp-form-row">
                        <div class="kp-form-group">
                            <label><?php _e('Business Name', 'kavipushp-bridals'); ?></label>
                            <input type="text" name="business_name" value="<?php echo esc_attr(get_option('kavipushp_business_name', 'Kavipushp Jewels Rental')); ?>">
                        </div>
                        <div class="kp-form-group">
                            <label><?php _e('Phone Number', 'kavipushp-bridals'); ?></label>
                            <input type="tel" name="business_phone" value="<?php echo esc_attr(get_option('kavipushp_business_phone')); ?>">
                        </div>
                    </div>

                    <div class="kp-form-row">
                        <div class="kp-form-group">
                            <label><?php _e('Email', 'kavipushp-bridals'); ?></label>
                            <input type="email" name="business_email" value="<?php echo esc_attr(get_option('kavipushp_business_email')); ?>">
                        </div>
                        <div class="kp-form-group">
                            <label><?php _e('GST Number', 'kavipushp-bridals'); ?></label>
                            <input type="text" name="gst_number" value="<?php echo esc_attr(get_option('kavipushp_gst_number')); ?>">
                        </div>
                    </div>

                    <div class="kp-form-group">
                        <label><?php _e('Business Address', 'kavipushp-bridals'); ?></label>
                        <textarea name="business_address" rows="3"><?php echo esc_textarea(get_option('kavipushp_business_address')); ?></textarea>
                    </div>

                    <div class="kp-form-group">
                        <label><?php _e('Rental Terms & Conditions', 'kavipushp-bridals'); ?></label>
                        <textarea name="rental_terms" rows="5"><?php echo esc_textarea(get_option('kavipushp_terms')); ?></textarea>
                    </div>

                    <div class="kp-form-actions">
                        <button type="submit" name="save_settings" class="button button-primary"><?php _e('Save Settings', 'kavipushp-bridals'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Helper Functions
 */

function kavipushp_get_total_customers() {
    global $wpdb;
    $table = $wpdb->prefix . 'kavipushp_customers';

    // Check if custom table exists
    if ($wpdb->get_var("SHOW TABLES LIKE '$table'") === $table) {
        return $wpdb->get_var("SELECT COUNT(*) FROM $table");
    }

    // Fallback: count unique customer emails from bookings
    $emails = $wpdb->get_col("SELECT DISTINCT meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_customer_email'");
    return count($emails);
}

function kavipushp_get_active_bookings_count() {
    $args = array(
        'post_type'      => 'booking',
        'posts_per_page' => -1,
        'meta_query'     => array(
            array(
                'key'     => '_booking_status',
                'value'   => array('confirmed', 'picked_up'),
                'compare' => 'IN',
            ),
        ),
    );
    $query = new WP_Query($args);
    return $query->found_posts;
}

function kavipushp_get_total_bookings_count() {
    return wp_count_posts('booking')->publish;
}

function kavipushp_get_recent_bookings($limit = 10) {
    return get_posts(array(
        'post_type'      => 'booking',
        'posts_per_page' => $limit,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ));
}

function kavipushp_get_next_available_date($set_id) {
    $bookings = get_posts(array(
        'post_type'      => 'booking',
        'posts_per_page' => 1,
        'meta_query'     => array(
            'relation' => 'AND',
            array(
                'key'   => '_bridal_set_id',
                'value' => $set_id,
            ),
            array(
                'key'     => '_booking_status',
                'value'   => array('confirmed', 'picked_up'),
                'compare' => 'IN',
            ),
            array(
                'key'     => '_return_date',
                'value'   => date('Y-m-d'),
                'compare' => '>=',
                'type'    => 'DATE',
            ),
        ),
        'meta_key'       => '_return_date',
        'orderby'        => 'meta_value',
        'order'          => 'ASC',
    ));

    if (!empty($bookings)) {
        $return_date = get_post_meta($bookings[0]->ID, '_return_date', true);
        return date('Y-m-d', strtotime($return_date . ' +1 day'));
    }

    return null;
}

function kavipushp_get_all_customers() {
    global $wpdb;

    // Get unique customers from bookings
    $results = $wpdb->get_results("
        SELECT DISTINCT
            pm1.meta_value as name,
            pm2.meta_value as email,
            pm3.meta_value as phone
        FROM {$wpdb->posts} p
        LEFT JOIN {$wpdb->postmeta} pm1 ON p.ID = pm1.post_id AND pm1.meta_key = '_customer_name'
        LEFT JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_customer_email'
        LEFT JOIN {$wpdb->postmeta} pm3 ON p.ID = pm3.post_id AND pm3.meta_key = '_customer_phone'
        WHERE p.post_type = 'booking' AND p.post_status = 'publish'
        AND pm2.meta_value IS NOT NULL AND pm2.meta_value != ''
        ORDER BY pm1.meta_value ASC
    ");

    $customers = array();
    foreach ($results as $row) {
        $booking_count = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) FROM {$wpdb->postmeta}
            WHERE meta_key = '_customer_email' AND meta_value = %s
        ", $row->email));

        $customers[] = array(
            'id'            => md5($row->email),
            'name'          => $row->name,
            'email'         => $row->email,
            'phone'         => $row->phone,
            'booking_count' => $booking_count,
        );
    }

    return $customers;
}

function kavipushp_get_customer($id) {
    // For now, return placeholder
    return null;
}

function kavipushp_save_customer($data) {
    // Save customer to custom table or as user meta
    // Implementation depends on requirements
}

/**
 * Create Custom Tables on Theme Activation
 */
function kavipushp_create_tables() {
    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'kavipushp_customers';

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        name varchar(255) NOT NULL,
        email varchar(255),
        phone varchar(20) NOT NULL,
        alt_phone varchar(20),
        address text,
        id_proof_type varchar(50),
        id_proof_number varchar(100),
        notes text,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY email (email),
        KEY phone (phone)
    ) $charset_collate;";

    // Invoices table
    $invoices_table = $wpdb->prefix . 'kavipushp_invoices';

    $sql .= "\nCREATE TABLE IF NOT EXISTS $invoices_table (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        booking_id bigint(20) NOT NULL,
        invoice_number varchar(50) NOT NULL,
        invoice_type varchar(20) NOT NULL,
        customer_name varchar(255),
        customer_phone varchar(20),
        customer_email varchar(255),
        customer_address text,
        set_name varchar(255),
        set_code varchar(100),
        function_date date,
        pickup_date date,
        return_date date,
        rent_amount decimal(10,2) DEFAULT 0,
        booking_amount decimal(10,2) DEFAULT 0,
        security_deposit decimal(10,2) DEFAULT 0,
        grand_total decimal(10,2) DEFAULT 0,
        customization_notes text,
        status varchar(20) DEFAULT 'generated',
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY booking_id (booking_id),
        KEY invoice_number (invoice_number)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
add_action('after_switch_theme', 'kavipushp_create_tables');

// Ensure invoices table exists on admin init
add_action('admin_init', function() {
    global $wpdb;
    $invoices_table = $wpdb->prefix . 'kavipushp_invoices';
    if ($wpdb->get_var("SHOW TABLES LIKE '$invoices_table'") !== $invoices_table) {
        kavipushp_create_tables();
    }
});

/**
 * AJAX: Save Invoice to Database
 */
function kavipushp_save_invoice() {
    check_ajax_referer('kavipushp_admin_nonce', 'nonce');

    global $wpdb;
    $table = $wpdb->prefix . 'kavipushp_invoices';

    $booking_id = intval($_POST['booking_id']);
    $invoice_type = sanitize_text_field($_POST['invoice_type']);

    if (!$booking_id || !$invoice_type) {
        wp_send_json_error(array('message' => 'Missing required fields'));
    }

    // Build invoice number prefix based on type
    $prefix_map = array('booking' => 'BK', 'pickup' => 'PK', 'final' => 'FN');
    $prefix = isset($prefix_map[$invoice_type]) ? $prefix_map[$invoice_type] : 'INV';
    $invoice_number = $prefix . '-' . str_pad($booking_id, 5, '0', STR_PAD_LEFT);

    // Check if this exact invoice already exists
    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM $table WHERE booking_id = %d AND invoice_type = %s",
        $booking_id, $invoice_type
    ));

    if ($exists) {
        wp_send_json_error(array('message' => 'Invoice already saved for this booking and type'));
    }

    $data = array(
        'booking_id'         => $booking_id,
        'invoice_number'     => $invoice_number,
        'invoice_type'       => $invoice_type,
        'customer_name'      => sanitize_text_field($_POST['customer_name']),
        'customer_phone'     => sanitize_text_field($_POST['customer_phone']),
        'customer_email'     => sanitize_email($_POST['customer_email']),
        'customer_address'   => sanitize_textarea_field($_POST['customer_address']),
        'set_name'           => sanitize_text_field($_POST['set_name']),
        'set_code'           => sanitize_text_field($_POST['set_code']),
        'function_date'      => sanitize_text_field($_POST['function_date']) ?: null,
        'pickup_date'        => sanitize_text_field($_POST['pickup_date']) ?: null,
        'return_date'        => sanitize_text_field($_POST['return_date']) ?: null,
        'rent_amount'        => floatval($_POST['rent_amount']),
        'booking_amount'     => floatval($_POST['booking_amount']),
        'security_deposit'   => floatval($_POST['security_deposit']),
        'grand_total'        => floatval($_POST['grand_total']),
        'customization_notes'=> sanitize_textarea_field($_POST['customization_notes']),
        'status'             => 'generated',
    );

    $result = $wpdb->insert($table, $data);

    if ($result) {
        wp_send_json_success(array(
            'message' => 'Invoice saved successfully!',
            'invoice_id' => $wpdb->insert_id,
            'invoice_number' => $invoice_number,
        ));
    } else {
        wp_send_json_error(array('message' => 'Failed to save invoice'));
    }
}
add_action('wp_ajax_kavipushp_save_invoice', 'kavipushp_save_invoice');

/**
 * AJAX: View Saved Invoice
 */
function kavipushp_view_saved_invoice() {
    check_ajax_referer('kavipushp_admin_nonce', 'nonce');

    global $wpdb;
    $table = $wpdb->prefix . 'kavipushp_invoices';

    $invoice_id = intval($_POST['invoice_id']);
    if (!$invoice_id) {
        wp_send_json_error(array('message' => 'Invalid invoice ID'));
    }

    $invoice = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $invoice_id));

    if (!$invoice) {
        wp_send_json_error(array('message' => 'Invoice not found'));
    }

    wp_send_json_success(array(
        'id'                 => $invoice->id,
        'booking_id'         => $invoice->booking_id,
        'invoice_number'     => $invoice->invoice_number,
        'invoice_type'       => $invoice->invoice_type,
        'customer_name'      => $invoice->customer_name,
        'customer_phone'     => $invoice->customer_phone,
        'customer_email'     => $invoice->customer_email,
        'customer_address'   => $invoice->customer_address,
        'set_name'           => $invoice->set_name,
        'set_code'           => $invoice->set_code,
        'function_date'      => $invoice->function_date,
        'pickup_date'        => $invoice->pickup_date,
        'return_date'        => $invoice->return_date,
        'rent_amount'        => $invoice->rent_amount,
        'booking_amount'     => $invoice->booking_amount,
        'security_deposit'   => $invoice->security_deposit,
        'grand_total'        => $invoice->grand_total,
        'customization_notes'=> $invoice->customization_notes,
        'status'             => $invoice->status,
        'created_at'         => $invoice->created_at,
    ));
}
add_action('wp_ajax_kavipushp_view_saved_invoice', 'kavipushp_view_saved_invoice');

/**
 * AJAX: Delete Saved Invoice
 */
function kavipushp_delete_saved_invoice() {
    check_ajax_referer('kavipushp_admin_nonce', 'nonce');

    global $wpdb;
    $table = $wpdb->prefix . 'kavipushp_invoices';

    $invoice_id = intval($_POST['invoice_id']);
    if (!$invoice_id) {
        wp_send_json_error(array('message' => 'Invalid invoice ID'));
    }

    $result = $wpdb->delete($table, array('id' => $invoice_id));

    if ($result) {
        wp_send_json_success(array('message' => 'Invoice deleted successfully'));
    } else {
        wp_send_json_error(array('message' => 'Failed to delete invoice'));
    }
}
add_action('wp_ajax_kavipushp_delete_saved_invoice', 'kavipushp_delete_saved_invoice');

/**
 * AJAX: Get Invoice Data
 */
function kavipushp_get_invoice() {
    check_ajax_referer('kavipushp_admin_nonce', 'nonce');

    $booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;

    if (!$booking_id) {
        wp_send_json_error();
    }

    $booking = get_post($booking_id);
    if (!$booking || $booking->post_type !== 'booking') {
        wp_send_json_error();
    }

    $customer_name = get_post_meta($booking_id, '_customer_name', true);
    $customer_email = get_post_meta($booking_id, '_customer_email', true);
    $customer_phone = get_post_meta($booking_id, '_customer_phone', true);
    $set_id = get_post_meta($booking_id, '_bridal_set_id', true);
    $set = $set_id ? get_post($set_id) : null;
    $set_code = $set_id ? get_post_meta($set_id, '_set_id', true) : '';
    $pickup_date = get_post_meta($booking_id, '_pickup_date', true);
    $return_date = get_post_meta($booking_id, '_return_date', true);
    $total = get_post_meta($booking_id, '_total_amount', true);
    $rental_price = $set_id ? get_post_meta($set_id, '_rental_price', true) : 0;
    $deposit = $set_id ? get_post_meta($set_id, '_deposit_amount', true) : 0;

    $days = 1;
    if ($pickup_date && $return_date) {
        $days = ceil((strtotime($return_date) - strtotime($pickup_date)) / (60 * 60 * 24)) + 1;
    }

    $rental_amount = $days * floatval($rental_price);

    wp_send_json_success(array(
        'invoice_number' => 'INV-' . str_pad($booking_id, 5, '0', STR_PAD_LEFT),
        'date'           => get_the_date('d/m/Y', $booking),
        'customer_name'  => $customer_name,
        'customer_email' => $customer_email,
        'customer_phone' => $customer_phone,
        'set_name'       => $set ? $set->post_title : 'N/A',
        'set_id'         => $set_code ?: 'N/A',
        'pickup_date'    => $pickup_date ? date('d/m/Y', strtotime($pickup_date)) : 'N/A',
        'return_date'    => $return_date ? date('d/m/Y', strtotime($return_date)) : 'N/A',
        'days'           => $days,
        'rate_per_day'   => number_format($rental_price, 2),
        'rental_amount'  => number_format($rental_amount, 2),
        'deposit'        => number_format($deposit, 2),
        'total'          => number_format($total, 2),
    ));
}
add_action('wp_ajax_kavipushp_get_invoice', 'kavipushp_get_invoice');
