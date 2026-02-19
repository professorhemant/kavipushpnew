<?php
/**
 * Admin Pages - Extended functionality matching Kavipushp app
 *
 * @package Kavipushp_Bridals
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handle booking delete early before headers are sent
 */
add_action('admin_init', function () {
    // Handle booking delete
    if (isset($_GET['page']) && $_GET['page'] === 'kavipushp-bookings'
        && isset($_GET['action']) && $_GET['action'] === 'delete'
        && isset($_GET['id'])
    ) {
        $booking_id = intval($_GET['id']);
        check_admin_referer('delete_booking');
        if ($booking_id) {
            wp_delete_post($booking_id, true);
            wp_redirect(admin_url('admin.php?page=kavipushp-bookings&deleted=1'));
            exit;
        }
    }

    // Handle customer delete
    if (isset($_GET['page']) && $_GET['page'] === 'kavipushp-customers'
        && isset($_GET['action']) && $_GET['action'] === 'delete'
        && isset($_GET['id'])
    ) {
        $customer_id = intval($_GET['id']);
        check_admin_referer('delete_customer');
        if ($customer_id) {
            kavipushp_delete_customer($customer_id);
            wp_redirect(admin_url('admin.php?page=kavipushp-customers&deleted=1'));
            exit;
        }
    }
});

/**
 * Enhanced Customer Management Page
 */
