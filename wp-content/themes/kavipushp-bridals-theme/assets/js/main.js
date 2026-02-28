/**
 * Kavipushp Bridals - Main JavaScript
 */

(function($) {
    'use strict';

    // Document Ready
    $(document).ready(function() {
        initMobileMenu();
        initStickyHeader();
        initQuickView();
        initWishlist();
        initGallery();
        initFilters();
        initAnimations();
    });

    /**
     * Mobile Menu Toggle
     */
    function initMobileMenu() {
        $('.menu-toggle').on('click', function() {
            $(this).toggleClass('active');
            $('.main-navigation').toggleClass('active');

            // Toggle icon
            var icon = $(this).find('i');
            if (icon.hasClass('fa-bars')) {
                icon.removeClass('fa-bars').addClass('fa-times');
            } else {
                icon.removeClass('fa-times').addClass('fa-bars');
            }
        });

        // Close menu when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.main-navigation, .menu-toggle').length) {
                $('.main-navigation').removeClass('active');
                $('.menu-toggle').removeClass('active');
                $('.menu-toggle i').removeClass('fa-times').addClass('fa-bars');
            }
        });
    }

    /**
     * Sticky Header
     */
    function initStickyHeader() {
        var header = $('.site-header');
        var headerOffset = header.offset().top;

        $(window).on('scroll', function() {
            if ($(window).scrollTop() > headerOffset + 100) {
                header.addClass('sticky');
            } else {
                header.removeClass('sticky');
            }
        });
    }

    /**
     * Quick View Modal
     */
    function initQuickView() {
        $(document).on('click', '.quick-view', function(e) {
            e.preventDefault();
            var productId = $(this).data('id');

            // Create modal if not exists
            if ($('#quick-view-modal').length === 0) {
                $('body').append(
                    '<div class="modal-overlay" id="quick-view-modal">' +
                        '<div class="modal">' +
                            '<div class="modal-header">' +
                                '<h2>Quick View</h2>' +
                                '<button class="modal-close">&times;</button>' +
                            '</div>' +
                            '<div class="modal-body" id="quick-view-content">' +
                                '<div class="spinner"></div>' +
                            '</div>' +
                        '</div>' +
                    '</div>'
                );
            }

            $('#quick-view-modal').addClass('active');

            // AJAX call to get product details
            $.ajax({
                url: kavipushp_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'kavipushp_quick_view',
                    nonce: kavipushp_ajax.nonce,
                    product_id: productId
                },
                success: function(response) {
                    if (response.success) {
                        $('#quick-view-content').html(response.data.html);
                    } else {
                        $('#quick-view-content').html('<p>Error loading product details.</p>');
                    }
                },
                error: function() {
                    $('#quick-view-content').html('<p>Error loading product details.</p>');
                }
            });
        });

        // Close modal
        $(document).on('click', '.modal-close, .modal-overlay', function(e) {
            if ($(e.target).is('.modal-overlay') || $(e.target).is('.modal-close')) {
                $('.modal-overlay').removeClass('active');
            }
        });

        // Close on ESC key
        $(document).on('keydown', function(e) {
            if (e.keyCode === 27) {
                $('.modal-overlay').removeClass('active');
            }
        });
    }

    /**
     * Wishlist Functionality
     */
    function initWishlist() {
        $(document).on('click', '.add-to-wishlist', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var productId = $btn.data('id');

            $btn.addClass('loading');

            // Get current wishlist from localStorage
            var wishlist = JSON.parse(localStorage.getItem('kavipushp_wishlist') || '[]');

            if (wishlist.indexOf(productId) === -1) {
                wishlist.push(productId);
                localStorage.setItem('kavipushp_wishlist', JSON.stringify(wishlist));
                $btn.addClass('active');
                $btn.find('i').removeClass('far').addClass('fas');
                showNotification('Added to wishlist!', 'success');
            } else {
                wishlist = wishlist.filter(function(id) { return id !== productId; });
                localStorage.setItem('kavipushp_wishlist', JSON.stringify(wishlist));
                $btn.removeClass('active');
                $btn.find('i').removeClass('fas').addClass('far');
                showNotification('Removed from wishlist', 'info');
            }

            $btn.removeClass('loading');
        });

        // Check existing wishlist items
        var wishlist = JSON.parse(localStorage.getItem('kavipushp_wishlist') || '[]');
        wishlist.forEach(function(productId) {
            $('.add-to-wishlist[data-id="' + productId + '"]')
                .addClass('active')
                .find('i').removeClass('far').addClass('fas');
        });
    }

    /**
     * Product Gallery
     */
    function initGallery() {
        $(document).on('click', '.thumb-image', function() {
            var largeUrl = $(this).data('large');
            $('#main-image').attr('src', largeUrl);
            $('.thumb-image').removeClass('active').css('border-color', 'transparent');
            $(this).addClass('active').css('border-color', 'var(--primary-color)');
        });

        // Lightbox
        $(document).on('click', '#main-image', function() {
            var src = $(this).attr('src');
            $('body').append(
                '<div class="lightbox" id="image-lightbox">' +
                    '<button class="lightbox-close">&times;</button>' +
                    '<img src="' + src + '" alt="Bridal Set">' +
                '</div>'
            );
            $('body').css('overflow', 'hidden');
        });

        $(document).on('click', '#image-lightbox, .lightbox-close', function() {
            $('#image-lightbox').remove();
            $('body').css('overflow', '');
        });
    }

    /**
     * Shop Filters
     */
    function initFilters() {
        // Auto-submit on filter change (radio buttons)
        $('.filter-options input[type="radio"]').on('change', function() {
            // Delay to allow visual feedback
            setTimeout(function() {
                $('#filter-form').submit();
            }, 300);
        });

        // Mobile filter toggle
        if ($('.shop-sidebar').length && $(window).width() < 992) {
            $('.shop-sidebar').before('<button class="btn btn-outline filter-toggle" style="margin-bottom: 20px;"><i class="fas fa-filter"></i> Filters</button>');

            $('.filter-toggle').on('click', function() {
                $('.shop-sidebar').slideToggle();
            });
        }
    }

    /**
     * Scroll Animations
     */
    function initAnimations() {
        var animatedElements = $('.category-card, .product-card, .feature-card, .step-card, .testimonial-card');

        function checkAnimation() {
            var windowHeight = $(window).height();
            var windowTop = $(window).scrollTop();
            var windowBottom = windowTop + windowHeight;

            animatedElements.each(function() {
                var element = $(this);
                var elementTop = element.offset().top;

                if (elementTop < windowBottom - 50) {
                    element.addClass('animated');
                }
            });
        }

        // Add CSS for animations
        $('head').append(
            '<style>' +
            '.category-card, .product-card, .feature-card, .step-card, .testimonial-card { opacity: 0; transform: translateY(30px); transition: opacity 0.6s ease, transform 0.6s ease; }' +
            '.category-card.animated, .product-card.animated, .feature-card.animated, .step-card.animated, .testimonial-card.animated { opacity: 1; transform: translateY(0); }' +
            '</style>'
        );

        $(window).on('scroll load', checkAnimation);
        checkAnimation();
    }

    /**
     * Show Notification
     */
    function showNotification(message, type) {
        var notification = $(
            '<div class="notification ' + type + '">' +
                '<span>' + message + '</span>' +
                '<button class="notification-close">&times;</button>' +
            '</div>'
        );

        $('body').append(notification);

        setTimeout(function() {
            notification.addClass('show');
        }, 100);

        setTimeout(function() {
            notification.removeClass('show');
            setTimeout(function() {
                notification.remove();
            }, 300);
        }, 3000);

        notification.find('.notification-close').on('click', function() {
            notification.removeClass('show');
            setTimeout(function() {
                notification.remove();
            }, 300);
        });
    }

    // Add notification styles
    $('head').append(
        '<style>' +
        '.notification { position: fixed; top: 100px; right: 20px; background: #fff; padding: 15px 40px 15px 20px; border-radius: 8px; box-shadow: 0 5px 20px rgba(0,0,0,0.15); z-index: 9999; transform: translateX(120%); transition: transform 0.3s ease; }' +
        '.notification.show { transform: translateX(0); }' +
        '.notification.success { border-left: 4px solid #28a745; }' +
        '.notification.error { border-left: 4px solid #dc3545; }' +
        '.notification.info { border-left: 4px solid #17a2b8; }' +
        '.notification-close { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; font-size: 20px; cursor: pointer; color: #999; }' +
        '.lightbox { position: fixed; inset: 0; background: rgba(0,0,0,0.9); z-index: 10000; display: flex; align-items: center; justify-content: center; }' +
        '.lightbox img { max-width: 90%; max-height: 90%; object-fit: contain; }' +
        '.lightbox-close { position: absolute; top: 20px; right: 30px; background: none; border: none; color: #fff; font-size: 40px; cursor: pointer; }' +
        '</style>'
    );

    /**
     * Date Picker Enhancement
     */
    $(document).on('change', '#pickup_date', function() {
        var pickupDate = new Date($(this).val());
        var minReturn = new Date(pickupDate);
        minReturn.setDate(minReturn.getDate() + 1);

        var minReturnStr = minReturn.toISOString().split('T')[0];
        $('#return_date').attr('min', minReturnStr);

        // Clear return date if it's before new minimum
        if ($('#return_date').val() && new Date($('#return_date').val()) <= pickupDate) {
            $('#return_date').val('');
        }
    });

    /**
     * Smooth Scroll for Anchor Links
     */
    $('a[href^="#"]').on('click', function(e) {
        var target = $($(this).attr('href'));
        if (target.length) {
            e.preventDefault();
            $('html, body').animate({
                scrollTop: target.offset().top - 100
            }, 600);
        }
    });

    /**
     * Search Form Enhancement
     */
    var searchTimer;
    $('.header-search input').on('input', function() {
        var query = $(this).val();
        var $results = $('.search-suggestions');

        clearTimeout(searchTimer);

        if (query.length < 3) {
            $results.remove();
            return;
        }

        searchTimer = setTimeout(function() {
            $.ajax({
                url: kavipushp_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'kavipushp_search_suggestions',
                    nonce: kavipushp_ajax.nonce,
                    query: query
                },
                success: function(response) {
                    if (response.success && response.data.suggestions.length) {
                        var html = '<div class="search-suggestions">';
                        response.data.suggestions.forEach(function(item) {
                            html += '<a href="' + item.url + '">' +
                                '<img src="' + item.image + '" alt="">' +
                                '<span>' + item.title + '</span>' +
                            '</a>';
                        });
                        html += '</div>';

                        $('.search-suggestions').remove();
                        $('.header-search').append(html);
                    }
                }
            });
        }, 300);
    });

    // Close search suggestions on click outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.header-search').length) {
            $('.search-suggestions').remove();
        }
    });

})(jQuery);
