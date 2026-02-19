<?php
/**
 * Booking Form Template Part
 *
 * @package Kavipushp_Bridals
 */

if (!defined('ABSPATH')) {
    exit;
}

$set_id = isset($atts['set_id']) ? intval($atts['set_id']) : 0;
$sets = array();

if ($set_id) {
    $set = get_post($set_id);
    if ($set && $set->post_type === 'bridal_set') {
        $sets = array($set);
    }
} else {
    $sets = get_posts(array(
        'post_type'      => 'bridal_set',
        'posts_per_page' => -1,
        'meta_query'     => array(
            array(
                'key'   => '_availability',
                'value' => 'available',
            ),
        ),
        'orderby'        => 'title',
        'order'          => 'ASC',
    ));
}
?>

<div class="booking-form-container">
    <form id="standalone-booking-form" class="booking-form">
        <?php wp_nonce_field('kavipushp_nonce', 'booking_nonce'); ?>

        <?php if (!$set_id): ?>
        <div class="form-group">
            <label for="booking_set_id"><?php _e('Select Bridal Set', 'kavipushp-bridals'); ?> *</label>
            <select id="booking_set_id" name="set_id" required>
                <option value=""><?php _e('Choose a set...', 'kavipushp-bridals'); ?></option>
                <?php foreach ($sets as $set):
                    $price = get_post_meta($set->ID, '_rental_price', true);
                    $set_code = get_post_meta($set->ID, '_set_id', true);
                ?>
                    <option value="<?php echo esc_attr($set->ID); ?>" data-price="<?php echo esc_attr($price); ?>">
                        <?php echo esc_html($set->post_title); ?>
                        <?php if ($set_code): ?>(<?php echo esc_html($set_code); ?>)<?php endif; ?>
                        - <?php echo number_format($price, 0); ?>/day
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php else: ?>
            <input type="hidden" name="set_id" value="<?php echo esc_attr($set_id); ?>">
            <div class="selected-set-info" style="background: var(--background-light); padding: 15px; border-radius: 10px; margin-bottom: 20px;">
                <strong><?php _e('Selected Set:', 'kavipushp-bridals'); ?></strong>
                <?php echo esc_html($sets[0]->post_title); ?>
            </div>
        <?php endif; ?>

        <div class="form-row">
            <div class="form-group">
                <label for="booking_pickup_date"><?php _e('Pickup Date', 'kavipushp-bridals'); ?> *</label>
                <input type="date" id="booking_pickup_date" name="pickup_date" required min="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="form-group">
                <label for="booking_return_date"><?php _e('Return Date', 'kavipushp-bridals'); ?> *</label>
                <input type="date" id="booking_return_date" name="return_date" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="booking_customer_name"><?php _e('Your Name', 'kavipushp-bridals'); ?> *</label>
            <input type="text" id="booking_customer_name" name="customer_name" required
                   value="<?php echo is_user_logged_in() ? esc_attr(wp_get_current_user()->display_name) : ''; ?>">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="booking_customer_email"><?php _e('Email', 'kavipushp-bridals'); ?> *</label>
                <input type="email" id="booking_customer_email" name="customer_email" required
                       value="<?php echo is_user_logged_in() ? esc_attr(wp_get_current_user()->user_email) : ''; ?>">
            </div>
            <div class="form-group">
                <label for="booking_customer_phone"><?php _e('Phone', 'kavipushp-bridals'); ?> *</label>
                <input type="tel" id="booking_customer_phone" name="customer_phone" required>
            </div>
        </div>

        <div class="form-group">
            <label for="booking_customer_address"><?php _e('Address', 'kavipushp-bridals'); ?></label>
            <textarea id="booking_customer_address" name="customer_address" rows="3" placeholder="<?php esc_attr_e('Your full address for delivery/pickup coordination', 'kavipushp-bridals'); ?>"></textarea>
        </div>

        <div class="form-group">
            <label for="booking_notes"><?php _e('Special Notes/Requests', 'kavipushp-bridals'); ?></label>
            <textarea id="booking_notes" name="notes" rows="2" placeholder="<?php esc_attr_e('Any special requirements or notes for your booking', 'kavipushp-bridals'); ?>"></textarea>
        </div>

        <!-- Rental Summary (dynamically updated) -->
        <div class="rental-summary" id="standalone-rental-summary" style="display: none;">
            <h4><?php _e('Rental Summary', 'kavipushp-bridals'); ?></h4>
            <div class="summary-row">
                <span><?php _e('Rental Days:', 'kavipushp-bridals'); ?></span>
                <span id="standalone-summary-days">-</span>
            </div>
            <div class="summary-row">
                <span><?php _e('Daily Rate:', 'kavipushp-bridals'); ?></span>
                <span id="standalone-summary-rate">-</span>
            </div>
            <div class="summary-row">
                <span><?php _e('Rental Total:', 'kavipushp-bridals'); ?></span>
                <span id="standalone-summary-rental">-</span>
            </div>
            <div class="summary-row">
                <span><?php _e('Security Deposit:', 'kavipushp-bridals'); ?></span>
                <span id="standalone-summary-deposit">-</span>
            </div>
            <div class="summary-row total">
                <span><?php _e('Grand Total:', 'kavipushp-bridals'); ?></span>
                <span id="standalone-summary-total">-</span>
            </div>
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%;">
            <i class="fas fa-calendar-check"></i> <?php _e('Submit Booking Request', 'kavipushp-bridals'); ?>
        </button>

        <p style="font-size: 13px; color: #666; margin-top: 15px; text-align: center;">
            <?php _e('By submitting, you agree to our rental terms and conditions. We will contact you to confirm your booking.', 'kavipushp-bridals'); ?>
        </p>
    </form>

    <div id="standalone-booking-message" style="margin-top: 15px;"></div>