function kavipushp_render_customers_enhanced() {
    $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
    $customer_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    // Handle form submission
    if (isset($_POST['save_customer']) && check_admin_referer('save_customer')) {
        $saved_id = kavipushp_save_customer_data($_POST);
        if ($saved_id) {
            echo '<div class="notice notice-success"><p>' . __('Customer saved successfully!', 'kavipushp-bridals') . '</p></div>';
        }
    }

    if (isset($_GET['deleted'])) {
        echo '<div class="notice notice-success is-dismissible"><p>' . __('Customer deleted successfully!', 'kavipushp-bridals') . '</p></div>';
    }

    ?>
    <div class="kavipushp-admin-wrap">
        <div class="kp-admin-header">
            <div class="kp-user-info">
                <i class="dashicons dashicons-admin-users"></i>
                <span><?php echo esc_html(wp_get_current_user()->user_email); ?></span>
            </div>
            <a href="<?php echo wp_logout_url(admin_url()); ?>" class="kp-logout-btn">
                <i class="dashicons dashicons-exit"></i> <?php _e('Logout', 'kavipushp-bridals'); ?>
            </a>
        </div>

        <div class="kp-page-title">
            <h1><?php _e('Customer Management', 'kavipushp-bridals'); ?></h1>
            <p><?php _e('Manage your customer database and contact information', 'kavipushp-bridals'); ?></p>
            <span class="kp-live-indicator"><span class="kp-dot-live"></span> <?php _e('Live data from database', 'kavipushp-bridals'); ?></span>
        </div>

        <?php if ($action === 'view' && $customer_id): ?>
            <?php $customer = kavipushp_get_customer_by_id($customer_id); ?>
            <?php if ($customer): ?>
            <div class="kp-card">
                <div class="kp-card-header" style="display:flex;justify-content:space-between;align-items:center;">
                    <h2><i class="dashicons dashicons-admin-users"></i> <?php echo esc_html($customer->full_name); ?></h2>
                    <div>
                        <a href="<?php echo admin_url('admin.php?page=kavipushp-customers&action=edit&id=' . $customer->id); ?>" class="button button-primary">
                            <i class="dashicons dashicons-edit"></i> <?php _e('Edit', 'kavipushp-bridals'); ?>
                        </a>
                        <a href="<?php echo admin_url('admin.php?page=kavipushp-customers'); ?>" class="button">
                            <i class="dashicons dashicons-arrow-left-alt"></i> <?php _e('Back to List', 'kavipushp-bridals'); ?>
                        </a>
                    </div>
                </div>
                <div class="kp-card-body">
                    <table class="widefat striped" style="border:none;">
                        <tr><th style="width:35%;"><?php _e('Full Name', 'kavipushp-bridals'); ?></th><td><?php echo esc_html($customer->full_name); ?></td></tr>
                        <tr><th><?php _e('Contact Number', 'kavipushp-bridals'); ?></th><td><?php echo esc_html($customer->phone); ?></td></tr>
                        <tr><th><?php _e('Email', 'kavipushp-bridals'); ?></th><td><?php echo esc_html($customer->email ?: 'N/A'); ?></td></tr>
                        <tr><th><?php _e('Address', 'kavipushp-bridals'); ?></th><td><?php echo esc_html($customer->address ?: 'N/A'); ?></td></tr>
                        <tr><th><?php _e('ID Proof Type', 'kavipushp-bridals'); ?></th><td><?php echo esc_html($customer->id_proof_type ?: 'N/A'); ?></td></tr>
                        <tr><th><?php _e('ID Proof Number', 'kavipushp-bridals'); ?></th><td><?php echo esc_html($customer->id_proof_number ?: 'N/A'); ?></td></tr>
                        <tr><th><?php _e('Function Date', 'kavipushp-bridals'); ?></th><td><?php echo $customer->function_date ? date('d/m/Y', strtotime($customer->function_date)) : 'N/A'; ?></td></tr>
                        <tr><th><?php _e('Pickup Date', 'kavipushp-bridals'); ?></th><td><?php echo $customer->pickup_date ? date('d/m/Y', strtotime($customer->pickup_date)) : 'N/A'; ?></td></tr>
                        <tr><th><?php _e('Return Date', 'kavipushp-bridals'); ?></th><td><?php echo $customer->return_date ? date('d/m/Y', strtotime($customer->return_date)) : 'N/A'; ?></td></tr>
                        <tr><th><?php _e('Booking Date', 'kavipushp-bridals'); ?></th><td><?php echo !empty($customer->booking_date) ? date('d/m/Y', strtotime($customer->booking_date)) : 'N/A'; ?></td></tr>
                        <tr><th><?php _e('Referral Source', 'kavipushp-bridals'); ?></th><td><?php echo esc_html(ucfirst($customer->referral_source ?: 'N/A')); ?></td></tr>
                        <tr><th><?php _e('Notes', 'kavipushp-bridals'); ?></th><td><?php echo esc_html($customer->notes ?: 'N/A'); ?></td></tr>
                    </table>
                </div>
            </div>
            <?php else: ?>
            <div class="notice notice-error"><p><?php _e('Customer not found.', 'kavipushp-bridals'); ?></p></div>
            <a href="<?php echo admin_url('admin.php?page=kavipushp-customers'); ?>" class="button"><?php _e('Back to List', 'kavipushp-bridals'); ?></a>
            <?php endif; ?>

        <?php elseif ($action === 'add' || $action === 'edit'): ?>
            <?php
            $customer = $customer_id ? kavipushp_get_customer_by_id($customer_id) : null;
            // Get all customers for dropdown selector
            $all_customers = kavipushp_get_all_customers_from_db();
            ?>
            <div class="kp-card">
                <div class="kp-card-header">
                    <a href="<?php echo admin_url('admin.php?page=kavipushp-customers'); ?>" class="button">
                        <i class="dashicons dashicons-arrow-left-alt"></i> <?php _e('Back to List', 'kavipushp-bridals'); ?>
                    </a>
                </div>
                <div class="kp-card-body">
                    <form method="post" class="kp-form kp-customer-form">
                        <?php wp_nonce_field('save_customer'); ?>
                        <input type="hidden" name="customer_id" id="customer_id" value="<?php echo esc_attr($customer_id); ?>">

                        <?php if ($action === 'add' && !empty($all_customers)): ?>
                        <h3 class="kp-section-title"><?php _e('Select Existing Customer', 'kavipushp-bridals'); ?></h3>
                        <div class="kp-form-group" style="margin-bottom: 20px;">
                            <label><?php _e('Search & Select Customer', 'kavipushp-bridals'); ?></label>
                            <select id="select_existing_customer" onchange="fillExistingCustomer(this.value)" style="min-width: 350px; padding: 8px;">
                                <option value=""><?php _e('-- Select existing customer or fill new below --', 'kavipushp-bridals'); ?></option>
                                <?php foreach ($all_customers as $c): ?>
                                    <option value="<?php echo esc_attr($c->id); ?>"
                                        data-name="<?php echo esc_attr($c->full_name); ?>"
                                        data-phone="<?php echo esc_attr($c->phone); ?>"
                                        data-email="<?php echo esc_attr($c->email); ?>"
                                        data-address="<?php echo esc_attr($c->address); ?>"
                                        data-id-proof-type="<?php echo esc_attr($c->id_proof_type); ?>"
                                        data-id-proof-number="<?php echo esc_attr($c->id_proof_number); ?>"
                                        data-function-date="<?php echo esc_attr($c->function_date); ?>"
                                        data-return-date="<?php echo esc_attr($c->return_date); ?>"
                                        data-pickup-date="<?php echo esc_attr($c->pickup_date); ?>">
                                        <?php echo esc_html($c->full_name . ' - ' . $c->phone); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description"><?php _e('Select a customer to edit their details, or leave empty to add a new customer.', 'kavipushp-bridals'); ?></p>
                        </div>
                        <script>
                        function fillExistingCustomer(customerId) {
                            if (!customerId) return;
                            var sel = document.getElementById('select_existing_customer');
                            var opt = sel.options[sel.selectedIndex];
                            // Redirect to edit page for selected customer
                            window.location.href = '<?php echo admin_url('admin.php?page=kavipushp-customers&action=edit&id='); ?>' + customerId;
                        }
                        </script>
                        <?php endif; ?>

                        <h3 class="kp-section-title"><?php _e('Customer Information', 'kavipushp-bridals'); ?></h3>

                        <div class="kp-form-row">
                            <div class="kp-form-group">
                                <label><?php _e('Full Name', 'kavipushp-bridals'); ?> *</label>
                                <input type="text" name="full_name" placeholder="<?php esc_attr_e('Enter customer name', 'kavipushp-bridals'); ?>" value="<?php echo $customer ? esc_attr($customer->full_name) : ''; ?>" required>
                            </div>
                            <div class="kp-form-group">
                                <label><?php _e('Contact Number', 'kavipushp-bridals'); ?> *</label>
                                <input type="tel" name="contact_number" placeholder="<?php esc_attr_e('Enter contact number', 'kavipushp-bridals'); ?>" value="<?php echo $customer ? esc_attr($customer->phone) : ''; ?>" required>
                            </div>
                        </div>

                        <div class="kp-form-group">
                            <label><?php _e('Address', 'kavipushp-bridals'); ?></label>
                            <input type="text" name="address" placeholder="<?php esc_attr_e('Enter complete address', 'kavipushp-bridals'); ?>" value="<?php echo $customer ? esc_attr($customer->address) : ''; ?>">
                        </div>

                        <div class="kp-form-group">
                            <label><?php _e('Email', 'kavipushp-bridals'); ?></label>
                            <input type="email" name="email" placeholder="<?php esc_attr_e('Enter email address', 'kavipushp-bridals'); ?>" value="<?php echo $customer ? esc_attr($customer->email) : ''; ?>">
                        </div>

                        <div class="kp-form-row">
                            <div class="kp-form-group">
                                <label><?php _e('ID Proof Type', 'kavipushp-bridals'); ?></label>
                                <input type="text" name="id_proof_type" placeholder="<?php esc_attr_e('e.g., Aadhaar, PAN, Passport', 'kavipushp-bridals'); ?>" value="<?php echo $customer ? esc_attr($customer->id_proof_type) : ''; ?>">
                            </div>
                            <div class="kp-form-group">
                                <label><?php _e('ID Proof Number', 'kavipushp-bridals'); ?></label>
                                <input type="text" name="id_proof_number" placeholder="<?php esc_attr_e('Enter ID proof number', 'kavipushp-bridals'); ?>" value="<?php echo $customer ? esc_attr($customer->id_proof_number) : ''; ?>">
                            </div>
                        </div>

                        <div class="kp-form-row kp-form-row-4">
                            <div class="kp-form-group">
                                <label><?php _e('Function Date', 'kavipushp-bridals'); ?> *</label>
                                <input type="date" name="function_date" id="function_date" value="<?php echo $customer ? esc_attr($customer->function_date) : ''; ?>" onchange="calculateReturnDate()">
                            </div>
                            <div class="kp-form-group">
                                <label><?php _e('Return Date', 'kavipushp-bridals'); ?> <small>(+1 day)</small></label>
                                <input type="date" name="return_date" id="return_date" value="<?php echo $customer ? esc_attr($customer->return_date) : ''; ?>" readonly style="background: #f5f5f5;">
                            </div>
                            <div class="kp-form-group">
                                <label><?php _e('Booking Date', 'kavipushp-bridals'); ?></label>
                                <input type="date" name="booking_date" value="<?php echo $customer ? esc_attr($customer->booking_date) : date('Y-m-d'); ?>">
                            </div>
                            <div class="kp-form-group">
                                <label><?php _e('Pickup Date', 'kavipushp-bridals'); ?> <small>(-1 day)</small></label>
                                <input type="date" name="pickup_date" id="pickup_date" value="<?php echo $customer ? esc_attr($customer->pickup_date) : ''; ?>" readonly style="background: #f5f5f5;">
                            </div>
                        </div>
                        <script>
                        function calculateReturnDate() {
                            var functionDate = document.getElementById('function_date').value;
                            if (functionDate) {
                                // Calculate return date (function date + 1 day)
                                var returnDateObj = new Date(functionDate);
                                returnDateObj.setDate(returnDateObj.getDate() + 1);
                                var returnDate = returnDateObj.toISOString().split('T')[0];
                                document.getElementById('return_date').value = returnDate;

                                // Calculate pickup date (function date - 1 day)
                                var pickupDateObj = new Date(functionDate);
                                pickupDateObj.setDate(pickupDateObj.getDate() - 1);
                                var pickupDate = pickupDateObj.toISOString().split('T')[0];
                                document.getElementById('pickup_date').value = pickupDate;
                            }
                        }
                        // Calculate on page load if function date exists
                        document.addEventListener('DOMContentLoaded', function() {
                            if (document.getElementById('function_date').value) {
                                calculateReturnDate();
                            }
                        });
                        </script>

                        <h3 class="kp-section-title"><?php _e('Visit Tracking Information', 'kavipushp-bridals'); ?></h3>

                        <div class="kp-form-group">
                            <label><?php _e('How Did They Hear About Us?', 'kavipushp-bridals'); ?></label>
                            <select name="referral_source">
                                <option value=""><?php _e('Select...', 'kavipushp-bridals'); ?></option>
                                <option value="google" <?php selected($customer ? $customer->referral_source : '', 'google'); ?>><?php _e('Google Search', 'kavipushp-bridals'); ?></option>
                                <option value="facebook" <?php selected($customer ? $customer->referral_source : '', 'facebook'); ?>><?php _e('Facebook', 'kavipushp-bridals'); ?></option>
                                <option value="instagram" <?php selected($customer ? $customer->referral_source : '', 'instagram'); ?>><?php _e('Instagram', 'kavipushp-bridals'); ?></option>
                                <option value="referral" <?php selected($customer ? $customer->referral_source : '', 'referral'); ?>><?php _e('Friend/Family Referral', 'kavipushp-bridals'); ?></option>
                                <option value="walkin" <?php selected($customer ? $customer->referral_source : '', 'walkin'); ?>><?php _e('Walk-in', 'kavipushp-bridals'); ?></option>
                                <option value="other" <?php selected($customer ? $customer->referral_source : '', 'other'); ?>><?php _e('Other', 'kavipushp-bridals'); ?></option>
                            </select>
                        </div>

                        <div class="kp-form-group">
                            <label><?php _e('Notes', 'kavipushp-bridals'); ?></label>
                            <textarea name="notes" rows="3" placeholder="<?php esc_attr_e('Additional notes about the customer', 'kavipushp-bridals'); ?>"><?php echo $customer ? esc_textarea($customer->notes) : ''; ?></textarea>
                        </div>

                        <div class="kp-form-actions">
                            <button type="submit" name="save_customer" class="button button-primary button-large">
                                <?php _e('Save Customer', 'kavipushp-bridals'); ?>
                            </button>
                            <a href="<?php echo admin_url('admin.php?page=kavipushp-customers'); ?>" class="button button-large">
                                <?php _e('Cancel', 'kavipushp-bridals'); ?>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <!-- Customers List -->
            <div class="kp-toolbar">
                <a href="<?php echo admin_url('admin.php?page=kavipushp-customers&action=add'); ?>" class="button button-primary">
                    <i class="dashicons dashicons-plus-alt2"></i> <?php _e('Add Customer', 'kavipushp-bridals'); ?>
                </a>
                <button class="button" onclick="kavipushpExportCustomers()">
                    <i class="dashicons dashicons-download"></i> <?php _e('Export to Excel', 'kavipushp-bridals'); ?>
                </button>
            </div>

            <div class="kp-card">
                <div class="kp-card-header">
                    <h2><?php _e('All Customers', 'kavipushp-bridals'); ?></h2>
                    <input type="text" id="search-customers" placeholder="<?php esc_attr_e('Search customers...', 'kavipushp-bridals'); ?>" class="kp-search-input">
                </div>
                <div class="kp-card-body">
                    <table class="kp-table" id="customers-table">
                        <thead>
                            <tr>
                                <th><?php _e('Name', 'kavipushp-bridals'); ?></th>
                                <th><?php _e('Contact', 'kavipushp-bridals'); ?></th>
                                <th><?php _e('Email', 'kavipushp-bridals'); ?></th>
                                <th><?php _e('Function Date', 'kavipushp-bridals'); ?></th>
                                <th><?php _e('Bookings', 'kavipushp-bridals'); ?></th>
                                <th><?php _e('Actions', 'kavipushp-bridals'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $customers = kavipushp_get_all_customers_from_db();
                            if (!empty($customers)):
                                foreach ($customers as $customer):
                            ?>
                            <tr>
                                <td><strong><?php echo esc_html($customer->full_name); ?></strong></td>
                                <td><?php echo esc_html($customer->phone); ?></td>
                                <td><?php echo esc_html($customer->email); ?></td>
                                <td><?php echo $customer->function_date ? date('d/m/Y', strtotime($customer->function_date)) : '-'; ?></td>
                                <td><?php echo intval($customer->booking_count); ?></td>
                                <td class="kp-actions">
                                    <a href="<?php echo admin_url('admin.php?page=kavipushp-customers&action=edit&id=' . $customer->id); ?>" class="button button-small" title="<?php esc_attr_e('Edit', 'kavipushp-bridals'); ?>">
                                        <i class="dashicons dashicons-edit"></i>
                                    </a>
                                    <a href="<?php echo admin_url('admin.php?page=kavipushp-customers&action=view&id=' . $customer->id); ?>" class="button button-small" title="<?php esc_attr_e('View', 'kavipushp-bridals'); ?>">
                                        <i class="dashicons dashicons-visibility"></i>
                                    </a>
                                    <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=kavipushp-customers&action=delete&id=' . $customer->id), 'delete_customer'); ?>" class="button button-small kp-delete-btn" title="<?php esc_attr_e('Delete', 'kavipushp-bridals'); ?>" onclick="return confirm('<?php esc_attr_e('Are you sure?', 'kavipushp-bridals'); ?>')">
                                        <i class="dashicons dashicons-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php
                                endforeach;
                            else:
                            ?>
                            <tr>
                                <td colspan="6" class="kp-no-data"><?php _e('No customers found. Add your first customer!', 'kavipushp-bridals'); ?></td>
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

function kavipushp_render_inventory() {
    // 1. PHP Logic: Handle CSV Upload and "Clear All"
    if (isset($_POST['kp_action']) && $_POST['kp_action'] === 'upload_inventory_csv') {
        if (!isset($_FILES['inventory_csv']) || $_FILES['inventory_csv']['error'] !== UPLOAD_ERR_OK) {
            echo '<div class="notice notice-error"><p>' . __('Error: File upload failed.', 'kavipushp-bridals') . '</p></div>';
        } else {
            $csv_file = $_FILES['inventory_csv']['tmp_name'];
            if (($handle = fopen($csv_file, "r")) !== FALSE) {
                fgetcsv($handle, 1000, ","); // Skip header row (s.no., title, category, set id/code, rental price)
                $count_new = 0;
                $count_updated = 0;
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    if (empty($data[3])) continue; // Skip if set_id is empty

                    // Mapping: s.no. [0], title [1], category [2], set id/code [3], rental price [4]
                    $title     = sanitize_text_field($data[1]);
                    $category  = sanitize_text_field($data[2]);
                    $set_id    = sanitize_text_field($data[3]);
                    $price     = floatval($data[4]);

                    // Generate title from category + set_id if title is empty
                    if (empty($title)) {
                        $title = trim(($category ?: 'Bridal Set') . ' ' . $set_id);
                    }

                    // Check if set with this set_id already exists
                    global $wpdb;
                    $existing_id = $wpdb->get_var($wpdb->prepare(
                        "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_set_id' AND meta_value = %s LIMIT 1",
                        $set_id
                    ));

                    if ($existing_id) {
                        // Update existing post and ensure it's published
                        wp_update_post(array(
                            'ID'          => $existing_id,
                            'post_title'  => $title,
                            'post_status' => 'publish',
                        ));
                        update_post_meta($existing_id, '_rental_price', $price);

                        // Set category if provided
                        if (!empty($category)) {
                            wp_set_object_terms($existing_id, $category, 'bridal_category');
                        }
                        $count_updated++;
                    } else {
                        // Create new post
                        $post_id = wp_insert_post(array(
                            'post_type'   => 'bridal_set',
                            'post_title'  => $title,
                            'post_status' => 'publish',
                        ));

                        if ($post_id && !is_wp_error($post_id)) {
                            update_post_meta($post_id, '_set_id', $set_id);
                            update_post_meta($post_id, '_rental_price', $price);
                            update_post_meta($post_id, '_availability', 'available');

                            // Set category if provided
                            if (!empty($category)) {
                                wp_set_object_terms($post_id, $category, 'bridal_category');
                            }
                            $count_new++;
                        }
                    }
                }
                fclose($handle);
                echo '<div class="notice notice-success"><p>' . sprintf(__('%d items updated, %d new items added!', 'kavipushp-bridals'), $count_updated, $count_new) . '</p></div>';
            }
        }
    }

    if (isset($_GET['action']) && $_GET['action'] === 'clear') {
        $all_sets = get_posts(array('post_type' => 'bridal_set', 'post_status' => array('publish', 'draft', 'trash', 'pending', 'private'), 'posts_per_page' => -1, 'fields' => 'ids'));
        foreach ($all_sets as $id) { wp_delete_post($id, true); }
        // Redirect to clean URL so action=clear doesn't persist
        wp_redirect(admin_url('admin.php?page=kavipushp-inventory&cleared=1'));
        exit;
    }
    if (isset($_GET['cleared'])) {
        echo '<div class="notice notice-warning"><p>' . __('Inventory cleared.', 'kavipushp-bridals') . '</p></div>';
    }
    ?>

    <div class="kavipushp-admin-wrap">
        <div class="kp-admin-header">
            <div class="kp-user-info">
                <i class="dashicons dashicons-admin-users"></i>
                <span><?php echo esc_html(wp_get_current_user()->user_email); ?></span>
            </div>
            <a href="<?php echo wp_logout_url(admin_url()); ?>" class="kp-logout-btn">
                <i class="dashicons dashicons-exit"></i> <?php _e('Logout', 'kavipushp-bridals'); ?>
            </a>
        </div>

        <div class="kp-page-title">
            <h1><?php _e('Jewelry Inventory', 'kavipushp-bridals'); ?></h1>
            <p><?php _e('Upload and manage your jewelry collection with CSV files', 'kavipushp-bridals'); ?></p>
        </div>

        <?php
        // Fetch all bridal sets once, reuse for both count and display
        $sets = get_posts(array(
            'post_type'      => 'bridal_set',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
        ));

        // Sort by numeric part of _set_id in ascending order
        usort($sets, function($a, $b) {
            $id_a = get_post_meta($a->ID, '_set_id', true);
            $id_b = get_post_meta($b->ID, '_set_id', true);
            $num_a = intval(preg_replace('/[^0-9]/', '', $id_a ?: '999999'));
            $num_b = intval(preg_replace('/[^0-9]/', '', $id_b ?: '999999'));
            return $num_a - $num_b;
        });

        $total_items = count($sets);
        ?>

        <div class="kp-card">
            <div class="kp-card-header kp-inventory-header">
                <div class="kp-inventory-title">
                    <h2><i class="dashicons dashicons-archive"></i> <?php printf(__('Jewelry Inventory (%d items)', 'kavipushp-bridals'), $total_items); ?></h2>
                    <span class="kp-db-indicator">Database</span>
                    <span class="kp-live-badge"><?php _e('Live', 'kavipushp-bridals'); ?></span>
                </div>
                <div class="kp-inventory-actions">
                    <form method="post" enctype="multipart/form-data" id="kp-inventory-upload-form" action="<?php echo admin_url('admin.php?page=kavipushp-inventory'); ?>" style="display:inline-block;">
                        <?php wp_nonce_field('kavipushp_inventory_upload', 'kp_inventory_nonce'); ?>
                        <input type="hidden" name="kp_action" value="upload_inventory_csv">
                        <button type="button" class="button" onclick="document.getElementById('csv-upload').click()">
                            <i class="dashicons dashicons-upload"></i> <?php _e('Upload CSV', 'kavipushp-bridals'); ?>
                        </button>
                        <input type="file" id="csv-upload" name="inventory_csv" accept=".csv" style="display:none;" onchange="document.getElementById('kp-inventory-upload-form').submit();">
                    </form>

                    <button class="button kp-btn-danger" onclick="return confirmClearAll()">
                        <?php _e('Clear All', 'kavipushp-bridals'); ?>
                    </button>
                    <button class="button" onclick="window.location.reload(); return false;">
                        <i class="dashicons dashicons-update"></i> <?php _e('Reload', 'kavipushp-bridals'); ?>
                    </button>
                    <script>
                    function confirmClearAll() {
                        if (confirm('Are you sure you want to clear all <?php echo $total_items; ?> items from inventory? This cannot be undone!')) {
                            window.location.href = '<?php echo admin_url("admin.php?page=kavipushp-inventory&action=clear"); ?>';
                        }
                        return false;
                    }
                    </script>
                </div>
            </div>
            <div class="kp-card-body">
                <div class="kp-search-bar">
                    <label><?php _e('Search Jewelry', 'kavipushp-bridals'); ?></label>
                    <input type="text" id="search-inventory" placeholder="<?php esc_attr_e('Search by name, category, or ITEMID...', 'kavipushp-bridals'); ?>">
                </div>

                <div class="kp-inventory-grid-new" id="inventory-grid">
                    <?php foreach ($sets as $set):
                        $set_code = get_post_meta($set->ID, '_set_id', true);
                        $rental_price = get_post_meta($set->ID, '_rental_price', true);
                        $categories = get_the_terms($set->ID, 'bridal_category');
                        $category_name = $categories && !is_wp_error($categories) ? $categories[0]->name : 'Uncategorized';
                    ?>
                    <div class="kp-inv-card">
                        <div class="kp-inv-card-top">
                            <label class="kp-inv-checkbox">
                                <input type="checkbox" name="selected_items[]" value="<?php echo $set->ID; ?>">
                                <span class="kp-checkmark"></span>
                            </label>
                            <span class="kp-inv-title-code"><?php echo esc_html($set->post_title); ?></span>
                        </div>
                        <div class="kp-inv-card-body">
                            <span class="kp-inv-category"><?php echo esc_html(strtoupper($category_name)); ?></span>
                            <h4 class="kp-inv-name"><?php echo esc_html($set->post_title); ?></h4>
                            <p class="kp-inv-id">ID: <?php echo esc_html($set_code ?: 'KP' . $set->ID); ?></p>
                        </div>
                        <div class="kp-inv-card-footer">
                            <span class="kp-inv-price"><?php echo number_format($rental_price); ?> <span class="kp-inv-per-day">/day</span></span>
                            <a href="<?php echo get_edit_post_link($set->ID); ?>" class="kp-inv-view-btn"><?php _e('VIEW DETAILS', 'kavipushp-bridals'); ?></a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <?php if (empty($sets)): ?>
                <div class="kp-empty-state">
                    <i class="dashicons dashicons-archive"></i>
                    <h3><?php _e('No items in inventory', 'kavipushp-bridals'); ?></h3>
                    <p><?php _e('Upload a CSV file or add items manually', 'kavipushp-bridals'); ?></p>
                    <a href="<?php echo admin_url('post-new.php?post_type=bridal_set'); ?>" class="button button-primary">
                        <?php _e('Add First Item', 'kavipushp-bridals'); ?>
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var searchInput = document.getElementById('search-inventory');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                var query = this.value.toLowerCase();
                var cards = document.querySelectorAll('#inventory-grid .kp-inv-card');
                cards.forEach(function(card) {
                    var title = (card.querySelector('.kp-inv-name') || {}).textContent || '';
                    var category = (card.querySelector('.kp-inv-category') || {}).textContent || '';
                    var itemId = (card.querySelector('.kp-inv-id') || {}).textContent || '';
                    var code = (card.querySelector('.kp-inv-title-code') || {}).textContent || '';
                    var text = (title + ' ' + category + ' ' + itemId + ' ' + code).toLowerCase();
                    card.style.display = text.indexOf(query) !== -1 ? '' : 'none';
                });
            });
        }
    });
    </script>
    <?php
}
/**
 * Enhanced Bookings Management Page
 */
