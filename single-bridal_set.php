<?php
/**
 * Single Bridal Set Template
 *
 * @package Kavipushp_Bridals
 */

get_header();

while (have_posts()): the_post();
    $rental_price = get_post_meta(get_the_ID(), '_rental_price', true);
    $deposit_amount = get_post_meta(get_the_ID(), '_deposit_amount', true);
    $set_id_code = get_post_meta(get_the_ID(), '_set_id', true);
    $availability = get_post_meta(get_the_ID(), '_availability', true);
    $includes = get_post_meta(get_the_ID(), '_set_includes', true);
    $weight = get_post_meta(get_the_ID(), '_weight', true);
    $material = get_post_meta(get_the_ID(), '_material', true);
    $gallery_images = get_post_meta(get_the_ID(), '_gallery_images', true);
    $categories = get_the_terms(get_the_ID(), 'bridal_category');
?>

<section class="product-single">
    <div class="container">
        <!-- Breadcrumb -->
        <div class="breadcrumb" style="margin-bottom: 30px; font-size: 14px;">
            <a href="<?php echo esc_url(home_url('/')); ?>"><?php _e('Home', 'kavipushp-bridals'); ?></a>
            <span style="margin: 0 10px;">/</span>
            <a href="<?php echo esc_url(get_post_type_archive_link('bridal_set')); ?>"><?php _e('Bridal Sets', 'kavipushp-bridals'); ?></a>
            <?php if ($categories && !is_wp_error($categories)): ?>
                <span style="margin: 0 10px;">/</span>
                <a href="<?php echo esc_url(get_term_link($categories[0])); ?>"><?php echo esc_html($categories[0]->name); ?></a>
            <?php endif; ?>
            <span style="margin: 0 10px;">/</span>
            <span><?php the_title(); ?></span>
        </div>

        <div class="product-single-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 50px;">
            <!-- Product Gallery -->
            <div class="product-gallery">
                <div class="gallery-main">
                    <?php if (has_post_thumbnail()): ?>
                        <img src="<?php the_post_thumbnail_url('product-large'); ?>" alt="<?php the_title_attribute(); ?>" id="main-image">
                    <?php else: ?>
                        <img src="<?php echo KAVIPUSHP_URI; ?>/assets/images/placeholder.jpg" alt="<?php the_title_attribute(); ?>" id="main-image">
                    <?php endif; ?>
                </div>

                <?php if (!empty($gallery_images)): ?>
                <div class="gallery-thumbs" style="display: flex; gap: 10px; margin-top: 15px;">
                    <?php if (has_post_thumbnail()): ?>
                        <img src="<?php the_post_thumbnail_url('thumbnail'); ?>"
                             data-large="<?php the_post_thumbnail_url('product-large'); ?>"
                             alt="<?php the_title_attribute(); ?>"
                             class="thumb-image active"
                             style="width: 80px; height: 80px; object-fit: cover; cursor: pointer; border-radius: 8px; border: 2px solid var(--primary-color);">
                    <?php endif; ?>
                    <?php
                    $image_ids = explode(',', $gallery_images);
                    foreach ($image_ids as $image_id):
                        $thumb_url = wp_get_attachment_image_url($image_id, 'thumbnail');
                        $large_url = wp_get_attachment_image_url($image_id, 'product-large');
                        if ($thumb_url):
                    ?>
                        <img src="<?php echo esc_url($thumb_url); ?>"
                             data-large="<?php echo esc_url($large_url); ?>"
                             alt="Gallery Image"
                             class="thumb-image"
                             style="width: 80px; height: 80px; object-fit: cover; cursor: pointer; border-radius: 8px; border: 2px solid transparent;">
                    <?php
                        endif;
                    endforeach;
                    ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Product Details -->
            <div class="product-details">
                <?php if ($set_id_code): ?>
                    <span style="color: #999; font-size: 14px;">Set ID: <?php echo esc_html($set_id_code); ?></span>
                <?php endif; ?>

                <h1><?php the_title(); ?></h1>

                <?php if ($categories && !is_wp_error($categories)): ?>
                    <div style="margin-bottom: 15px;">
                        <?php foreach ($categories as $cat): ?>
                            <a href="<?php echo esc_url(get_term_link($cat)); ?>"
                               style="background: var(--background-light); padding: 5px 15px; border-radius: 20px; font-size: 13px; color: var(--primary-color);">
                                <?php echo esc_html($cat->name); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="product-price" style="font-size: 36px; margin: 20px 0;">
                    <?php echo number_format($rental_price, 0); ?> <span style="font-size: 18px; color: #999;">/day</span>
                </div>

                <?php if ($deposit_amount): ?>
                    <p style="color: #666; margin-bottom: 15px;">
                        <strong><?php _e('Security Deposit:', 'kavipushp-bridals'); ?></strong>
                        <?php echo number_format($deposit_amount, 0); ?> (Refundable)
                    </p>
                <?php endif; ?>

                <!-- Availability Status -->
                <?php if ($availability === 'available'): ?>
                    <div class="product-availability">
                        <i class="fas fa-check-circle"></i> <?php _e('Available for Rent', 'kavipushp-bridals'); ?>
                    </div>
                <?php elseif ($availability === 'rented'): ?>
                    <div class="product-availability unavailable">
                        <i class="fas fa-times-circle"></i> <?php _e('Currently Rented', 'kavipushp-bridals'); ?>
                    </div>
                <?php elseif ($availability === 'maintenance'): ?>
                    <div class="product-availability unavailable">
                        <i class="fas fa-tools"></i> <?php _e('Under Maintenance', 'kavipushp-bridals'); ?>
                    </div>
                <?php endif; ?>

                <!-- Description -->
                <div class="product-description">
                    <?php the_content(); ?>
                </div>

                <!-- Set Details -->
                <div style="background: var(--background-light); padding: 20px; border-radius: 10px; margin: 25px 0;">
                    <h4 style="margin-bottom: 15px;"><?php _e('Set Details', 'kavipushp-bridals'); ?></h4>
                    <table style="width: 100%;">
                        <?php if ($includes): ?>
                        <tr>
                            <td style="padding: 8px 0; color: #666;"><strong><?php _e('Includes:', 'kavipushp-bridals'); ?></strong></td>
                            <td style="padding: 8px 0;"><?php echo nl2br(esc_html($includes)); ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($material): ?>
                        <tr>
                            <td style="padding: 8px 0; color: #666;"><strong><?php _e('Material:', 'kavipushp-bridals'); ?></strong></td>
                            <td style="padding: 8px 0;"><?php echo esc_html($material); ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($weight): ?>
                        <tr>
                            <td style="padding: 8px 0; color: #666;"><strong><?php _e('Weight:', 'kavipushp-bridals'); ?></strong></td>
                            <td style="padding: 8px 0;"><?php echo esc_html($weight); ?> grams</td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>

                <!-- Booking Form -->
                <?php if ($availability === 'available'): ?>
                <div class="booking-form">
                    <h3><?php _e('Book This Set', 'kavipushp-bridals'); ?></h3>
                    <form id="rental-booking-form">
                        <input type="hidden" name="set_id" value="<?php the_ID(); ?>">

                        <div class="form-row">
                            <div class="form-group">
                                <label for="pickup_date"><?php _e('Pickup Date', 'kavipushp-bridals'); ?> *</label>
                                <input type="date" id="pickup_date" name="pickup_date" required min="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="form-group">
                                <label for="return_date"><?php _e('Return Date', 'kavipushp-bridals'); ?> *</label>
                                <input type="date" id="return_date" name="return_date" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="customer_name"><?php _e('Your Name', 'kavipushp-bridals'); ?> *</label>
                            <input type="text" id="customer_name" name="customer_name" required>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="customer_email"><?php _e('Email', 'kavipushp-bridals'); ?> *</label>
                                <input type="email" id="customer_email" name="customer_email" required>
                            </div>
                            <div class="form-group">
                                <label for="customer_phone"><?php _e('Phone', 'kavipushp-bridals'); ?> *</label>
                                <input type="tel" id="customer_phone" name="customer_phone" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="customer_address"><?php _e('Address', 'kavipushp-bridals'); ?></label>
                            <textarea id="customer_address" name="customer_address" rows="3"></textarea>
                        </div>

                        <!-- Rental Summary -->
                        <div class="rental-summary" id="rental-summary" style="display: none;">
                            <h4><?php _e('Rental Summary', 'kavipushp-bridals'); ?></h4>
                            <div class="summary-row">
                                <span><?php _e('Rental Days:', 'kavipushp-bridals'); ?></span>
                                <span id="summary-days">-</span>
                            </div>
                            <div class="summary-row">
                                <span><?php _e('Daily Rate:', 'kavipushp-bridals'); ?></span>
                                <span id="summary-rate"><?php echo number_format($rental_price, 0); ?></span>
                            </div>
                            <div class="summary-row">
                                <span><?php _e('Rental Total:', 'kavipushp-bridals'); ?></span>
                                <span id="summary-rental-total">-</span>
                            </div>
                            <div class="summary-row">
                                <span><?php _e('Security Deposit:', 'kavipushp-bridals'); ?></span>
                                <span id="summary-deposit"><?php echo number_format($deposit_amount, 0); ?></span>
                            </div>
                            <div class="summary-row total">
                                <span><?php _e('Grand Total:', 'kavipushp-bridals'); ?></span>
                                <span id="summary-grand-total">-</span>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 20px;">
                            <i class="fas fa-calendar-check"></i> <?php _e('Book Now', 'kavipushp-bridals'); ?>
                        </button>
                    </form>
                    <div id="booking-message" style="margin-top: 15px;"></div>
                </div>

                <script>
                jQuery(document).ready(function($) {
                    var rentalPrice = <?php echo floatval($rental_price); ?>;
                    var deposit = <?php echo floatval($deposit_amount); ?>;

                    // Calculate rental when dates change
                    $('#pickup_date, #return_date').on('change', function() {
                        var pickup = $('#pickup_date').val();
                        var returnDate = $('#return_date').val();

                        if (pickup && returnDate) {
                            var pickupDate = new Date(pickup);
                            var returnDateObj = new Date(returnDate);
                            var days = Math.ceil((returnDateObj - pickupDate) / (1000 * 60 * 60 * 24)) + 1;

                            if (days > 0) {
                                var rentalTotal = days * rentalPrice;
                                var grandTotal = rentalTotal + deposit;

                                $('#summary-days').text(days);
                                $('#summary-rental-total').text(rentalTotal.toLocaleString());
                                $('#summary-grand-total').text(grandTotal.toLocaleString());
                                $('#rental-summary').slideDown();
                            } else {
                                $('#rental-summary').slideUp();
                            }
                        }
                    });

                    // Form submission
                    $('#rental-booking-form').on('submit', function(e) {
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
                                set_id: $('input[name="set_id"]').val(),
                                pickup_date: $('#pickup_date').val(),
                                return_date: $('#return_date').val(),
                                customer_name: $('#customer_name').val(),
                                customer_email: $('#customer_email').val(),
                                customer_phone: $('#customer_phone').val(),
                                customer_address: $('#customer_address').val()
                            },
                            success: function(response) {
                                if (response.success) {
                                    $('#booking-message').html('<div class="alert alert-success"><i class="fas fa-check-circle"></i> ' + response.data.message + '</div>');
                                    $('#rental-booking-form')[0].reset();
                                    $('#rental-summary').hide();
                                } else {
                                    $('#booking-message').html('<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> ' + response.data.message + '</div>');
                                }
                            },
                            error: function() {
                                $('#booking-message').html('<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php _e("An error occurred. Please try again.", "kavipushp-bridals"); ?></div>');
                            },
                            complete: function() {
                                $btn.html(originalText).prop('disabled', false);
                            }
                        });
                    });

                    // Gallery image switching
                    $('.thumb-image').on('click', function() {
                        var largeUrl = $(this).data('large');
                        $('#main-image').attr('src', largeUrl);
                        $('.thumb-image').css('border-color', 'transparent');
                        $(this).css('border-color', 'var(--primary-color)');
                    });
                });
                </script>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle"></i>
                        <?php _e('This set is currently not available for booking. Please check back later or contact us for more information.', 'kavipushp-bridals'); ?>
                    </div>
                    <a href="<?php echo esc_url(get_post_type_archive_link('bridal_set')); ?>" class="btn btn-primary">
                        <?php _e('Browse Other Sets', 'kavipushp-bridals'); ?>
                    </a>
                <?php endif; ?>

                <!-- Contact for Inquiry -->
                <div style="margin-top: 30px; padding-top: 30px; border-top: 1px solid var(--border-color);">
                    <p><i class="fas fa-phone-alt" style="color: var(--primary-color);"></i>
                        <?php _e('Have questions? Call us:', 'kavipushp-bridals'); ?>
                        <a href="tel:<?php echo esc_attr(get_theme_mod('contact_phone', '+91 98765 43210')); ?>" style="color: var(--primary-color); font-weight: 600;">
                            <?php echo esc_html(get_theme_mod('contact_phone', '+91 98765 43210')); ?>
                        </a>
                    </p>
                    <?php if (get_theme_mod('social_whatsapp')): ?>
                    <a href="https://wa.me/<?php echo esc_attr(get_theme_mod('social_whatsapp')); ?>?text=<?php echo urlencode('Hi, I am interested in renting ' . get_the_title()); ?>"
                       class="btn btn-outline" style="margin-top: 10px;" target="_blank">
                        <i class="fab fa-whatsapp"></i> <?php _e('Inquire on WhatsApp', 'kavipushp-bridals'); ?>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Related Sets -->
        <?php
        $related_args = array(
            'post_type'      => 'bridal_set',
            'posts_per_page' => 4,
            'post__not_in'   => array(get_the_ID()),
            'orderby'        => 'rand',
        );

        if ($categories && !is_wp_error($categories)) {
            $category_ids = wp_list_pluck($categories, 'term_id');
            $related_args['tax_query'] = array(
                array(
                    'taxonomy' => 'bridal_category',
                    'field'    => 'term_id',
                    'terms'    => $category_ids,
                ),
            );
        }

        $related_sets = new WP_Query($related_args);

        if ($related_sets->have_posts()):
        ?>
        <div class="related-products" style="margin-top: 80px;">
            <div class="section-title">
                <h2><?php _e('You May Also Like', 'kavipushp-bridals'); ?></h2>
            </div>
            <div class="products-grid">
                <?php while ($related_sets->have_posts()): $related_sets->the_post();
                    $rel_price = get_post_meta(get_the_ID(), '_rental_price', true);
                    $rel_availability = get_post_meta(get_the_ID(), '_availability', true);
                    $rel_categories = get_the_terms(get_the_ID(), 'bridal_category');
                ?>
                <div class="product-card">
                    <div class="product-image">
                        <?php if (has_post_thumbnail()): ?>
                            <a href="<?php the_permalink(); ?>">
                                <?php the_post_thumbnail('product-thumb'); ?>
                            </a>
                        <?php endif; ?>
                        <?php if ($rel_availability === 'available'): ?>
                            <span class="product-badge"><?php _e('Available', 'kavipushp-bridals'); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="product-info">
                        <?php if ($rel_categories && !is_wp_error($rel_categories)): ?>
                            <span class="product-category"><?php echo esc_html($rel_categories[0]->name); ?></span>
                        <?php endif; ?>
                        <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                        <div class="product-meta">
                            <span class="product-price"><?php echo number_format($rel_price, 0); ?> <span>/day</span></span>
                        </div>
                    </div>
                </div>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php endwhile; ?>

<?php get_footer(); ?>
