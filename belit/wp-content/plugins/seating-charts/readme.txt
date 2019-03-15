=== Seating Charts ===
Contributors: Tickera
Donate link: https://tickera.com
Tags: seating charts, tickera, woocommerce
Requires at least: 4.3
Tested up to: 4.9.5

Create seating charts for your event

== Description ==

Create seating charts for your event

== Installation ==

1. Upload this add-on zip to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Install and activate Tickera plugin
4. Install and activate WooCommerce plugin (if used with WooCommerce)
5. Install and activate Bridge for WooCommerce add-on (if used with WooCommerce)
6. Create an Event (http://yoursite.com/wp-admin/post-new.php?post_type=tc_events)
7. Create a Product (wp-admin/post-new.php?post_type=product) - if you want to use the add-on with WooCommerce or create a Ticke Type if you want to use the add-on with standalone version of Tickera
5. Create a new Seat Chart (http://yoursite.com/wp-admin/post-new.php?post_type=tc_seat_charts)
7. Sell your event tickets and enjoy!

== Changelog ==

= 0.39 =
* Improvements with mobile controls
* Fixed issue with being able to add same seat to the cart

= 0.38 =
* Fixed issue with being able to select bought seats
* Removed Firebase privacy policy

= 0.37 =
* Fixed issue with removing from cart (WooCommerce)
* Fixed issue with AJAX error

= 0.36 =
* Fixed issue with WooCommerce Germanized

= 0.35 =
* Added Firebase privacy policy statement
* Fixed issue with variation being able to be added to cart multiple times by fast clicking
* Fixed issue with being able to select blocked seats

= 0.34 =
* Added option for deleting plugin's data to the Delete Info tab in admin settings (3.2.8.5 version of Tickera plugin is required)

= 0.33 =
* Improved user experience on mobile devices

= 0.32 =
* Removed hover for unavailable seats on the front-end

= 0.31 =
* Added new status "Unavailable" for seats which will be shown if ticket selling dates are used
* Fixed issue with removing seatings cookie data in WooCommerce before resuming order (when a user goes back in the browser from order details page and tries to continue with order) 

= 0.30 =
* Fixed issue with deleting chart after trashing a post

= 0.29 =
* Fixed issue with table seat selection

= 0.28 =
* Added additional strings for translation
* Fixed compatibility issues with WordPress 4.9 (table colors)
* Added check for an empty ticket type when assigning a ticket type to a seating group, table or standing area
* Fixed issue with removing items from WooCommerce cart when Firebase integration is active

= 0.27 =
* Added hooks for developers
* Fixed issue with double tap on mobiles

= 0.26 =
* Fixed issue with seating chart centering across different devices and screen sizes
* Resolved issue with minumum tickets in Firefox browser

= 0.25 =
* Added possibility of removing seats from the cart table
* Fixed the issue with the dash in the seat label name
* Added link to the Seating Charts add-on documentation
* Firebase check added. You will not be able to save if all fields are not filled
* Fixed issue with minimum and maximum tickets per order
* Fixed issue with removing variable WooCommerce products from cart

= 0.24 =
* Fixed panning on touch devices 
* Fixed various issue with multiple seatings shortcodes on a page
* Added more touch functionality with CSS

= 0.23 =
* Save seats cart for users permanently in WooCommerce 

= 0.22 =
* Changed setting lables to required - to avoid errors caused by users
* Added functionality to force unique seat labels

= 0.21 =
* Changed zoom functionality on the front-end (show same zoom level set on the admin side)

= 0.2 =
* Fix for quantity selector located on the cart page for standing area

= 0.1 =
* First beta release