function kavipushp_render_bookings_management() {
    if (isset($_GET['deleted'])) {
        echo '<div class="notice notice-success is-dismissible"><p>' . __('Booking deleted successfully!', 'kavipushp-bridals') . '</p></div>';
    }
    ?>
    <div class="kavipushp-admin-wrap">
        <div class="kp-admin-header">
            <div class="kp-user-info">
                <i class="dashicons dashicons-admin-users"></i>
                <span><?php echo esc_html(wp_get_current_user()->user_email); ?></span>
            </div>
            <a href="<?php echo wp_logout_url(admin_url()); ?>" class="kp-logout-btn">
                <i class="dashicons dashicons-exit"></i> <?php _e('Logout', 'kavipushp-bridals'); ?>
            </a>
        </div>

        <div class="kp-page-title-row">
            <div>
                <h1><?php _e('Bookings Management', 'kavipushp-bridals'); ?></h1>
                <p><?php _e('Manage jewelry rental bookings and schedules', 'kavipushp-bridals'); ?></p>
                <div class="kp-data-indicators">
                    <span class="kp-live-indicator"><span class="kp-dot-live"></span> <?php _e('Live data from database', 'kavipushp-bridals'); ?></span>
                    <span class="kp-info-badge"><i class="dashicons dashicons-admin-users"></i> <?php echo kavipushp_get_total_customers(); ?> <?php _e('customers loaded', 'kavipushp-bridals'); ?></span>
                    <span class="kp-info-badge"><i class="dashicons dashicons-archive"></i> <?php echo wp_count_posts('bridal_set')->publish; ?> <?php _e('jewelry items loaded', 'kavipushp-bridals'); ?></span>
                </div>
            </div>
            <div class="kp-page-actions">
                <button class="button" onclick="kavipushpExportBookings()">
                    <i class="dashicons dashicons-download"></i> <?php _e('Export to Excel', 'kavipushp-bridals'); ?>
                </button>
                <a href="<?php echo admin_url('post-new.php?post_type=booking'); ?>" class="button button-primary">
                    <i class="dashicons dashicons-plus-alt2"></i> <?php _e('New Booking', 'kavipushp-bridals'); ?>
                </a>
            </div>
        </div>

        <?php
        $bookings = get_posts(array(
            'post_type'      => 'booking',
            'posts_per_page' => 20,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ));
        $total_bookings = wp_count_posts('booking')->publish;
        ?>

        <div class="kp-card">
            <div class="kp-card-header">
                <h2><i class="dashicons dashicons-calendar-alt"></i> <?php printf(__('All Bookings (%d) - Latest First', 'kavipushp-bridals'), $total_bookings); ?></h2>
            </div>
            <div class="kp-card-body kp-bookings-list">
                <?php if (!empty($bookings)): ?>
                    <?php foreach ($bookings as $booking):
                        $customer_name = get_post_meta($booking->ID, '_customer_name', true);
                        $customer_email = get_post_meta($booking->ID, '_customer_email', true);
                        $customer_phone = get_post_meta($booking->ID, '_customer_phone', true);
                        $customer_address = get_post_meta($booking->ID, '_customer_address', true);
                        $set_id = get_post_meta($booking->ID, '_bridal_set_id', true);
                        $set = $set_id ? get_post($set_id) : null;
                        $set_code = $set_id ? get_post_meta($set_id, '_set_id', true) : '';
                        $pickup_date = get_post_meta($booking->ID, '_pickup_date', true);
                        $return_date = get_post_meta($booking->ID, '_return_date', true);
                        $function_date = get_post_meta($booking->ID, '_function_date', true);
                        if (!$function_date) $function_date = $pickup_date; // Fallback for older bookings
                        $status = get_post_meta($booking->ID, '_booking_status', true);
                        $total = get_post_meta($booking->ID, '_total_amount', true);
                        $booking_uid = 'BK-' . substr(md5($booking->ID), 0, 8);
                    ?>
                    <div class="kp-booking-card">
                        <div class="kp-booking-header">
                            <div class="kp-booking-customer">
                                <h3><?php echo esc_html($customer_name); ?></h3>
                                <p class="kp-booking-id">Booking ID: <?php echo esc_html($booking_uid); ?> | Contact: <?php echo esc_html($customer_phone); ?></p>
                            </div>
                            <div class="kp-booking-total">
                                <span class="kp-price">₹<?php echo number_format($total); ?></span>
                                <span class="kp-label"><?php _e('Total Rent', 'kavipushp-bridals'); ?></span>
                                <span class="kp-date"><?php _e('Created:', 'kavipushp-bridals'); ?> <?php echo get_the_date('d/m/Y h:i A', $booking); ?></span>
                            </div>
                        </div>

                        <div class="kp-booking-customer-info">
                            <div class="kp-customer-badge">
                                <i class="dashicons dashicons-yes-alt"></i>
                                <span><?php _e('Customer Found in Database:', 'kavipushp-bridals'); ?></span>
                            </div>
                            <p><?php _e('Address:', 'kavipushp-bridals'); ?> <?php echo esc_html($customer_address ?: 'N/A'); ?></p>
                            <p><?php _e('Email:', 'kavipushp-bridals'); ?> <?php echo esc_html($customer_email); ?></p>
                        </div>

                        <div class="kp-booking-dates">
                            <div class="kp-date-item">
                                <span class="kp-label"><?php _e('Function Date:', 'kavipushp-bridals'); ?></span>
                                <span class="kp-value"><?php echo $function_date ? date('d/m/Y', strtotime($function_date)) : 'N/A'; ?></span>
                            </div>
                            <div class="kp-date-item">
                                <span class="kp-label"><?php _e('Pickup Date:', 'kavipushp-bridals'); ?></span>
                                <span class="kp-value"><?php echo $pickup_date ? date('d/m/Y', strtotime($pickup_date)) : 'N/A'; ?></span>
                            </div>
                            <div class="kp-date-item">
                                <span class="kp-label"><?php _e('Return Date:', 'kavipushp-bridals'); ?></span>
                                <span class="kp-value"><?php echo $return_date ? date('d/m/Y', strtotime($return_date)) : 'N/A'; ?></span>
                            </div>
                        </div>

                        <div class="kp-booking-items">
                            <span class="kp-label"><?php _e('Selected Items:', 'kavipushp-bridals'); ?></span>
                            <?php if ($set): ?>
                            <div class="kp-item-row">
                                <span class="kp-item-code"><?php echo esc_html($set_code ?: $set->post_title); ?></span>
                                <span class="kp-item-price">₹<?php echo number_format($total); ?></span>
                            </div>
                            <?php else: ?>
                            <p><?php _e('No items selected', 'kavipushp-bridals'); ?></p>
                            <?php endif; ?>
                        </div>

                        <div class="kp-booking-footer">
                            <div class="kp-booking-status">
                                <span class="kp-label"><?php _e('Status:', 'kavipushp-bridals'); ?></span>
                                <span class="kp-status kp-status-<?php echo esc_attr($status); ?>"><?php echo strtoupper($status); ?></span>
                            </div>
                            <div class="kp-booking-actions">
                                <a href="<?php echo admin_url('post.php?post=' . $booking->ID . '&action=edit'); ?>" class="button button-small">
                                    <i class="dashicons dashicons-edit"></i> <?php _e('Edit', 'kavipushp-bridals'); ?>
                                </a>
                                <a href="#" class="button button-small" onclick="kavipushpViewBooking(<?php echo $booking->ID; ?>)">
                                    <i class="dashicons dashicons-visibility"></i> <?php _e('View', 'kavipushp-bridals'); ?>
                                </a>
                                <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=kavipushp-bookings&action=delete&id=' . $booking->ID), 'delete_booking'); ?>" class="button button-small kp-delete-btn" onclick="return confirm('Delete this booking?')">
                                    <i class="dashicons dashicons-trash"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="kp-empty-state">
                        <i class="dashicons dashicons-calendar-alt"></i>
                        <h3><?php _e('No bookings yet', 'kavipushp-bridals'); ?></h3>
                        <p><?php _e('Create your first booking to get started', 'kavipushp-bridals'); ?></p>
                        <a href="<?php echo admin_url('post-new.php?post_type=booking'); ?>" class="button button-primary">
                            <?php _e('Create First Booking', 'kavipushp-bridals'); ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Booking View Modal -->
    <div id="kp-booking-modal-overlay" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.6);z-index:99999;justify-content:center;align-items:center;">
        <div style="background:#fff;border-radius:8px;max-width:600px;width:90%;max-height:80vh;overflow-y:auto;padding:30px;position:relative;">
            <button onclick="document.getElementById('kp-booking-modal-overlay').style.display='none'" style="position:absolute;top:10px;right:15px;background:none;border:none;font-size:24px;cursor:pointer;">&times;</button>
            <div id="kp-booking-modal-content">
                <p><?php _e('Loading...', 'kavipushp-bridals'); ?></p>
            </div>
        </div>
    </div>

    <script>
    function kavipushpViewBooking(bookingId) {
        var overlay = document.getElementById('kp-booking-modal-overlay');
        var content = document.getElementById('kp-booking-modal-content');
        overlay.style.display = 'flex';
        content.innerHTML = '<p>Loading...</p>';

        var xhr = new XMLHttpRequest();
        xhr.open('POST', ajaxurl);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (xhr.status === 200) {
                var res = JSON.parse(xhr.responseText);
                if (res.success) {
                    var b = res.data;
                    content.innerHTML =
                        '<h2 style="margin-top:0;"><i class="dashicons dashicons-calendar-alt"></i> ' + b.booking_id + '</h2>' +
                        '<table class="widefat striped" style="border:none;">' +
                        '<tr><th style="width:40%;"><?php _e('Customer Name', 'kavipushp-bridals'); ?></th><td>' + b.customer_name + '</td></tr>' +
                        '<tr><th><?php _e('Phone', 'kavipushp-bridals'); ?></th><td>' + b.customer_phone + '</td></tr>' +
                        '<tr><th><?php _e('Email', 'kavipushp-bridals'); ?></th><td>' + b.customer_email + '</td></tr>' +
                        '<tr><th><?php _e('Address', 'kavipushp-bridals'); ?></th><td>' + b.customer_address + '</td></tr>' +
                        '<tr><th><?php _e('Bridal Set', 'kavipushp-bridals'); ?></th><td>' + b.set_name + '</td></tr>' +
                        '<tr><th><?php _e('Function Date', 'kavipushp-bridals'); ?></th><td>' + b.function_date + '</td></tr>' +
                        '<tr><th><?php _e('Pickup Date', 'kavipushp-bridals'); ?></th><td>' + b.pickup_date + '</td></tr>' +
                        '<tr><th><?php _e('Return Date', 'kavipushp-bridals'); ?></th><td>' + b.return_date + '</td></tr>' +
                        '<tr><th><?php _e('Total Amount', 'kavipushp-bridals'); ?></th><td>₹' + b.total_amount + '</td></tr>' +
                        '<tr><th><?php _e('Status', 'kavipushp-bridals'); ?></th><td><span class="kp-status kp-status-' + b.status + '">' + b.status.toUpperCase() + '</span></td></tr>' +
                        '<tr><th><?php _e('Created', 'kavipushp-bridals'); ?></th><td>' + b.created_date + '</td></tr>' +
                        '</table>';
                } else {
                    content.innerHTML = '<p style="color:red;"><?php _e('Booking not found.', 'kavipushp-bridals'); ?></p>';
                }
            }
        };
        xhr.send('action=kavipushp_view_booking&booking_id=' + bookingId + '&_wpnonce=<?php echo wp_create_nonce('kavipushp_view_booking'); ?>');
    }

    document.getElementById('kp-booking-modal-overlay').addEventListener('click', function(e) {
        if (e.target === this) this.style.display = 'none';
    });
    </script>
    <?php
}