</div>

<script>
jQuery(document).ready(function($) {
    var currentPrice = 0;
    var currentDeposit = 0;

    // Get set price when selected
    $('#booking_set_id').on('change', function() {
        var $selected = $(this).find(':selected');
        currentPrice = parseFloat($selected.data('price')) || 0;

        // Fetch deposit via AJAX
        if ($(this).val()) {
            $.ajax({
                url: kavipushp_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'kavipushp_get_set_deposit',
                    nonce: kavipushp_ajax.nonce,
                    set_id: $(this).val()
                },
                success: function(response) {
                    if (response.success) {
                        currentDeposit = parseFloat(response.data.deposit) || 0;
                        updateSummary();
                    }
                }
            });
        }
    });

    // Update summary on date change
    $('#booking_pickup_date, #booking_return_date').on('change', function() {
        updateSummary();
    });

    function updateSummary() {
        var pickup = $('#booking_pickup_date').val();
        var returnDate = $('#booking_return_date').val();

        if (pickup && returnDate && currentPrice > 0) {
            var pickupDate = new Date(pickup);
            var returnDateObj = new Date(returnDate);
            var days = Math.ceil((returnDateObj - pickupDate) / (1000 * 60 * 60 * 24)) + 1;

            if (days > 0) {
                var rentalTotal = days * currentPrice;
                var grandTotal = rentalTotal + currentDeposit;

                $('#standalone-summary-days').text(days);
                $('#standalone-summary-rate').text(currentPrice.toLocaleString());
                $('#standalone-summary-rental').text(rentalTotal.toLocaleString());
                $('#standalone-summary-deposit').text(currentDeposit.toLocaleString());
                $('#standalone-summary-total').text(grandTotal.toLocaleString());
                $('#standalone-rental-summary').slideDown();
            } else {
                $('#standalone-rental-summary').slideUp();
            }
        }
    }

    // Form submission
    $('#standalone-booking-form').on('submit', function(e) {
        e.preventDefault();

        var $btn = $(this).find('button[type="submit"]');
        var originalText = $btn.html();
        $btn.html('<i class="fas fa-spinner fa-spin"></i> <?php _e("Processing...", "kavipushp-bridals"); ?>').prop('disabled', true);

        $.ajax({
            url: kavipushp_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'kavipushp_create_booking',
                nonce: kavipushp_ajax.nonce,
                set_id: $('[name="set_id"]').val(),
                pickup_date: $('#booking_pickup_date').val(),
                return_date: $('#booking_return_date').val(),
                customer_name: $('#booking_customer_name').val(),
                customer_email: $('#booking_customer_email').val(),
                customer_phone: $('#booking_customer_phone').val(),
                customer_address: $('#booking_customer_address').val()
            },
            success: function(response) {
                if (response.success) {
                    $('#standalone-booking-message').html('<div class="alert alert-success"><i class="fas fa-check-circle"></i> ' + response.data.message + '</div>');
                    $('#standalone-booking-form')[0].reset();
                    $('#standalone-rental-summary').hide();
                } else {
                    $('#standalone-booking-message').html('<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> ' + response.data.message + '</div>');
                }
            },
            error: function() {
                $('#standalone-booking-message').html('<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php _e("An error occurred. Please try again.", "kavipushp-bridals"); ?></div>');
            },
            complete: function() {
                $btn.html(originalText).prop('disabled', false);
            }
        });
    });
});
</script>
