================================================================================
                        KAVIPUSHP BRIDALS THEME
                    Jewelry Rental Business Theme for WordPress
================================================================================

Version: 1.0.0
Author: Kavipushp
Requires WordPress: 6.0+
Requires PHP: 8.0+
License: GPL v2 or later

================================================================================
                              INSTALLATION
================================================================================

1. Upload Theme:
   - Download the 'kavipushp-bridals-theme' folder
   - Upload it to: wp-content/themes/
   - Or use WordPress Dashboard > Appearance > Themes > Add New > Upload Theme

2. Activate Theme:
   - Go to Appearance > Themes
   - Click "Activate" on Kavipushp Bridals

3. Install Required Plugins (Optional but Recommended):
   - WooCommerce (for extended e-commerce features)
   - Contact Form 7 (for contact forms)

4. Initial Setup:
   - Go to Appearance > Customize to configure:
     * Logo
     * Contact Information
     * Social Media Links
     * Hero Section
     * Colors

================================================================================
                              FEATURES
================================================================================

BRIDAL SETS MANAGEMENT
----------------------
- Custom Post Type for Bridal Sets
- Categories & Tags
- Rental pricing per day
- Security deposit management
- Availability status tracking
- Image gallery for each set
- Set ID/Code for easy reference

BOOKING SYSTEM
--------------
- Customer booking form on each product
- Date-based availability checking
- Automatic price calculation
- Email notifications (customer & admin)
- Booking status management:
  * Pending
  * Confirmed
  * Picked Up
  * Returned
  * Completed
  * Cancelled

CUSTOMER FEATURES
-----------------
- User registration & login
- My Account dashboard
- Booking history
- Wishlist (browser-based)
- Profile management

ADMIN FEATURES
--------------
- Dashboard widgets with statistics
- Booking management
- Inventory overview
- Sample data generator (for testing)
- Easy customization via WordPress Customizer

================================================================================
                           SHORTCODES
================================================================================

[featured_sets count="8"]
    Display featured bridal sets
    Parameters:
    - count: Number of sets to show (default: 8)
    - category: Category slug to filter

[bridal_categories count="6"]
    Display category cards
    Parameters:
    - count: Number of categories (default: 6)

[booking_form set_id="123"]
    Display booking form
    Parameters:
    - set_id: Specific set ID (optional, shows dropdown if not set)

================================================================================
                         PAGE TEMPLATES
================================================================================

1. Front Page (front-page.php)
   - Hero section
   - Categories
   - Featured sets
   - How it works
   - Features
   - Testimonials
   - CTA

2. My Account (page-my-account.php)
   - Create a page and select "My Account" template
   - Features dashboard, bookings, wishlist, profile

================================================================================
                      CUSTOMIZER OPTIONS
================================================================================

1. General Settings
   - Currency symbol

2. Hero Section
   - Title & subtitle
   - Background image

3. Contact Information
   - Phone, Email, Address
   - Business hours

4. Social Media
   - Facebook, Instagram, Twitter, YouTube
   - WhatsApp (for floating button)

5. Footer Settings
   - About text

6. Colors
   - Primary color
   - Secondary color

================================================================================
                       SAMPLE DATA
================================================================================

To populate with test data:
1. Go to Dashboard > Bridal Sets > Import Samples
2. Choose number of sets (1-400)
3. Click "Generate Sample Data"

This creates:
- 10 categories with descriptions
- Specified number of bridal sets with:
  * Random pricing
  * Random categories
  * Sample descriptions
  * Set IDs (KP001, KP002, etc.)

================================================================================
                         MENU LOCATIONS
================================================================================

1. Primary Menu - Main navigation in header
2. Footer Menu - Links in footer widget area

================================================================================
                       WIDGET AREAS
================================================================================

1. Shop Sidebar - Filters on shop page
2. Footer Widget 1, 2, 3 - Footer columns

================================================================================
                          SUPPORT
================================================================================

For support and feature requests:
- Email: support@kavipushp.com
- Documentation: [Coming Soon]

================================================================================
                        FILE STRUCTURE
================================================================================

kavipushp-bridals-theme/
├── assets/
│   ├── css/
│   │   └── custom.css
│   ├── js/
│   │   ├── admin.js
│   │   └── main.js
│   └── images/
├── inc/
│   ├── customizer.php
│   ├── sample-data.php
│   └── template-functions.php
├── template-parts/
│   └── booking-form.php
├── woocommerce/
├── 404.php
├── archive-bridal_set.php
├── footer.php
├── front-page.php
├── functions.php
├── header.php
├── index.php
├── page.php
├── page-my-account.php
├── search.php
├── single-bridal_set.php
├── style.css
├── taxonomy-bridal_category.php
└── README.txt

================================================================================
                         CHANGELOG
================================================================================

Version 1.0.0 (Initial Release)
- Complete bridal set management
- Booking system with email notifications
- Customer account features
- Admin dashboard widgets
- Sample data generator
- Responsive design
- Customizer integration

================================================================================

Thank you for using Kavipushp Bridals Theme!