/**
 * Enhanced Invoice Management Page
 */
function kavipushp_render_invoices_enhanced() {
    $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
    $invoice_booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;
    ?>
    <div class="kavipushp-admin-wrap">
        <div class="kp-admin-header">
            <div class="kp-user-info">
                <i class="dashicons dashicons-admin-users"></i>
                <span><?php echo esc_html(wp_get_current_user()->user_email); ?></span>
            </div>
            <a href="<?php echo wp_logout_url(admin_url()); ?>" class="kp-logout-btn">
                <i class="dashicons dashicons-exit"></i> <?php _e('Logout', 'kavipushp-bridals'); ?>
            </a>
        </div>

        <div class="kp-page-title-row">
            <div>
                <h1><?php _e('Invoice Management', 'kavipushp-bridals'); ?></h1>
                <p><?php _e('Track and manage all rental invoices', 'kavipushp-bridals'); ?></p>
            </div>
            <div class="kp-page-actions">
                <?php if ($action === 'generate'): ?>
                <a href="<?php echo admin_url('admin.php?page=kavipushp-invoices'); ?>" class="button">
                    <i class="dashicons dashicons-arrow-left-alt"></i> <?php _e('Back to Invoices', 'kavipushp-bridals'); ?>
                </a>
                <?php else: ?>
                <button class="button" onclick="location.reload()">
                    <i class="dashicons dashicons-update"></i> <?php _e('Refresh Data', 'kavipushp-bridals'); ?>
                </button>
                <a href="<?php echo admin_url('admin.php?page=kavipushp-invoices&action=generate'); ?>" class="button button-primary">
                    <i class="dashicons dashicons-plus-alt2"></i> <?php _e('Generate Invoice', 'kavipushp-bridals'); ?>
                </a>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($action === 'generate'): ?>
        <!-- Generate Invoice Form -->
        <?php
        // Get all bookings for selection
        $all_bookings = get_posts(array(
            'post_type'      => 'booking',
            'post_status'    => array('publish', 'draft', 'private'),
            'posts_per_page' => -1,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ));
        ?>
        <div class="kp-card">
            <div class="kp-card-header">
                <h2><i class="dashicons dashicons-media-text"></i> <?php _e('Generate Invoice', 'kavipushp-bridals'); ?></h2>
            </div>
            <div class="kp-card-body">
                <div style="display: flex; gap: 20px; margin-bottom: 20px; flex-wrap: wrap;">
                    <div class="kp-form-group">
                        <label><strong><?php _e('Select Booking', 'kavipushp-bridals'); ?></strong></label>
                        <select id="invoice_booking_select" onchange="updateInvoicePreview()" style="min-width: 400px; padding: 8px;">
                            <option value=""><?php _e('-- Select a Booking --', 'kavipushp-bridals'); ?></option>
                            <?php foreach ($all_bookings as $b):
                                $b_name = get_post_meta($b->ID, '_customer_name', true);
                                $b_phone = get_post_meta($b->ID, '_customer_phone', true);
                                $b_status = get_post_meta($b->ID, '_booking_status', true);
                                $b_set_id = get_post_meta($b->ID, '_bridal_set_id', true);
                                $b_set = $b_set_id ? get_post($b_set_id) : null;
                                $b_set_name = $b_set ? $b_set->post_title : '';
                                $b_date = get_the_date('d/m/Y', $b);
                            ?>
                            <option value="<?php echo esc_attr($b->ID); ?>"
                                data-name="<?php echo esc_attr($b_name); ?>"
                                data-phone="<?php echo esc_attr($b_phone); ?>"
                                data-email="<?php echo esc_attr(get_post_meta($b->ID, '_customer_email', true)); ?>"
                                data-address="<?php echo esc_attr(get_post_meta($b->ID, '_customer_address', true)); ?>"
                                data-set-name="<?php echo esc_attr($b_set_name); ?>"
                                data-set-code="<?php echo esc_attr($b_set_id ? get_post_meta($b_set_id, '_set_id', true) : ''); ?>"
                                data-function-date="<?php echo esc_attr(get_post_meta($b->ID, '_function_date', true)); ?>"
                                data-pickup-date="<?php echo esc_attr(get_post_meta($b->ID, '_pickup_date', true)); ?>"
                                data-return-date="<?php echo esc_attr(get_post_meta($b->ID, '_return_date', true)); ?>"
                                data-rent="<?php echo esc_attr(get_post_meta($b->ID, '_total_amount', true)); ?>"
                                data-booking-amount="<?php echo esc_attr(get_post_meta($b->ID, '_booking_amount', true)); ?>"
                                data-customization="<?php echo esc_attr(get_post_meta($b->ID, '_booking_notes', true)); ?>"
                                data-status="<?php echo esc_attr($b_status); ?>"
                                data-date="<?php echo esc_attr($b_date); ?>"
                                <?php selected($invoice_booking_id, $b->ID); ?>>
                                <?php echo esc_html($b_name . ' - ' . $b_set_name . ' (' . $b_date . ') [' . strtoupper($b_status) . ']'); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="kp-form-group">
                        <label><strong><?php _e('Invoice Type', 'kavipushp-bridals'); ?></strong></label>
                        <div style="display: flex; gap: 10px; margin-top: 5px;">
                            <label style="display: flex; align-items: center; gap: 5px; padding: 8px 16px; border: 2px solid #c9a86c; border-radius: 6px; cursor: pointer; background: #c9a86c; color: #fff;">
                                <input type="radio" name="invoice_type" value="booking" checked onchange="updateInvoicePreview()" style="display: none;">
                                <i class="dashicons dashicons-calendar-alt"></i> <?php _e('Booking Invoice', 'kavipushp-bridals'); ?>
                            </label>
                            <label style="display: flex; align-items: center; gap: 5px; padding: 8px 16px; border: 2px solid #ddd; border-radius: 6px; cursor: pointer; background: #fff; color: #333;">
                                <input type="radio" name="invoice_type" value="pickup" onchange="updateInvoicePreview()" style="display: none;">
                                <i class="dashicons dashicons-car"></i> <?php _e('Pickup Invoice', 'kavipushp-bridals'); ?>
                            </label>
                            <label style="display: flex; align-items: center; gap: 5px; padding: 8px 16px; border: 2px solid #ddd; border-radius: 6px; cursor: pointer; background: #fff; color: #333;">
                                <input type="radio" name="invoice_type" value="final" onchange="updateInvoicePreview()" style="display: none;">
                                <i class="dashicons dashicons-yes-alt"></i> <?php _e('Final Invoice', 'kavipushp-bridals'); ?>
                            </label>
                        </div>
                    </div>

                    <div id="amount-received-group" class="kp-form-group" style="display: none;">
                        <label><strong><?php _e('Amount Received (₹)', 'kavipushp-bridals'); ?></strong></label>
                        <input type="number" id="amount_received" value="0" min="0" onchange="updateInvoicePreview()" style="padding: 6px 10px; width: 200px; border: 1px solid #ddd; border-radius: 4px;">
                    </div>

                    <div id="damages-amount-group" class="kp-form-group" style="display: none;">
                        <label><strong><?php _e('Damages Amount (₹)', 'kavipushp-bridals'); ?></strong></label>
                        <input type="number" id="damages_amount" value="0" min="0" onchange="updateInvoicePreview()" style="padding: 6px 10px; width: 200px; border: 1px solid #ddd; border-radius: 4px;">
                    </div>

                    <div id="set-image-group" class="kp-form-group">
                        <label><strong><?php _e('Bridal Set Image', 'kavipushp-bridals'); ?></strong></label>
                        <input type="file" id="set_image_upload" accept="image/*" onchange="handleSetImageUpload(this)" style="padding: 6px 0; display: block;">
                        <div id="set-image-preview-thumb" style="display:none; margin-top:8px;">
                            <img id="set-image-thumb" src="" style="max-width:150px; max-height:120px; border:1px solid #ddd; border-radius:4px; display:block;">
                            <button type="button" onclick="clearSetImage()" style="margin-top:5px; padding:4px 10px; border:1px solid #ccc; border-radius:4px; cursor:pointer; background:#fff; color:#c00; font-size:12px;">Remove Image</button>
                        </div>
                    </div>

                    <div id="set-includes-group" class="kp-form-group">
                        <label><strong><?php _e('Set Includes', 'kavipushp-bridals'); ?></strong></label>
                        <textarea id="set_includes" rows="3" placeholder="e.g. Necklace, Ear rings, Nath, Bangles..." oninput="updateInvoicePreview()" style="padding: 6px 10px; width: 100%; border: 1px solid #ddd; border-radius: 4px; resize: vertical; box-sizing: border-box; max-width: 500px;"></textarea>
                    </div>

                </div>

                <!-- Invoice Preview -->
                <div id="invoice-preview" style="display: none;">
                    <div style="background: #f9f9f9; border: 1px solid #ddd; border-radius: 8px; padding: 30px; max-width: 800px;">
                        <div style="text-align: center; border-bottom: 2px solid #c9a86c; padding-bottom: 15px; margin-bottom: 20px;">
                            <h2 style="margin: 0; color: #1a1f36;"><?php echo esc_html(get_option('kavipushp_business_name', 'Kavipushp Jewels Rental')); ?></h2>
                            <p style="color: #666; margin: 5px 0 0; font-size: 13px; line-height: 1.6;">
                                <?php echo esc_html(get_option('kavipushp_business_address', '')); ?><br>
                                <i class="dashicons dashicons-phone" style="font-size: 13px; width: 13px; height: 13px;"></i> <?php echo esc_html(get_option('kavipushp_business_phone', '')); ?>
                                &nbsp;|&nbsp;
                                <i class="dashicons dashicons-email" style="font-size: 13px; width: 13px; height: 13px;"></i> <?php echo esc_html(get_option('kavipushp_business_email', '')); ?>
                            </p>
                        </div>

                        <h3 style="text-align: center; color: #1a1f36;" id="inv-title"></h3>
                        <p style="text-align: center; color: #666;" id="inv-number-date"></p>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 20px 0;">
                            <div>
                                <h4 style="color: #c9a86c; margin: 0 0 8px;"><?php _e('Bill To', 'kavipushp-bridals'); ?></h4>
                                <p id="inv-customer-name" style="margin: 3px 0; font-weight: bold;"></p>
                                <p id="inv-customer-phone" style="margin: 3px 0;"></p>
                                <p id="inv-customer-email" style="margin: 3px 0;"></p>
                                <p id="inv-customer-address" style="margin: 3px 0;"></p>
                            </div>
                            <div>
                                <h4 style="color: #c9a86c; margin: 0 0 8px;"><?php _e('Rental Period', 'kavipushp-bridals'); ?></h4>
                                <p style="margin: 3px 0;"><strong><?php _e('Function:', 'kavipushp-bridals'); ?></strong> <span id="inv-function-date"></span></p>
                                <p style="margin: 3px 0;"><strong><?php _e('Pickup:', 'kavipushp-bridals'); ?></strong> <span id="inv-pickup-date"></span></p>
                                <p style="margin: 3px 0;"><strong><?php _e('Return:', 'kavipushp-bridals'); ?></strong> <span id="inv-return-date"></span></p>
                            </div>
                        </div>

                        <div id="inv-set-image-section" style="display:none; text-align:center; margin: 15px 0;">
                            <img id="inv-set-image" src="" style="max-width:200px; max-height:160px; border:1px solid #ddd; border-radius:6px;">
                        </div>

                        <div id="inv-set-includes-section" style="display:none; background:#f9f0ff; border:1px solid #e0c8f0; border-radius:6px; padding:12px 15px; margin: 10px 0 15px 0;">
                            <strong style="color:#7b4fa6;"><?php _e('Set Includes:', 'kavipushp-bridals'); ?></strong>
                            <span id="inv-set-includes-text" style="color:#333; margin-left:6px;"></span>
                        </div>

                        <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
                            <thead>
                                <tr style="background: #f0f0f0;">
                                    <th style="padding: 10px; text-align: left; border-bottom: 2px solid #ddd;"><?php _e('Item', 'kavipushp-bridals'); ?></th>
                                    <th style="padding: 10px; text-align: left; border-bottom: 2px solid #ddd;"><?php _e('Set Code', 'kavipushp-bridals'); ?></th>
                                    <th style="padding: 10px; text-align: right; border-bottom: 2px solid #ddd;"><?php _e('Amount', 'kavipushp-bridals'); ?></th>
                                </tr>
                            </thead>
                            <tbody id="inv-table-body">
                            </tbody>
                        </table>

                        <div id="inv-customization" style="display: none; background: #fff8e1; border: 1px solid #f0e68c; border-radius: 6px; padding: 12px 15px; margin: 15px 0;">
                            <strong style="color: #c9a86c;"><i class="dashicons dashicons-edit" style="font-size: 14px;"></i> <?php _e('Customization:', 'kavipushp-bridals'); ?></strong>
                            <span id="inv-customization-text" style="color: #333; margin-left: 5px;"></span>
                        </div>

                        <p id="inv-status" style="text-align: center; margin: 15px 0;"></p>
                    </div>

                    <div style="margin-top: 20px; display: flex; gap: 10px;">
                        <button class="button button-primary button-large" onclick="saveGeneratedInvoice()">
                            <i class="dashicons dashicons-download"></i> <?php _e('Save Invoice', 'kavipushp-bridals'); ?>
                        </button>
                        <button class="button button-large" onclick="printGeneratedInvoice()">
                            <i class="dashicons dashicons-printer"></i> <?php _e('Print Invoice', 'kavipushp-bridals'); ?>
                        </button>
                    </div>
                    <div id="invoice-save-msg" style="display:none; margin-top: 10px;"></div>
                </div>

                <div id="invoice-empty" style="text-align: center; padding: 40px; color: #999;">
                    <i class="dashicons dashicons-media-text" style="font-size: 48px; width: 48px; height: 48px;"></i>
                    <p><?php _e('Select a booking and invoice type above to preview and generate the invoice.', 'kavipushp-bridals'); ?></p>
                </div>
            </div>
        </div>

        <script>
        function formatDate(dateStr) {
            if (!dateStr) return 'N/A';
            var d = new Date(dateStr);
            return String(d.getDate()).padStart(2, '0') + '/' + String(d.getMonth() + 1).padStart(2, '0') + '/' + d.getFullYear();
        }

        var kpPaymentStatus = 'all_paid';
        var kpSetImage = '';

        function handleSetImageUpload(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    kpSetImage = e.target.result;
                    document.getElementById('set-image-thumb').src = kpSetImage;
                    document.getElementById('set-image-preview-thumb').style.display = 'block';
                    updateInvoicePreview();
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        function clearSetImage() {
            kpSetImage = '';
            document.getElementById('set_image_upload').value = '';
            document.getElementById('set-image-thumb').src = '';
            document.getElementById('set-image-preview-thumb').style.display = 'none';
            updateInvoicePreview();
        }

        function getSelectedInvoiceType() {
            var radios = document.querySelectorAll('input[name="invoice_type"]');
            for (var i = 0; i < radios.length; i++) {
                if (radios[i].checked) return radios[i].value;
            }
            return 'booking';
        }

        // Style the radio buttons on change
        document.addEventListener('change', function(e) {
            if (e.target.name === 'invoice_type') {
                var labels = document.querySelectorAll('input[name="invoice_type"]');
                labels.forEach(function(radio) {
                    var label = radio.closest('label');
                    if (radio.checked) {
                        label.style.background = '#c9a86c';
                        label.style.color = '#fff';
                        label.style.borderColor = '#c9a86c';
                    } else {
                        label.style.background = '#fff';
                        label.style.color = '#333';
                        label.style.borderColor = '#ddd';
                    }
                });
            }
        });

        function getInvoiceConfig(type, rent, bookingAmount, amountReceived, damagesAmount, paymentStatus) {
            var securityDeposit = 2000;
            amountReceived = amountReceived || 0;
            damagesAmount = damagesAmount || 0;
            paymentStatus = paymentStatus || 'all_paid';
            var config = {
                title: '',
                prefix: '',
                rows: [],
                grandTotal: 0,
                afterTotalRows: [],
                footerNote: ''
            };

            if (type === 'booking') {
                config.title = 'BOOKING INVOICE';
                config.prefix = 'BK';
                config.rows = [
                    { label: 'Bridal Set Rent', amount: rent, color: '' }
                ];
                if (bookingAmount > 0) {
                    config.rows.push({ label: 'Less: Booking Amount', amount: bookingAmount, color: '#e74c3c', isDeduction: true });
                }
                config.grandTotal = rent - bookingAmount;
                config.footerNote = 'This is a booking confirmation invoice. Remaining amount to be paid at the time of pickup.';
            } else if (type === 'pickup') {
                config.title = 'PICKUP INVOICE';
                config.prefix = 'PK';
                var rows = [
                    { label: 'Bridal Set Rent', amount: rent, color: '' }
                ];
                if (bookingAmount > 0) {
                    rows.push({ label: 'Less: Booking Amount', amount: bookingAmount, color: '#e74c3c', isDeduction: true });
                }
                rows.push({ label: 'Security Deposit (Refundable)', amount: securityDeposit, color: '#2980b9' });
                config.rows = rows;
                config.grandTotal = rent - bookingAmount + securityDeposit;
                var balance = config.grandTotal - amountReceived;
                config.afterTotalRows = [
                    { label: 'Less: Amount Received', amount: amountReceived, color: '#27ae60', isDeduction: true },
                    { label: 'Balance Amount', amount: balance, color: '#e74c3c', isBold: true }
                ];
                config.footerNote = 'Security deposit of \u20B92,000 will be refunded upon safe return of jewelry.';
            } else if (type === 'final') {
                config.title = 'FINAL INVOICE';
                config.prefix = 'FN';
                var rows = [
                    { label: 'Bridal Set Rent', amount: rent, color: '' }
                ];
                if (bookingAmount > 0) {
                    rows.push({ label: 'Less: Booking Amount Paid', amount: bookingAmount, color: '#e74c3c', isDeduction: true });
                }
                rows.push({ label: 'Security Deposit (Refundable)', amount: securityDeposit, color: '#2980b9' });
                config.rows = rows;
                config.grandTotal = rent - bookingAmount + securityDeposit;
                var securityRefund = securityDeposit - damagesAmount;
                var balance = config.grandTotal - securityRefund;
                var isAllPaid = paymentStatus === 'all_paid';
                var statusMsg = isAllPaid ? '\u2705 All Clear' : '\u26A0 Pending';
                config.afterTotalRows = [
                    { label: 'Less: Security Refund (Security \u2212 Damage Charges)', amount: securityRefund, color: '#27ae60', isDeduction: true },
                    { label: 'Balance Amount', amount: balance, color: isAllPaid ? '#27ae60' : '#e74c3c', isBold: true },
                    { label: 'Status', amount: null, statusMsg: statusMsg, isStatus: true }
                ];
                config.footerNote = isAllPaid
                    ? 'All payments cleared. Security deposit refunded. Thank you for choosing our services!'
                    : 'Payment of \u20B9' + balance.toLocaleString('en-IN') + ' is pending. Please clear dues.';
            }

            return config;
        }

        function updateInvoicePreview() {
            var sel = document.getElementById('invoice_booking_select');
            var bookingId = sel.value;

            if (!bookingId) {
                document.getElementById('invoice-preview').style.display = 'none';
                document.getElementById('invoice-empty').style.display = 'block';
                return;
            }

            var opt = sel.options[sel.selectedIndex];
            var invoiceType = getSelectedInvoiceType();

            var name = opt.dataset.name || '';
            var rent = parseFloat(opt.dataset.rent) || 0;
            var bookingAmount = parseFloat(opt.dataset.bookingAmount) || 0;

            // Show/hide amount received input (pickup only)
            var amtReceivedGroup = document.getElementById('amount-received-group');
            if (invoiceType === 'pickup') {
                amtReceivedGroup.style.display = 'block';
            } else {
                amtReceivedGroup.style.display = 'none';
                document.getElementById('amount_received').value = 0;
            }
            // Show/hide damages amount input (final only)
            var damagesGroup = document.getElementById('damages-amount-group');
            if (invoiceType === 'final') {
                damagesGroup.style.display = 'block';
            } else {
                damagesGroup.style.display = 'none';
                document.getElementById('damages_amount').value = 0;
                kpPaymentStatus = 'all_paid';
            }
            var amountReceived = parseFloat(document.getElementById('amount_received').value) || 0;
            var damagesAmount = parseFloat(document.getElementById('damages_amount').value) || 0;

            var config = getInvoiceConfig(invoiceType, rent, bookingAmount, amountReceived, damagesAmount, kpPaymentStatus);

            var invNum = config.prefix + '-' + String(bookingId).padStart(5, '0');

            document.getElementById('inv-title').textContent = config.title;
            document.getElementById('inv-number-date').textContent = 'Invoice #: ' + invNum + ' | Date: ' + (opt.dataset.date || '');
            document.getElementById('inv-customer-name').textContent = name;
            document.getElementById('inv-customer-phone').textContent = opt.dataset.phone || '';
            document.getElementById('inv-customer-email').textContent = opt.dataset.email || '';
            document.getElementById('inv-customer-address').textContent = opt.dataset.address || 'N/A';
            document.getElementById('inv-function-date').textContent = formatDate(opt.dataset.functionDate);
            document.getElementById('inv-pickup-date').textContent = formatDate(opt.dataset.pickupDate);
            document.getElementById('inv-return-date').textContent = formatDate(opt.dataset.returnDate);

            // Build all non-select rows as HTML string
            var tbodyHtml = '';
            var statusRowData = null;

            // Set name row
            tbodyHtml += '<tr>' +
                '<td style="padding: 10px; border-bottom: 1px solid #eee;">' + (opt.dataset.setName || '') + '</td>' +
                '<td style="padding: 10px; border-bottom: 1px solid #eee;">' + (opt.dataset.setCode || '') + '</td>' +
                '<td style="padding: 10px; text-align: right; border-bottom: 1px solid #eee;">\u20B9' + rent.toLocaleString('en-IN') + '</td>' +
                '</tr>';

            // Additional rows based on invoice type
            config.rows.forEach(function(row, index) {
                if (index === 0) return;
                var colorStyle = row.color ? ' color: ' + row.color + ';' : '';
                var amountText = row.isDeduction ? '- \u20B9' + row.amount.toLocaleString('en-IN') : '\u20B9' + row.amount.toLocaleString('en-IN');
                tbodyHtml += '<tr>' +
                    '<td style="padding: 10px; border-bottom: 1px solid #eee;' + colorStyle + '" colspan="2">' + row.label + '</td>' +
                    '<td style="padding: 10px; text-align: right; border-bottom: 1px solid #eee;' + colorStyle + '">' + amountText + '</td>' +
                    '</tr>';
            });

            // Grand total row
            tbodyHtml += '<tr style="font-weight: bold; font-size: 16px;">' +
                '<td style="padding: 12px; border-top: 2px solid #c9a86c;" colspan="2">Grand Total</td>' +
                '<td style="padding: 12px; text-align: right; border-top: 2px solid #c9a86c;">\u20B9' + config.grandTotal.toLocaleString('en-IN') + '</td>' +
                '</tr>';

            // After-total rows — collect status row separately, render rest as HTML
            if (config.afterTotalRows && config.afterTotalRows.length > 0) {
                config.afterTotalRows.forEach(function(row) {
                    if (row.isStatus) {
                        statusRowData = row; // will be appended via DOM after innerHTML is set
                        return;
                    }
                    var colorStyle = row.color ? ' color: ' + row.color + ';' : '';
                    var boldStyle = row.isBold ? ' font-weight: bold; font-size: 15px;' : '';
                    var amountText = row.isDeduction ? '- \u20B9' + row.amount.toLocaleString('en-IN') : '\u20B9' + row.amount.toLocaleString('en-IN');
                    tbodyHtml += '<tr>' +
                        '<td style="padding: 10px; border-bottom: 1px solid #eee;' + colorStyle + boldStyle + '" colspan="2">' + row.label + '</td>' +
                        '<td style="padding: 10px; text-align: right; border-bottom: 1px solid #eee;' + colorStyle + boldStyle + '">' + amountText + '</td>' +
                        '</tr>';
                });
            }

            // Set tbody HTML (no <select> inside, safe to use innerHTML)
            var tbody = document.getElementById('inv-table-body');
            tbody.innerHTML = tbodyHtml;

            // Append status row with <select> via DOM methods (avoids tbody innerHTML parsing issues)
            if (statusRowData) {
                var isAllClear = statusRowData.statusMsg.indexOf('All Clear') !== -1;
                var bgColor = isAllClear ? '#e8f5e9' : '#fff3e0';
                var txtColor = isAllClear ? '#27ae60' : '#e67e22';

                var tr = document.createElement('tr');

                var td1 = document.createElement('td');
                td1.colSpan = 2;
                td1.style.cssText = 'padding: 10px; border-bottom: 1px solid #eee; font-weight: bold; color: ' + txtColor + ';';
                td1.textContent = 'Payment Status';

                var td2 = document.createElement('td');
                td2.style.cssText = 'padding: 10px; border-bottom: 1px solid #eee; text-align: right;';

                var sel = document.createElement('select');
                sel.style.cssText = 'padding: 4px 8px; border: 1px solid #ddd; border-radius: 4px; background: ' + bgColor + '; color: ' + txtColor + '; font-weight: bold; cursor: pointer;';
                sel.addEventListener('change', function() {
                    kpPaymentStatus = this.value;
                    updateInvoicePreview();
                });

                var o1 = document.createElement('option');
                o1.value = 'all_paid';
                o1.textContent = '\u2705 All Clear';
                o1.selected = isAllClear;

                var o2 = document.createElement('option');
                o2.value = 'pending';
                o2.textContent = '\u26A0 Pending';
                o2.selected = !isAllClear;

                sel.appendChild(o1);
                sel.appendChild(o2);
                td2.appendChild(sel);
                tr.appendChild(td1);
                tr.appendChild(td2);
                tbody.appendChild(tr);
            }

            // Customization text
            var customization = opt.dataset.customization || '';
            if (customization.trim()) {
                document.getElementById('inv-customization-text').textContent = customization;
                document.getElementById('inv-customization').style.display = 'block';
            } else {
                document.getElementById('inv-customization').style.display = 'none';
            }

            // Set image
            if (kpSetImage) {
                document.getElementById('inv-set-image').src = kpSetImage;
                document.getElementById('inv-set-image-section').style.display = 'block';
            } else {
                document.getElementById('inv-set-image-section').style.display = 'none';
            }

            // Set Includes
            var setIncludes = document.getElementById('set_includes').value.trim();
            if (setIncludes) {
                document.getElementById('inv-set-includes-text').textContent = setIncludes;
                document.getElementById('inv-set-includes-section').style.display = 'block';
            } else {
                document.getElementById('inv-set-includes-section').style.display = 'none';
            }

            document.getElementById('inv-status').innerHTML = '<span style="background: #c9a86c; color: #fff; padding: 5px 15px; border-radius: 4px;">' + config.title + '</span>';

            document.getElementById('invoice-preview').style.display = 'block';
            document.getElementById('invoice-empty').style.display = 'none';
        }

        function printGeneratedInvoice() {
            var sel = document.getElementById('invoice_booking_select');
            var opt = sel.options[sel.selectedIndex];
            if (!opt || !opt.value) return;

            var invoiceType = getSelectedInvoiceType();
            var name = opt.dataset.name || '';
            var phone = opt.dataset.phone || '';
            var email = opt.dataset.email || '';
            var address = opt.dataset.address || 'N/A';
            var setName = opt.dataset.setName || '';
            var setCode = opt.dataset.setCode || '';
            var rent = parseFloat(opt.dataset.rent) || 0;
            var bookingAmount = parseFloat(opt.dataset.bookingAmount) || 0;
            var functionDate = formatDate(opt.dataset.functionDate);
            var pickupDate = formatDate(opt.dataset.pickupDate);
            var returnDate = formatDate(opt.dataset.returnDate);
            var invDate = opt.dataset.date || '';

            var customization = opt.dataset.customization || '';
            var setIncludes = document.getElementById('set_includes').value.trim();

            var amountReceived = parseFloat(document.getElementById('amount_received').value) || 0;
            var damagesAmount = parseFloat(document.getElementById('damages_amount').value) || 0;
            var config = getInvoiceConfig(invoiceType, rent, bookingAmount, amountReceived, damagesAmount, kpPaymentStatus);
            var invNum = config.prefix + '-' + String(opt.value).padStart(5, '0');

            // Build table rows for print
            var tableRows = '<tr><td>' + setName + '</td><td>' + setCode + '</td><td style="text-align:right;">\u20B9' + rent.toLocaleString('en-IN') + '</td></tr>';
            config.rows.forEach(function(row, index) {
                if (index === 0) return;
                var colorStyle = row.color ? ' style="color:' + row.color + ';"' : '';
                var amountText = row.isDeduction ? '- \u20B9' + row.amount.toLocaleString('en-IN') : '\u20B9' + row.amount.toLocaleString('en-IN');
                tableRows += '<tr><td colspan="2"' + colorStyle + '>' + row.label + '</td><td style="text-align:right;' + (row.color ? 'color:' + row.color + ';' : '') + '">' + amountText + '</td></tr>';
            });
            tableRows += '<tr class="total-row"><td colspan="2">Grand Total</td><td style="text-align:right;">\u20B9' + config.grandTotal.toLocaleString('en-IN') + '</td></tr>';
            if (config.afterTotalRows && config.afterTotalRows.length > 0) {
                config.afterTotalRows.forEach(function(row) {
                    if (row.isStatus) {
                        var isAllClear = row.statusMsg.indexOf('All Clear') !== -1;
                        tableRows += '<tr><td colspan="3" style="text-align:center; font-weight:bold; font-size:16px; padding:12px; background:' + (isAllClear ? '#e8f5e9; color:#27ae60;' : '#fff3e0; color:#e67e22;') + '">' + row.statusMsg + '</td></tr>';
                        return;
                    }
                    var colorStyle = row.color ? ' color:' + row.color + ';' : '';
                    var boldStyle = row.isBold ? ' font-weight:bold;' : '';
                    var amountText = row.isDeduction ? '- \u20B9' + row.amount.toLocaleString('en-IN') : '\u20B9' + row.amount.toLocaleString('en-IN');
                    tableRows += '<tr><td colspan="2" style="' + colorStyle + boldStyle + '">' + row.label + '</td><td style="text-align:right;' + colorStyle + boldStyle + '">' + amountText + '</td></tr>';
                });
            }

            var businessName = '<?php echo esc_js(get_option("kavipushp_business_name", "Kavipushp Jewels Rental")); ?>';
            var businessAddress = '<?php echo esc_js(get_option("kavipushp_business_address", "")); ?>';
            var businessPhone = '<?php echo esc_js(get_option("kavipushp_business_phone", "")); ?>';
            var businessEmail = '<?php echo esc_js(get_option("kavipushp_business_email", "")); ?>';

            var printWindow = window.open('', '_blank');
            printWindow.document.write(
                '<!DOCTYPE html><html><head><title>' + config.title + ' ' + invNum + '</title>' +
                '<style>' +
                'body { font-family: Arial, sans-serif; padding: 40px; max-width: 800px; margin: 0 auto; }' +
                '.header { text-align: center; border-bottom: 2px solid #c9a86c; padding-bottom: 20px; margin-bottom: 30px; }' +
                '.header h1 { color: #1a1f36; margin: 0 0 8px 0; font-size: 26px; }' +
                '.header p { color: #666; margin: 0; font-size: 13px; line-height: 1.6; }' +
                '.header .contact-line { margin-top: 5px; font-size: 12px; }' +
                '.info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px; }' +
                '.info-block h3 { color: #c9a86c; margin: 0 0 10px 0; font-size: 14px; text-transform: uppercase; }' +
                '.info-block p { margin: 5px 0; color: #333; }' +
                'table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }' +
                'th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }' +
                'th { background: #f5f5f5; font-weight: 600; }' +
                '.total-row { font-weight: bold; font-size: 18px; }' +
                '.total-row td { border-top: 2px solid #c9a86c; }' +
                '.footer { text-align: center; color: #666; font-size: 12px; margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd; }' +
                '@media print { body { padding: 20px; } }' +
                '</style></head><body>' +
                '<div class="header"><h1>' + businessName + '</h1>' +
                '<p>' + businessAddress + '</p>' +
                '<p class="contact-line">\u260E ' + businessPhone + ' &nbsp;|&nbsp; \u2709 ' + businessEmail + '</p></div>' +
                '<h2 style="text-align:center;color:#1a1f36;">' + config.title + '</h2>' +
                '<p style="text-align:center;color:#666;">Invoice #: ' + invNum + ' | Date: ' + invDate + '</p>' +
                '<div class="info-grid">' +
                '<div class="info-block"><h3>Bill To</h3>' +
                '<p><strong>' + name + '</strong></p><p>' + phone + '</p><p>' + email + '</p><p>' + address + '</p></div>' +
                '<div class="info-block"><h3>Rental Period</h3>' +
                '<p><strong>Function:</strong> ' + functionDate + '</p>' +
                '<p><strong>Pickup:</strong> ' + pickupDate + '</p>' +
                '<p><strong>Return:</strong> ' + returnDate + '</p></div>' +
                '</div>' +
                (kpSetImage ? '<div style="text-align:center;margin:15px 0;"><img src="' + kpSetImage + '" style="max-width:200px;max-height:160px;border:1px solid #ddd;border-radius:6px;"></div>' : '') +
                (setIncludes ? '<div style="background:#f9f0ff;border:1px solid #e0c8f0;border-radius:6px;padding:12px 15px;margin:10px 0 15px 0;"><strong style="color:#7b4fa6;">Set Includes:</strong> <span style="color:#333;margin-left:6px;">' + setIncludes + '</span></div>' : '') +
                '<table><thead><tr><th>Item</th><th>Set Code</th><th style="text-align:right;">Amount</th></tr></thead>' +
                '<tbody>' + tableRows + '</tbody></table>' +
                (customization.trim() ? '<div style="background:#fff8e1;border:1px solid #f0e68c;border-radius:6px;padding:12px 15px;margin:15px 0;"><strong style="color:#c9a86c;">Customization:</strong> ' + customization + '</div>' : '') +
                '<div class="footer"><p>Thank you for choosing ' + businessName + '!</p>' +
                '<p>' + config.footerNote + '</p></div>' +
                '<script>window.onload = function() { window.print(); }<\/script>' +
                '</body></html>'
            );
            printWindow.document.close();
        }

        function saveGeneratedInvoice() {
            var sel = document.getElementById('invoice_booking_select');
            var opt = sel.options[sel.selectedIndex];
            if (!opt || !opt.value) {
                alert('Please select a booking first.');
                return;
            }

            var invoiceType = getSelectedInvoiceType();
            var rent = parseFloat(opt.dataset.rent) || 0;
            var bookingAmount = parseFloat(opt.dataset.bookingAmount) || 0;
            var amountReceived = parseFloat(document.getElementById('amount_received').value) || 0;
            var damagesAmount = parseFloat(document.getElementById('damages_amount').value) || 0;
            var config = getInvoiceConfig(invoiceType, rent, bookingAmount, amountReceived, damagesAmount, kpPaymentStatus);
            var invNum = config.prefix + '-' + String(opt.value).padStart(5, '0');

            var data = {
                action: 'kavipushp_save_invoice',
                _wpnonce: '<?php echo wp_create_nonce("kavipushp_save_invoice"); ?>',
                booking_id: opt.value,
                invoice_number: invNum,
                invoice_type: invoiceType,
                customer_name: opt.dataset.name || '',
                customer_phone: opt.dataset.phone || '',
                customer_email: opt.dataset.email || '',
                customer_address: opt.dataset.address || '',
                set_name: opt.dataset.setName || '',
                set_code: opt.dataset.setCode || '',
                function_date: opt.dataset.functionDate || '',
                pickup_date: opt.dataset.pickupDate || '',
                return_date: opt.dataset.returnDate || '',
                rent_amount: rent,
                booking_amount: bookingAmount,
                security_deposit: (invoiceType === 'booking') ? 0 : 2000,
                grand_total: config.grandTotal,
                customization_notes: opt.dataset.customization || ''
            };

            var msgDiv = document.getElementById('invoice-save-msg');
            msgDiv.style.display = 'block';
            msgDiv.innerHTML = '<p style="color:#999;">Saving invoice...</p>';

            var xhr = new XMLHttpRequest();
            xhr.open('POST', ajaxurl);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    var res = JSON.parse(xhr.responseText);
                    if (res.success) {
                        msgDiv.innerHTML = '<div class="notice notice-success"><p><i class="dashicons dashicons-yes-alt"></i> ' + res.data.message + '</p></div>';
                    } else {
                        msgDiv.innerHTML = '<div class="notice notice-error"><p>' + (res.data || 'Error saving invoice.') + '</p></div>';
                    }
                }
            };

            var params = [];
            for (var key in data) {
                params.push(encodeURIComponent(key) + '=' + encodeURIComponent(data[key]));
            }
            xhr.send(params.join('&'));
        }

        // Auto-load if booking_id is provided
        document.addEventListener('DOMContentLoaded', function() {
            var sel = document.getElementById('invoice_booking_select');
            if (sel && sel.value) {
                updateInvoicePreview();
            }
        });
        </script>

        <?php else: ?>

        <?php
        $bookings = get_posts(array(
            'post_type'      => 'booking',
            'posts_per_page' => 30,
            'meta_query'     => array(
                array(
                    'key'     => '_booking_status',
                    'value'   => array('confirmed', 'picked_up', 'returned', 'completed'),
                    'compare' => 'IN',
                ),
            ),
            'orderby'        => 'date',
            'order'          => 'DESC',
        ));
        ?>

        <div class="kp-card">
            <div class="kp-card-header">
                <h2><i class="dashicons dashicons-media-text"></i> <?php printf(__('All Invoices (%d)', 'kavipushp-bridals'), count($bookings)); ?></h2>
            </div>
            <div class="kp-card-body kp-invoices-list">
                <?php if (!empty($bookings)): ?>
                    <?php foreach ($bookings as $booking):
                        $customer_name = get_post_meta($booking->ID, '_customer_name', true);
                        $customer_phone = get_post_meta($booking->ID, '_customer_phone', true);
                        $customer_address = get_post_meta($booking->ID, '_customer_address', true);
                        $status = get_post_meta($booking->ID, '_booking_status', true);
                        $total = get_post_meta($booking->ID, '_total_amount', true);
                        $inv_booking_amount = get_post_meta($booking->ID, '_booking_amount', true);
                        $set_id = get_post_meta($booking->ID, '_bridal_set_id', true);
                        $security_deposit = 2000;
                        $inv_grand_total = floatval($total) - floatval($inv_booking_amount) + $security_deposit;

                        // Generate invoice numbers
                        $invoice_date = get_the_date('Ymd', $booking);
                        $initials = strtoupper(substr($customer_name, 0, 2));
                        $fin_invoice = "FIN-{$invoice_date}-{$initials}-" . str_pad($booking->ID, 4, '0', STR_PAD_LEFT);
                        $pic_invoice = "PIC-{$invoice_date}-{$initials}-" . str_pad($booking->ID, 4, '0', STR_PAD_LEFT);

                        $invoice_type = ($status === 'picked_up' || $status === 'returned') ? 'pickup' : 'final';
                        $invoice_num = $invoice_type === 'pickup' ? $pic_invoice : $fin_invoice;
                    ?>
                    <div class="kp-invoice-card">
                        <div class="kp-invoice-header">
                            <div class="kp-invoice-info">
                                <h3>
                                    <?php echo esc_html($invoice_num); ?>
                                    <span class="kp-invoice-type kp-type-<?php echo $invoice_type; ?>"><?php echo ucfirst($invoice_type); ?></span>
                                    <span class="kp-invoice-status"><?php _e('Pending', 'kavipushp-bridals'); ?></span>
                                </h3>
                                <p class="kp-invoice-meta"><?php _e('Invoice:', 'kavipushp-bridals'); ?> <?php echo esc_html($invoice_num); ?></p>
                                <p><?php _e('Customer:', 'kavipushp-bridals'); ?> <?php echo esc_html($customer_name); ?></p>
                                <p><?php _e('Contact:', 'kavipushp-bridals'); ?> <?php echo esc_html($customer_phone); ?></p>
                                <p><?php _e('Address:', 'kavipushp-bridals'); ?> <?php echo esc_html($customer_address ?: 'N/A'); ?></p>
                            </div>
                            <div class="kp-invoice-actions">
                                <?php if ($invoice_type === 'pickup'): ?>
                                <button class="button" onclick="kavipushpGenerateReturn(<?php echo $booking->ID; ?>)">
                                    <?php _e('Generate Return', 'kavipushp-bridals'); ?>
                                </button>
                                <?php endif; ?>
                                <button class="button" onclick="kavipushpPrintInvoice(<?php echo $booking->ID; ?>)">
                                    <i class="dashicons dashicons-printer"></i> <?php _e('Print All 3', 'kavipushp-bridals'); ?>
                                </button>
                                <a href="<?php echo admin_url('post.php?post=' . $booking->ID . '&action=edit'); ?>" class="button button-small">
                                    <i class="dashicons dashicons-edit"></i> <?php _e('Edit', 'kavipushp-bridals'); ?>
                                </a>
                                <a href="<?php echo admin_url('admin.php?page=kavipushp-invoices&action=generate&booking_id=' . $booking->ID); ?>" class="button button-small">
                                    <i class="dashicons dashicons-visibility"></i> <?php _e('View', 'kavipushp-bridals'); ?>
                                </a>
                                <button class="button button-small kp-delete-btn">
                                    <i class="dashicons dashicons-trash"></i>
                                </button>
                            </div>
                        </div>
                        <div class="kp-invoice-details">
                            <div class="kp-detail-item">
                                <i class="dashicons dashicons-calendar"></i>
                                <span><?php echo get_the_date('d/m/Y', $booking); ?></span>
                            </div>
                            <div class="kp-detail-item">
                                <i class="dashicons dashicons-money-alt"></i>
                                <span><?php _e('Rent:', 'kavipushp-bridals'); ?> ₹<?php echo number_format($total); ?></span>
                            </div>
                            <?php if (floatval($inv_booking_amount) > 0): ?>
                            <div class="kp-detail-item" style="color: #e74c3c;">
                                <i class="dashicons dashicons-minus"></i>
                                <span><?php _e('Booking Amt:', 'kavipushp-bridals'); ?> ₹<?php echo number_format($inv_booking_amount); ?></span>
                            </div>
                            <?php endif; ?>
                            <div class="kp-detail-item kp-security-amount">
                                <i class="dashicons dashicons-shield"></i>
                                <span><?php _e('Security:', 'kavipushp-bridals'); ?> ₹<?php echo number_format($security_deposit); ?></span>
                            </div>
                            <div class="kp-detail-item" style="font-weight: bold;">
                                <i class="dashicons dashicons-tag"></i>
                                <span><?php _e('Total:', 'kavipushp-bridals'); ?> ₹<?php echo number_format($inv_grand_total); ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="kp-empty-state">
                        <i class="dashicons dashicons-media-text"></i>
                        <h3><?php _e('No invoices yet', 'kavipushp-bridals'); ?></h3>
                        <p><?php _e('Invoices will appear here once bookings are confirmed', 'kavipushp-bridals'); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php endif; // end generate/list action ?>
    </div>
    <?php
}

