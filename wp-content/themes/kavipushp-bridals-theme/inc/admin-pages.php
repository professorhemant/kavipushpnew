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

    // Handle invoice (booking) delete
    if (isset($_GET['page']) && $_GET['page'] === 'kavipushp-invoices'
        && isset($_GET['action']) && $_GET['action'] === 'delete'
        && isset($_GET['id'])
    ) {
        $booking_id = intval($_GET['id']);
        check_admin_referer('delete_invoice_' . $booking_id);
        if ($booking_id) {
            wp_delete_post($booking_id, true);
            wp_redirect(admin_url('admin.php?page=kavipushp-invoices&deleted=1'));
            exit;
        }
    }

    // Handle saved invoice delete (from wp_kavipushp_invoices table)
    if (isset($_GET['page']) && $_GET['page'] === 'kavipushp-invoices'
        && isset($_GET['action']) && $_GET['action'] === 'delete_saved'
        && isset($_GET['id'])
    ) {
        $inv_id = intval($_GET['id']);
        check_admin_referer('delete_saved_invoice_' . $inv_id);
        if ($inv_id) {
            global $wpdb;
            $wpdb->delete($wpdb->prefix . 'kavipushp_invoices', array('id' => $inv_id));
            wp_redirect(admin_url('admin.php?page=kavipushp-invoices&deleted=1'));
            exit;
        }
    }

    // Handle bulk delete bookings
    if (isset($_POST['page']) && $_POST['page'] === 'kavipushp-bookings'
        && isset($_POST['bulk_action']) && in_array($_POST['bulk_action'], ['delete_selected', 'delete_all'])
    ) {
        check_admin_referer('kp_bulk_bookings');
        if ($_POST['bulk_action'] === 'delete_all') {
            $all = get_posts(array('post_type' => 'booking', 'posts_per_page' => -1, 'fields' => 'ids'));
            foreach ($all as $id) { wp_delete_post($id, true); }
        } elseif (!empty($_POST['booking_ids']) && is_array($_POST['booking_ids'])) {
            foreach (array_map('intval', $_POST['booking_ids']) as $id) { wp_delete_post($id, true); }
        }
        wp_redirect(admin_url('admin.php?page=kavipushp-bookings&deleted=1'));
        exit;
    }

    // Handle bulk delete customers
    if (isset($_POST['page']) && $_POST['page'] === 'kavipushp-customers'
        && isset($_POST['bulk_action']) && in_array($_POST['bulk_action'], ['delete_selected', 'delete_all'])
    ) {
        check_admin_referer('kp_bulk_customers');
        global $wpdb;
        $table = $wpdb->prefix . 'kavipushp_customers';
        if ($_POST['bulk_action'] === 'delete_all') {
            $all_ids = $wpdb->get_col("SELECT id FROM $table");
            foreach ($all_ids as $id) { kavipushp_delete_customer(intval($id)); }
        } elseif (!empty($_POST['customer_ids']) && is_array($_POST['customer_ids'])) {
            foreach (array_map('intval', $_POST['customer_ids']) as $id) { kavipushp_delete_customer($id); }
        }
        wp_redirect(admin_url('admin.php?page=kavipushp-customers&deleted=1'));
        exit;
    }

    // Handle bulk delete invoices
    if (isset($_POST['page']) && $_POST['page'] === 'kavipushp-invoices'
        && isset($_POST['bulk_action']) && in_array($_POST['bulk_action'], ['delete_selected', 'delete_all'])
    ) {
        check_admin_referer('kp_bulk_invoices');
        global $wpdb;
        if ($_POST['bulk_action'] === 'delete_all') {
            $wpdb->query("DELETE FROM {$wpdb->prefix}kavipushp_invoices");
        } elseif (!empty($_POST['invoice_ids']) && is_array($_POST['invoice_ids'])) {
            $ids = implode(',', array_map('intval', $_POST['invoice_ids']));
            $wpdb->query("DELETE FROM {$wpdb->prefix}kavipushp_invoices WHERE id IN ($ids)");
        }
        wp_redirect(admin_url('admin.php?page=kavipushp-invoices&deleted=1'));
        exit;
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
                            <select id="select_existing_customer" onchange="fillExistingCustomer(this.value)" style="width:100%;max-width:450px;padding:8px;">
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
                                <label><?php _e('Return Date', 'kavipushp-bridals'); ?> <small>(auto +1 day, editable)</small></label>
                                <input type="date" name="return_date" id="return_date" value="<?php echo $customer ? esc_attr($customer->return_date) : ''; ?>">
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
                        var returnDateManuallyEdited = false;
                        document.addEventListener('DOMContentLoaded', function() {
                            // If return_date already has a saved value, treat it as manually set
                            if (document.getElementById('return_date').value) {
                                returnDateManuallyEdited = true;
                            }
                            document.getElementById('return_date').addEventListener('change', function() {
                                returnDateManuallyEdited = true;
                            });
                            // On page load, only auto-fill pickup date (not return date if already saved)
                            if (document.getElementById('function_date').value) {
                                calculateReturnDate();
                            }
                        });
                        function calculateReturnDate() {
                            var functionDate = document.getElementById('function_date').value;
                            if (functionDate) {
                                // Only auto-set return date if user hasn't manually edited it
                                if (!returnDateManuallyEdited) {
                                    var returnDateObj = new Date(functionDate);
                                    returnDateObj.setDate(returnDateObj.getDate() + 1);
                                    document.getElementById('return_date').value = returnDateObj.toISOString().split('T')[0];
                                }
                                // Calculate pickup date (function date - 1 day)
                                var pickupDateObj = new Date(functionDate);
                                pickupDateObj.setDate(pickupDateObj.getDate() - 1);
                                document.getElementById('pickup_date').value = pickupDateObj.toISOString().split('T')[0];
                            }
                        }
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

            <form method="post" id="kp-customers-bulk-form">
                <?php wp_nonce_field('kp_bulk_customers'); ?>
                <input type="hidden" name="page" value="kavipushp-customers">

            <div class="kp-card">
                <div class="kp-card-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
                    <h2><?php _e('All Customers', 'kavipushp-bridals'); ?></h2>
                    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                        <input type="text" id="search-customers" placeholder="<?php esc_attr_e('Search customers...', 'kavipushp-bridals'); ?>" class="kp-search-input">
                        <button type="submit" name="bulk_action" value="delete_selected" class="button kp-delete-btn" onclick="return confirm('<?php esc_attr_e('Delete selected customers?', 'kavipushp-bridals'); ?>')">
                            <i class="dashicons dashicons-trash"></i> <?php _e('Delete Selected', 'kavipushp-bridals'); ?>
                        </button>
                        <button type="submit" name="bulk_action" value="delete_all" class="button" style="background:#e74c3c;color:#fff;border-color:#c0392b;" onclick="return confirm('<?php esc_attr_e('Delete ALL customers? This cannot be undone!', 'kavipushp-bridals'); ?>')">
                            <i class="dashicons dashicons-trash"></i> <?php _e('Delete All', 'kavipushp-bridals'); ?>
                        </button>
                    </div>
                </div>
                <div class="kp-card-body">
                    <table class="kp-table" id="customers-table">
                        <thead>
                            <tr>
                                <th style="width:30px;"><input type="checkbox" id="kp-select-all-customers" title="<?php esc_attr_e('Select All', 'kavipushp-bridals'); ?>"></th>
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
                                <td><input type="checkbox" name="customer_ids[]" value="<?php echo intval($customer->id); ?>" class="kp-customer-cb"></td>
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
                                <td colspan="7" class="kp-no-data"><?php _e('No customers found. Add your first customer!', 'kavipushp-bridals'); ?></td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            </form>
            <script>
            document.getElementById('kp-select-all-customers') && document.getElementById('kp-select-all-customers').addEventListener('change', function() {
                document.querySelectorAll('.kp-customer-cb').forEach(function(cb) { cb.checked = this.checked; }, this);
            });
            </script>
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
                fgetcsv($handle, 1000, ","); // Skip header row
                $count_new = 0;
                $count_updated = 0;
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    // Auto-detect CSV format by column count:
                    // 4-col: Box No [0], Item No/Barcode [1], Item Name [2], Price [3]
                    // 5-col: s.no [0], Title [1], Category [2], Set ID [3], Price [4]
                    $col_count = count($data);
                    if ($col_count >= 5) {
                        $title     = sanitize_text_field(trim($data[1]));
                        $category  = sanitize_text_field(trim($data[2]));
                        $set_id    = sanitize_text_field(trim($data[3]));
                        $raw_price = isset($data[4]) ? trim($data[4]) : '0';
                    } else {
                        $category  = sanitize_text_field(trim($data[0])); // Box No as category
                        $set_id    = sanitize_text_field(trim($data[1])); // Item No / barcode
                        $title     = sanitize_text_field(trim($data[2])); // Item Name
                        $raw_price = isset($data[3]) ? trim($data[3]) : '0';
                    }

                    if (empty($set_id)) continue; // Skip rows with no barcode

                    // Strip ₹, commas, currency symbols — "₹5,000" → 5000
                    $price = floatval(preg_replace('/[^\d.]/u', '', $raw_price));

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
                <div class="kp-inventory-title" style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                    <!-- View tabs -->
                    <div style="display:flex;border:2px solid #8e44ad;border-radius:8px;overflow:hidden;">
                        <button id="kp-tab-cards" onclick="kpSwitchView('cards')"
                            style="padding:7px 16px;font-size:13px;font-weight:600;border:none;cursor:pointer;background:#8e44ad;color:#fff;">
                            <i class="dashicons dashicons-grid-view" style="font-size:14px;width:14px;height:14px;vertical-align:middle;margin-right:4px;"></i>Card View
                        </button>
                        <button id="kp-tab-table" onclick="kpSwitchView('table')"
                            style="padding:7px 16px;font-size:13px;font-weight:600;border:none;cursor:pointer;background:#fff;color:#8e44ad;">
                            <i class="dashicons dashicons-list-view" style="font-size:14px;width:14px;height:14px;vertical-align:middle;margin-right:4px;"></i>All Products
                        </button>
                    </div>
                    <span class="kp-db-indicator">Database</span>
                    <span class="kp-live-badge"><?php _e('Live', 'kavipushp-bridals'); ?></span>
                    <span style="font-size:13px;color:#888;"><?php echo $total_items; ?> items</span>
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
                    <button class="button button-primary" style="background:#8e44ad;border-color:#7d3c98;" onclick="document.getElementById('kp-bulk-img-input').click()">
                        <i class="dashicons dashicons-images-alt2"></i> <?php _e('Bulk Upload Images', 'kavipushp-bridals'); ?>
                    </button>
                    <input type="file" id="kp-bulk-img-input" accept="image/*" multiple style="display:none;" onchange="kpBulkImagesSelected(this)">
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
                <!-- Bulk Upload Panel (shown after files are selected) -->
                <div id="kp-bulk-upload-panel" style="display:none;margin-bottom:16px;background:#faf7ff;border:2px solid #8e44ad;border-radius:10px;padding:18px 20px;"></div>

                <!-- ═══ CARD VIEW ═══════════════════════════════════════════════ -->
                <div id="kp-view-cards">
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
                        // Options store is the primary source (survives deploys + Clear All)
                        $thumb_url = $set_code ? get_option('kp_setimg_' . sanitize_key($set_code), '') : '';
                        if (!$thumb_url) {
                            $thumb_id = get_post_thumbnail_id($set->ID);
                            if ($thumb_id) {
                                $thumb_url = wp_get_attachment_image_url($thumb_id, 'medium');
                                if (!$thumb_url) $thumb_url = wp_get_attachment_image_url($thumb_id, 'full');
                                if (!$thumb_url) $thumb_url = wp_get_attachment_url($thumb_id);
                            }
                        }
                    ?>
                    <div class="kp-inv-card" data-set-code="<?php echo esc_attr($set_code ?: 'KP' . $set->ID); ?>" data-post-id="<?php echo $set->ID; ?>">
                        <div class="kp-inv-img-zone" onclick="kpTriggerUpload(<?php echo $set->ID; ?>)">
                            <?php if ($thumb_url): ?>
                            <img src="<?php echo esc_attr($thumb_url); ?>" alt="<?php echo esc_attr($set->post_title); ?>">
                            <?php else: ?>
                            <div class="kp-inv-img-placeholder">
                                <span class="dashicons dashicons-camera"></span>
                                <span>Add Photo</span>
                            </div>
                            <?php endif; ?>
                            <div class="kp-inv-img-overlay">
                                <button class="kp-inv-img-btn" type="button" onclick="event.stopPropagation(); kpTriggerUpload(<?php echo $set->ID; ?>)">
                                    <span class="dashicons dashicons-upload"></span> Change
                                </button>
                                <?php if ($thumb_url): ?>
                                <button class="kp-inv-img-btn remove" type="button" onclick="event.stopPropagation(); kpRemoveImage(<?php echo $set->ID; ?>, this)">
                                    <span class="dashicons dashicons-trash"></span> Remove
                                </button>
                                <?php endif; ?>
                            </div>
                            <input type="file" id="kp-img-input-<?php echo $set->ID; ?>" accept="image/*" style="display:none;" onchange="kpUploadImage(<?php echo $set->ID; ?>, this)">
                        </div>
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
                            <span class="kp-inv-price"><?php if (floatval($rental_price) > 0): ?><?php echo number_format($rental_price); ?> <span class="kp-inv-per-day">/day</span><?php else: ?><span style="color:#e74c3c;font-size:11px;">No price</span><?php endif; ?></span>
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
                </div><!-- /#kp-view-cards -->

                <!-- ═══ TABLE VIEW — ALL PRODUCTS ══════════════════════════════ -->
                <div id="kp-view-table" style="display:none;">

                    <!-- Search + filter bar -->
                    <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:14px;">
                        <input type="text" id="kp-tbl-search" placeholder="Search by name, barcode, category..."
                            style="flex:1;min-width:220px;padding:8px 12px;border:1px solid #d0b8e8;border-radius:6px;font-size:13px;">
                        <select id="kp-tbl-cat" style="padding:8px 10px;border:1px solid #d0b8e8;border-radius:6px;font-size:13px;background:#fff;">
                            <option value="">All Categories</option>
                            <?php
                            $all_cats = get_terms(array('taxonomy' => 'bridal_category', 'hide_empty' => false));
                            if ($all_cats && !is_wp_error($all_cats)):
                                foreach ($all_cats as $tc): ?>
                            <option value="<?php echo esc_attr(strtolower($tc->name)); ?>"><?php echo esc_html($tc->name); ?></option>
                            <?php endforeach; endif; ?>
                        </select>
                        <span id="kp-tbl-count" style="font-size:13px;color:#8e44ad;font-weight:600;"><?php echo $total_items; ?> items</span>
                        <button class="button" onclick="kpTblExportCSV()" style="white-space:nowrap;">
                            <i class="dashicons dashicons-download"></i> Export CSV
                        </button>
                        <button class="button" onclick="window.print()" style="white-space:nowrap;">
                            <i class="dashicons dashicons-printer"></i> Print
                        </button>
                    </div>

                    <!-- Table -->
                    <div style="overflow-x:auto;border-radius:8px;border:1px solid #e0d0f5;">
                    <table id="kp-tbl" style="width:100%;border-collapse:collapse;font-size:13px;">
                        <thead>
                            <tr style="background:linear-gradient(135deg,#8e44ad,#6c3483);color:#fff;">
                                <th style="padding:11px 10px;width:76px;text-align:center;">Image</th>
                                <th style="padding:11px 8px;width:44px;">#</th>
                                <th style="padding:11px 10px;cursor:pointer;white-space:nowrap;" onclick="kpTblSort('code')">Item No <span>&#8597;</span></th>
                                <th style="padding:11px 10px;cursor:pointer;" onclick="kpTblSort('name')">Item Name <span>&#8597;</span></th>
                                <th style="padding:11px 10px;cursor:pointer;" onclick="kpTblSort('cat')">Category <span>&#8597;</span></th>
                                <th style="padding:11px 10px;cursor:pointer;text-align:right;white-space:nowrap;" onclick="kpTblSort('price')">Rental Price <span>&#8597;</span></th>
                                <th style="padding:11px 10px;text-align:center;width:60px;">Edit</th>
                            </tr>
                        </thead>
                        <tbody id="kp-tbl-body">
                        <?php
                        $tbl_sno = 0;
                        foreach ($sets as $tbl_set):
                            $tbl_sno++;
                            $tbl_code  = get_post_meta($tbl_set->ID, '_set_id', true) ?: ('KP' . $tbl_set->ID);
                            $tbl_price = (float) get_post_meta($tbl_set->ID, '_rental_price', true);
                            $tbl_cats  = get_the_terms($tbl_set->ID, 'bridal_category');
                            $tbl_cat   = ($tbl_cats && !is_wp_error($tbl_cats)) ? $tbl_cats[0]->name : 'Uncategorized';
                            $tbl_img   = get_option('kp_setimg_' . sanitize_key($tbl_code), '');
                            if (!$tbl_img) {
                                $tbl_tid = get_post_thumbnail_id($tbl_set->ID);
                                if ($tbl_tid) $tbl_img = wp_get_attachment_image_url($tbl_tid, 'thumbnail') ?: wp_get_attachment_url($tbl_tid);
                            }
                            $tbl_bg = ($tbl_sno % 2 === 0) ? '#faf7ff' : '#fff';
                        ?>
                        <tr class="kp-tbl-row"
                            data-name="<?php echo esc_attr(strtolower($tbl_set->post_title)); ?>"
                            data-code="<?php echo esc_attr(strtolower($tbl_code)); ?>"
                            data-cat="<?php echo esc_attr(strtolower($tbl_cat)); ?>"
                            data-price="<?php echo esc_attr($tbl_price); ?>"
                            style="background:<?php echo $tbl_bg; ?>;border-bottom:1px solid #ede8f5;"
                            onmouseenter="this.style.background='#f3eeff'" onmouseleave="this.style.background='<?php echo $tbl_bg; ?>'">

                            <!-- Image — click to upload -->
                            <td style="padding:6px 10px;text-align:center;vertical-align:middle;">
                                <div style="position:relative;display:inline-block;cursor:pointer;"
                                    onclick="document.getElementById('kp-tbl-img-<?php echo $tbl_set->ID; ?>').click()" title="Click to change photo">
                                    <?php if ($tbl_img): ?>
                                    <img src="<?php echo esc_attr($tbl_img); ?>" alt=""
                                        style="width:56px;height:56px;object-fit:cover;border-radius:6px;border:2px solid #d0b8e8;display:block;">
                                    <?php else: ?>
                                    <div style="width:56px;height:56px;border-radius:6px;border:2px dashed #d0b8e8;display:flex;flex-direction:column;align-items:center;justify-content:center;background:#f8f3ff;color:#b8a0d0;font-size:10px;gap:2px;">
                                        <span class="dashicons dashicons-camera" style="font-size:16px;width:16px;height:16px;"></span>
                                        <span>Add</span>
                                    </div>
                                    <?php endif; ?>
                                    <input type="file" id="kp-tbl-img-<?php echo $tbl_set->ID; ?>" accept="image/*" style="display:none;"
                                        onchange="kpTblUpload(<?php echo $tbl_set->ID; ?>, this)">
                                </div>
                            </td>

                            <td style="padding:6px 8px;color:#aaa;vertical-align:middle;text-align:center;"><?php echo $tbl_sno; ?></td>
                            <td style="padding:6px 10px;font-weight:700;color:#8e44ad;vertical-align:middle;white-space:nowrap;"><?php echo esc_html($tbl_code); ?></td>
                            <td style="padding:6px 10px;font-weight:500;vertical-align:middle;"><?php echo esc_html($tbl_set->post_title); ?></td>
                            <td style="padding:6px 10px;vertical-align:middle;">
                                <span style="background:#f3eeff;color:#6c3483;border-radius:12px;padding:2px 10px;font-size:12px;white-space:nowrap;"><?php echo esc_html($tbl_cat); ?></span>
                            </td>
                            <td style="padding:6px 10px;text-align:right;vertical-align:middle;white-space:nowrap;" class="kp-price-cell" data-post-id="<?php echo $tbl_set->ID; ?>" data-price="<?php echo esc_attr($tbl_price); ?>">
                                <span class="kp-price-display" onclick="kpTblEditPrice(this)" title="Click to edit price" style="cursor:pointer;">
                                    <?php if ($tbl_price > 0): ?>
                                    <strong style="color:#27ae60;font-size:14px;">&#8377;<?php echo number_format($tbl_price); ?></strong>
                                    <span style="font-size:11px;color:#aaa;">/day</span>
                                    <?php else: ?>
                                    <span style="color:#e74c3c;font-size:12px;border-bottom:1px dashed #e74c3c;">Set Price</span>
                                    <?php endif; ?>
                                </span>
                            </td>
                            <td style="padding:6px 10px;text-align:center;vertical-align:middle;">
                                <a href="<?php echo get_edit_post_link($tbl_set->ID); ?>" style="color:#8e44ad;" title="Edit">
                                    <span class="dashicons dashicons-edit" style="font-size:16px;width:16px;height:16px;"></span>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    </div><!-- /overflow-x -->
                </div><!-- /#kp-view-table -->

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

    var kpSetImageNonce = '<?php echo wp_create_nonce("kp_set_image"); ?>';

    function kpTriggerUpload(postId) {
        document.getElementById('kp-img-input-' + postId).click();
    }

    function kpUploadImage(postId, input) {
        if (!input.files || !input.files[0]) return;
        var zone = input.closest('.kp-inv-img-zone');
        zone.classList.add('kp-inv-img-uploading');
        var fd = new FormData();
        fd.append('action', 'kavipushp_upload_set_image');
        fd.append('_wpnonce', kpSetImageNonce);
        fd.append('post_id', postId);
        fd.append('image', input.files[0]);
        var xhr = new XMLHttpRequest();
        xhr.open('POST', ajaxurl);
        xhr.onload = function() {
            zone.classList.remove('kp-inv-img-uploading');
            input.value = '';
            var res;
            try { res = JSON.parse(xhr.responseText); } catch(e) {
                alert('Upload error: server returned unexpected response. Check for PHP errors.');
                return;
            }
            if (res.success) {
                var existing = zone.querySelector('img');
                var placeholder = zone.querySelector('.kp-inv-img-placeholder');
                if (placeholder) placeholder.remove();
                if (existing) {
                    existing.src = res.data.url;
                } else {
                    var img = document.createElement('img');
                    img.src = res.data.url;
                    img.alt = '';
                    zone.insertBefore(img, zone.querySelector('.kp-inv-img-overlay'));
                }
                var overlay = zone.querySelector('.kp-inv-img-overlay');
                if (overlay && !overlay.querySelector('.remove')) {
                    var btn = document.createElement('button');
                    btn.className = 'kp-inv-img-btn remove';
                    btn.type = 'button';
                    btn.innerHTML = '<span class="dashicons dashicons-trash"></span> Remove';
                    (function(pid, b) {
                        b.onclick = function(e) { e.stopPropagation(); kpRemoveImage(pid, b); };
                    })(postId, btn);
                    overlay.appendChild(btn);
                }
            } else {
                alert('Upload failed: ' + (res.data || 'Unknown error'));
            }
        };
        xhr.onerror = function() {
            zone.classList.remove('kp-inv-img-uploading');
            alert('Upload failed. Please try again.');
        };
        xhr.send(fd);
    }

    function kpRemoveImage(postId, btn) {
        if (!confirm('Remove image from this set?')) return;
        var zone = btn.closest('.kp-inv-img-zone');
        var fd = new FormData();
        fd.append('action', 'kavipushp_remove_set_image');
        fd.append('_wpnonce', kpSetImageNonce);
        fd.append('post_id', postId);
        var xhr = new XMLHttpRequest();
        xhr.open('POST', ajaxurl);
        xhr.onload = function() {
            var res = JSON.parse(xhr.responseText);
            if (res.success) {
                var img = zone.querySelector('img');
                if (img) img.remove();
                btn.remove();
                if (!zone.querySelector('.kp-inv-img-placeholder')) {
                    var ph = document.createElement('div');
                    ph.className = 'kp-inv-img-placeholder';
                    ph.innerHTML = '<span class="dashicons dashicons-camera"></span><span>Add Photo</span>';
                    zone.insertBefore(ph, zone.querySelector('.kp-inv-img-overlay'));
                }
            }
        };
        xhr.send(fd);
    }

    // ─── VIEW TABS (Card / Table) ──────────────────────────────────────────────
    function kpSwitchView(view) {
        var isCards = (view === 'cards');
        document.getElementById('kp-view-cards').style.display = isCards ? '' : 'none';
        document.getElementById('kp-view-table').style.display = isCards ? 'none' : '';
        document.getElementById('kp-tab-cards').style.background = isCards ? '#8e44ad' : '#fff';
        document.getElementById('kp-tab-cards').style.color     = isCards ? '#fff' : '#8e44ad';
        document.getElementById('kp-tab-table').style.background = isCards ? '#fff' : '#8e44ad';
        document.getElementById('kp-tab-table').style.color     = isCards ? '#8e44ad' : '#fff';
        try { localStorage.setItem('kp_inv_view', view); } catch(e) {}
    }
    // Restore last used view on page load
    document.addEventListener('DOMContentLoaded', function() {
        try {
            var saved = localStorage.getItem('kp_inv_view');
            if (saved === 'table') kpSwitchView('table');
        } catch(e) {}
    });

    // ─── TABLE VIEW: search / sort / upload / export ───────────────────────────
    var kpTblSortState = { col: null, asc: true };

    function kpTblFilter() {
        var q   = (document.getElementById('kp-tbl-search').value || '').toLowerCase().trim();
        var cat = (document.getElementById('kp-tbl-cat').value || '').toLowerCase().trim();
        var rows = document.querySelectorAll('#kp-tbl-body .kp-tbl-row');
        var vis = 0;
        rows.forEach(function(r) {
            var show = (!q || r.dataset.name.includes(q) || r.dataset.code.includes(q) || r.dataset.cat.includes(q))
                    && (!cat || r.dataset.cat.includes(cat));
            r.style.display = show ? '' : 'none';
            if (show) vis++;
        });
        document.getElementById('kp-tbl-count').textContent = vis + ' items';
    }
    document.addEventListener('DOMContentLoaded', function() {
        var s = document.getElementById('kp-tbl-search');
        var c = document.getElementById('kp-tbl-cat');
        if (s) s.addEventListener('input', kpTblFilter);
        if (c) c.addEventListener('change', kpTblFilter);
    });

    function kpTblSort(col) {
        var asc = (kpTblSortState.col === col) ? !kpTblSortState.asc : true;
        kpTblSortState = { col: col, asc: asc };
        var tbody = document.getElementById('kp-tbl-body');
        if (!tbody) return;
        var rows = Array.from(tbody.querySelectorAll('.kp-tbl-row'));
        rows.sort(function(a, b) {
            var va = a.dataset[col] || '', vb = b.dataset[col] || '';
            if (col === 'price') { return asc ? parseFloat(va)||0 - (parseFloat(vb)||0) : (parseFloat(vb)||0) - (parseFloat(va)||0); }
            return asc ? va.localeCompare(vb) : vb.localeCompare(va);
        });
        rows.forEach(function(r) { tbody.appendChild(r); });
        var v = 0;
        tbody.querySelectorAll('.kp-tbl-row').forEach(function(r) {
            if (r.style.display !== 'none') { v++; r.style.background = v%2===0?'#faf7ff':'#fff'; }
        });
    }

    function kpTblUpload(postId, input) {
        if (!input.files || !input.files[0]) return;
        var wrap = input.closest('div[style*="relative"]');
        if (wrap) wrap.style.opacity = '0.5';
        var fd = new FormData();
        fd.append('action', 'kavipushp_upload_set_image');
        fd.append('_wpnonce', kpSetImageNonce);
        fd.append('post_id', postId);
        fd.append('image', input.files[0]);
        var xhr = new XMLHttpRequest();
        xhr.open('POST', ajaxurl);
        xhr.onload = function() {
            if (wrap) wrap.style.opacity = '1';
            input.value = '';
            try {
                var res = JSON.parse(xhr.responseText);
                if (res.success && wrap) {
                    var old = wrap.querySelector('img');
                    var ph  = wrap.querySelector('div[style*="dashed"]');
                    if (ph) ph.remove();
                    if (old) { old.src = res.data.url; }
                    else {
                        var img = document.createElement('img');
                        img.src = res.data.url;
                        img.style.cssText = 'width:56px;height:56px;object-fit:cover;border-radius:6px;border:2px solid #d0b8e8;display:block;';
                        wrap.insertBefore(img, input);
                    }
                    // Sync same image to card view if card exists
                    var card = document.querySelector('#inventory-grid .kp-inv-card[data-post-id="'+postId+'"]');
                    if (card) {
                        var zone = card.querySelector('.kp-inv-img-zone');
                        if (zone) {
                            var cImg = zone.querySelector('img');
                            var cPh  = zone.querySelector('.kp-inv-img-placeholder');
                            if (cPh) cPh.remove();
                            if (cImg) { cImg.src = res.data.url; }
                            else {
                                var ni = document.createElement('img'); ni.src = res.data.url;
                                zone.insertBefore(ni, zone.querySelector('.kp-inv-img-overlay'));
                            }
                        }
                    }
                }
            } catch(e) {}
        };
        xhr.onerror = function() { if (wrap) wrap.style.opacity = '1'; };
        xhr.send(fd);
    }

    function kpTblEditPrice(displaySpan) {
        var cell   = displaySpan.closest('.kp-price-cell');
        var postId = cell.dataset.postId;
        var cur    = parseFloat(cell.dataset.price) || 0;

        // Replace span with input
        displaySpan.style.display = 'none';
        var inp = document.createElement('input');
        inp.type  = 'number';
        inp.value = cur > 0 ? cur : '';
        inp.min   = '0';
        inp.placeholder = 'Enter price';
        inp.style.cssText = 'width:90px;padding:4px 6px;border:2px solid #8e44ad;border-radius:5px;font-size:13px;text-align:right;outline:none;';
        cell.appendChild(inp);
        inp.focus();
        inp.select();

        function save() {
            var val = parseFloat(inp.value) || 0;
            inp.remove();
            displaySpan.style.display = '';

            // Optimistic UI update
            cell.dataset.price = val;
            var row = cell.closest('.kp-tbl-row');
            if (row) row.dataset.price = val;
            if (val > 0) {
                displaySpan.innerHTML = '<strong style="color:#27ae60;font-size:14px;">&#8377;' + val.toLocaleString('en-IN') + '</strong><span style="font-size:11px;color:#aaa;">/day</span>';
            } else {
                displaySpan.innerHTML = '<span style="color:#e74c3c;font-size:12px;border-bottom:1px dashed #e74c3c;">Set Price</span>';
            }

            // AJAX save
            var fd = new FormData();
            fd.append('action', 'kavipushp_save_set_price');
            fd.append('_wpnonce', kpSetImageNonce);
            fd.append('post_id', postId);
            fd.append('price', val);
            var xhr = new XMLHttpRequest();
            xhr.open('POST', ajaxurl);
            xhr.onload = function() {
                try {
                    var res = JSON.parse(xhr.responseText);
                    if (res.success) {
                        // Sync card view price too
                        var card = document.querySelector('#inventory-grid .kp-inv-card[data-post-id="'+postId+'"]');
                        if (card) {
                            var priceEl = card.querySelector('.kp-inv-price');
                            if (priceEl) priceEl.innerHTML = (val > 0 ? val.toLocaleString('en-IN') : '0') + ' <span class="kp-inv-per-day">/day</span>';
                        }
                    }
                } catch(e) {}
            };
            xhr.send(fd);
        }

        inp.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') save();
            if (e.key === 'Escape') { inp.remove(); displaySpan.style.display = ''; }
        });
        inp.addEventListener('blur', save);
    }

    function kpTblExportCSV() {
        var rows = document.querySelectorAll('#kp-tbl-body .kp-tbl-row');
        var csv = 'S.No,Item No,Item Name,Category,Rental Price\n';
        var n = 0;
        rows.forEach(function(r) {
            if (r.style.display === 'none') return;
            n++;
            var tds = r.querySelectorAll('td');
            var name  = (tds[3] ? tds[3].textContent.trim() : '').replace(/,/g,'');
            csv += n + ',"' + (r.dataset.code||'') + '","' + name + '","' + (r.dataset.cat||'') + '",' + (r.dataset.price||0) + '\n';
        });
        var a = document.createElement('a');
        a.href = URL.createObjectURL(new Blob([csv], {type:'text/csv'}));
        a.download = 'kavipushp-products.csv';
        a.click();
    }

    // ─── BULK IMAGE UPLOAD ─────────────────────────────────────────────────────
    function kpBulkImagesSelected(input) {
        if (!input.files || !input.files.length) return;
        var files = Array.from(input.files);
        input.value = ''; // reset so same folder can be re-selected

        // Build barcode → card map (exact lowercase key)
        var codeMap = {};
        document.querySelectorAll('#inventory-grid .kp-inv-card[data-set-code]').forEach(function(card) {
            var code = card.dataset.setCode.trim().toLowerCase();
            if (code) codeMap[code] = { postId: card.dataset.postId, card: card };
        });

        var matched = [], unmatched = [];
        files.forEach(function(file) {
            var name = file.name.replace(/\.[^.]+$/, '').trim().toLowerCase();
            var hit = null;
            // 1. Exact match
            if (codeMap[name]) {
                hit = codeMap[name];
            } else {
                // 2. Barcode is substring of filename (e.g. "BS001_photo" → "bs001")
                for (var code in codeMap) {
                    if (name.includes(code) || code.includes(name)) {
                        hit = codeMap[code];
                        break;
                    }
                }
            }
            if (hit) {
                matched.push({ file: file, postId: hit.postId, card: hit.card, displayName: file.name });
            } else {
                unmatched.push(file.name);
            }
        });

        kpShowBulkPanel(matched, unmatched);
    }

    function kpShowBulkPanel(matched, unmatched) {
        var panel = document.getElementById('kp-bulk-upload-panel');
        if (!panel) return;
        var html = '<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">'
            + '<h3 style="margin:0;color:#8e44ad;font-size:16px;">&#128248; Bulk Image Upload Preview</h3>'
            + '<button onclick="kpCloseBulkPanel()" style="background:none;border:none;cursor:pointer;font-size:18px;color:#999;">&#x2715;</button>'
            + '</div>';

        html += '<div style="display:flex;gap:10px;margin-bottom:14px;">'
            + '<span style="background:#27ae60;color:#fff;border-radius:20px;padding:4px 14px;font-size:13px;font-weight:600;">&#10003; ' + matched.length + ' matched</span>'
            + '<span style="background:#e74c3c;color:#fff;border-radius:20px;padding:4px 14px;font-size:13px;font-weight:600;">&#10007; ' + unmatched.length + ' unmatched</span>'
            + '</div>';

        if (matched.length > 0) {
            html += '<div style="max-height:180px;overflow-y:auto;margin-bottom:14px;background:#fff;border:1px solid #e0d0f5;border-radius:6px;padding:10px;">';
            matched.forEach(function(m) {
                html += '<div style="display:flex;align-items:center;gap:8px;padding:4px 0;border-bottom:1px solid #f3eeff;">'
                    + '<span style="color:#27ae60;font-size:16px;">&#10003;</span>'
                    + '<span style="font-size:13px;color:#555;">' + m.displayName + '</span>'
                    + '<span style="margin-left:auto;font-size:12px;color:#8e44ad;background:#f3eeff;border-radius:4px;padding:2px 8px;">ID: ' + (m.card ? m.card.dataset.setCode : '') + '</span>'
                    + '</div>';
            });
            html += '</div>';
        }

        if (unmatched.length > 0) {
            html += '<div style="max-height:100px;overflow-y:auto;margin-bottom:14px;background:#fff8f7;border:1px solid #fad7d3;border-radius:6px;padding:10px;">';
            html += '<p style="margin:0 0 6px;font-size:12px;color:#e74c3c;font-weight:600;">No matching barcode found:</p>';
            unmatched.forEach(function(name) {
                html += '<div style="font-size:12px;color:#999;padding:2px 0;">&#10007; ' + name + '</div>';
            });
            html += '</div>';
        }

        // Progress bar (hidden initially)
        html += '<div id="kp-bulk-progress-wrap" style="display:none;margin-bottom:12px;">'
            + '<div style="display:flex;justify-content:space-between;font-size:12px;color:#555;margin-bottom:4px;">'
            + '<span id="kp-bulk-progress-label">Uploading...</span>'
            + '<span id="kp-bulk-progress-count">0 / ' + matched.length + '</span>'
            + '</div>'
            + '<div style="background:#e8d5f5;border-radius:6px;height:10px;overflow:hidden;">'
            + '<div id="kp-bulk-progress-bar" style="width:0%;height:100%;background:#8e44ad;transition:width 0.3s;"></div>'
            + '</div>'
            + '</div>';

        html += '<div id="kp-bulk-done-msg" style="display:none;padding:10px;background:#e8f8f0;border:1px solid #27ae60;border-radius:6px;color:#27ae60;font-weight:600;margin-bottom:12px;">&#10003; All images uploaded successfully!</div>';

        if (matched.length > 0) {
            html += '<button id="kp-bulk-start-btn" onclick="kpStartBulkUpload()" style="background:#8e44ad;color:#fff;border:none;border-radius:6px;padding:9px 22px;cursor:pointer;font-size:14px;font-weight:600;">&#9650; Upload ' + matched.length + ' Images Now</button>';
        }

        panel.innerHTML = html;
        panel.style.display = 'block';
        panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

        // Store matched list on the panel for upload
        panel._matchedQueue = matched;
    }

    function kpCloseBulkPanel() {
        var panel = document.getElementById('kp-bulk-upload-panel');
        if (panel) { panel.style.display = 'none'; panel._matchedQueue = null; }
    }

    function kpStartBulkUpload() {
        var panel = document.getElementById('kp-bulk-upload-panel');
        if (!panel || !panel._matchedQueue || !panel._matchedQueue.length) return;
        var queue = panel._matchedQueue.slice();
        var total = queue.length;
        var done = 0;

        document.getElementById('kp-bulk-start-btn').disabled = true;
        document.getElementById('kp-bulk-start-btn').textContent = 'Uploading...';
        document.getElementById('kp-bulk-progress-wrap').style.display = 'block';

        function uploadNext() {
            if (!queue.length) {
                document.getElementById('kp-bulk-progress-bar').style.width = '100%';
                document.getElementById('kp-bulk-progress-label').textContent = 'Done!';
                document.getElementById('kp-bulk-progress-count').textContent = total + ' / ' + total;
                document.getElementById('kp-bulk-done-msg').style.display = 'block';
                document.getElementById('kp-bulk-start-btn').style.display = 'none';
                return;
            }
            var item = queue.shift();
            var fd = new FormData();
            fd.append('action', 'kavipushp_upload_set_image');
            fd.append('_wpnonce', kpSetImageNonce);
            fd.append('post_id', item.postId);
            fd.append('image', item.file);

            document.getElementById('kp-bulk-progress-label').textContent = 'Uploading: ' + item.displayName;

            var xhr = new XMLHttpRequest();
            xhr.open('POST', ajaxurl);
            xhr.onload = function() {
                done++;
                var pct = Math.round((done / total) * 100);
                document.getElementById('kp-bulk-progress-bar').style.width = pct + '%';
                document.getElementById('kp-bulk-progress-count').textContent = done + ' / ' + total;
                try {
                    var res = JSON.parse(xhr.responseText);
                    if (res.success && item.card) {
                        // Update the card image live
                        var zone = item.card.querySelector('.kp-inv-img-zone');
                        if (zone) {
                            var existing = zone.querySelector('img');
                            var placeholder = zone.querySelector('.kp-inv-img-placeholder');
                            if (placeholder) placeholder.remove();
                            if (existing) {
                                existing.src = res.data.url;
                            } else {
                                var img = document.createElement('img');
                                img.src = res.data.url;
                                img.alt = '';
                                zone.insertBefore(img, zone.querySelector('.kp-inv-img-overlay'));
                            }
                            var overlay = zone.querySelector('.kp-inv-img-overlay');
                            if (overlay && !overlay.querySelector('.remove')) {
                                var rmBtn = document.createElement('button');
                                rmBtn.className = 'kp-inv-img-btn remove';
                                rmBtn.type = 'button';
                                rmBtn.innerHTML = '<span class="dashicons dashicons-trash"></span> Remove';
                                (function(pid, b) {
                                    b.onclick = function(e) { e.stopPropagation(); kpRemoveImage(pid, b); };
                                })(item.postId, rmBtn);
                                overlay.appendChild(rmBtn);
                            }
                        }
                    }
                } catch(e) {}
                uploadNext();
            };
            xhr.onerror = function() { done++; uploadNext(); };
            xhr.send(fd);
        }

        uploadNext();
    }
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
    if (isset($_GET['booking_saved'])) {
        echo '<div class="notice notice-success is-dismissible"><p><strong>' . __('Booking saved successfully!', 'kavipushp-bridals') . '</strong></p></div>';
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
                <a href="<?php echo esc_url(home_url('/')); ?>" class="button">
                    <i class="dashicons dashicons-admin-home"></i> <?php _e('Home', 'kavipushp-bridals'); ?>
                </a>
                <button class="button" onclick="kavipushpExportBookings()">
                    <i class="dashicons dashicons-download"></i> <?php _e('Export to Excel', 'kavipushp-bridals'); ?>
                </button>
                <a href="<?php echo admin_url('post-new.php?post_type=booking'); ?>" class="button button-primary">
                    <i class="dashicons dashicons-plus-alt2"></i> <?php _e('New Booking', 'kavipushp-bridals'); ?>
                </a>
            </div>
        </div>

        <?php
        global $wpdb;
        $search_query   = isset($_GET['kp_search']) ? sanitize_text_field(trim($_GET['kp_search'])) : '';
        $total_bookings = wp_count_posts('booking')->publish;

        if ($search_query !== '') {
            // --- Find bridal sets matching title (WordPress search) ---
            $sets_by_title = get_posts(array(
                'post_type'      => 'bridal_set',
                'posts_per_page' => -1,
                's'              => $search_query,
                'fields'         => 'ids',
            ));

            // --- Find bridal sets matching set ID/code (postmeta LIKE) ---
            $sets_by_code = $wpdb->get_col($wpdb->prepare(
                "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_set_id' AND meta_value LIKE %s",
                '%' . $wpdb->esc_like($search_query) . '%'
            ));

            $matching_set_ids = array_unique(array_merge(
                array_map('intval', $sets_by_title),
                array_map('intval', $sets_by_code)
            ));

            // --- Build meta_query: match set ID OR customer name ---
            $meta_query = array('relation' => 'OR',
                array('key' => '_customer_name', 'value' => $search_query, 'compare' => 'LIKE'),
            );
            if (!empty($matching_set_ids)) {
                $meta_query[] = array('key' => '_bridal_set_id', 'value' => $matching_set_ids, 'compare' => 'IN');
            }

            $bookings = get_posts(array(
                'post_type'      => 'booking',
                'posts_per_page' => -1,
                'orderby'        => 'date',
                'order'          => 'DESC',
                'meta_query'     => $meta_query,
            ));
        } else {
            $bookings = get_posts(array(
                'post_type'      => 'booking',
                'posts_per_page' => -1,
                'orderby'        => 'date',
                'order'          => 'DESC',
            ));
        }
        ?>

        <!-- Search Bar -->
        <form method="get" action="" style="margin-bottom:16px;">
            <input type="hidden" name="page" value="kavipushp-bookings">
            <div style="display:flex;gap:8px;align-items:center;max-width:600px;">
                <div style="flex:1;position:relative;">
                    <i class="dashicons dashicons-search" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#999;pointer-events:none;"></i>
                    <input type="text" name="kp_search" value="<?php echo esc_attr($search_query); ?>"
                        placeholder="<?php esc_attr_e('Search by Set Title, Set ID, or Customer Name…', 'kavipushp-bridals'); ?>"
                        style="width:100%;padding:8px 12px 8px 34px;border:1px solid #ddd;border-radius:6px;font-size:14px;box-shadow:0 1px 3px rgba(0,0,0,0.06);"
                        autofocus>
                </div>
                <button type="submit" class="button button-primary" style="padding:8px 16px;">
                    <i class="dashicons dashicons-search" style="margin-right:4px;"></i><?php _e('Search', 'kavipushp-bridals'); ?>
                </button>
                <?php if ($search_query): ?>
                <a href="<?php echo admin_url('admin.php?page=kavipushp-bookings'); ?>" class="button" style="padding:8px 16px;">
                    <i class="dashicons dashicons-no-alt" style="margin-right:4px;"></i><?php _e('Clear', 'kavipushp-bridals'); ?>
                </a>
                <?php endif; ?>
            </div>
            <?php if ($search_query): ?>
            <p style="margin:8px 0 0;font-size:13px;color:#666;">
                <?php printf(
                    _n('Found <strong>%d booking</strong> for &ldquo;%s&rdquo;', 'Found <strong>%d bookings</strong> for &ldquo;%s&rdquo;', count($bookings), 'kavipushp-bridals'),
                    count($bookings), esc_html($search_query)
                ); ?>
            </p>
            <?php endif; ?>
        </form>

        <form method="post" id="kp-bookings-bulk-form">
            <?php wp_nonce_field('kp_bulk_bookings'); ?>
            <input type="hidden" name="page" value="kavipushp-bookings">
        <div class="kp-card">
            <div class="kp-card-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
                <h2><i class="dashicons dashicons-calendar-alt"></i>
                    <?php if ($search_query): ?>
                        <?php printf(__('Search Results (%d)', 'kavipushp-bridals'), count($bookings)); ?>
                    <?php else: ?>
                        <?php printf(__('All Bookings (%d) - Latest First', 'kavipushp-bridals'), $total_bookings); ?>
                    <?php endif; ?>
                </h2>
                <?php if (!empty($bookings)): ?>
                <div style="display:flex;gap:8px;align-items:center;">
                    <label style="font-size:13px;cursor:pointer;"><input type="checkbox" id="kp-select-all-bookings" style="margin-right:4px;"><?php _e('Select All', 'kavipushp-bridals'); ?></label>
                    <button type="submit" name="bulk_action" value="delete_selected" class="button kp-delete-btn" onclick="return confirm('<?php esc_attr_e('Delete selected bookings?', 'kavipushp-bridals'); ?>')">
                        <i class="dashicons dashicons-trash"></i> <?php _e('Delete Selected', 'kavipushp-bridals'); ?>
                    </button>
                    <button type="submit" name="bulk_action" value="delete_all" class="button" style="background:#e74c3c;color:#fff;border-color:#c0392b;" onclick="return confirm('<?php esc_attr_e('Delete ALL bookings? This cannot be undone!', 'kavipushp-bridals'); ?>')">
                        <i class="dashicons dashicons-trash"></i> <?php _e('Delete All', 'kavipushp-bridals'); ?>
                    </button>
                </div>
                <?php endif; ?>
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
                        // Use options store first (persists across deploys)
                        $set_thumb_url = $set_code ? get_option('kp_setimg_' . sanitize_key($set_code), '') : '';
                        if (!$set_thumb_url && $set_id) {
                            $set_thumb_id = get_post_thumbnail_id($set_id);
                            if ($set_thumb_id) {
                                $set_thumb_url = wp_get_attachment_image_url($set_thumb_id, 'medium');
                                if (!$set_thumb_url) $set_thumb_url = wp_get_attachment_image_url($set_thumb_id, 'full');
                            }
                        }
                        $booking_category = get_post_meta($booking->ID, '_bridal_set_category', true);
                        if (!$booking_category && $set_id) {
                            $bk_cat_terms = get_the_terms($set_id, 'bridal_category');
                            $booking_category = ($bk_cat_terms && !is_wp_error($bk_cat_terms)) ? $bk_cat_terms[0]->name : '';
                        }
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
                            <div style="display:flex;align-items:flex-start;gap:10px;flex:1;">
                                <input type="checkbox" name="booking_ids[]" value="<?php echo $booking->ID; ?>" class="kp-booking-cb" style="margin-top:4px;width:16px;height:16px;cursor:pointer;flex-shrink:0;">
                                <div class="kp-booking-customer">
                                    <h3><?php echo esc_html($customer_name); ?></h3>
                                    <p class="kp-booking-id">Booking ID: <?php echo esc_html($booking_uid); ?> | Contact: <?php echo esc_html($customer_phone); ?></p>
                                </div>
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
                            <?php if ($set_thumb_url): ?>
                            <div style="margin:8px 0;">
                                <img src="<?php echo esc_attr($set_thumb_url); ?>" alt="<?php echo esc_attr($set->post_title); ?>"
                                    style="width:90px;height:90px;object-fit:cover;border-radius:8px;border:2px solid #e2c98a;box-shadow:0 2px 6px rgba(0,0,0,0.12);display:block;">
                            </div>
                            <?php endif; ?>
                            <div class="kp-item-row" style="flex-wrap:wrap;gap:6px;align-items:center;">
                                <span class="kp-item-code" style="font-weight:600;"><?php echo esc_html($set->post_title); ?></span>
                                <?php if ($set_code): ?>
                                <span class="kp-item-code" style="background:#fef9ec; color:#b7791f; border:1px solid #f6e05e;">
                                    <i class="dashicons dashicons-tag" style="font-size:12px;width:12px;height:12px;vertical-align:middle;margin-right:2px;"></i>
                                    <?php echo esc_html($set_code); ?>
                                </span>
                                <?php endif; ?>
                                <?php if ($booking_category): ?>
                                <span class="kp-item-code" style="background:#e8f4f8; color:#2980b9;"><?php echo esc_html($booking_category); ?></span>
                                <?php endif; ?>
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
        </form>
    </div>

    <script>
    document.getElementById('kp-select-all-bookings') && document.getElementById('kp-select-all-bookings').addEventListener('change', function() {
        document.querySelectorAll('.kp-booking-cb').forEach(function(cb) { cb.checked = this.checked; }, this);
    });
    </script>

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
                        (b.set_category ? '<tr><th><?php _e('Category', 'kavipushp-bridals'); ?></th><td>' + b.set_category + '</td></tr>' : '') +
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
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
                <?php if ($action === 'generate' || $action === 'view_saved'): ?>
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

        <?php if ($action === 'view_saved'):
        // View a saved invoice from wp_kavipushp_invoices table
        global $wpdb;
        $inv_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $inv = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}kavipushp_invoices WHERE id = %d", $inv_id));
        if ($inv):
            // Build set includes from booking jewelry meta
            $inv_booking_id = intval($inv->booking_id);
            $inv_jewelry = array(
                'Nath'           => get_post_meta($inv_booking_id, '_nath', true),
                'Maang Teeka'    => get_post_meta($inv_booking_id, '_maang_teeka', true),
                'Ring'           => get_post_meta($inv_booking_id, '_ring', true),
                'Matha Patti'    => get_post_meta($inv_booking_id, '_matha_patti', true),
                'Sheesh Patti'   => get_post_meta($inv_booking_id, '_sheesh_patti', true),
                'Hath Phool'     => get_post_meta($inv_booking_id, '_hath_phool', true),
                'Pasa'           => get_post_meta($inv_booking_id, '_pasa', true),
                'Any Other Item' => get_post_meta($inv_booking_id, '_any_other_item', true),
            );
            $inv_set_includes_parts = array();
            foreach ($inv_jewelry as $lbl => $val) {
                if ($val) $inv_set_includes_parts[] = $lbl . ': ' . $val;
            }
            $inv_set_includes = implode(' | ', $inv_set_includes_parts);

            // Get set image for this invoice — options store is primary (persists across deploys)
            $inv_set_image_url = '';
            if ($inv_booking_id) {
                $inv_b_set_id = get_post_meta($inv_booking_id, '_bridal_set_id', true);
                if ($inv_b_set_id) {
                    $inv_set_code = get_post_meta($inv_b_set_id, '_set_id', true);
                    if ($inv_set_code) {
                        $inv_set_image_url = get_option('kp_setimg_' . sanitize_key($inv_set_code), '');
                    }
                    if (!$inv_set_image_url) {
                        $inv_thumb_id = get_post_thumbnail_id($inv_b_set_id);
                        if ($inv_thumb_id) {
                            $inv_set_image_url = wp_get_attachment_image_url($inv_thumb_id, 'medium');
                            if (!$inv_set_image_url) $inv_set_image_url = wp_get_attachment_image_url($inv_thumb_id, 'full');
                            if (!$inv_set_image_url) $inv_set_image_url = wp_get_attachment_url($inv_thumb_id);
                        }
                    }
                }
            }

            $type_labels = array('booking' => __('Booking Invoice', 'kavipushp-bridals'), 'pickup' => __('Pickup Invoice', 'kavipushp-bridals'), 'final' => __('Final Invoice', 'kavipushp-bridals'));
            $type_label = isset($type_labels[$inv->invoice_type]) ? $type_labels[$inv->invoice_type] : ucfirst($inv->invoice_type);
            $biz_name    = get_option('kavipushp_business_name', 'Kavipushp Jewels Rental');
            $biz_address = get_option('kavipushp_business_address', '');
            $biz_phone   = get_option('kavipushp_business_phone', '');
            $biz_email   = get_option('kavipushp_business_email', '');
        ?>
        <div class="kp-card" id="saved-invoice-view">
            <div class="kp-card-body">
                <div style="background:#fff; border:1px solid #ddd; border-radius:8px; padding:30px; max-width:800px; margin:0 auto;" id="printable-invoice">
                    <div style="text-align:center; border-bottom:2px solid #c9a86c; padding-bottom:15px; margin-bottom:20px;">
                        <h2 style="margin:0; color:#1a1f36;"><?php echo esc_html($biz_name); ?></h2>
                        <p style="color:#666; margin:5px 0 0; font-size:13px; line-height:1.6;">
                            <?php echo esc_html($biz_address); ?><br>
                            <?php echo esc_html($biz_phone); ?> &nbsp;|&nbsp; <?php echo esc_html($biz_email); ?>
                        </p>
                    </div>

                    <h3 style="text-align:center; color:#1a1f36;"><?php echo esc_html($type_label); ?></h3>
                    <p style="text-align:center; color:#666;"><?php echo esc_html($inv->invoice_number); ?> &nbsp;&bull;&nbsp; <?php echo date('d/m/Y', strtotime($inv->created_at)); ?></p>

                    <?php if ($inv_set_image_url): ?>
                    <div style="text-align:center; margin:15px 0;">
                        <img src="<?php echo esc_attr($inv_set_image_url); ?>" style="max-width:200px; max-height:160px; border:1px solid #ddd; border-radius:6px;">
                    </div>
                    <?php endif; ?>

                    <div class="kp-invoice-grid-2col" style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin:20px 0;">
                        <div>
                            <h4 style="color:#c9a86c; margin:0 0 8px;"><?php _e('Bill To', 'kavipushp-bridals'); ?></h4>
                            <p style="margin:3px 0; font-weight:bold;"><?php echo esc_html($inv->customer_name); ?></p>
                            <p style="margin:3px 0;"><?php echo esc_html($inv->customer_phone); ?></p>
                            <?php if ($inv->customer_email): ?><p style="margin:3px 0;"><?php echo esc_html($inv->customer_email); ?></p><?php endif; ?>
                            <?php if ($inv->customer_address): ?><p style="margin:3px 0;"><?php echo esc_html($inv->customer_address); ?></p><?php endif; ?>
                        </div>
                        <div>
                            <h4 style="color:#c9a86c; margin:0 0 8px;"><?php _e('Rental Period', 'kavipushp-bridals'); ?></h4>
                            <?php if ($inv->function_date && $inv->function_date !== '0000-00-00'): ?><p style="margin:3px 0;"><strong><?php _e('Function:', 'kavipushp-bridals'); ?></strong> <?php echo date('d/m/Y', strtotime($inv->function_date)); ?></p><?php endif; ?>
                            <?php if ($inv->pickup_date && $inv->pickup_date !== '0000-00-00'): ?><p style="margin:3px 0;"><strong><?php _e('Pickup:', 'kavipushp-bridals'); ?></strong> <?php echo date('d/m/Y', strtotime($inv->pickup_date)); ?></p><?php endif; ?>
                            <?php if ($inv->return_date && $inv->return_date !== '0000-00-00'): ?><p style="margin:3px 0;"><strong><?php _e('Return:', 'kavipushp-bridals'); ?></strong> <?php echo date('d/m/Y', strtotime($inv->return_date)); ?></p><?php endif; ?>
                        </div>
                    </div>

                    <table style="width:100%; border-collapse:collapse; margin:20px 0;">
                        <thead>
                            <tr style="background:#f0f0f0;">
                                <th style="padding:10px; text-align:left; border-bottom:2px solid #ddd;"><?php _e('Item', 'kavipushp-bridals'); ?></th>
                                <th style="padding:10px; text-align:left; border-bottom:2px solid #ddd;"><?php _e('Category', 'kavipushp-bridals'); ?></th>
                                <th style="padding:10px; text-align:left; border-bottom:2px solid #ddd;"><?php _e('Set Code', 'kavipushp-bridals'); ?></th>
                                <th style="padding:10px; text-align:right; border-bottom:2px solid #ddd;"><?php _e('Amount', 'kavipushp-bridals'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="padding:10px; border-bottom:1px solid #eee;"><?php echo esc_html($inv->set_name ?: __('Bridal Jewellery Set', 'kavipushp-bridals')); ?></td>
                                <td style="padding:10px; border-bottom:1px solid #eee;"><?php echo esc_html(isset($inv->set_category) ? $inv->set_category : ''); ?></td>
                                <td style="padding:10px; border-bottom:1px solid #eee;"><?php echo esc_html($inv->set_code ?: 'N/A'); ?></td>
                                <td style="padding:10px; border-bottom:1px solid #eee; text-align:right;">₹<?php echo number_format($inv->rent_amount, 2); ?></td>
                            </tr>
                            <?php if (floatval($inv->booking_amount) > 0): ?>
                            <tr>
                                <td style="padding:10px; border-bottom:1px solid #eee; color:#e74c3c;" colspan="3"><?php _e('Less: Booking Amount Paid', 'kavipushp-bridals'); ?></td>
                                <td style="padding:10px; border-bottom:1px solid #eee; text-align:right; color:#e74c3c;">- ₹<?php echo number_format($inv->booking_amount, 2); ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr style="background:#f9f9f9; font-weight:bold;">
                                <td style="padding:12px 10px; border-bottom:3px solid #8B4513;" colspan="3"><?php _e('Remaining Balance', 'kavipushp-bridals'); ?></td>
                                <td style="padding:12px 10px; text-align:right; color:#c9a86c; font-size:16px; border-bottom:3px solid #8B4513;">₹<?php echo number_format($inv->grand_total, 2); ?></td>
                            </tr>
                            <?php if ($inv->invoice_type === 'pickup'):
                                $sec = floatval($inv->security_deposit);
                                $grand = floatval($inv->grand_total);
                                $total_received = $grand + $sec;
                            ?>
                            <tr>
                                <td style="padding:10px; border-bottom:1px solid #eee; font-weight:600; color:#555;" colspan="3"><?php _e('Remaining Rent Received', 'kavipushp-bridals'); ?></td>
                                <td style="padding:10px; border-bottom:1px solid #eee; text-align:right; font-weight:600; color:#555;">₹<?php echo number_format($grand, 2); ?></td>
                            </tr>
                            <?php if ($sec > 0): ?>
                            <tr>
                                <td style="padding:10px; border-bottom:1px solid #eee; font-weight:600; color:#2980b9;" colspan="3"><?php _e('Security Received', 'kavipushp-bridals'); ?></td>
                                <td style="padding:10px; border-bottom:1px solid #eee; text-align:right; font-weight:600; color:#2980b9;">₹<?php echo number_format($sec, 2); ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <td style="padding:10px; font-weight:bold; color:#27ae60;" colspan="3"><?php _e('Total Amount Received on Pickup', 'kavipushp-bridals'); ?></td>
                                <td style="padding:10px; text-align:right; font-weight:bold; color:#27ae60;">₹<?php echo number_format($total_received, 2); ?></td>
                            </tr>
                            <?php elseif ($inv->invoice_type === 'final'):
                                $sec_f = floatval($inv->security_deposit);
                                $grand_f = floatval($inv->grand_total);
                                $dp_f = floatval($inv->damages_paid ?? 0);
                                $total_recvd_f = $grand_f + $sec_f;
                                $sec_refund = $sec_f - $dp_f;
                                $refund_color = $sec_refund >= 0 ? '#27ae60' : '#e74c3c';
                            ?>
                            <?php if ($sec_f > 0): ?>
                            <tr>
                                <td style="padding:10px; border-bottom:1px solid #eee; font-weight:600; color:#2980b9;" colspan="3"><?php _e('Security Received on Pickup', 'kavipushp-bridals'); ?></td>
                                <td style="padding:10px; border-bottom:1px solid #eee; text-align:right; font-weight:600; color:#2980b9;">₹<?php echo number_format($sec_f, 2); ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <td style="padding:10px; border-bottom:1px solid #eee; font-weight:bold; color:#27ae60;" colspan="3"><?php _e('Total Received on Pickup', 'kavipushp-bridals'); ?></td>
                                <td style="padding:10px; border-bottom:1px solid #eee; text-align:right; font-weight:bold; color:#27ae60;">₹<?php echo number_format($total_recvd_f, 2); ?></td>
                            </tr>
                            <?php if ($dp_f > 0): ?>
                            <tr>
                                <td style="padding:10px; border-bottom:1px solid #eee; color:#e74c3c;" colspan="3"><?php _e('Less: Damage or Late Charges or Security Hold', 'kavipushp-bridals'); ?></td>
                                <td style="padding:10px; border-bottom:1px solid #eee; text-align:right; color:#e74c3c;">- ₹<?php echo number_format($dp_f, 2); ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <td style="padding:10px; font-weight:bold; color:<?php echo $refund_color; ?>;" colspan="3"><?php _e('Security Refund after Damage or Late Charges or Security Hold', 'kavipushp-bridals'); ?></td>
                                <td style="padding:10px; text-align:right; font-weight:bold; color:<?php echo $refund_color; ?>;">₹<?php echo number_format($sec_refund, 2); ?></td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <?php if ($inv->customization_notes): ?>
                    <div style="background:#fff8e1; border:1px solid #f0e68c; border-radius:6px; padding:12px 15px; margin:15px 0;">
                        <strong style="color:#c9a86c;"><?php _e('Customization:', 'kavipushp-bridals'); ?></strong>
                        <span style="color:#333; margin-left:5px;"><?php echo esc_html($inv->customization_notes); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($inv->stylist_name)): ?>
                    <div style="background:#f0f7ff; border:1px solid #c8dff8; border-radius:6px; padding:12px 15px; margin:15px 0;">
                        <strong style="color:#2980b9;"><i class="dashicons dashicons-admin-users" style="font-size:14px; vertical-align:middle;"></i> <?php _e('Stylist Who Attended:', 'kavipushp-bridals'); ?></strong>
                        <span style="color:#333; margin-left:5px;"><?php echo esc_html($inv->stylist_name); ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($inv->security_hold_reason)): ?>
                    <div style="background:#fff3e0; border:1px solid #ffe0b2; border-radius:6px; padding:12px 15px; margin:15px 0;">
                        <strong style="color:#e67e22;"><?php _e('Reasons for Security Hold:', 'kavipushp-bridals'); ?></strong>
                        <span style="color:#333; margin-left:5px;"><?php echo esc_html($inv->security_hold_reason); ?></span>
                    </div>
                    <?php endif; ?>

                    <div style="margin:20px 0; padding:15px; border:1px solid #ddd; border-radius:6px; background:#fafafa;">
                        <h5 style="margin:0 0 10px; color:#1a1f36;"><?php _e('Kavipushp Jewels – Terms & Conditions', 'kavipushp-bridals'); ?></h5>
                        <ol style="margin:0; padding-left:18px; font-size:12px; color:#555; line-height:1.8;">
                            <li><?php _e('Jewellery is rented only for the period mentioned; late return will be charged per day.', 'kavipushp-bridals'); ?></li>
                            <li><?php _e('A refundable security deposit may be collected and returned after condition check.', 'kavipushp-bridals'); ?></li>
                            <li><?php _e('Any damage, loss, or missing parts will be charged as per repair or replacement value mentioned before dispatch in the whatsapp group.', 'kavipushp-bridals'); ?></li>
                            <li><?php _e('Full payment must be made before delivery; advance/booking amount is non-refundable.', 'kavipushp-bridals'); ?></li>
                            <li><?php _e('Customer must provide valid ID proof and is responsible for the safety of the jewellery during the rental period.', 'kavipushp-bridals'); ?></li>
                            <li><?php _e('All disputes are subject to local jurisdiction.', 'kavipushp-bridals'); ?></li>
                            <li><?php _e('There shall be no discount on the rental prices of the set.', 'kavipushp-bridals'); ?></li>
                            <li><?php _e('Rental price does not include customization charges if applicable.', 'kavipushp-bridals'); ?></li>
                            <li><?php _e('Booking amount is neither refundable nor adjustable or transferable in any condition, no exceptions.', 'kavipushp-bridals'); ?></li>
                            <li><?php _e('No excuses like jewellery will be provided by in-laws, in-laws insisting on wearing gold only, wedding postponed or cancelled are entertained.', 'kavipushp-bridals'); ?></li>
                            <li><?php _e('Articles agreed on at the time of booking will only be provided; extra article will be chargeable.', 'kavipushp-bridals'); ?></li>
                            <li><?php _e('Use official number 9977722271 for any query or requirements; no responsibility for any personal number of employee.', 'kavipushp-bridals'); ?></li>
                            <li><?php _e('Rental clarification: First 3 days normal rent of Bridal Set, next 2 days Rs.500 extra (if informed at the time of booking).', 'kavipushp-bridals'); ?></li>
                            <li><?php _e('If the bridal set is not returned within the informed timeline, per day rental charges will be applicable as extra charge.', 'kavipushp-bridals'); ?></li>
                        </ol>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:30px; margin-top:30px;">
                            <div><div style="border-top:1px solid #333; width:100%; margin-bottom:6px;"></div><p style="margin:0; font-size:12px; color:#555;"><?php _e('Customer Signature & Date', 'kavipushp-bridals'); ?></p></div>
                            <div><div style="border-top:1px solid #333; width:100%; margin-bottom:6px;"></div><p style="margin:0; font-size:12px; color:#555;"><?php _e('Authorized Signatory', 'kavipushp-bridals'); ?></p></div>
                        </div>
                    </div>
                </div>

                <div style="margin-top:20px; display:flex; gap:10px; flex-wrap:wrap;">
                    <button class="button button-primary button-large" onclick="printSavedInvoiceClean()">
                        <i class="dashicons dashicons-printer"></i> <?php _e('Print Invoice', 'kavipushp-bridals'); ?>
                    </button>
                    <button class="button button-large" onclick="createSavedInvoicePDF(this)" style="background:#c9a86c;border-color:#b8965a;color:#fff;">
                        <i class="dashicons dashicons-media-document"></i> <?php _e('Create PDF', 'kavipushp-bridals'); ?>
                    </button>
                    <a href="<?php echo admin_url('admin.php?page=kavipushp-invoices'); ?>" class="button button-large">
                        <i class="dashicons dashicons-arrow-left-alt"></i> <?php _e('Back to Invoices', 'kavipushp-bridals'); ?>
                    </a>
                </div>
                <script>
                async function createSavedInvoicePDF(btn) {
                    var element = document.getElementById('printable-invoice');
                    if (!element) return;
                    var origHtml = btn.innerHTML;
                    btn.innerHTML = '<i class="dashicons dashicons-update"></i> Generating...';
                    btn.disabled = true;
                    try {
                        var canvas = await html2canvas(element, { scale: 2, useCORS: true, backgroundColor: '#ffffff', logging: false });
                        var { jsPDF } = window.jspdf;
                        var pdf = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });
                        var pageW = pdf.internal.pageSize.getWidth();
                        var pageH = pdf.internal.pageSize.getHeight();
                        var imgH = (canvas.height * pageW) / canvas.width;
                        var finalH = Math.min(imgH, pageH);
                        pdf.addImage(canvas.toDataURL('image/jpeg', 0.95), 'JPEG', 0, 0, pageW, finalH);
                        pdf.save('<?php echo esc_js($inv->invoice_number . '-' . preg_replace('/[^a-z0-9]/i', '-', $inv->customer_name)); ?>.pdf');
                    } catch(e) { console.error('PDF error:', e); alert('PDF generation failed: ' + e.message); }
                    btn.innerHTML = origHtml; btn.disabled = false;
                }
                function printSavedInvoiceClean() {
                    var bizName    = <?php echo wp_json_encode($biz_name); ?>;
                    var bizAddress = <?php echo wp_json_encode($biz_address); ?>;
                    var bizPhone   = <?php echo wp_json_encode($biz_phone); ?>;
                    var bizEmail   = <?php echo wp_json_encode($biz_email); ?>;
                    var typeLabel  = <?php echo wp_json_encode($type_label); ?>;
                    var invNum     = <?php echo wp_json_encode($inv->invoice_number); ?>;
                    var invDate    = <?php echo wp_json_encode(date('d/m/Y', strtotime($inv->created_at))); ?>;

                    var name       = <?php echo wp_json_encode($inv->customer_name); ?>;
                    var phone      = <?php echo wp_json_encode($inv->customer_phone); ?>;
                    var email      = <?php echo wp_json_encode($inv->customer_email ?: ''); ?>;
                    var address    = <?php echo wp_json_encode($inv->customer_address ?: 'N/A'); ?>;

                    var funcDate   = <?php echo wp_json_encode($inv->function_date && $inv->function_date !== '0000-00-00' ? date('d/m/Y', strtotime($inv->function_date)) : ''); ?>;
                    var pickDate   = <?php echo wp_json_encode($inv->pickup_date && $inv->pickup_date !== '0000-00-00' ? date('d/m/Y', strtotime($inv->pickup_date)) : ''); ?>;
                    var retDate    = <?php echo wp_json_encode($inv->return_date && $inv->return_date !== '0000-00-00' ? date('d/m/Y', strtotime($inv->return_date)) : ''); ?>;

                    var setName    = <?php echo wp_json_encode($inv->set_name ?: 'Bridal Jewellery Set'); ?>;
                    var setCode    = <?php echo wp_json_encode($inv->set_code ?: ''); ?>;
                    var setCategory = <?php echo wp_json_encode(isset($inv->set_category) ? $inv->set_category : ''); ?>;
                    var rent          = <?php echo floatval($inv->rent_amount); ?>;
                    var bookingAmt    = <?php echo floatval($inv->booking_amount); ?>;
                    var secDep        = <?php echo floatval($inv->security_deposit); ?>;
                    var amountReceived = <?php echo floatval($inv->amount_received ?? 0); ?>;
                    var damagesPaid   = <?php echo floatval($inv->damages_paid ?? 0); ?>;
                    var grandTotal    = <?php echo floatval($inv->grand_total); ?>;
                    var secHoldReason = <?php echo wp_json_encode(isset($inv->security_hold_reason) ? $inv->security_hold_reason : ''); ?>;
                    var customization = <?php echo wp_json_encode($inv->customization_notes ?: ''); ?>;
                    var stylist    = <?php echo wp_json_encode($inv->stylist_name ?: ''); ?>;
                    var setIncludes = <?php echo wp_json_encode($inv_set_includes); ?>;
                    var invSetImageUrl = <?php echo wp_json_encode($inv_set_image_url); ?>;

                    var footerNotes = {
                        booking: 'This is a booking confirmation invoice. Remaining amount to be paid at the time of pickup.',
                        pickup:  'This is a pickup invoice. Please ensure all items are in good condition.',
                        final:   'This is the final settlement invoice. Thank you for renting with us!'
                    };
                    var invType = <?php echo wp_json_encode($inv->invoice_type); ?>;
                    var footerNote = footerNotes[invType] || '';

                    // Build table rows
                    var tableRows = '<tr><td>' + setName + '</td><td>' + setCategory + '</td><td>' + setCode + '</td><td style="text-align:right;">\u20B9' + rent.toLocaleString('en-IN') + '</td></tr>';
                    if (bookingAmt > 0) {
                        tableRows += '<tr><td colspan="3" style="color:#e74c3c;">Less: Booking Amount</td><td style="text-align:right;color:#e74c3c;">- \u20B9' + bookingAmt.toLocaleString('en-IN') + '</td></tr>';
                    }
                    tableRows += '<tr class="total-row"><td colspan="3" style="border-bottom:3px solid #8B4513;">Remaining Balance</td><td style="text-align:right;border-bottom:3px solid #8B4513;">\u20B9' + grandTotal.toLocaleString('en-IN') + '</td></tr>';
                    if (invType === 'pickup') {
                        tableRows += '<tr><td colspan="3" style="font-weight:600;background:#f5f5f5;color:#555;">Remaining Rent Received</td><td style="text-align:right;font-weight:600;background:#f5f5f5;color:#555;">\u20B9' + grandTotal.toLocaleString('en-IN') + '</td></tr>';
                        if (secDep > 0) {
                            tableRows += '<tr><td colspan="3" style="font-weight:600;background:#f5f5f5;color:#2980b9;">Security Received</td><td style="text-align:right;font-weight:600;background:#f5f5f5;color:#2980b9;">\u20B9' + secDep.toLocaleString('en-IN') + '</td></tr>';
                        }
                        var totalReceived = grandTotal + secDep;
                        tableRows += '<tr><td colspan="3" style="font-weight:bold;font-size:13px;color:#27ae60;">Total Amount Received on Pickup</td><td style="text-align:right;font-weight:bold;font-size:13px;color:#27ae60;">\u20B9' + totalReceived.toLocaleString('en-IN') + '</td></tr>';
                    } else if (invType === 'final') {
                        var totalRecvd = grandTotal + secDep;
                        var secRefund = secDep - damagesPaid;
                        if (secDep > 0) {
                            tableRows += '<tr><td colspan="3" style="font-weight:600;color:#2980b9;">Security Received on Pickup</td><td style="text-align:right;font-weight:600;color:#2980b9;">\u20B9' + secDep.toLocaleString('en-IN') + '</td></tr>';
                        }
                        tableRows += '<tr><td colspan="3" style="font-weight:bold;color:#27ae60;">Total Received on Pickup</td><td style="text-align:right;font-weight:bold;color:#27ae60;">\u20B9' + totalRecvd.toLocaleString('en-IN') + '</td></tr>';
                        if (damagesPaid > 0) {
                            tableRows += '<tr><td colspan="3" style="color:#e74c3c;">Less: Damage or Late Charges or Security Hold</td><td style="text-align:right;color:#e74c3c;">- \u20B9' + damagesPaid.toLocaleString('en-IN') + '</td></tr>';
                        }
                        var refundColor = secRefund >= 0 ? '#27ae60' : '#e74c3c';
                        tableRows += '<tr><td colspan="3" style="font-weight:bold;font-size:13px;color:' + refundColor + ';">Security Refund after Damage or Late Charges or Security Hold</td><td style="text-align:right;font-weight:bold;font-size:13px;color:' + refundColor + ';">\u20B9' + secRefund.toLocaleString('en-IN') + '</td></tr>';
                    }

                    var rentalPeriod = '';
                    if (funcDate) rentalPeriod += '<p><strong>Function:</strong> ' + funcDate + '</p>';
                    if (pickDate) rentalPeriod += '<p><strong>Pickup:</strong> ' + pickDate + '</p>';
                    if (retDate)  rentalPeriod += '<p><strong>Return:</strong> ' + retDate + '</p>';

                    var pw = window.open('', '_blank');
                    pw.document.write(
                        '<!DOCTYPE html><html><head><title>' + typeLabel + ' ' + invNum + '</title>' +
                        '<style>' +
                        '@page { size: A4; margin: 10mm 12mm; }' +
                        'html, body { font-family: Arial, sans-serif; font-size: 11px; margin: 0; padding: 0; width: 210mm; max-width: 210mm; }' +
                        '.header { text-align: center; border-bottom: 2px solid #c9a86c; padding-bottom: 8px; margin-bottom: 10px; }' +
                        '.header h1 { color: #1a1f36; margin: 0 0 3px 0; font-size: 18px; }' +
                        '.header p { color: #666; margin: 0; font-size: 11px; line-height: 1.4; }' +
                        '.header .contact-line { margin-top: 3px; font-size: 10px; }' +
                        '.info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 10px; }' +
                        '.info-block h3 { color: #c9a86c; margin: 0 0 4px 0; font-size: 11px; text-transform: uppercase; }' +
                        '.info-block p { margin: 2px 0; color: #333; font-size: 11px; }' +
                        'table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }' +
                        'th, td { padding: 6px 8px; text-align: left; border-bottom: 1px solid #ddd; font-size: 11px; }' +
                        'th { background: #f5f5f5; font-weight: 600; }' +
                        '.total-row { font-weight: bold; font-size: 13px; }' +
                        '.total-row td { border-top: 2px solid #c9a86c; }' +
                        '.footer { text-align: center; color: #666; font-size: 10px; margin-top: 10px; padding-top: 8px; border-top: 1px solid #ddd; }' +
                        'ol { margin: 4px 0; padding-left: 16px; font-size: 10px; color: #555; line-height: 1.6; }' +
                        '@media print { html, body { width: 210mm; } * { -webkit-print-color-adjust: exact; print-color-adjust: exact; } }' +
                        '</style></head><body>' +
                        '<div class="header"><h1>' + bizName + '</h1>' +
                        '<p>' + bizAddress + '</p>' +
                        '<p class="contact-line">\u260E ' + bizPhone + ' &nbsp;|&nbsp; \u2709 ' + bizEmail + '</p></div>' +
                        '<h2 style="text-align:center;color:#1a1f36;">' + typeLabel.toUpperCase() + '</h2>' +
                        '<p style="text-align:center;color:#666;">Invoice #: ' + invNum + ' | Date: ' + invDate + '</p>' +
                        '<div class="info-grid">' +
                        '<div class="info-block"><h3>Bill To</h3>' +
                        '<p><strong>' + name + '</strong></p><p>' + phone + '</p>' + (email ? '<p>' + email + '</p>' : '') + '<p>' + address + '</p></div>' +
                        '<div class="info-block"><h3>Rental Period</h3>' + rentalPeriod + '</div>' +
                        '</div>' +
                        (invSetImageUrl ? '<div style="text-align:center;margin:15px 0;"><img src="' + invSetImageUrl + '" style="max-width:200px;max-height:160px;border:1px solid #ddd;border-radius:6px;"></div>' : '') +
                        (setIncludes ? '<div style="background:#f9f0ff;border:1px solid #e0c8f0;border-radius:6px;padding:12px 15px;margin:10px 0 15px 0;"><strong style="color:#7b4fa6;">Set Includes:</strong> <span style="color:#333;margin-left:6px;">' + setIncludes + '</span></div>' : '') +
                        '<table><thead><tr><th>Item</th><th>Category</th><th>Set Code</th><th style="text-align:right;">Amount</th></tr></thead>' +
                        '<tbody>' + tableRows + '</tbody></table>' +
                        (customization ? '<div style="background:#fff8e1;border:1px solid #f0e68c;border-radius:6px;padding:12px 15px;margin:15px 0;"><strong style="color:#c9a86c;">Customization:</strong> ' + customization + '</div>' : '') +
                        (stylist ? '<div style="background:#f0f7ff;border:1px solid #c8dff8;border-radius:6px;padding:12px 15px;margin:15px 0;"><strong style="color:#2980b9;">Stylist Who Attended:</strong> ' + stylist + '</div>' : '') +
                        (secHoldReason ? '<div style="background:#fff3e0;border:1px solid #ffe0b2;border-radius:6px;padding:12px 15px;margin:15px 0;"><strong style="color:#e67e22;">Reasons for Security Hold:</strong> <span style="color:#333;margin-left:6px;">' + secHoldReason + '</span></div>' : '') +
                        (invType === 'booking' ?
                        '<div style="margin:8px 0;padding:10px 12px;border:2px solid #e74c3c;border-radius:6px;background:#fff8f8;">' +
                        '<h5 style="margin:0 0 8px;color:#e74c3c;font-size:12px;font-weight:bold;border-bottom:1px solid #f5c6c6;padding-bottom:5px;">Important to Note Customers</h5>' +
                        '<table style="width:100%;border-collapse:collapse;font-size:11px;">' +
                        '<tr><td style="padding:4px 0;color:#333;font-weight:500;">Remaining Bridal Rent</td><td style="text-align:right;font-weight:bold;color:#333;">\u20b9' + grandTotal.toLocaleString('en-IN') + '</td></tr>' +
                        '<tr><td style="padding:4px 0;color:#333;font-weight:500;">Security to be Paid on Pickup</td><td style="text-align:right;font-weight:bold;color:#333;">\u20b9' + secDep.toLocaleString('en-IN') + '</td></tr>' +
                        '<tr><td colspan="2"><hr style="border:none;border-top:1.5px solid #e74c3c;margin:4px 0;"></td></tr>' +
                        '<tr><td style="padding:4px 0;color:#e74c3c;font-weight:bold;font-size:12px;">Total to be Paid by Customer on Pickup</td><td style="text-align:right;font-weight:bold;font-size:12px;color:#e74c3c;">\u20b9' + (grandTotal + secDep).toLocaleString('en-IN') + '</td></tr>' +
                        '</table></div>'
                        : '') +
                        '<div style="margin:8px 0;padding:8px 12px;border:1px solid #ddd;border-radius:6px;background:#fafafa;">' +
                        '<h5 style="margin:0 0 5px;color:#1a1f36;font-size:11px;">Kavipushp Jewels \u2013 Terms &amp; Conditions</h5>' +
                        '<ol>' +
                        '<li>Jewellery is rented only for the period mentioned; late return will be charged per day.</li>' +
                        '<li>A refundable security deposit may be collected and returned after condition check.</li>' +
                        '<li>Any damage, loss, or missing parts will be charged as per repair or replacement value mentioned before dispatch in the whatsapp group.</li>' +
                        '<li>Full payment must be made before delivery; advance/booking amount is non-refundable.</li>' +
                        '<li>Customer must provide valid ID proof and is responsible for the safety of the jewellery during the rental period.</li>' +
                        '<li>All disputes are subject to local jurisdiction.</li>' +
                        '<li>There shall be no discount on the rental prices of the set.</li>' +
                        '<li>Rental price does not include customization charges if applicable.</li>' +
                        '<li>Booking amount is neither refundable nor adjustable or transferable in any condition, no exceptions.</li>' +
                        '<li>No excuses like jewellery will be provided by in-laws, in-laws insisting on wearing gold only, wedding postponed or cancelled are entertained.</li>' +
                        '<li>Articles agreed on at the time of booking will only be provided; extra article will be chargeable.</li>' +
                        '<li>Use official number 9977722271 for any query or requirements; no responsibility for any personal number of employee.</li>' +
                        '<li>Rental clarification: First 3 days normal rent of Bridal Set, next 2 days Rs.500 extra (if informed at the time of booking).</li>' +
                        '<li>If the bridal set is not returned within the informed timeline, per day rental charges will be applicable as extra charge.</li>' +
                        '</ol>' +
                        '<p style="margin:6px 0 0;font-size:11px;font-weight:bold;color:#333;">Accepted and Agreed By:</p>' +
                        '<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:15px;">' +
                        '<div><div style="border-top:1px solid #333;width:100%;margin-bottom:6px;"></div><p style="margin:0;font-size:12px;color:#555;">Customer Signature &amp; Date</p></div>' +
                        '<div><div style="border-top:1px solid #333;width:100%;margin-bottom:6px;"></div><p style="margin:0;font-size:12px;color:#555;">Authorized Signatory</p></div>' +
                        '</div></div>' +
                        '<div class="footer"><p>Thank you for choosing ' + bizName + '!</p><p>' + footerNote + '</p></div>' +
                        '<script>window.onload = function() { window.print(); }<\/script>' +
                        '</body></html>'
                    );
                    pw.document.close();
                }
                </script>
            </div>
        </div>
        <?php else: ?>
        <div class="notice notice-error"><p><?php _e('Invoice not found.', 'kavipushp-bridals'); ?></p></div>
        <?php endif; ?>

        <?php elseif ($action === 'generate'): ?>
        <!-- Generate Invoice Form -->
        <?php
        global $wpdb;
        // Get all bookings for selection
        $all_bookings = get_posts(array(
            'post_type'      => 'booking',
            'post_status'    => array('publish', 'draft', 'private'),
            'posts_per_page' => -1,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ));
        // Fetch security deposit per booking: prefer pickup invoice, fall back to booking invoice
        $all_sec_invoices = $wpdb->get_results(
            "SELECT booking_id, security_deposit, invoice_type FROM {$wpdb->prefix}kavipushp_invoices WHERE invoice_type IN ('booking','pickup') ORDER BY FIELD(invoice_type,'booking','pickup'), id ASC"
        );
        $pickup_invoices = array();
        foreach ($all_sec_invoices as $row) {
            // pickup overwrites booking if both exist
            $pickup_invoices[$row->booking_id] = floatval($row->security_deposit);
        }
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
                                $b_set_image = '';
                                if ($b_set_id) {
                                    $b_set_code_img = get_post_meta($b_set_id, '_set_id', true);
                                    if ($b_set_code_img) {
                                        $b_set_image = get_option('kp_setimg_' . sanitize_key($b_set_code_img), '');
                                    }
                                    if (!$b_set_image) {
                                        $b_thumb_id = get_post_thumbnail_id($b_set_id);
                                        if ($b_thumb_id) {
                                            $b_set_image = wp_get_attachment_image_url($b_thumb_id, 'medium');
                                            if (!$b_set_image) $b_set_image = wp_get_attachment_image_url($b_thumb_id, 'full');
                                            if (!$b_set_image) $b_set_image = wp_get_attachment_url($b_thumb_id);
                                        }
                                    }
                                }
                                $b_customized_set = get_post_meta($b->ID, '_customized_bridal_set', true);
                                $b_set_category = get_post_meta($b->ID, '_bridal_set_category', true);
                                if (!$b_set_category && $b_set_id) {
                                    $b_cat_terms = get_the_terms($b_set_id, 'bridal_category');
                                    $b_set_category = ($b_cat_terms && !is_wp_error($b_cat_terms)) ? $b_cat_terms[0]->name : '';
                                }
                                $b_display_name = $b_set_name ?: $b_customized_set;
                                $b_date = get_the_date('d/m/Y', $b);
                            ?>
                            <option value="<?php echo esc_attr($b->ID); ?>"
                                data-name="<?php echo esc_attr($b_name); ?>"
                                data-phone="<?php echo esc_attr($b_phone); ?>"
                                data-email="<?php echo esc_attr(get_post_meta($b->ID, '_customer_email', true)); ?>"
                                data-address="<?php echo esc_attr(get_post_meta($b->ID, '_customer_address', true)); ?>"
                                data-set-name="<?php echo esc_attr($b_set_name); ?>"
                                data-set-code="<?php echo esc_attr($b_set_id ? get_post_meta($b_set_id, '_set_id', true) : ''); ?>"
                                data-category="<?php echo esc_attr($b_set_category); ?>"
                                data-function-date="<?php echo esc_attr(get_post_meta($b->ID, '_function_date', true)); ?>"
                                data-pickup-date="<?php echo esc_attr(get_post_meta($b->ID, '_pickup_date', true)); ?>"
                                data-return-date="<?php echo esc_attr(get_post_meta($b->ID, '_return_date', true)); ?>"
                                data-rent="<?php echo esc_attr(get_post_meta($b->ID, '_total_amount', true)); ?>"
                                data-booking-amount="<?php echo esc_attr(get_post_meta($b->ID, '_booking_amount', true)); ?>"
                                data-customization="<?php echo esc_attr(get_post_meta($b->ID, '_booking_notes', true)); ?>"
                                data-stylist="<?php echo esc_attr(get_post_meta($b->ID, '_stylist_attended', true)); ?>"
                                data-nath="<?php echo esc_attr(get_post_meta($b->ID, '_nath', true)); ?>"
                                data-maang-teeka="<?php echo esc_attr(get_post_meta($b->ID, '_maang_teeka', true)); ?>"
                                data-ring="<?php echo esc_attr(get_post_meta($b->ID, '_ring', true)); ?>"
                                data-matha-patti="<?php echo esc_attr(get_post_meta($b->ID, '_matha_patti', true)); ?>"
                                data-sheesh-patti="<?php echo esc_attr(get_post_meta($b->ID, '_sheesh_patti', true)); ?>"
                                data-hath-phool="<?php echo esc_attr(get_post_meta($b->ID, '_hath_phool', true)); ?>"
                                data-pasa="<?php echo esc_attr(get_post_meta($b->ID, '_pasa', true)); ?>"
                                data-any-other-item="<?php echo esc_attr(get_post_meta($b->ID, '_any_other_item', true)); ?>"
                                data-customized-bridal-set="<?php echo esc_attr($b_customized_set); ?>"
                                data-status="<?php echo esc_attr($b_status); ?>"
                                data-date="<?php echo esc_attr($b_date); ?>"
                                data-pickup-security="<?php echo esc_attr(isset($pickup_invoices[$b->ID]) ? $pickup_invoices[$b->ID] : 0); ?>"
                                data-set-image="<?php echo esc_attr($b_set_image); ?>"
                                <?php selected($invoice_booking_id, $b->ID); ?>>
                                <?php echo esc_html($b_name . ' - ' . $b_display_name . ' (' . $b_date . ') [' . strtoupper($b_status) . ']'); ?>
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

                    <div id="security-amount-group" class="kp-form-group" style="display: none;">
                        <label><strong><?php _e('Security Amount (₹)', 'kavipushp-bridals'); ?></strong></label>
                        <input type="number" id="security_amount" value="0" min="0" onchange="updateInvoicePreview()" style="padding: 6px 10px; width: 200px; border: 1px solid #ddd; border-radius: 4px;">
                    </div>

                    <div id="amount-received-group" class="kp-form-group" style="display: none;">
                        <label><strong id="amount-received-label"><?php _e('Amount Received at Pickup (₹)', 'kavipushp-bridals'); ?></strong></label>
                        <input type="number" id="amount_received" value="0" min="0" onchange="updateInvoicePreview()" style="padding: 6px 10px; width: 200px; border: 1px solid #ddd; border-radius: 4px;">
                    </div>

                    <div id="damages-paid-group" class="kp-form-group" style="display: none;">
                        <label><strong><?php _e('Damage or Late Charges or Security Hold (₹)', 'kavipushp-bridals'); ?></strong></label>
                        <input type="number" id="damages_paid" value="0" min="0" onchange="updateInvoicePreview()" style="padding: 6px 10px; width: 200px; border: 1px solid #ddd; border-radius: 4px;">
                    </div>

                    <div id="security-hold-reason-group" class="kp-form-group" style="display: none;">
                        <label><strong><?php _e('Reasons for Security Hold', 'kavipushp-bridals'); ?></strong></label>
                        <textarea id="security_hold_reason" rows="3" placeholder="<?php esc_attr_e('Enter reasons for security hold, damage or late charges (if any)...', 'kavipushp-bridals'); ?>" style="padding: 6px 10px; width: 400px; border: 1px solid #ddd; border-radius: 4px; display: block;"></textarea>
                    </div>

                    <div id="set-image-group" class="kp-form-group">
                        <label><strong><?php _e('Bridal Set Image', 'kavipushp-bridals'); ?></strong></label>
                        <input type="file" id="set_image_upload" accept="image/*" onchange="handleSetImageUpload(this)" style="padding: 6px 0; display: block;">
                        <div id="set-image-preview-thumb" style="display:none; margin-top:8px;">
                            <img id="set-image-thumb" src="" style="max-width:150px; max-height:120px; border:1px solid #ddd; border-radius:4px; display:block;">
                            <button type="button" onclick="clearSetImage()" style="margin-top:5px; padding:4px 10px; border:1px solid #ccc; border-radius:4px; cursor:pointer; background:#fff; color:#c00; font-size:12px;">Remove Image</button>
                        </div>
                    </div>

                    <textarea id="set_includes" style="display:none;"></textarea>

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
                                    <th style="padding: 10px; text-align: left; border-bottom: 2px solid #ddd;"><?php _e('Category', 'kavipushp-bridals'); ?></th>
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
                        <div id="inv-stylist" style="display: none; background: #f0f7ff; border: 1px solid #c8dff8; border-radius: 6px; padding: 12px 15px; margin: 15px 0;">
                            <strong style="color: #2980b9;"><i class="dashicons dashicons-admin-users" style="font-size: 14px;"></i> <?php _e('Stylist Who Attended:', 'kavipushp-bridals'); ?></strong>
                            <span id="inv-stylist-text" style="color: #333; margin-left: 5px;"></span>
                        </div>

                        <!-- Important to Note Customers -->
                        <div id="inv-important-note" style="margin: 20px 0; padding: 15px; border: 2px solid #e74c3c; border-radius: 8px; background: #fff8f8;">
                            <h5 style="margin: 0 0 12px; color: #e74c3c; font-size: 14px; font-weight: bold; border-bottom: 1px solid #f5c6c6; padding-bottom: 8px;"><?php _e('Important to Note Customers', 'kavipushp-bridals'); ?></h5>
                            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                                <tr>
                                    <td style="padding: 6px 0; color: #333; font-weight: 500;"><?php _e('Remaining Bridal Rent', 'kavipushp-bridals'); ?></td>
                                    <td style="padding: 6px 0; text-align: right; font-weight: bold; color: #333;">&#8377;<span id="inv-note-remaining-rent">0</span></td>
                                </tr>
                                <tr>
                                    <td style="padding: 6px 0; color: #333; font-weight: 500;"><?php _e('Security to be Paid on Pickup', 'kavipushp-bridals'); ?></td>
                                    <td style="padding: 6px 0; text-align: right;">
                                        <span style="font-weight: bold; color: #333;">&#8377;</span><input type="number" id="inv-security-pickup" value="0" min="0" style="width: 100px; padding: 3px 6px; border: 1px solid #e74c3c; border-radius: 4px; text-align: right; font-weight: bold;" oninput="onSecurityPickupInput()">
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" style="padding: 4px 0;"><hr style="border: none; border-top: 1.5px solid #e74c3c; margin: 4px 0;"></td>
                                </tr>
                                <tr>
                                    <td style="padding: 6px 0; color: #e74c3c; font-weight: bold; font-size: 14px;"><?php _e('Total to be Paid by Customer on Pickup', 'kavipushp-bridals'); ?></td>
                                    <td style="padding: 6px 0; text-align: right; font-weight: bold; font-size: 14px; color: #e74c3c;">&#8377;<span id="inv-note-total-pickup">0</span></td>
                                </tr>
                            </table>
                        </div>

                        <div style="margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 6px; background: #fafafa;">
                            <h5 style="margin: 0 0 10px; color: #1a1f36;"><?php _e('Kavipushp Jewels &ndash; Terms &amp; Conditions', 'kavipushp-bridals'); ?></h5>
                            <ol style="margin: 0; padding-left: 18px; font-size: 12px; color: #555; line-height: 1.8;">
                                <li><?php _e('Jewellery is rented only for the period mentioned; late return will be charged per day.', 'kavipushp-bridals'); ?></li>
                                <li><?php _e('A refundable security deposit may be collected and returned after condition check.', 'kavipushp-bridals'); ?></li>
                                <li><?php _e('Any damage, loss, or missing parts will be charged as per repair or replacement value mentioned before dispatch in the whatsapp group.', 'kavipushp-bridals'); ?></li>
                                <li><?php _e('Full payment must be made before delivery; advance/booking amount is non-refundable.', 'kavipushp-bridals'); ?></li>
                                <li><?php _e('Customer must provide valid ID proof and is responsible for the safety of the jewellery during the rental period.', 'kavipushp-bridals'); ?></li>
                                <li><?php _e('All disputes are subject to local jurisdiction.', 'kavipushp-bridals'); ?></li>
                                <li><?php _e('There shall be no discount on the rental prices of the set.', 'kavipushp-bridals'); ?></li>
                                <li><?php _e('Rental price does not include customization charges if applicable.', 'kavipushp-bridals'); ?></li>
                                <li><?php _e('Booking amount is neither refundable nor adjustable or transferable in any condition, no exceptions.', 'kavipushp-bridals'); ?></li>
                                <li><?php _e('No excuses like jewellery will be provided by in-laws, in-laws insisting on wearing gold only, wedding postponed or cancelled are entertained.', 'kavipushp-bridals'); ?></li>
                                <li><?php _e('Articles agreed on at the time of booking will only be provided; extra article will be chargeable.', 'kavipushp-bridals'); ?></li>
                                <li><?php _e('Use official number 9977722271 for any query or requirements; no responsibility for any personal number of employee.', 'kavipushp-bridals'); ?></li>
                                <li><?php _e('Rental clarification: First 3 days normal rent of Bridal Set, next 2 days Rs.500 extra (if informed at the time of booking).', 'kavipushp-bridals'); ?></li>
                                <li><?php _e('If the bridal set is not returned within the informed timeline, per day rental charges will be applicable as extra charge.', 'kavipushp-bridals'); ?></li>
                            </ol>
                            <p style="margin: 10px 0 0; font-size: 12px; font-weight: bold; color: #333;"><?php _e('Accepted and Agreed By:', 'kavipushp-bridals'); ?></p>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-top: 30px;">
                                <div>
                                    <div style="border-top: 1px solid #333; width: 100%; margin-bottom: 6px;"></div>
                                    <p style="margin: 0; font-size: 12px; color: #555;"><?php _e('Customer Signature &amp; Date', 'kavipushp-bridals'); ?></p>
                                </div>
                                <div>
                                    <div style="border-top: 1px solid #333; width: 100%; margin-bottom: 6px;"></div>
                                    <p style="margin: 0; font-size: 12px; color: #555;"><?php _e('Authorized Signatory', 'kavipushp-bridals'); ?></p>
                                </div>
                            </div>
                        </div>

                        <p id="inv-status" style="text-align: center; margin: 15px 0;"></p>
                    </div>

                    <div style="margin-top: 20px; display: flex; gap: 10px; flex-wrap:wrap;">
                        <button class="button button-primary button-large" onclick="saveGeneratedInvoice()">
                            <i class="dashicons dashicons-download"></i> <?php _e('Save Invoice', 'kavipushp-bridals'); ?>
                        </button>
                        <button class="button button-large" onclick="printGeneratedInvoice()">
                            <i class="dashicons dashicons-printer"></i> <?php _e('Print Invoice', 'kavipushp-bridals'); ?>
                        </button>
                        <button class="button button-large" onclick="createGeneratedInvoicePDF(this)" style="background:#c9a86c;border-color:#b8965a;color:#fff;">
                            <i class="dashicons dashicons-media-document"></i> <?php _e('Create PDF', 'kavipushp-bridals'); ?>
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

        function getInvoiceConfig(type, rent, bookingAmount, amountReceived, damagesAmount, paymentStatus, securityDeposit, damagesPaid) {
            securityDeposit = securityDeposit || 0;
            amountReceived = amountReceived || 0;
            damagesAmount = damagesAmount || 0;
            damagesPaid = damagesPaid || 0;
            paymentStatus = paymentStatus || 'all_paid';
            var config = {
                title: '',
                prefix: '',
                rows: [],
                grandTotal: 0,
                grandTotalLabel: 'Remaining Balance',
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
                config.rows = rows;
                config.grandTotal = rent - bookingAmount;
                config.afterTotalRows = [];
                config.afterTotalRows.push({ label: 'Remaining Bridal Rent', amount: config.grandTotal, color: '#555', isSubtotal: true });
                config.afterTotalRows.push({ label: 'Security to be Paid on Pickup', amount: securityDeposit, color: '#2980b9', isSubtotal: true });
                var totalReceived = config.grandTotal + securityDeposit;
                config.afterTotalRows.push({ label: 'Total to be Paid by Customer on Pickup', amount: totalReceived, color: '#27ae60', isBold: true });
                config.footerNote = 'Security amount will be adjusted in final payment.';
            } else if (type === 'final') {
                config.title = 'FINAL INVOICE';
                config.prefix = 'FN';
                var rows = [
                    { label: 'Bridal Set Rent', amount: rent, color: '' }
                ];
                if (bookingAmount > 0) {
                    rows.push({ label: 'Less: Booking Amount', amount: bookingAmount, color: '#e74c3c', isDeduction: true });
                }
                config.rows = rows;
                config.grandTotal = rent - bookingAmount;
                config.grandTotalLabel = 'Remaining Rent Received on Pickup';
                var totalReceived = config.grandTotal + securityDeposit;
                var securityRefund = securityDeposit - damagesPaid;
                config.afterTotalRows = [];
                config.afterTotalRows.push({ label: 'Total Received on Pickup', amount: totalReceived, color: '#27ae60', isBold: true });
                if (damagesPaid > 0) {
                    config.afterTotalRows.push({ label: 'Less: Damage or Late Charges or Security Hold', amount: damagesPaid, color: '#e74c3c', isDeduction: true });
                }
                var refundColor = securityRefund >= 0 ? '#27ae60' : '#e74c3c';
                config.afterTotalRows.push({ label: 'Security Refund after Damage or Late Charges or Security Hold', amount: securityRefund, color: refundColor, isBold: true });
                config.footerNote = securityRefund >= 0
                    ? 'Security refund of \u20B9' + securityRefund.toLocaleString('en-IN') + ' to be returned to customer.'
                    : 'Customer owes \u20B9' + Math.abs(securityRefund).toLocaleString('en-IN') + ' towards damage charges.';
            }

            return config;
        }

        function updateInvoicePreview() {
            // Always clear the save message when preview updates
            var msgDiv = document.getElementById('invoice-save-msg');
            if (msgDiv) { msgDiv.style.display = 'none'; msgDiv.innerHTML = ''; }

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

            // Show/hide security amount and amount received inputs
            var secAmtGroup = document.getElementById('security-amount-group');
            var amtReceivedGroup = document.getElementById('amount-received-group');
            var amtReceivedLabel = document.getElementById('amount-received-label');
            if (invoiceType === 'pickup') {
                if (secAmtGroup) secAmtGroup.style.display = 'block';
                amtReceivedGroup.style.display = 'none';
                document.getElementById('amount_received').value = 0;
            } else if (invoiceType === 'final') {
                if (secAmtGroup) secAmtGroup.style.display = 'block';
                var pickupSecurity = parseFloat(opt.dataset.pickupSecurity) || 0;
                if (pickupSecurity > 0) document.getElementById('security_amount').value = pickupSecurity;
                amtReceivedGroup.style.display = 'none';
                document.getElementById('amount_received').value = 0;
            } else {
                if (secAmtGroup) { secAmtGroup.style.display = 'none'; document.getElementById('security_amount').value = 0; }
                amtReceivedGroup.style.display = 'none';
                document.getElementById('amount_received').value = 0;
            }
            // Read securityDeposit AFTER auto-fill so final invoice gets correct value
            var securityDeposit = parseFloat(document.getElementById('security_amount').value) || 0;
            // Show/hide damages paid input (final only)
            var damagesPaidGroup = document.getElementById('damages-paid-group');
            var secHoldReasonGroup = document.getElementById('security-hold-reason-group');
            if (invoiceType === 'final') {
                if (damagesPaidGroup) damagesPaidGroup.style.display = 'block';
                if (secHoldReasonGroup) secHoldReasonGroup.style.display = 'block';
            } else {
                if (damagesPaidGroup) { damagesPaidGroup.style.display = 'none'; document.getElementById('damages_paid').value = 0; }
                if (secHoldReasonGroup) { secHoldReasonGroup.style.display = 'none'; document.getElementById('security_hold_reason').value = ''; }
                kpPaymentStatus = 'all_paid';
            }
            var amountReceived = parseFloat(document.getElementById('amount_received').value) || 0;
            var damagesAmount = 0;
            var damagesPaid = parseFloat(document.getElementById('damages_paid').value) || 0;

            var config = getInvoiceConfig(invoiceType, rent, bookingAmount, amountReceived, damagesAmount, kpPaymentStatus, securityDeposit, damagesPaid);

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
                '<td style="padding: 10px; border-bottom: 1px solid #eee;">' + (opt.dataset.setName || opt.dataset.customizedBridalSet || '') + '</td>' +
                '<td style="padding: 10px; border-bottom: 1px solid #eee;">' + (opt.dataset.category || '') + '</td>' +
                '<td style="padding: 10px; border-bottom: 1px solid #eee;">' + (opt.dataset.setCode || '') + '</td>' +
                '<td style="padding: 10px; text-align: right; border-bottom: 1px solid #eee;">\u20B9' + rent.toLocaleString('en-IN') + '</td>' +
                '</tr>';

            // Additional rows based on invoice type
            config.rows.forEach(function(row, index) {
                if (index === 0) return;
                var colorStyle = row.color ? ' color: ' + row.color + ';' : '';
                var amountText = row.isDeduction ? '- \u20B9' + row.amount.toLocaleString('en-IN') : '\u20B9' + row.amount.toLocaleString('en-IN');
                tbodyHtml += '<tr>' +
                    '<td style="padding: 10px; border-bottom: 1px solid #eee;' + colorStyle + '" colspan="3">' + row.label + '</td>' +
                    '<td style="padding: 10px; text-align: right; border-bottom: 1px solid #eee;' + colorStyle + '">' + amountText + '</td>' +
                    '</tr>';
            });

            // Remaining balance row
            tbodyHtml += '<tr style="font-weight: bold; font-size: 16px;">' +
                '<td style="padding: 12px; border-top: 2px solid #c9a86c; border-bottom: 3px solid #8B4513;" colspan="3">' + (config.grandTotalLabel || 'Remaining Balance') + '</td>' +
                '<td style="padding: 12px; text-align: right; border-top: 2px solid #c9a86c; border-bottom: 3px solid #8B4513;">\u20B9' + config.grandTotal.toLocaleString('en-IN') + '</td>' +
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
                    var subtotalStyle = row.isSubtotal ? ' font-weight: 600; background: #f5f5f5;' : '';
                    var amountText = row.isDeduction ? '- \u20B9' + row.amount.toLocaleString('en-IN') : '\u20B9' + row.amount.toLocaleString('en-IN');
                    tbodyHtml += '<tr>' +
                        '<td style="padding: 10px; border-bottom: 1px solid #eee;' + colorStyle + boldStyle + subtotalStyle + '" colspan="3">' + row.label + '</td>' +
                        '<td style="padding: 10px; text-align: right; border-bottom: 1px solid #eee;' + colorStyle + boldStyle + subtotalStyle + '">' + amountText + '</td>' +
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
                td1.colSpan = 3;
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

            // Stylist
            var stylist = opt.dataset.stylist || '';
            if (stylist.trim()) {
                document.getElementById('inv-stylist-text').textContent = stylist;
                document.getElementById('inv-stylist').style.display = 'block';
            } else {
                document.getElementById('inv-stylist').style.display = 'none';
            }

            // Set image — prefer manual upload, fall back to stored inventory image
            var invSetImg = kpSetImage || (opt.dataset.setImage || '');
            if (invSetImg) {
                document.getElementById('inv-set-image').src = invSetImg;
                document.getElementById('inv-set-image-section').style.display = 'block';
            } else {
                document.getElementById('inv-set-image-section').style.display = 'none';
            }

            // Set Includes — auto-build from booking jewelry fields
            var jewelryLabels = [
                { key: 'nath',        label: 'Nath' },
                { key: 'maangTeeka',  label: 'Maang Teeka' },
                { key: 'ring',        label: 'Ring' },
                { key: 'mathaPatti',  label: 'Matha Patti' },
                { key: 'sheeshPatti', label: 'Sheesh Patti' },
                { key: 'hathPhool',   label: 'Hath Phool' },
                { key: 'pasa',         label: 'Pasa' },
                { key: 'anyOtherItem', label: 'Any Other Item' }
            ];
            var autoIncludes = [];
            jewelryLabels.forEach(function(j) {
                var val = opt.dataset[j.key] || '';
                if (val) autoIncludes.push(j.label + ': ' + val);
            });
            var autoIncludesText = autoIncludes.join(' | ');
            // Merge with any manual text in the textarea
            var manualText = document.getElementById('set_includes').value.trim();
            var setIncludes = autoIncludesText || manualText;
            if (setIncludes) {
                document.getElementById('inv-set-includes-text').textContent = setIncludes;
                document.getElementById('inv-set-includes-section').style.display = 'block';
            } else {
                document.getElementById('inv-set-includes-section').style.display = 'none';
            }

            document.getElementById('inv-status').innerHTML = '<span style="background: #c9a86c; color: #fff; padding: 5px 15px; border-radius: 4px;">' + config.title + '</span>';

            // Update "Important to Note Customers" section (not shown on pickup invoices)
            var invImportantNote = document.getElementById('inv-important-note');
            if (invImportantNote) invImportantNote.style.display = (invoiceType === 'booking') ? 'block' : 'none';
            var noteRemainingEl = document.getElementById('inv-note-remaining-rent');
            var secPickupEl = document.getElementById('inv-security-pickup');
            var noteTotalEl = document.getElementById('inv-note-total-pickup');
            if (noteRemainingEl) noteRemainingEl.textContent = config.grandTotal.toLocaleString('en-IN');
            if (secPickupEl && invoiceType === 'final') {
                secPickupEl.value = securityDeposit;
            }
            if (noteTotalEl && secPickupEl) {
                var secPickupVal = parseFloat(secPickupEl.value) || 0;
                noteTotalEl.textContent = (config.grandTotal + secPickupVal).toLocaleString('en-IN');
            }

            document.getElementById('invoice-preview').style.display = 'block';
            document.getElementById('invoice-empty').style.display = 'none';
        }

        function onSecurityPickupInput() {
            var secPickup = parseFloat(document.getElementById('inv-security-pickup').value) || 0;
            // Sync to security_amount field so pickup/final invoices use this value
            var secAmtInput = document.getElementById('security_amount');
            if (secAmtInput) secAmtInput.value = secPickup;
            // Update "Total to be Paid" in note section
            var noteRemainingEl = document.getElementById('inv-note-remaining-rent');
            var remaining = noteRemainingEl ? (parseFloat(noteRemainingEl.textContent.replace(/,/g, '')) || 0) : 0;
            var noteTotalEl = document.getElementById('inv-note-total-pickup');
            if (noteTotalEl) noteTotalEl.textContent = (remaining + secPickup).toLocaleString('en-IN');
        }

        async function createGeneratedInvoicePDF(btn) {
            var previewEl = document.getElementById('invoice-preview');
            if (!previewEl || previewEl.style.display === 'none') { alert('Please select a booking first.'); return; }
            var origHtml = btn.innerHTML;
            btn.innerHTML = '<i class="dashicons dashicons-update"></i> Generating...';
            btn.disabled = true;
            try {
                var sel = document.getElementById('invoice_booking_select');
                var opt = sel && sel.options[sel.selectedIndex];
                var custName = (opt && opt.dataset.name) ? opt.dataset.name.replace(/[^a-z0-9]/gi, '-') : 'invoice';
                var invType = getSelectedInvoiceType ? getSelectedInvoiceType() : 'invoice';
                var innerDiv = previewEl.querySelector('div');
                var target = innerDiv || previewEl;
                var canvas = await html2canvas(target, { scale: 2, useCORS: true, backgroundColor: '#ffffff', logging: false });
                var { jsPDF } = window.jspdf;
                var pdf = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });
                var pageW = pdf.internal.pageSize.getWidth();
                var pageH = pdf.internal.pageSize.getHeight();
                var imgH = (canvas.height * pageW) / canvas.width;
                var finalH = Math.min(imgH, pageH);
                pdf.addImage(canvas.toDataURL('image/jpeg', 0.95), 'JPEG', 0, 0, pageW, finalH);
                pdf.save(invType + '-' + custName + '.pdf');
            } catch(e) { console.error('PDF error:', e); alert('PDF generation failed: ' + e.message); }
            btn.innerHTML = origHtml; btn.disabled = false;
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
            var setName = opt.dataset.setName || opt.dataset.customizedBridalSet || '';
            var setCode = opt.dataset.setCode || '';
            var setCategory = opt.dataset.category || '';
            var rent = parseFloat(opt.dataset.rent) || 0;
            var bookingAmount = parseFloat(opt.dataset.bookingAmount) || 0;
            var securityDeposit = parseFloat(document.getElementById('security_amount').value) || 0;
            var securityPickup = parseFloat(document.getElementById('inv-security-pickup').value) || 0;
            var functionDate = formatDate(opt.dataset.functionDate);
            var pickupDate = formatDate(opt.dataset.pickupDate);
            var returnDate = formatDate(opt.dataset.returnDate);
            var invDate = opt.dataset.date || '';

            var customization = opt.dataset.customization || '';
            var stylist = opt.dataset.stylist || '';
            var secHoldReasonVal = (document.getElementById('security_hold_reason') ? document.getElementById('security_hold_reason').value : '') || '';
            // Auto-build Set Includes from booking jewelry fields
            var _jewelryLabels = [
                { key: 'nath',        label: 'Nath' },
                { key: 'maangTeeka',  label: 'Maang Teeka' },
                { key: 'ring',        label: 'Ring' },
                { key: 'mathaPatti',  label: 'Matha Patti' },
                { key: 'sheeshPatti', label: 'Sheesh Patti' },
                { key: 'hathPhool',   label: 'Hath Phool' },
                { key: 'pasa',         label: 'Pasa' },
                { key: 'anyOtherItem', label: 'Any Other Item' }
            ];
            var _autoIncludes = [];
            _jewelryLabels.forEach(function(j) {
                var val = opt.dataset[j.key] || '';
                if (val) _autoIncludes.push(j.label + ': ' + val);
            });
            var setIncludes = _autoIncludes.join(' | ') || document.getElementById('set_includes').value.trim();

            var amountReceived = parseFloat(document.getElementById('amount_received').value) || 0;
            var damagesAmount = 0;
            var damagesPaid = parseFloat(document.getElementById('damages_paid').value) || 0;
            var config = getInvoiceConfig(invoiceType, rent, bookingAmount, amountReceived, damagesAmount, kpPaymentStatus, securityDeposit, damagesPaid);
            var invNum = config.prefix + '-' + String(opt.value).padStart(5, '0');

            // Build table rows for print
            var tableRows = '<tr><td>' + setName + '</td><td>' + setCategory + '</td><td>' + setCode + '</td><td style="text-align:right;">\u20B9' + rent.toLocaleString('en-IN') + '</td></tr>';
            config.rows.forEach(function(row, index) {
                if (index === 0) return;
                var colorStyle = row.color ? ' style="color:' + row.color + ';"' : '';
                var amountText = row.isDeduction ? '- \u20B9' + row.amount.toLocaleString('en-IN') : '\u20B9' + row.amount.toLocaleString('en-IN');
                tableRows += '<tr><td colspan="3"' + colorStyle + '>' + row.label + '</td><td style="text-align:right;' + (row.color ? 'color:' + row.color + ';' : '') + '">' + amountText + '</td></tr>';
            });
            tableRows += '<tr class="total-row"><td colspan="3" style="border-bottom:3px solid #8B4513;">' + (config.grandTotalLabel || 'Remaining Balance') + '</td><td style="text-align:right;border-bottom:3px solid #8B4513;">\u20B9' + config.grandTotal.toLocaleString('en-IN') + '</td></tr>';
            if (config.afterTotalRows && config.afterTotalRows.length > 0) {
                config.afterTotalRows.forEach(function(row) {
                    if (row.isStatus) {
                        var isAllClear = row.statusMsg.indexOf('All Clear') !== -1;
                        tableRows += '<tr><td colspan="4" style="text-align:center; font-weight:bold; font-size:16px; padding:12px; background:' + (isAllClear ? '#e8f5e9; color:#27ae60;' : '#fff3e0; color:#e67e22;') + '">' + row.statusMsg + '</td></tr>';
                        return;
                    }
                    var colorStyle = row.color ? ' color:' + row.color + ';' : '';
                    var boldStyle = row.isBold ? ' font-weight:bold;' : '';
                    var amountText = row.isDeduction ? '- \u20B9' + row.amount.toLocaleString('en-IN') : '\u20B9' + row.amount.toLocaleString('en-IN');
                    tableRows += '<tr><td colspan="3" style="' + colorStyle + boldStyle + '">' + row.label + '</td><td style="text-align:right;' + colorStyle + boldStyle + '">' + amountText + '</td></tr>';
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
                '@page { size: A4; margin: 10mm 12mm; }' +
                'html, body { font-family: Arial, sans-serif; font-size: 11px; margin: 0; padding: 0; width: 210mm; max-width: 210mm; }' +
                '.header { text-align: center; border-bottom: 2px solid #c9a86c; padding-bottom: 8px; margin-bottom: 10px; }' +
                '.header h1 { color: #1a1f36; margin: 0 0 3px 0; font-size: 18px; }' +
                '.header p { color: #666; margin: 0; font-size: 11px; line-height: 1.4; }' +
                '.header .contact-line { margin-top: 3px; font-size: 10px; }' +
                '.info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 10px; }' +
                '.info-block h3 { color: #c9a86c; margin: 0 0 4px 0; font-size: 11px; text-transform: uppercase; }' +
                '.info-block p { margin: 2px 0; color: #333; font-size: 11px; }' +
                'table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }' +
                'th, td { padding: 6px 8px; text-align: left; border-bottom: 1px solid #ddd; font-size: 11px; }' +
                'th { background: #f5f5f5; font-weight: 600; }' +
                '.total-row { font-weight: bold; font-size: 13px; }' +
                '.total-row td { border-top: 2px solid #c9a86c; }' +
                '.footer { text-align: center; color: #666; font-size: 10px; margin-top: 10px; padding-top: 8px; border-top: 1px solid #ddd; }' +
                'ol { margin: 4px 0; padding-left: 16px; font-size: 10px; color: #555; line-height: 1.6; }' +
                '@media print { html, body { width: 210mm; } * { -webkit-print-color-adjust: exact; print-color-adjust: exact; } }' +
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
                ((kpSetImage || opt.dataset.setImage) ? '<div style="text-align:center;margin:15px 0;"><img src="' + (kpSetImage || opt.dataset.setImage) + '" style="max-width:200px;max-height:160px;border:1px solid #ddd;border-radius:6px;"></div>' : '') +
                (setIncludes ? '<div style="background:#f9f0ff;border:1px solid #e0c8f0;border-radius:6px;padding:12px 15px;margin:10px 0 15px 0;"><strong style="color:#7b4fa6;">Set Includes:</strong> <span style="color:#333;margin-left:6px;">' + setIncludes + '</span></div>' : '') +
                '<table><thead><tr><th>Item</th><th>Category</th><th>Set Code</th><th style="text-align:right;">Amount</th></tr></thead>' +
                '<tbody>' + tableRows + '</tbody></table>' +
                (customization.trim() ? '<div style="background:#fff8e1;border:1px solid #f0e68c;border-radius:6px;padding:12px 15px;margin:15px 0;"><strong style="color:#c9a86c;">Customization:</strong> ' + customization + '</div>' : '') +
                (stylist.trim() ? '<div style="background:#f0f7ff;border:1px solid #c8dff8;border-radius:6px;padding:12px 15px;margin:15px 0;"><strong style="color:#2980b9;">Stylist Who Attended:</strong> ' + stylist + '</div>' : '') +
                (secHoldReasonVal.trim() ? '<div style="background:#fff3e0;border:1px solid #ffe0b2;border-radius:6px;padding:12px 15px;margin:15px 0;"><strong style="color:#e67e22;">Reasons for Security Hold:</strong> <span style="color:#333;margin-left:6px;">' + secHoldReasonVal + '</span></div>' : '') +
                (invoiceType === 'booking' ?
                '<div style="margin:8px 0;padding:10px 12px;border:2px solid #e74c3c;border-radius:6px;background:#fff8f8;">' +
                '<h5 style="margin:0 0 8px;color:#e74c3c;font-size:12px;font-weight:bold;border-bottom:1px solid #f5c6c6;padding-bottom:5px;">Important to Note Customers</h5>' +
                '<table style="width:100%;border-collapse:collapse;font-size:11px;">' +
                '<tr><td style="padding:4px 0;color:#333;font-weight:500;">Remaining Bridal Rent</td><td style="text-align:right;font-weight:bold;color:#333;">\u20B9' + config.grandTotal.toLocaleString('en-IN') + '</td></tr>' +
                '<tr><td style="padding:4px 0;color:#333;font-weight:500;">Security to be Paid on Pickup</td><td style="text-align:right;font-weight:bold;color:#333;">\u20B9' + securityPickup.toLocaleString('en-IN') + '</td></tr>' +
                '<tr><td colspan="2"><hr style="border:none;border-top:1.5px solid #e74c3c;margin:4px 0;"></td></tr>' +
                '<tr><td style="padding:4px 0;color:#e74c3c;font-weight:bold;font-size:12px;">Total to be Paid by Customer on Pickup</td><td style="text-align:right;font-weight:bold;font-size:12px;color:#e74c3c;">\u20B9' + (config.grandTotal + securityPickup).toLocaleString('en-IN') + '</td></tr>' +
                '</table></div>'
                : '') +
                '<div style="margin:8px 0;padding:8px 12px;border:1px solid #ddd;border-radius:6px;background:#fafafa;">' +
                '<h5 style="margin:0 0 5px;color:#1a1f36;font-size:11px;">Kavipushp Jewels \u2013 Terms &amp; Conditions</h5>' +
                '<ol style="margin:0;padding-left:16px;font-size:10px;color:#555;line-height:1.6;">' +
                '<li>Jewellery is rented only for the period mentioned; late return will be charged per day.</li>' +
                '<li>A refundable security deposit may be collected and returned after condition check.</li>' +
                '<li>Any damage, loss, or missing parts will be charged as per repair or replacement value mentioned before dispatch in the whatsapp group.</li>' +
                '<li>Full payment must be made before delivery; advance/booking amount is non-refundable.</li>' +
                '<li>Customer must provide valid ID proof and is responsible for the safety of the jewellery during the rental period.</li>' +
                '<li>All disputes are subject to local jurisdiction.</li>' +
                '<li>There shall be no discount on the rental prices of the set.</li>' +
                '<li>Rental price does not include customization charges if applicable.</li>' +
                '<li>Booking amount is neither refundable nor adjustable or transferable in any condition, no exceptions.</li>' +
                '<li>No excuses like jewellery will be provided by in-laws, in-laws insisting on wearing gold only, wedding postponed or cancelled are entertained.</li>' +
                '<li>Articles agreed on at the time of booking will only be provided; extra article will be chargeable.</li>' +
                '<li>Use official number 9977722271 for any query or requirements; no responsibility for any personal number of employee.</li>' +
                '<li>Rental clarification: First 3 days normal rent of Bridal Set, next 2 days Rs.500 extra (if informed at the time of booking).</li>' +
                '<li>If the bridal set is not returned within the informed timeline, per day rental charges will be applicable as extra charge.</li>' +
                '</ol>' +
                '<p style="margin:6px 0 0;font-size:11px;font-weight:bold;color:#333;">Accepted and Agreed By:</p>' +
                '<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:15px;">' +
                '<div><div style="border-top:1px solid #333;width:100%;margin-bottom:6px;"></div><p style="margin:0;font-size:12px;color:#555;">Customer Signature &amp; Date</p></div>' +
                '<div><div style="border-top:1px solid #333;width:100%;margin-bottom:6px;"></div><p style="margin:0;font-size:12px;color:#555;">Authorized Signatory</p></div>' +
                '</div>' +
                '</div>' +
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
            var securityDeposit = invoiceType === 'booking'
                ? (parseFloat(document.getElementById('inv-security-pickup').value) || 0)
                : (parseFloat(document.getElementById('security_amount').value) || 0);
            var amountReceived = parseFloat(document.getElementById('amount_received').value) || 0;
            var damagesAmount = 0;
            var damagesPaid = parseFloat(document.getElementById('damages_paid').value) || 0;
            var config = getInvoiceConfig(invoiceType, rent, bookingAmount, amountReceived, damagesAmount, kpPaymentStatus, securityDeposit, damagesPaid);
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
                set_name: opt.dataset.setName || opt.dataset.customizedBridalSet || '',
                set_code: opt.dataset.setCode || '',
                set_category: opt.dataset.category || '',
                function_date: opt.dataset.functionDate || '',
                pickup_date: opt.dataset.pickupDate || '',
                return_date: opt.dataset.returnDate || '',
                rent_amount: rent,
                booking_amount: bookingAmount,
                security_deposit: securityDeposit,
                amount_received: amountReceived,
                damages_paid: damagesPaid,
                grand_total: config.grandTotal,
                customization_notes: opt.dataset.customization || '',
                stylist_name: opt.dataset.stylist || '',
                security_hold_reason: (document.getElementById('security_hold_reason') ? document.getElementById('security_hold_reason').value : '') || ''
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
        // Saved Invoices from wp_kavipushp_invoices table
        global $wpdb;
        $inv_table = $wpdb->prefix . 'kavipushp_invoices';
        $saved_invoices = array();
        $saved_count = 0;
        if ($wpdb->get_var("SHOW TABLES LIKE '$inv_table'") === $inv_table) {
            $saved_invoices = $wpdb->get_results("SELECT * FROM $inv_table ORDER BY created_at DESC");
            $saved_count = count($saved_invoices);
        }
        ?>

        <form method="post" id="kp-invoices-bulk-form">
            <?php wp_nonce_field('kp_bulk_invoices'); ?>
            <input type="hidden" name="page" value="kavipushp-invoices">

        <div class="kp-card" style="margin-bottom: 24px;">
            <div class="kp-card-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
                <h2><i class="dashicons dashicons-media-text"></i> <?php printf(__('Saved Invoices (%d)', 'kavipushp-bridals'), $saved_count); ?></h2>
                <?php if (!empty($saved_invoices)): ?>
                <div style="display:flex;gap:8px;align-items:center;">
                    <button type="submit" name="bulk_action" value="delete_selected" class="button kp-delete-btn" onclick="return confirm('<?php esc_attr_e('Delete selected invoices?', 'kavipushp-bridals'); ?>')">
                        <i class="dashicons dashicons-trash"></i> <?php _e('Delete Selected', 'kavipushp-bridals'); ?>
                    </button>
                    <button type="submit" name="bulk_action" value="delete_all" class="button" style="background:#e74c3c;color:#fff;border-color:#c0392b;" onclick="return confirm('<?php esc_attr_e('Delete ALL invoices? This cannot be undone!', 'kavipushp-bridals'); ?>')">
                        <i class="dashicons dashicons-trash"></i> <?php _e('Delete All', 'kavipushp-bridals'); ?>
                    </button>
                </div>
                <?php endif; ?>
            </div>
            <div class="kp-card-body">
                <?php if (!empty($saved_invoices)): ?>
                <div style="overflow-x:auto;">
                <table class="wp-list-table widefat fixed striped" style="min-width:600px;">
                    <thead>
                        <tr>
                            <th style="width:30px;"><input type="checkbox" id="kp-select-all-invoices" title="<?php esc_attr_e('Select All', 'kavipushp-bridals'); ?>"></th>
                            <th><?php _e('Invoice #', 'kavipushp-bridals'); ?></th>
                            <th><?php _e('Type', 'kavipushp-bridals'); ?></th>
                            <th><?php _e('Customer', 'kavipushp-bridals'); ?></th>
                            <th><?php _e('Phone', 'kavipushp-bridals'); ?></th>
                            <th><?php _e('Bridal Set Rent', 'kavipushp-bridals'); ?></th>
                            <th><?php _e('Damages or Late Charges', 'kavipushp-bridals'); ?></th>
                            <th><?php _e('Security Refund', 'kavipushp-bridals'); ?></th>
                            <th><?php _e('Date', 'kavipushp-bridals'); ?></th>
                            <th><?php _e('Action', 'kavipushp-bridals'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($saved_invoices as $inv):
                            $type_labels = array('booking' => 'Booking', 'pickup' => 'Pickup', 'final' => 'Final');
                            $type_label = isset($type_labels[$inv->invoice_type]) ? $type_labels[$inv->invoice_type] : ucfirst($inv->invoice_type);
                            $type_colors = array('booking' => '#3498db', 'pickup' => '#e67e22', 'final' => '#27ae60');
                            $type_color = isset($type_colors[$inv->invoice_type]) ? $type_colors[$inv->invoice_type] : '#666';
                        ?>
                        <tr>
                            <td><input type="checkbox" name="invoice_ids[]" value="<?php echo intval($inv->id); ?>" class="kp-invoice-cb"></td>
                            <td><strong><?php echo esc_html($inv->invoice_number); ?></strong></td>
                            <td><span style="background:<?php echo $type_color; ?>; color:#fff; padding:2px 8px; border-radius:10px; font-size:11px;"><?php echo esc_html($type_label); ?></span></td>
                            <td><?php echo esc_html($inv->customer_name); ?></td>
                            <td><?php echo esc_html($inv->customer_phone); ?></td>
                            <td><strong>₹<?php echo number_format($inv->rent_amount); ?></strong></td>
                            <td><?php if ($inv->invoice_type === 'final'): ?><strong style="color:#e74c3c;">₹<?php echo number_format($inv->damages_paid); ?></strong><?php else: ?>-<?php endif; ?></td>
                            <td><?php if ($inv->invoice_type === 'final'):
                                $sec_refund = floatval($inv->security_deposit) - floatval($inv->damages_paid);
                                $refund_color = $sec_refund >= 0 ? '#27ae60' : '#e74c3c';
                            ?><strong style="color:<?php echo $refund_color; ?>;">₹<?php echo number_format($sec_refund); ?></strong><?php else: ?>-<?php endif; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($inv->created_at)); ?></td>
                            <td>
                                <a href="<?php echo admin_url('admin.php?page=kavipushp-invoices&action=view_saved&id=' . $inv->id); ?>" class="button button-small"><?php _e('View', 'kavipushp-bridals'); ?></a>
                                <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=kavipushp-invoices&action=delete_saved&id=' . $inv->id), 'delete_saved_invoice_' . $inv->id); ?>" class="button button-small kp-delete-btn" onclick="return confirm('<?php esc_attr_e('Delete this saved invoice?', 'kavipushp-bridals'); ?>')" style="color:#e74c3c;">
                                    <i class="dashicons dashicons-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
                <script>
                document.getElementById('kp-select-all-invoices') && document.getElementById('kp-select-all-invoices').addEventListener('change', function() {
                    document.querySelectorAll('.kp-invoice-cb').forEach(function(cb) { cb.checked = this.checked; }, this);
                });
                </script>
                <?php else: ?>
                <div style="text-align:center; padding:20px; color:#999;">
                    <i class="dashicons dashicons-media-text" style="font-size:36px; width:36px; height:36px;"></i>
                    <p><?php _e('No saved invoices yet. Generate and save invoices using the Generate Invoice button.', 'kavipushp-bridals'); ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        </form>

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
        return $wpdb->get_results("SELECT *, 0 as booking_count FROM $table ORDER BY id DESC");
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
        $saved_id = intval($data['customer_id']);
    } else {
        $wpdb->insert($table, $customer_data);
        $saved_id = $wpdb->insert_id;
    }

    // Sync return_date to all associated bookings so bookings page reflects the change
    if ($return_date && $saved_id) {
        // Match bookings by _customer_id (direct link), then fallback to email/phone
        $booking_ids = $wpdb->get_col($wpdb->prepare(
            "SELECT DISTINCT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_customer_id' AND meta_value = %s",
            $saved_id
        ));
        // Fallback: also match by email and phone for older bookings without _customer_id
        $customer_email = sanitize_email($data['email'] ?? '');
        $customer_phone = sanitize_text_field($data['contact_number'] ?? '');
        if ($customer_email) {
            $ids = $wpdb->get_col($wpdb->prepare(
                "SELECT DISTINCT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_customer_email' AND meta_value = %s",
                $customer_email
            ));
            $booking_ids = array_merge($booking_ids, $ids);
        }
        if ($customer_phone) {
            $ids = $wpdb->get_col($wpdb->prepare(
                "SELECT DISTINCT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_customer_phone' AND meta_value = %s",
                $customer_phone
            ));
            $booking_ids = array_merge($booking_ids, $ids);
        }
        $unique_booking_ids = array_unique($booking_ids);
        foreach ($unique_booking_ids as $booking_id) {
            update_post_meta($booking_id, '_return_date', $return_date);
        }
        // Also sync return_date to saved invoices for those bookings
        if (!empty($unique_booking_ids)) {
            $ids_placeholder = implode(',', array_map('intval', $unique_booking_ids));
            $wpdb->query($wpdb->prepare(
                "UPDATE {$wpdb->prefix}kavipushp_invoices SET return_date = %s WHERE booking_id IN ($ids_placeholder)",
                $return_date
            ));
        }
    }

    return $saved_id;
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
    $bv_category = get_post_meta($booking_id, '_bridal_set_category', true);
    if (!$bv_category && $set_id) {
        $bv_cat_terms = get_the_terms($set_id, 'bridal_category');
        $bv_category = ($bv_cat_terms && !is_wp_error($bv_cat_terms)) ? $bv_cat_terms[0]->name : '';
    }
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
        'set_category'     => $bv_category ?: '',
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

/**
 * All Products page — table view with image, matching CSV columns
 */
function kavipushp_render_all_products() {
    // Handle inline image upload (same AJAX as inventory page uses)
    $sets = get_posts(array(
        'post_type'      => 'bridal_set',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
    ));

    // Sort by numeric part of _set_id ascending
    usort($sets, function($a, $b) {
        $ia = intval(preg_replace('/[^0-9]/', '', get_post_meta($a->ID, '_set_id', true) ?: '999999'));
        $ib = intval(preg_replace('/[^0-9]/', '', get_post_meta($b->ID, '_set_id', true) ?: '999999'));
        return $ia - $ib;
    });

    $total = count($sets);
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

        <div class="kp-page-title" style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:12px;">
            <div>
                <h1><?php _e('All Products', 'kavipushp-bridals'); ?></h1>
                <p><?php printf(__('%d jewelry items in inventory', 'kavipushp-bridals'), $total); ?></p>
            </div>
            <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-top:4px;">
                <a href="<?php echo admin_url('admin.php?page=kavipushp-inventory'); ?>" class="button">
                    <i class="dashicons dashicons-grid-view"></i> <?php _e('Card View', 'kavipushp-bridals'); ?>
                </a>
                <button class="button" onclick="kpApPrint()">
                    <i class="dashicons dashicons-printer"></i> <?php _e('Print', 'kavipushp-bridals'); ?>
                </button>
                <button class="button" onclick="kpApExportCSV()">
                    <i class="dashicons dashicons-download"></i> <?php _e('Export CSV', 'kavipushp-bridals'); ?>
                </button>
            </div>
        </div>

        <div class="kp-card">
            <div class="kp-card-body" style="padding:0;">

                <!-- Search + filter bar -->
                <div style="padding:14px 18px;border-bottom:1px solid #ede8f5;display:flex;gap:10px;align-items:center;flex-wrap:wrap;background:#faf7ff;">
                    <input type="text" id="kp-ap-search" placeholder="Search by name, barcode, category..."
                        style="flex:1;min-width:220px;padding:8px 12px;border:1px solid #d0b8e8;border-radius:6px;font-size:13px;">
                    <select id="kp-ap-cat-filter" style="padding:8px 10px;border:1px solid #d0b8e8;border-radius:6px;font-size:13px;background:#fff;">
                        <option value=""><?php _e('All Categories', 'kavipushp-bridals'); ?></option>
                        <?php
                        $cats = get_terms(array('taxonomy' => 'bridal_category', 'hide_empty' => false));
                        if ($cats && !is_wp_error($cats)):
                            foreach ($cats as $cat): ?>
                        <option value="<?php echo esc_attr(strtolower($cat->name)); ?>"><?php echo esc_html($cat->name); ?></option>
                        <?php endforeach; endif; ?>
                    </select>
                    <span id="kp-ap-count" style="font-size:13px;color:#8e44ad;font-weight:600;"><?php echo $total; ?> items</span>
                </div>

                <!-- Products Table -->
                <div style="overflow-x:auto;">
                <table id="kp-ap-table" style="width:100%;border-collapse:collapse;font-size:13px;">
                    <thead>
                        <tr style="background:linear-gradient(135deg,#8e44ad,#6c3483);color:#fff;text-align:left;">
                            <th style="padding:12px 10px;width:80px;text-align:center;">Image</th>
                            <th style="padding:12px 10px;width:50px;">#</th>
                            <th style="padding:12px 10px;cursor:pointer;" onclick="kpApSort('code')">Item No <span class="kp-ap-sort-icon">&#8597;</span></th>
                            <th style="padding:12px 10px;cursor:pointer;" onclick="kpApSort('name')">Item Name <span class="kp-ap-sort-icon">&#8597;</span></th>
                            <th style="padding:12px 10px;cursor:pointer;" onclick="kpApSort('cat')">Category <span class="kp-ap-sort-icon">&#8597;</span></th>
                            <th style="padding:12px 10px;cursor:pointer;text-align:right;" onclick="kpApSort('price')">Rental Price <span class="kp-ap-sort-icon">&#8597;</span></th>
                            <th style="padding:12px 10px;text-align:center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="kp-ap-tbody">
                    <?php
                    $sno = 0;
                    foreach ($sets as $set):
                        $sno++;
                        $set_code     = get_post_meta($set->ID, '_set_id', true) ?: ('KP' . $set->ID);
                        $rental_price = (float) get_post_meta($set->ID, '_rental_price', true);
                        $cats         = get_the_terms($set->ID, 'bridal_category');
                        $cat_name     = ($cats && !is_wp_error($cats)) ? $cats[0]->name : 'Uncategorized';

                        // Image: options store first (survives deploys)
                        $img_url = get_option('kp_setimg_' . sanitize_key($set_code), '');
                        if (!$img_url) {
                            $thumb_id = get_post_thumbnail_id($set->ID);
                            if ($thumb_id) {
                                $img_url = wp_get_attachment_image_url($thumb_id, 'thumbnail');
                                if (!$img_url) $img_url = wp_get_attachment_url($thumb_id);
                            }
                        }

                        $row_bg = ($sno % 2 === 0) ? '#faf7ff' : '#fff';
                    ?>
                    <tr class="kp-ap-row"
                        data-name="<?php echo esc_attr(strtolower($set->post_title)); ?>"
                        data-code="<?php echo esc_attr(strtolower($set_code)); ?>"
                        data-cat="<?php echo esc_attr(strtolower($cat_name)); ?>"
                        data-price="<?php echo esc_attr($rental_price); ?>"
                        style="background:<?php echo $row_bg; ?>;border-bottom:1px solid #ede8f5;transition:background 0.15s;"
                        onmouseenter="this.style.background='#f3eeff'" onmouseleave="this.style.background='<?php echo $row_bg; ?>'">

                        <!-- Image cell — click to change photo -->
                        <td style="padding:8px 10px;text-align:center;vertical-align:middle;">
                            <div class="kp-ap-img-cell" data-post-id="<?php echo $set->ID; ?>" style="position:relative;display:inline-block;cursor:pointer;" onclick="kpApTriggerUpload(<?php echo $set->ID; ?>)" title="Click to change photo">
                                <?php if ($img_url): ?>
                                <img src="<?php echo esc_attr($img_url); ?>" alt=""
                                    style="width:60px;height:60px;object-fit:cover;border-radius:6px;border:2px solid #d0b8e8;display:block;">
                                <?php else: ?>
                                <div style="width:60px;height:60px;border-radius:6px;border:2px dashed #d0b8e8;display:flex;flex-direction:column;align-items:center;justify-content:center;background:#f8f3ff;color:#b8a0d0;font-size:10px;gap:2px;">
                                    <span class="dashicons dashicons-camera" style="font-size:18px;width:18px;height:18px;"></span>
                                    <span>Add</span>
                                </div>
                                <?php endif; ?>
                                <div style="position:absolute;inset:0;border-radius:6px;background:rgba(142,68,173,0.7);display:none;align-items:center;justify-content:center;" class="kp-ap-img-hover">
                                    <span class="dashicons dashicons-camera" style="color:#fff;font-size:20px;width:20px;height:20px;"></span>
                                </div>
                                <input type="file" id="kp-ap-img-<?php echo $set->ID; ?>" accept="image/*" style="display:none;" onchange="kpApUploadImage(<?php echo $set->ID; ?>, this)">
                            </div>
                        </td>

                        <td style="padding:8px 10px;color:#999;vertical-align:middle;"><?php echo $sno; ?></td>
                        <td style="padding:8px 10px;font-weight:600;color:#8e44ad;vertical-align:middle;"><?php echo esc_html($set_code); ?></td>
                        <td style="padding:8px 10px;font-weight:500;vertical-align:middle;"><?php echo esc_html($set->post_title); ?></td>
                        <td style="padding:8px 10px;vertical-align:middle;">
                            <span style="background:#f3eeff;color:#6c3483;border-radius:12px;padding:3px 10px;font-size:12px;"><?php echo esc_html($cat_name); ?></span>
                        </td>
                        <td style="padding:8px 10px;text-align:right;vertical-align:middle;">
                            <?php if ($rental_price > 0): ?>
                            <span style="font-weight:700;color:#27ae60;font-size:14px;">&#8377;<?php echo number_format($rental_price); ?></span>
                            <span style="font-size:11px;color:#999;">/day</span>
                            <?php else: ?>
                            <span style="color:#ccc;font-size:12px;">—</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding:8px 10px;text-align:center;vertical-align:middle;">
                            <a href="<?php echo get_edit_post_link($set->ID); ?>" class="button button-small" title="Edit" style="padding:3px 8px;">
                                <i class="dashicons dashicons-edit" style="font-size:13px;width:13px;height:13px;margin:0;vertical-align:middle;"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                </div><!-- /overflow-x -->

                <?php if (empty($sets)): ?>
                <div class="kp-empty-state">
                    <i class="dashicons dashicons-archive"></i>
                    <h3><?php _e('No products yet', 'kavipushp-bridals'); ?></h3>
                    <p><?php _e('Upload a CSV on the Inventory page to get started', 'kavipushp-bridals'); ?></p>
                </div>
                <?php endif; ?>

            </div><!-- /kp-card-body -->
        </div><!-- /kp-card -->
    </div><!-- /kavipushp-admin-wrap -->

    <style>
    #kp-ap-table thead th { white-space:nowrap; }
    .kp-ap-img-cell:hover .kp-ap-img-hover { display:flex !important; }
    @media print {
        .kp-admin-header, .kp-page-title .button, #kp-ap-search, #kp-ap-cat-filter,
        #kp-ap-count, .button, .kp-logout-btn { display:none !important; }
        #kp-ap-table { font-size:11px; }
        #kp-ap-table td, #kp-ap-table th { padding:5px 6px !important; }
        .kp-ap-img-cell img { width:40px !important; height:40px !important; }
    }
    </style>

    <script>
    var kpApNonce = '<?php echo wp_create_nonce("kp_set_image"); ?>';
    var kpApRows  = null; // cached NodeList

    // ── Search + Category filter ───────────────────────────────────────────────
    function kpApFilter() {
        var q   = document.getElementById('kp-ap-search').value.toLowerCase().trim();
        var cat = document.getElementById('kp-ap-cat-filter').value.toLowerCase().trim();
        var rows = document.querySelectorAll('#kp-ap-tbody .kp-ap-row');
        var visible = 0;
        rows.forEach(function(row) {
            var nameMatch = !q || row.dataset.name.includes(q) || row.dataset.code.includes(q) || row.dataset.cat.includes(q);
            var catMatch  = !cat || row.dataset.cat.includes(cat);
            var show = nameMatch && catMatch;
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });
        document.getElementById('kp-ap-count').textContent = visible + ' items';
    }
    document.getElementById('kp-ap-search').addEventListener('input', kpApFilter);
    document.getElementById('kp-ap-cat-filter').addEventListener('change', kpApFilter);

    // ── Column sort ───────────────────────────────────────────────────────────
    var kpApSortState = { col: null, asc: true };
    function kpApSort(col) {
        var asc = (kpApSortState.col === col) ? !kpApSortState.asc : true;
        kpApSortState = { col: col, asc: asc };
        var tbody = document.getElementById('kp-ap-tbody');
        var rows  = Array.from(tbody.querySelectorAll('.kp-ap-row'));
        rows.sort(function(a, b) {
            var va = a.dataset[col] || '';
            var vb = b.dataset[col] || '';
            if (col === 'price') { va = parseFloat(va)||0; vb = parseFloat(vb)||0; return asc ? va-vb : vb-va; }
            return asc ? va.localeCompare(vb) : vb.localeCompare(va);
        });
        rows.forEach(function(r) { tbody.appendChild(r); });
        // re-stripe
        var vis = 0;
        tbody.querySelectorAll('.kp-ap-row').forEach(function(r) {
            if (r.style.display !== 'none') { vis++; r.style.background = vis%2===0?'#faf7ff':'#fff'; }
        });
    }

    // ── Image upload (per row) ─────────────────────────────────────────────────
    function kpApTriggerUpload(postId) {
        document.getElementById('kp-ap-img-' + postId).click();
    }

    function kpApUploadImage(postId, input) {
        if (!input.files || !input.files[0]) return;
        var cell = input.closest('.kp-ap-img-cell');
        cell.style.opacity = '0.5';
        var fd = new FormData();
        fd.append('action', 'kavipushp_upload_set_image');
        fd.append('_wpnonce', kpApNonce);
        fd.append('post_id', postId);
        fd.append('image', input.files[0]);
        var xhr = new XMLHttpRequest();
        xhr.open('POST', ajaxurl);
        xhr.onload = function() {
            cell.style.opacity = '1';
            input.value = '';
            try {
                var res = JSON.parse(xhr.responseText);
                if (res.success) {
                    var existing = cell.querySelector('img');
                    var placeholder = cell.querySelector('div[style*="dashed"]');
                    if (placeholder) placeholder.remove();
                    if (existing) {
                        existing.src = res.data.url;
                    } else {
                        var img = document.createElement('img');
                        img.src = res.data.url;
                        img.alt = '';
                        img.style.cssText = 'width:60px;height:60px;object-fit:cover;border-radius:6px;border:2px solid #d0b8e8;display:block;';
                        cell.insertBefore(img, cell.querySelector('.kp-ap-img-hover'));
                    }
                }
            } catch(e) {}
        };
        xhr.onerror = function() { cell.style.opacity = '1'; };
        xhr.send(fd);
    }

    // ── Print ──────────────────────────────────────────────────────────────────
    function kpApPrint() { window.print(); }

    // ── Export CSV ─────────────────────────────────────────────────────────────
    function kpApExportCSV() {
        var rows = document.querySelectorAll('#kp-ap-tbody .kp-ap-row');
        var csv  = 'S.No,Item No,Item Name,Category,Rental Price\n';
        var sno  = 0;
        rows.forEach(function(row) {
            if (row.style.display === 'none') return;
            sno++;
            var cols = row.querySelectorAll('td');
            var code  = (row.dataset.code || '').replace(/,/g, '');
            var name  = ((cols[3] && cols[3].textContent) || '').trim().replace(/,/g, '');
            var cat   = (row.dataset.cat || '').replace(/,/g, '');
            var price = row.dataset.price || '0';
            csv += sno + ',"' + code + '","' + name + '","' + cat + '",' + price + '\n';
        });
        var blob = new Blob([csv], { type: 'text/csv' });
        var a    = document.createElement('a');
        a.href   = URL.createObjectURL(blob);
        a.download = 'kavipushp-products.csv';
        a.click();
    }
    </script>
    <?php
}

/**
 * Booked Sets & Dates page
 */
function kavipushp_render_booked_sets() {
    // Fetch all bookings
    $bookings = get_posts(array(
        'post_type'      => 'booking',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'meta_value',
        'meta_key'       => '_function_date',
        'order'          => 'ASC',
    ));

    // Build rows: one row per booking
    $rows = array();
    foreach ($bookings as $booking) {
        $set_id       = get_post_meta($booking->ID, '_bridal_set_id', true);
        $set          = $set_id ? get_post($set_id) : null;
        $set_code     = $set_id ? get_post_meta($set_id, '_set_id', true) : '';
        $rental_price = $set_id ? (float) get_post_meta($set_id, '_rental_price', true) : 0;
        $pickup_date  = get_post_meta($booking->ID, '_pickup_date', true);
        $function_date= get_post_meta($booking->ID, '_function_date', true);
        $return_date  = get_post_meta($booking->ID, '_return_date', true);

        // Available From = return_date + 1 day
        $available_from = '';
        if ($return_date) {
            try {
                $d = new DateTime($return_date);
                $d->modify('+1 day');
                $available_from = $d->format('Y-m-d');
            } catch (Exception $e) {}
        }

        // Category
        $cat_terms = $set_id ? get_the_terms($set_id, 'bridal_category') : false;
        $category  = ($cat_terms && !is_wp_error($cat_terms)) ? $cat_terms[0]->name : '—';

        // Image
        $img_url = $set_code ? get_option('kp_setimg_' . sanitize_key($set_code), '') : '';
        if (!$img_url && $set_id) {
            $tid = get_post_thumbnail_id($set_id);
            if ($tid) $img_url = wp_get_attachment_image_url($tid, 'thumbnail') ?: wp_get_attachment_url($tid);
        }

        $rows[] = array(
            'booking_id'     => $booking->ID,
            'set_name'       => $set ? $set->post_title : '—',
            'set_code'       => $set_code ?: ('KP' . $set_id),
            'category'       => $category,
            'price'          => $rental_price,
            'img_url'        => $img_url,
            'pickup_date'    => $pickup_date,
            'function_date'  => $function_date,
            'return_date'    => $return_date,
            'available_from' => $available_from,
        );
    }

    $total = count($rows);
    ?>
    <div class="kavipushp-admin-wrap">
        <div class="kp-admin-header">
            <div class="kp-user-info">
                <i class="dashicons dashicons-admin-users"></i>
                <span><?php echo esc_html(wp_get_current_user()->user_email); ?></span>
            </div>
            <a href="<?php echo wp_logout_url(admin_url()); ?>" class="kp-logout-btn">
                <i class="dashicons dashicons-exit"></i> Logout
            </a>
        </div>

        <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:18px;">
            <div>
                <h1 style="font-size:22px;margin:0 0 4px;">Booked Sets &amp; Dates</h1>
                <p style="margin:0;color:#888;font-size:13px;"><?php echo $total; ?> active bookings</p>
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <button class="button" onclick="kpBsdPrint()"><i class="dashicons dashicons-printer"></i> Print</button>
                <button class="button" onclick="kpBsdExport()"><i class="dashicons dashicons-download"></i> Export CSV</button>
            </div>
        </div>

        <!-- Filter bar -->
        <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin-bottom:14px;background:#faf7ff;padding:12px 16px;border-radius:8px;border:1px solid #e0d0f5;">
            <input type="text" id="kp-bsd-search" placeholder="Search by Item No, name, category..."
                style="flex:1;min-width:200px;padding:8px 12px;border:1px solid #d0b8e8;border-radius:6px;font-size:13px;">
            <select id="kp-bsd-cat" style="padding:8px 10px;border:1px solid #d0b8e8;border-radius:6px;font-size:13px;background:#fff;">
                <option value="">All Categories</option>
                <?php
                $cats = get_terms(array('taxonomy' => 'bridal_category', 'hide_empty' => false));
                if ($cats && !is_wp_error($cats)):
                    foreach ($cats as $c): ?>
                <option value="<?php echo esc_attr(strtolower($c->name)); ?>"><?php echo esc_html($c->name); ?></option>
                <?php endforeach; endif; ?>
            </select>
            <input type="date" id="kp-bsd-date-from" title="Function date from" style="padding:7px 10px;border:1px solid #d0b8e8;border-radius:6px;font-size:13px;">
            <input type="date" id="kp-bsd-date-to" title="Function date to" style="padding:7px 10px;border:1px solid #d0b8e8;border-radius:6px;font-size:13px;">
            <span id="kp-bsd-count" style="font-size:13px;font-weight:600;color:#8e44ad;"><?php echo $total; ?> items</span>
        </div>

        <!-- Table -->
        <div style="overflow-x:auto;border-radius:10px;border:1px solid #e0d0f5;box-shadow:0 2px 8px rgba(142,68,173,0.07);">
        <table id="kp-bsd-table" style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
                <tr style="background:linear-gradient(135deg,#8e44ad,#6c3483);color:#fff;text-align:left;">
                    <th style="padding:12px 10px;width:42px;">#</th>
                    <th style="padding:12px 10px;width:70px;text-align:center;">Image</th>
                    <th style="padding:12px 10px;white-space:nowrap;cursor:pointer;" onclick="kpBsdSort('code')">Item No &#8597;</th>
                    <th style="padding:12px 10px;cursor:pointer;" onclick="kpBsdSort('cat')">Category &#8597;</th>
                    <th style="padding:12px 10px;text-align:right;white-space:nowrap;cursor:pointer;" onclick="kpBsdSort('price')">Price &#8597;</th>
                    <th style="padding:12px 10px;white-space:nowrap;cursor:pointer;" onclick="kpBsdSort('pickup')">Pickup Date &#8597;</th>
                    <th style="padding:12px 10px;white-space:nowrap;cursor:pointer;" onclick="kpBsdSort('func')">Function Date &#8597;</th>
                    <th style="padding:12px 10px;white-space:nowrap;cursor:pointer;" onclick="kpBsdSort('ret')">Return Date &#8597;</th>
                    <th style="padding:12px 10px;white-space:nowrap;cursor:pointer;" onclick="kpBsdSort('avail')">Available From &#8597;</th>
                </tr>
            </thead>
            <tbody id="kp-bsd-tbody">
            <?php
            $sno = 0;
            foreach ($rows as $row):
                $sno++;
                $bg = ($sno % 2 === 0) ? '#faf7ff' : '#fff';

                // Format dates for display
                $fmt = function($d) { return $d ? date('d/m/Y', strtotime($d)) : '—'; };

                // Highlight: if available_from is today or past → green, future → normal
                $avail_class = '';
                if ($row['available_from']) {
                    $avail_class = (strtotime($row['available_from']) <= strtotime('today')) ? 'color:#27ae60;font-weight:700;' : 'color:#e67e22;font-weight:600;';
                }
            ?>
            <tr class="kp-bsd-row"
                data-code="<?php echo esc_attr(strtolower($row['set_code'])); ?>"
                data-name="<?php echo esc_attr(strtolower($row['set_name'])); ?>"
                data-cat="<?php echo esc_attr(strtolower($row['category'])); ?>"
                data-price="<?php echo esc_attr($row['price']); ?>"
                data-pickup="<?php echo esc_attr($row['pickup_date']); ?>"
                data-func="<?php echo esc_attr($row['function_date']); ?>"
                data-ret="<?php echo esc_attr($row['return_date']); ?>"
                data-avail="<?php echo esc_attr($row['available_from']); ?>"
                style="background:<?php echo $bg; ?>;border-bottom:1px solid #ede8f5;"
                onmouseenter="this.style.background='#f3eeff'" onmouseleave="this.style.background='<?php echo $bg; ?>'">

                <td style="padding:8px 10px;color:#aaa;text-align:center;vertical-align:middle;"><?php echo $sno; ?></td>

                <td style="padding:6px 10px;text-align:center;vertical-align:middle;">
                    <?php if ($row['img_url']): ?>
                    <img src="<?php echo esc_attr($row['img_url']); ?>" alt=""
                        style="width:56px;height:56px;object-fit:cover;border-radius:6px;border:2px solid #d0b8e8;">
                    <?php else: ?>
                    <div style="width:56px;height:56px;border-radius:6px;border:2px dashed #d0b8e8;display:inline-flex;align-items:center;justify-content:center;background:#f8f3ff;">
                        <span class="dashicons dashicons-format-image" style="color:#d0b8e8;font-size:20px;width:20px;height:20px;"></span>
                    </div>
                    <?php endif; ?>
                </td>

                <td style="padding:8px 10px;vertical-align:middle;">
                    <strong style="color:#8e44ad;"><?php echo esc_html($row['set_code']); ?></strong><br>
                    <span style="font-size:11px;color:#999;"><?php echo esc_html($row['set_name']); ?></span>
                </td>

                <td style="padding:8px 10px;vertical-align:middle;">
                    <span style="background:#f3eeff;color:#6c3483;border-radius:12px;padding:3px 10px;font-size:12px;white-space:nowrap;"><?php echo esc_html($row['category']); ?></span>
                </td>

                <td style="padding:8px 10px;text-align:right;vertical-align:middle;white-space:nowrap;">
                    <?php if ($row['price'] > 0): ?>
                    <strong style="color:#27ae60;">&#8377;<?php echo number_format($row['price']); ?></strong>
                    <span style="font-size:11px;color:#aaa;">/day</span>
                    <?php else: ?>
                    <span style="color:#ddd;">—</span>
                    <?php endif; ?>
                </td>

                <td style="padding:8px 10px;vertical-align:middle;white-space:nowrap;">
                    <span style="background:#fff3cd;color:#856404;border-radius:5px;padding:3px 8px;font-size:12px;"><?php echo $fmt($row['pickup_date']); ?></span>
                </td>

                <td style="padding:8px 10px;vertical-align:middle;white-space:nowrap;">
                    <span style="background:#e8d5f5;color:#6c3483;border-radius:5px;padding:3px 8px;font-size:12px;font-weight:600;"><?php echo $fmt($row['function_date']); ?></span>
                </td>

                <td style="padding:8px 10px;vertical-align:middle;white-space:nowrap;">
                    <span style="background:#fde8e8;color:#c0392b;border-radius:5px;padding:3px 8px;font-size:12px;"><?php echo $fmt($row['return_date']); ?></span>
                </td>

                <td style="padding:8px 10px;vertical-align:middle;white-space:nowrap;">
                    <span style="<?php echo $avail_class; ?>font-size:13px;"><?php echo $fmt($row['available_from']); ?></span>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>

        <?php if (empty($rows)): ?>
        <div class="kp-empty-state" style="text-align:center;padding:40px;">
            <i class="dashicons dashicons-calendar-alt" style="font-size:48px;color:#d0b8e8;"></i>
            <h3>No bookings yet</h3>
            <p>Create bookings to see sets and their dates here.</p>
        </div>
        <?php endif; ?>
    </div>

    <style>
    @media print {
        .kp-admin-header, .kp-logout-btn, button, input, select, #kp-bsd-search, #kp-bsd-cat { display:none !important; }
        #kp-bsd-table { font-size:11px; }
        #kp-bsd-table td, #kp-bsd-table th { padding:5px 6px !important; }
    }
    </style>

    <script>
    // ── Filter ────────────────────────────────────────────────────────────────
    function kpBsdFilter() {
        var q    = (document.getElementById('kp-bsd-search').value || '').toLowerCase().trim();
        var cat  = (document.getElementById('kp-bsd-cat').value   || '').toLowerCase().trim();
        var dFrom = document.getElementById('kp-bsd-date-from').value;
        var dTo   = document.getElementById('kp-bsd-date-to').value;
        var rows  = document.querySelectorAll('#kp-bsd-tbody .kp-bsd-row');
        var vis = 0;
        rows.forEach(function(r) {
            var textMatch = !q || r.dataset.code.includes(q) || r.dataset.name.includes(q) || r.dataset.cat.includes(q);
            var catMatch  = !cat || r.dataset.cat.includes(cat);
            var fd = r.dataset.func || '';
            var dateMatch = (!dFrom || fd >= dFrom) && (!dTo || fd <= dTo);
            var show = textMatch && catMatch && dateMatch;
            r.style.display = show ? '' : 'none';
            if (show) vis++;
        });
        document.getElementById('kp-bsd-count').textContent = vis + ' items';
    }
    document.addEventListener('DOMContentLoaded', function() {
        ['kp-bsd-search','kp-bsd-cat','kp-bsd-date-from','kp-bsd-date-to'].forEach(function(id) {
            var el = document.getElementById(id);
            if (el) el.addEventListener(el.tagName==='SELECT'?'change':'input', kpBsdFilter);
        });
    });

    // ── Sort ──────────────────────────────────────────────────────────────────
    var kpBsdSortState = { col: null, asc: true };
    function kpBsdSort(col) {
        var asc = (kpBsdSortState.col === col) ? !kpBsdSortState.asc : true;
        kpBsdSortState = { col: col, asc: asc };
        var tbody = document.getElementById('kp-bsd-tbody');
        var rows  = Array.from(tbody.querySelectorAll('.kp-bsd-row'));
        rows.sort(function(a, b) {
            var va = a.dataset[col] || '', vb = b.dataset[col] || '';
            if (col === 'price') return asc ? (parseFloat(va)||0)-(parseFloat(vb)||0) : (parseFloat(vb)||0)-(parseFloat(va)||0);
            return asc ? va.localeCompare(vb) : vb.localeCompare(va);
        });
        rows.forEach(function(r) { tbody.appendChild(r); });
        var v = 0;
        tbody.querySelectorAll('.kp-bsd-row').forEach(function(r) {
            if (r.style.display !== 'none') { v++; r.style.background = v%2===0?'#faf7ff':'#fff'; }
        });
    }

    // ── Print ─────────────────────────────────────────────────────────────────
    function kpBsdPrint() { window.print(); }

    // ── Export CSV ────────────────────────────────────────────────────────────
    function kpBsdExport() {
        var rows = document.querySelectorAll('#kp-bsd-tbody .kp-bsd-row');
        var csv  = 'S.No,Item No,Category,Price,Pickup Date,Function Date,Return Date,Available From\n';
        var n = 0;
        rows.forEach(function(r) {
            if (r.style.display === 'none') return;
            n++;
            var tds = r.querySelectorAll('td');
            csv += n + ',"' + (r.dataset.code||'') + '","' + (r.dataset.cat||'') + '",'
                + (r.dataset.price||0) + ',"' + (r.dataset.pickup||'') + '","'
                + (r.dataset.func||'') + '","' + (r.dataset.ret||'') + '","'
                + (r.dataset.avail||'') + '"\n';
        });
        var a = document.createElement('a');
        a.href = URL.createObjectURL(new Blob([csv], {type:'text/csv'}));
        a.download = 'booked-sets.csv';
        a.click();
    }
    </script>
    <?php
}

// ═══════════════════════════════════════════════════════════════════════════════
// CATEGORY INVENTORY PAGES (Sheeshpatti, Maang Teeka, etc.)
// ═══════════════════════════════════════════════════════════════════════════════

function kavipushp_render_category_inventory($page_title, $term_name) {
    // Find the taxonomy term — try exact name first, then slug
    $term = get_term_by('name', $term_name, 'bridal_category');
    if (!$term) {
        $term = get_term_by('slug', sanitize_title($term_name), 'bridal_category');
    }

    if ($term && !is_wp_error($term)) {
        $sets = get_posts(array(
            'post_type'      => 'bridal_set',
            'posts_per_page' => -1,
            'tax_query'      => array(array(
                'taxonomy' => 'bridal_category',
                'field'    => 'term_id',
                'terms'    => $term->term_id,
            )),
        ));
    } else {
        $sets = array();
    }

    // Sort numerically by set ID (B0001, B0002 …)
    usort($sets, function($a, $b) {
        $id_a = get_post_meta($a->ID, '_set_id', true);
        $id_b = get_post_meta($b->ID, '_set_id', true);
        $num_a = intval(preg_replace('/[^0-9]/', '', $id_a ?: '999999'));
        $num_b = intval(preg_replace('/[^0-9]/', '', $id_b ?: '999999'));
        return $num_a - $num_b;
    });

    $total = count($sets);
    $nonce = wp_create_nonce('kp_set_image');
    ?>
    <div class="kp-admin-wrap" style="font-family:'Segoe UI',sans-serif;padding:24px 28px;max-width:1400px;">

        <!-- Header -->
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:10px;">
            <div>
                <h1 style="margin:0;font-size:26px;color:#4a1d7a;font-weight:700;letter-spacing:.5px;">
                    <span class="dashicons dashicons-star-filled" style="font-size:24px;vertical-align:middle;margin-right:6px;color:#8e44ad;"></span>
                    <?php echo esc_html($page_title); ?>
                </h1>
                <p style="margin:4px 0 0;color:#888;font-size:13px;">
                    <?php if ($term): ?>
                        <?php echo $total; ?> item<?php echo $total !== 1 ? 's' : ''; ?> in this category
                    <?php else: ?>
                        Category <strong><?php echo esc_html($term_name); ?></strong> not found in database yet.
                        <a href="<?php echo admin_url('edit-tags.php?taxonomy=bridal_category&post_type=bridal_set'); ?>" style="color:#8e44ad;">Add it here</a>
                    <?php endif; ?>
                </p>
            </div>
            <a href="<?php echo admin_url('edit-tags.php?taxonomy=bridal_category&post_type=bridal_set'); ?>"
               style="background:#8e44ad;color:#fff;padding:8px 16px;border-radius:7px;text-decoration:none;font-size:13px;font-weight:600;">
                Manage Categories
            </a>
        </div>

        <?php if (empty($sets)): ?>
        <div style="background:#faf7ff;border:2px dashed #c39bd3;border-radius:12px;padding:40px;text-align:center;color:#7d3c98;">
            <span class="dashicons dashicons-info" style="font-size:36px;display:block;margin-bottom:10px;"></span>
            <p style="font-size:16px;margin:0;">No items found in <strong><?php echo esc_html($page_title); ?></strong> category.</p>
            <p style="font-size:13px;color:#aaa;margin-top:6px;">
                Upload a CSV from <a href="<?php echo admin_url('admin.php?page=kavipushp-inventory'); ?>" style="color:#8e44ad;">Jewelry Inventory</a>
                and assign items to this category.
            </p>
        </div>
        <?php else: ?>

        <!-- Search -->
        <div style="margin-bottom:16px;">
            <input type="text" id="kp-cat-search" placeholder="Search by name or Item ID…"
                   onkeyup="kpCatFilter()"
                   style="width:100%;max-width:380px;padding:9px 14px;border:2px solid #d0b8e8;border-radius:8px;font-size:14px;outline:none;">
        </div>

        <!-- Card grid -->
        <div class="kp-inventory-grid-new" id="kp-cat-grid">
            <?php foreach ($sets as $set):
                $set_code     = get_post_meta($set->ID, '_set_id', true);
                $rental_price = get_post_meta($set->ID, '_rental_price', true);
                $thumb_url    = $set_code ? get_option('kp_setimg_' . sanitize_key($set_code), '') : '';
                if (!$thumb_url) {
                    $thumb_id = get_post_thumbnail_id($set->ID);
                    if ($thumb_id) {
                        $thumb_url = wp_get_attachment_image_url($thumb_id, 'medium');
                        if (!$thumb_url) $thumb_url = wp_get_attachment_url($thumb_id);
                    }
                }
            ?>
            <div class="kp-inv-card"
                 data-set-code="<?php echo esc_attr($set_code ?: 'KP'.$set->ID); ?>"
                 data-post-id="<?php echo $set->ID; ?>"
                 data-title="<?php echo esc_attr(strtolower($set->post_title)); ?>"
                 data-code="<?php echo esc_attr(strtolower($set_code ?: '')); ?>">

                <!-- Image zone -->
                <div class="kp-inv-img-zone" onclick="kpCatTriggerUpload(<?php echo $set->ID; ?>)">
                    <?php if ($thumb_url): ?>
                    <img src="<?php echo esc_attr($thumb_url); ?>" alt="">
                    <?php else: ?>
                    <div class="kp-inv-img-placeholder">
                        <span class="dashicons dashicons-camera"></span>
                        <span>Add Photo</span>
                    </div>
                    <?php endif; ?>
                    <div class="kp-inv-img-overlay">
                        <button class="kp-inv-img-btn" type="button"
                                onclick="event.stopPropagation();kpCatTriggerUpload(<?php echo $set->ID; ?>)">
                            <span class="dashicons dashicons-upload"></span> Change
                        </button>
                        <?php if ($thumb_url): ?>
                        <button class="kp-inv-img-btn remove" type="button"
                                onclick="event.stopPropagation();kpCatRemoveImage(<?php echo $set->ID; ?>,this)">
                            <span class="dashicons dashicons-trash"></span> Remove
                        </button>
                        <?php endif; ?>
                    </div>
                    <input type="file" id="kp-cat-img-<?php echo $set->ID; ?>" accept="image/*"
                           style="display:none;"
                           onchange="kpCatUploadImage(<?php echo $set->ID; ?>,this)">
                </div>

                <!-- Card top -->
                <div class="kp-inv-card-top">
                    <span class="kp-inv-title-code"><?php echo esc_html($set->post_title); ?></span>
                </div>

                <!-- Card body -->
                <div class="kp-inv-card-body">
                    <span class="kp-inv-category"><?php echo esc_html(strtoupper($page_title)); ?></span>
                    <h4 class="kp-inv-name"><?php echo esc_html($set->post_title); ?></h4>
                    <p class="kp-inv-id">ID: <?php echo esc_html($set_code ?: 'KP'.$set->ID); ?></p>
                </div>

                <!-- Card footer — clickable price -->
                <div class="kp-inv-card-footer">
                    <span class="kp-price-cell" data-post-id="<?php echo $set->ID; ?>" data-price="<?php echo floatval($rental_price); ?>"
                          style="cursor:pointer;" title="Click to edit price">
                        <span class="kp-cat-price-display">
                            <?php if (floatval($rental_price) > 0): ?>
                            <strong style="color:#27ae60;font-size:14px;">&#8377;<?php echo number_format($rental_price); ?></strong><span style="font-size:11px;color:#aaa;">/day</span>
                            <?php else: ?>
                            <span style="color:#e74c3c;font-size:12px;border-bottom:1px dashed #e74c3c;" onclick="kpCatEditPrice(this)">Set Price</span>
                            <?php endif; ?>
                        </span>
                    </span>
                    <a href="<?php echo get_edit_post_link($set->ID); ?>"
                       class="kp-inv-view-btn">VIEW DETAILS</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php endif; ?>
    </div>

    <script>
    var kpCatNonce = '<?php echo $nonce; ?>';

    function kpCatFilter() {
        var q = document.getElementById('kp-cat-search').value.toLowerCase();
        document.querySelectorAll('#kp-cat-grid .kp-inv-card').forEach(function(c) {
            var match = c.dataset.title.indexOf(q) > -1 || c.dataset.code.indexOf(q) > -1;
            c.style.display = match ? '' : 'none';
        });
    }

    function kpCatTriggerUpload(postId) {
        document.getElementById('kp-cat-img-' + postId).click();
    }

    function kpCatUploadImage(postId, input) {
        if (!input.files || !input.files[0]) return;
        var zone = input.closest('.kp-inv-img-zone');
        zone.classList.add('kp-inv-img-uploading');
        var fd = new FormData();
        fd.append('action',    'kavipushp_upload_set_image');
        fd.append('_wpnonce',  kpCatNonce);
        fd.append('post_id',   postId);
        fd.append('image',     input.files[0]);
        var xhr = new XMLHttpRequest();
        xhr.open('POST', ajaxurl);
        xhr.onload = function() {
            zone.classList.remove('kp-inv-img-uploading');
            input.value = '';
            var res;
            try { res = JSON.parse(xhr.responseText); } catch(e) {
                alert('Upload error: unexpected server response.'); return;
            }
            if (res.success) {
                var existing = zone.querySelector('img');
                var ph = zone.querySelector('.kp-inv-img-placeholder');
                if (ph) ph.remove();
                if (existing) { existing.src = res.data.url; }
                else {
                    var img = document.createElement('img');
                    img.src = res.data.url; img.alt = '';
                    zone.insertBefore(img, zone.querySelector('.kp-inv-img-overlay'));
                }
                var overlay = zone.querySelector('.kp-inv-img-overlay');
                if (overlay && !overlay.querySelector('.remove')) {
                    var btn = document.createElement('button');
                    btn.className = 'kp-inv-img-btn remove'; btn.type = 'button';
                    btn.innerHTML = '<span class="dashicons dashicons-trash"></span> Remove';
                    (function(pid, b) {
                        b.onclick = function(e) { e.stopPropagation(); kpCatRemoveImage(pid, b); };
                    })(postId, btn);
                    overlay.appendChild(btn);
                }
            } else {
                alert('Upload failed: ' + (res.data || 'Unknown error'));
            }
        };
        xhr.onerror = function() { zone.classList.remove('kp-inv-img-uploading'); alert('Upload failed.'); };
        xhr.send(fd);
    }

    function kpCatRemoveImage(postId, btn) {
        if (!confirm('Remove image from this set?')) return;
        var zone = btn.closest('.kp-inv-img-zone');
        var fd = new FormData();
        fd.append('action',   'kavipushp_remove_set_image');
        fd.append('_wpnonce', kpCatNonce);
        fd.append('post_id',  postId);
        var xhr = new XMLHttpRequest();
        xhr.open('POST', ajaxurl);
        xhr.onload = function() {
            var res;
            try { res = JSON.parse(xhr.responseText); } catch(e) { return; }
            if (res.success) {
                var img = zone.querySelector('img');
                if (img) img.remove();
                if (!zone.querySelector('.kp-inv-img-placeholder')) {
                    var ph = document.createElement('div');
                    ph.className = 'kp-inv-img-placeholder';
                    ph.innerHTML = '<span class="dashicons dashicons-camera"></span><span>Add Photo</span>';
                    zone.insertBefore(ph, zone.querySelector('.kp-inv-img-overlay'));
                }
                btn.remove();
            }
        };
        xhr.send(fd);
    }

    function kpCatEditPrice(displayEl) {
        var cell   = displayEl.closest('.kp-price-cell');
        if (!cell) cell = displayEl.parentElement.closest('.kp-price-cell');
        var postId = cell.dataset.postId;
        var cur    = parseFloat(cell.dataset.price) || 0;
        var span   = cell.querySelector('.kp-cat-price-display');
        span.style.display = 'none';
        var inp = document.createElement('input');
        inp.type = 'number'; inp.value = cur > 0 ? cur : ''; inp.min = '0';
        inp.placeholder = 'Enter price';
        inp.style.cssText = 'width:90px;padding:4px 6px;border:2px solid #8e44ad;border-radius:5px;font-size:13px;text-align:right;outline:none;';
        cell.appendChild(inp);
        inp.focus(); inp.select();
        function save() {
            var val = parseFloat(inp.value) || 0;
            inp.remove(); span.style.display = '';
            cell.dataset.price = val;
            if (val > 0) {
                span.innerHTML = '<strong style="color:#27ae60;font-size:14px;">&#8377;' + val.toLocaleString('en-IN') + '</strong><span style="font-size:11px;color:#aaa;">/day</span>';
            } else {
                span.innerHTML = '<span style="color:#e74c3c;font-size:12px;border-bottom:1px dashed #e74c3c;" onclick="kpCatEditPrice(this)">Set Price</span>';
            }
            var fd = new FormData();
            fd.append('action',   'kavipushp_save_set_price');
            fd.append('_wpnonce', kpCatNonce);
            fd.append('post_id',  postId);
            fd.append('price',    val);
            var xhr = new XMLHttpRequest();
            xhr.open('POST', ajaxurl);
            xhr.send(fd);
        }
        inp.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') save();
            if (e.key === 'Escape') { inp.remove(); span.style.display = ''; }
        });
        inp.addEventListener('blur', save);
    }

    // Make price cells clickable
    document.querySelectorAll('#kp-cat-grid .kp-price-cell').forEach(function(cell) {
        cell.addEventListener('click', function() {
            var disp = cell.querySelector('.kp-cat-price-display');
            if (disp && !cell.querySelector('input')) kpCatEditPrice(disp);
        });
    });
    </script>
    <?php
}

// ── 8 category wrapper functions ───────────────────────────────────────────────
function kavipushp_render_sheeshpatti()  { kavipushp_render_category_inventory('Sheeshpatti',  'Sheeshpatti'); }
function kavipushp_render_maang_teeka()  { kavipushp_render_category_inventory('Maang Teeka',  'Maang Teeka'); }
function kavipushp_render_maatha_patti() { kavipushp_render_category_inventory('Maatha Patti', 'Maatha Patti'); }
function kavipushp_render_groom_haar()   { kavipushp_render_category_inventory('Groom Haar',   'Groom Haar'); }
function kavipushp_render_kalangi()      { kavipushp_render_category_inventory('Kalangi',       'Kalangi'); }
function kavipushp_render_borla()        { kavipushp_render_category_inventory('Borla',         'Borla'); }
function kavipushp_render_nath()         { kavipushp_render_category_inventory('Nath',          'Nath'); }
function kavipushp_render_haathphool()   { kavipushp_render_category_inventory('Haathphool',    'Haathphool'); }
