<?php
/**
 * Template Name: My Account
 *
 * @package Kavipushp_Bridals
 */

// Redirect if not logged in
if (!is_user_logged_in()) {
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}

get_header();

$current_user = wp_get_current_user();
$user_bookings = kavipushp_get_user_bookings($current_user->user_email);
$active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'dashboard';
?>

<section class="account-section">
    <div class="container">
        <div class="account-layout">
            <!-- Sidebar -->
            <aside class="account-sidebar">
                <div class="account-user">
                    <?php echo get_avatar($current_user->ID, 100); ?>
                    <h3><?php echo esc_html($current_user->display_name); ?></h3>
                    <span style="color: #999;"><?php echo esc_html($current_user->user_email); ?></span>
                </div>

                <nav class="account-menu">
                    <a href="?tab=dashboard" class="<?php echo $active_tab === 'dashboard' ? 'active' : ''; ?>">
                        <i class="fas fa-home"></i> <?php _e('Dashboard', 'kavipushp-bridals'); ?>
                    </a>
                    <a href="?tab=bookings" class="<?php echo $active_tab === 'bookings' ? 'active' : ''; ?>">
                        <i class="fas fa-calendar-alt"></i> <?php _e('My Bookings', 'kavipushp-bridals'); ?>
                    </a>
                    <a href="?tab=wishlist" class="<?php echo $active_tab === 'wishlist' ? 'active' : ''; ?>">
                        <i class="fas fa-heart"></i> <?php _e('Wishlist', 'kavipushp-bridals'); ?>
                    </a>
                    <a href="?tab=profile" class="<?php echo $active_tab === 'profile' ? 'active' : ''; ?>">
                        <i class="fas fa-user"></i> <?php _e('Edit Profile', 'kavipushp-bridals'); ?>
                    </a>
                    <a href="<?php echo esc_url(wp_logout_url(home_url('/'))); ?>">
                        <i class="fas fa-sign-out-alt"></i> <?php _e('Logout', 'kavipushp-bridals'); ?>
                    </a>
                </nav>
            </aside>

            <!-- Content -->
            <div class="account-content">
                <?php if ($active_tab === 'dashboard'): ?>
                    <!-- Dashboard -->
                    <h2><?php _e('Dashboard', 'kavipushp-bridals'); ?></h2>

                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 40px;">
                        <div style="background: var(--background-light); padding: 25px; border-radius: 10px; text-align: center;">
                            <div style="font-size: 36px; font-weight: 700; color: var(--primary-color);">
                                <?php echo count($user_bookings); ?>
                            </div>
                            <div><?php _e('Total Bookings', 'kavipushp-bridals'); ?></div>
                        </div>
                        <div style="background: var(--background-light); padding: 25px; border-radius: 10px; text-align: center;">
                            <div style="font-size: 36px; font-weight: 700; color: var(--success-color);">
                                <?php
                                $active = array_filter($user_bookings, function($b) {
                                    $status = get_post_meta($b->ID, '_booking_status', true);
                                    return in_array($status, array('confirmed', 'picked_up'));
                                });
                                echo count($active);
                                ?>
                            </div>
                            <div><?php _e('Active Rentals', 'kavipushp-bridals'); ?></div>
                        </div>
                        <div style="background: var(--background-light); padding: 25px; border-radius: 10px; text-align: center;">
                            <div style="font-size: 36px; font-weight: 700; color: var(--warning-color);">
                                <?php
                                $pending = array_filter($user_bookings, function($b) {
                                    return get_post_meta($b->ID, '_booking_status', true) === 'pending';
                                });
                                echo count($pending);
                                ?>
                            </div>
                            <div><?php _e('Pending', 'kavipushp-bridals'); ?></div>
                        </div>
                    </div>

                    <h3 style="margin-bottom: 20px;"><?php _e('Recent Bookings', 'kavipushp-bridals'); ?></h3>
                    <?php
                    $recent_bookings = array_slice($user_bookings, 0, 5);
                    if (!empty($recent_bookings)):
                    ?>
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th><?php _e('Set', 'kavipushp-bridals'); ?></th>
                                <th><?php _e('Dates', 'kavipushp-bridals'); ?></th>
                                <th><?php _e('Status', 'kavipushp-bridals'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_bookings as $booking):
                                $set_id = get_post_meta($booking->ID, '_bridal_set_id', true);
                                $set = $set_id ? get_post($set_id) : null;
                                $status = get_post_meta($booking->ID, '_booking_status', true);
                                $pickup = get_post_meta($booking->ID, '_pickup_date', true);
                                $return = get_post_meta($booking->ID, '_return_date', true);
                            ?>
                            <tr>
                                <td>
                                    <?php if ($set): ?>
                                        <a href="<?php echo get_permalink($set->ID); ?>"><?php echo esc_html($set->post_title); ?></a>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date_i18n('M j', strtotime($pickup)) . ' - ' . date_i18n('M j, Y', strtotime($return)); ?></td>
                                <td><span class="order-status <?php echo esc_attr($status); ?>"><?php echo kavipushp_get_booking_status_label($status); ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <a href="?tab=bookings" class="btn btn-outline" style="margin-top: 20px;"><?php _e('View All Bookings', 'kavipushp-bridals'); ?></a>
                    <?php else: ?>
                    <p><?php _e('No bookings yet.', 'kavipushp-bridals'); ?></p>
                    <a href="<?php echo esc_url(get_post_type_archive_link('bridal_set')); ?>" class="btn btn-primary"><?php _e('Browse Collection', 'kavipushp-bridals'); ?></a>
                    <?php endif; ?>

                <?php elseif ($active_tab === 'bookings'): ?>
                    <!-- All Bookings -->
                    <h2><?php _e('My Bookings', 'kavipushp-bridals'); ?></h2>

                    <?php if (!empty($user_bookings)): ?>
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th><?php _e('Booking #', 'kavipushp-bridals'); ?></th>
                                <th><?php _e('Set', 'kavipushp-bridals'); ?></th>
                                <th><?php _e('Dates', 'kavipushp-bridals'); ?></th>
                                <th><?php _e('Total', 'kavipushp-bridals'); ?></th>
                                <th><?php _e('Status', 'kavipushp-bridals'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($user_bookings as $booking):
                                $set_id = get_post_meta($booking->ID, '_bridal_set_id', true);
                                $set = $set_id ? get_post($set_id) : null;
                                $status = get_post_meta($booking->ID, '_booking_status', true);
                                $pickup = get_post_meta($booking->ID, '_pickup_date', true);
                                $return = get_post_meta($booking->ID, '_return_date', true);
                                $total = get_post_meta($booking->ID, '_total_amount', true);
                            ?>
                            <tr>
                                <td>#<?php echo $booking->ID; ?></td>
                                <td>
                                    <?php if ($set): ?>
                                        <a href="<?php echo get_permalink($set->ID); ?>"><?php echo esc_html($set->post_title); ?></a>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date_i18n('M j', strtotime($pickup)) . ' - ' . date_i18n('M j, Y', strtotime($return)); ?></td>
                                <td><?php echo kavipushp_format_price($total); ?></td>
                                <td><span class="order-status <?php echo esc_attr($status); ?>"><?php echo kavipushp_get_booking_status_label($status); ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-calendar-times"></i>
                        <h3><?php _e('No Bookings Yet', 'kavipushp-bridals'); ?></h3>
                        <p><?php _e('Start exploring our beautiful bridal collection!', 'kavipushp-bridals'); ?></p>
                        <a href="<?php echo esc_url(get_post_type_archive_link('bridal_set')); ?>" class="btn btn-primary"><?php _e('Browse Collection', 'kavipushp-bridals'); ?></a>
                    </div>
                    <?php endif; ?>

                <?php elseif ($active_tab === 'wishlist'): ?>
                    <!-- Wishlist -->
                    <h2><?php _e('My Wishlist', 'kavipushp-bridals'); ?></h2>
                    <div id="wishlist-container">
                        <p><?php _e('Your wishlist items are stored in your browser. They will appear here.', 'kavipushp-bridals'); ?></p>
                        <div id="wishlist-items" class="products-grid"></div>
                    </div>
                    <script>
                    jQuery(document).ready(function($) {
                        var wishlist = JSON.parse(localStorage.getItem('kavipushp_wishlist') || '[]');
                        if (wishlist.length === 0) {
                            $('#wishlist-container').html('<div class="empty-state"><i class="fas fa-heart"></i><h3><?php _e("Your wishlist is empty", "kavipushp-bridals"); ?></h3><p><?php _e("Start adding items you love!", "kavipushp-bridals"); ?></p><a href="<?php echo esc_url(get_post_type_archive_link("bridal_set")); ?>" class="btn btn-primary"><?php _e("Browse Collection", "kavipushp-bridals"); ?></a></div>');
                        } else {
                            $.ajax({
                                url: kavipushp_ajax.ajax_url,
                                type: 'POST',
                                data: {
                                    action: 'kavipushp_get_wishlist_items',
                                    nonce: kavipushp_ajax.nonce,
                                    ids: wishlist
                                },
                                success: function(response) {
                                    if (response.success) {
                                        $('#wishlist-items').html(response.data.html);
                                    }
                                }
                            });
                        }
                    });
                    </script>

                <?php elseif ($active_tab === 'profile'): ?>
                    <!-- Edit Profile -->
                    <h2><?php _e('Edit Profile', 'kavipushp-bridals'); ?></h2>

                    <?php
                    // Handle form submission
                    if (isset($_POST['update_profile']) && wp_verify_nonce($_POST['profile_nonce'], 'update_profile')) {
                        $user_data = array(
                            'ID'           => $current_user->ID,
                            'display_name' => sanitize_text_field($_POST['display_name']),
                            'first_name'   => sanitize_text_field($_POST['first_name']),
                            'last_name'    => sanitize_text_field($_POST['last_name']),
                        );

                        $result = wp_update_user($user_data);

                        if (!is_wp_error($result)) {
                            echo '<div class="alert alert-success"><i class="fas fa-check-circle"></i> ' . __('Profile updated successfully!', 'kavipushp-bridals') . '</div>';
                            $current_user = wp_get_current_user();
                        } else {
                            echo '<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> ' . $result->get_error_message() . '</div>';
                        }
                    }
                    ?>

                    <form method="post" class="profile-form">
                        <?php wp_nonce_field('update_profile', 'profile_nonce'); ?>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="first_name"><?php _e('First Name', 'kavipushp-bridals'); ?></label>
                                <input type="text" id="first_name" name="first_name" value="<?php echo esc_attr($current_user->first_name); ?>">
                            </div>
                            <div class="form-group">
                                <label for="last_name"><?php _e('Last Name', 'kavipushp-bridals'); ?></label>
                                <input type="text" id="last_name" name="last_name" value="<?php echo esc_attr($current_user->last_name); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="display_name"><?php _e('Display Name', 'kavipushp-bridals'); ?></label>
                            <input type="text" id="display_name" name="display_name" value="<?php echo esc_attr($current_user->display_name); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="user_email"><?php _e('Email', 'kavipushp-bridals'); ?></label>
                            <input type="email" id="user_email" value="<?php echo esc_attr($current_user->user_email); ?>" disabled>
                            <p class="description"><?php _e('Email cannot be changed here.', 'kavipushp-bridals'); ?></p>
                        </div>

                        <button type="submit" name="update_profile" class="btn btn-primary">
                            <?php _e('Update Profile', 'kavipushp-bridals'); ?>
                        </button>
                    </form>

                    <hr style="margin: 40px 0;">

                    <h3><?php _e('Change Password', 'kavipushp-bridals'); ?></h3>
                    <p><a href="<?php echo esc_url(wp_lostpassword_url()); ?>"><?php _e('Click here to reset your password', 'kavipushp-bridals'); ?></a></p>

                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php get_footer(); ?>