/**
 * Settings Page with Password Protection
 */
function kavipushp_render_settings_enhanced() {
    $settings_password = get_option('kavipushp_settings_password', 'admin123');
    $is_authenticated = isset($_SESSION['kavipushp_settings_auth']) && $_SESSION['kavipushp_settings_auth'] === true;

    // Check password submission
    if (isset($_POST['settings_password'])) {
        if ($_POST['settings_password'] === $settings_password) {
            $_SESSION['kavipushp_settings_auth'] = true;
            $is_authenticated = true;
        } else {
            echo '<div class="notice notice-error"><p>' . __('Incorrect password', 'kavipushp-bridals') . '</p></div>';
        }
    }

    // Save new password
    if (isset($_POST['new_settings_password']) && $is_authenticated) {
        update_option('kavipushp_settings_password', sanitize_text_field($_POST['new_settings_password']));
        echo '<div class="notice notice-success"><p>' . __('Password updated successfully!', 'kavipushp-bridals') . '</p></div>';
    }

    // Save settings
    if (isset($_POST['save_settings']) && $is_authenticated && check_admin_referer('kavipushp_settings')) {
        update_option('kavipushp_business_name', sanitize_text_field($_POST['business_name']));
        update_option('kavipushp_business_phone', sanitize_text_field($_POST['business_phone']));
        update_option('kavipushp_business_email', sanitize_email($_POST['business_email']));
        update_option('kavipushp_business_address', sanitize_textarea_field($_POST['business_address']));
        update_option('kavipushp_gst_number', sanitize_text_field($_POST['gst_number']));
        echo '<div class="notice notice-success"><p>' . __('Settings saved!', 'kavipushp-bridals') . '</p></div>';
    }
    ?>
    <div class="kavipushp-admin-wrap">
        <?php if (!$is_authenticated): ?>
        <!-- Password Protection Screen -->
        <div class="kp-settings-lock">
            <div class="kp-lock-card">
                <div class="kp-lock-icon">
                    <i class="dashicons dashicons-lock"></i>
                </div>
                <h2><?php _e('Settings Access', 'kavipushp-bridals'); ?></h2>
                <p><?php _e('Please enter the password to access Settings', 'kavipushp-bridals'); ?></p>

                <form method="post" class="kp-lock-form">
                    <div class="kp-form-group">
                        <label><?php _e('Password', 'kavipushp-bridals'); ?></label>
                        <div class="kp-password-input">
                            <input type="password" name="settings_password" id="settings-password" placeholder="<?php esc_attr_e('Enter settings password', 'kavipushp-bridals'); ?>" required>
                            <button type="button" class="kp-toggle-password" onclick="togglePasswordVisibility()">
                                <i class="dashicons dashicons-visibility"></i>
                            </button>
                        </div>
                    </div>
                    <button type="submit" class="button button-primary button-large kp-btn-full">
                        <?php _e('Access Settings', 'kavipushp-bridals'); ?>
                    </button>
                </form>

                <button class="button button-large kp-btn-full kp-btn-outline" onclick="document.getElementById('change-password-form').style.display='block'">
                    <i class="dashicons dashicons-admin-generic"></i> <?php _e('Set Custom Password', 'kavipushp-bridals'); ?>
                </button>

                <div class="kp-demo-password">
                    <strong><?php _e('Demo Password:', 'kavipushp-bridals'); ?></strong> admin123
                </div>

                <form method="post" id="change-password-form" style="display:none; margin-top: 20px;">
                    <div class="kp-form-group">
                        <input type="password" name="new_settings_password" placeholder="<?php esc_attr_e('Enter new password', 'kavipushp-bridals'); ?>" required>
                    </div>
                    <button type="submit" class="button"><?php _e('Update Password', 'kavipushp-bridals'); ?></button>
                </form>
            </div>
        </div>

        <script>
        function togglePasswordVisibility() {
            var input = document.getElementById('settings-password');
            input.type = input.type === 'password' ? 'text' : 'password';
        }
        </script>

        <?php else: ?>
        <!-- Settings Form (Authenticated) -->
        <div class="kp-page-title">
            <h1><?php _e('Settings', 'kavipushp-bridals'); ?></h1>
            <p><?php _e('Configure your rental business settings', 'kavipushp-bridals'); ?></p>
        </div>

        <!-- Bookings Per Month Card -->
        <?php
        global $wpdb;
        $monthly_bookings = $wpdb->get_results("
            SELECT
                DATE_FORMAT(pm.meta_value, '%Y-%m') as month_key,
                DATE_FORMAT(pm.meta_value, '%M %Y') as month_label,
                COUNT(*) as total
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_function_date'
            WHERE p.post_type = 'booking' AND p.post_status IN ('publish', 'draft', 'private')
            AND pm.meta_value IS NOT NULL AND pm.meta_value != ''
            GROUP BY month_key
            ORDER BY month_key DESC
            LIMIT 12
        ");
        $total_all = array_sum(array_column($monthly_bookings, 'total'));
        ?>
        <div class="kp-card" style="margin-bottom: 20px;">
            <div class="kp-card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <h2><i class="dashicons dashicons-chart-bar"></i> <?php _e('Total Bookings Per Month', 'kavipushp-bridals'); ?></h2>
                <span style="background: #c9a86c; color: #fff; padding: 4px 14px; border-radius: 20px; font-size: 13px; font-weight: 600;">
                    <?php printf(__('Total: %d', 'kavipushp-bridals'), $total_all); ?>
                </span>
            </div>
            <div class="kp-card-body">
                <?php if (!empty($monthly_bookings)): ?>
                <table class="kp-table" style="margin: 0;">
                    <thead>
                        <tr>
                            <th><?php _e('Month', 'kavipushp-bridals'); ?></th>
                            <th style="text-align: center;"><?php _e('No. of Bookings', 'kavipushp-bridals'); ?></th>
                            <th style="width: 50%;"><?php _e('', 'kavipushp-bridals'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $max_bookings = max(array_column($monthly_bookings, 'total'));
                        foreach ($monthly_bookings as $mb):
                            $bar_width = $max_bookings > 0 ? ($mb->total / $max_bookings) * 100 : 0;
                        ?>
                        <tr>
                            <td><strong><?php echo esc_html($mb->month_label); ?></strong></td>
                            <td style="text-align: center;">
                                <span style="background: #e8f5e9; color: #2ecc71; padding: 3px 12px; border-radius: 12px; font-weight: 700; font-size: 14px;">
                                    <?php echo intval($mb->total); ?>
                                </span>
                            </td>
                            <td>
                                <div style="background: #f0f0f0; border-radius: 8px; height: 22px; overflow: hidden;">
                                    <div style="background: linear-gradient(90deg, #c9a86c, #e8c87e); height: 100%; width: <?php echo $bar_width; ?>%; border-radius: 8px; transition: width 0.5s;"></div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p style="text-align: center; color: #999; padding: 20px;"><?php _e('No bookings found yet.', 'kavipushp-bridals'); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Rental Revenue Per Month Card -->
        <?php
        $monthly_revenue = $wpdb->get_results("
            SELECT
                DATE_FORMAT(fd.meta_value, '%Y-%m') as month_key,
                DATE_FORMAT(fd.meta_value, '%M %Y') as month_label,
                COALESCE(SUM(CAST(ta.meta_value AS DECIMAL(10,2))), 0) as total_rent,
                COALESCE(SUM(CAST(ba.meta_value AS DECIMAL(10,2))), 0) as total_booking_amt,
                COUNT(*) as bookings
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} fd ON p.ID = fd.post_id AND fd.meta_key = '_function_date'
            LEFT JOIN {$wpdb->postmeta} ta ON p.ID = ta.post_id AND ta.meta_key = '_total_amount'
            LEFT JOIN {$wpdb->postmeta} ba ON p.ID = ba.post_id AND ba.meta_key = '_booking_amount'
            WHERE p.post_type = 'booking' AND p.post_status IN ('publish', 'draft', 'private')
            AND fd.meta_value IS NOT NULL AND fd.meta_value != ''
            GROUP BY month_key
            ORDER BY month_key DESC
            LIMIT 12
        ");
        $grand_revenue = 0;
        foreach ($monthly_revenue as $mr) {
            $grand_revenue += floatval($mr->total_rent);
        }
        ?>
        <div class="kp-card" style="margin-bottom: 20px;">
            <div class="kp-card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <h2><i class="dashicons dashicons-money-alt"></i> <?php _e('Rental Revenue Per Month', 'kavipushp-bridals'); ?></h2>
                <span style="background: #27ae60; color: #fff; padding: 4px 14px; border-radius: 20px; font-size: 13px; font-weight: 600;">
                    <?php printf(__('Total: ₹%s', 'kavipushp-bridals'), number_format($grand_revenue)); ?>
                </span>
            </div>
            <div class="kp-card-body">
                <?php if (!empty($monthly_revenue)): ?>
                <table class="kp-table" style="margin: 0;">
                    <thead>
                        <tr>
                            <th><?php _e('Month', 'kavipushp-bridals'); ?></th>
                            <th style="text-align: center;"><?php _e('Bookings', 'kavipushp-bridals'); ?></th>
                            <th style="text-align: right;"><?php _e('Bridal Set Rent', 'kavipushp-bridals'); ?></th>
                            <th style="text-align: right;"><?php _e('Booking Amt Received', 'kavipushp-bridals'); ?></th>
                            <th style="width: 30%;"><?php _e('', 'kavipushp-bridals'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $max_revenue = max(array_map(function($r) { return floatval($r->total_rent); }, $monthly_revenue));
                        foreach ($monthly_revenue as $mr):
                            $rent = floatval($mr->total_rent);
                            $booking_amt = floatval($mr->total_booking_amt);
                            $bar_width = $max_revenue > 0 ? ($rent / $max_revenue) * 100 : 0;
                        ?>
                        <tr>
                            <td><strong><?php echo esc_html($mr->month_label); ?></strong></td>
                            <td style="text-align: center;">
                                <span style="background: #e3f2fd; color: #2980b9; padding: 3px 10px; border-radius: 12px; font-weight: 600; font-size: 13px;">
                                    <?php echo intval($mr->bookings); ?>
                                </span>
                            </td>
                            <td style="text-align: right; font-weight: 700; color: #1a1f36;">₹<?php echo number_format($rent); ?></td>
                            <td style="text-align: right; color: #27ae60; font-weight: 600;">₹<?php echo number_format($booking_amt); ?></td>
                            <td>
                                <div style="background: #f0f0f0; border-radius: 8px; height: 22px; overflow: hidden;">
                                    <div style="background: linear-gradient(90deg, #27ae60, #2ecc71); height: 100%; width: <?php echo $bar_width; ?>%; border-radius: 8px; transition: width 0.5s;"></div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p style="text-align: center; color: #999; padding: 20px;"><?php _e('No revenue data found yet.', 'kavipushp-bridals'); ?></p>
                <?php endif; ?>
            </div>
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
                            <label><?php _e('Phone', 'kavipushp-bridals'); ?></label>
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
                        <label><?php _e('Address', 'kavipushp-bridals'); ?></label>
                        <textarea name="business_address" rows="3"><?php echo esc_textarea(get_option('kavipushp_business_address')); ?></textarea>
                    </div>

                    <div class="kp-form-actions">
                        <button type="submit" name="save_settings" class="button button-primary"><?php _e('Save Settings', 'kavipushp-bridals'); ?></button>
                        <a href="<?php echo admin_url('admin.php?page=kavipushp-settings&logout=1'); ?>" class="button"><?php _e('Lock Settings', 'kavipushp-bridals'); ?></a>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Database Helper Functions
 */
function kavipushp_get_all_customers_from_db() {
    global $wpdb;
    $table = $wpdb->prefix . 'kavipushp_customers';

    // Check if custom table exists
    if ($wpdb->get_var("SHOW TABLES LIKE '$table'") === $table) {
        return $wpdb->get_results("SELECT *, 0 as booking_count FROM $table ORDER BY full_name ASC");
    }

    // Fallback to bookings data
    $results = $wpdb->get_results("
        SELECT DISTINCT
            pm1.meta_value as full_name,
            pm2.meta_value as email,
            pm3.meta_value as phone,
            pm4.meta_value as address,
            pm5.meta_value as function_date,
            MIN(p.ID) as id
        FROM {$wpdb->posts} p
        LEFT JOIN {$wpdb->postmeta} pm1 ON p.ID = pm1.post_id AND pm1.meta_key = '_customer_name'
        LEFT JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_customer_email'
        LEFT JOIN {$wpdb->postmeta} pm3 ON p.ID = pm3.post_id AND pm3.meta_key = '_customer_phone'
        LEFT JOIN {$wpdb->postmeta} pm4 ON p.ID = pm4.post_id AND pm4.meta_key = '_customer_address'
        LEFT JOIN {$wpdb->postmeta} pm5 ON p.ID = pm5.post_id AND pm5.meta_key = '_pickup_date'
        WHERE p.post_type = 'booking' AND p.post_status = 'publish'
        AND pm1.meta_value IS NOT NULL AND pm1.meta_value != ''
        GROUP BY pm2.meta_value
        ORDER BY pm1.meta_value ASC
    ");

    foreach ($results as &$row) {
        $row->booking_count = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) FROM {$wpdb->postmeta}
            WHERE meta_key = '_customer_email' AND meta_value = %s
        ", $row->email));
    }

    return $results;
}

function kavipushp_get_customer_by_id($id) {
    global $wpdb;
    $table = $wpdb->prefix . 'kavipushp_customers';

    if ($wpdb->get_var("SHOW TABLES LIKE '$table'") === $table) {
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id));
    }
    return null;
}

function kavipushp_save_customer_data($data) {
    global $wpdb;
    $table = $wpdb->prefix . 'kavipushp_customers';

    // Ensure table exists
    kavipushp_create_tables();

    // Calculate return_date as function_date + 1 day if not provided
    $return_date = '';
    if (!empty($data['return_date'])) {
        $return_date = sanitize_text_field($data['return_date']);
    } elseif (!empty($data['function_date'])) {
        $func_date = new DateTime($data['function_date']);
        $func_date->modify('+1 day');
        $return_date = $func_date->format('Y-m-d');
    }

    // Calculate pickup_date as function_date - 1 day if not provided
    $pickup_date = '';
    if (!empty($data['pickup_date'])) {
        $pickup_date = sanitize_text_field($data['pickup_date']);
    } elseif (!empty($data['function_date'])) {
        $func_date_pickup = new DateTime($data['function_date']);
        $func_date_pickup->modify('-1 day');
        $pickup_date = $func_date_pickup->format('Y-m-d');
    }

    $customer_data = array(
        'full_name'       => sanitize_text_field($data['full_name']),
        'phone'           => sanitize_text_field($data['contact_number']),
        'email'           => sanitize_email($data['email']),
        'address'         => sanitize_textarea_field($data['address']),
        'id_proof_type'   => sanitize_text_field($data['id_proof_type']),
        'id_proof_number' => sanitize_text_field($data['id_proof_number']),
        'function_date'   => sanitize_text_field($data['function_date']),
        'return_date'     => $return_date,
        'booking_date'    => sanitize_text_field($data['booking_date']),
        'pickup_date'     => $pickup_date,
        'referral_source' => sanitize_text_field($data['referral_source']),
        'notes'           => sanitize_textarea_field($data['notes']),
    );

    if (!empty($data['customer_id'])) {
        $wpdb->update($table, $customer_data, array('id' => intval($data['customer_id'])));
        return intval($data['customer_id']);
    } else {
        $wpdb->insert($table, $customer_data);
        return $wpdb->insert_id;
    }
}

function kavipushp_delete_customer($id) {
    global $wpdb;
    $table = $wpdb->prefix . 'kavipushp_customers';
    $wpdb->delete($table, array('id' => $id));
}

/**
 * AJAX handler for viewing booking details
 */
add_action('wp_ajax_kavipushp_view_booking', function () {
    check_ajax_referer('kavipushp_view_booking', '_wpnonce');

    $booking_id = intval($_POST['booking_id']);
    $booking = get_post($booking_id);

    if (!$booking || $booking->post_type !== 'booking') {
        wp_send_json_error('Booking not found');
    }

    $set_id = get_post_meta($booking_id, '_bridal_set_id', true);
    $set = $set_id ? get_post($set_id) : null;
    $set_code = $set_id ? get_post_meta($set_id, '_set_id', true) : '';
    $function_date = get_post_meta($booking_id, '_function_date', true);
    $pickup_date = get_post_meta($booking_id, '_pickup_date', true);
    if (!$function_date) $function_date = $pickup_date;

    wp_send_json_success(array(
        'booking_id'       => 'BK-' . substr(md5($booking_id), 0, 8),
        'customer_name'    => get_post_meta($booking_id, '_customer_name', true) ?: 'N/A',
        'customer_phone'   => get_post_meta($booking_id, '_customer_phone', true) ?: 'N/A',
        'customer_email'   => get_post_meta($booking_id, '_customer_email', true) ?: 'N/A',
        'customer_address' => get_post_meta($booking_id, '_customer_address', true) ?: 'N/A',
        'set_name'         => $set ? ($set_code ?: $set->post_title) : 'N/A',
        'function_date'    => $function_date ? date('d/m/Y', strtotime($function_date)) : 'N/A',
        'pickup_date'      => $pickup_date ? date('d/m/Y', strtotime($pickup_date)) : 'N/A',
        'return_date'      => ($rd = get_post_meta($booking_id, '_return_date', true)) ? date('d/m/Y', strtotime($rd)) : 'N/A',
        'total_amount'     => number_format(get_post_meta($booking_id, '_total_amount', true)),
        'status'           => get_post_meta($booking_id, '_booking_status', true) ?: 'pending',
        'created_date'     => get_the_date('d/m/Y h:i A', $booking),
    ));
});

// Start session for settings auth
add_action('init', function() {
    if (!session_id()) {
        session_start();
    }

    // Handle settings logout
    if (isset($_GET['page']) && $_GET['page'] === 'kavipushp-settings' && isset($_GET['logout'])) {
        unset($_SESSION['kavipushp_settings_auth']);
        wp_redirect(admin_url('admin.php?page=kavipushp-settings'));
        exit;
    }
});

/**
 * Update customers table schema
 */
function kavipushp_update_customers_table() {
    global $wpdb;
    $table = $wpdb->prefix . 'kavipushp_customers';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        full_name varchar(255) NOT NULL,
        email varchar(255),
        phone varchar(20) NOT NULL,
        alt_phone varchar(20),
        address text,
        id_proof_type varchar(50),
        id_proof_number varchar(100),
        function_date date,
        return_date date,
        booking_date date,
        pickup_date date,
        referral_source varchar(50),
        notes text,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY email (email),
        KEY phone (phone)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
add_action('after_switch_theme', 'kavipushp_update_customers_table');
