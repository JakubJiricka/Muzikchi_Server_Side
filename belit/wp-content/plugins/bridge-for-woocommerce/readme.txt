=== Bridge for WooCommerce ===
Contributors: Tickera
Donate link: https://tickera.com
Tags: woocommerce, tickera
Requires at least: 4.3.1
Tested up to: 4.9.5
WC requires at least: 2.3.13
WC tested up to: 3.3.5

Leverage the power of both WooCommerce and Tickera to manage events and sell tickets

== Description ==

Leverage the power of both WooCommerce and Tickera to manage events and sell tickets

= Sell Event Tickets with WooCommerce =

With more than 10.000.000 downloads, WooCommerce is certainly the most popular e-commerce system for the WordPress platform. We have harnessed the power of almighty WooCommerce and came up with this great integration with Tickera Event Ticketing System called Bridge for WooCommerce. Sell tickets on your site and deliver them to your buyers using the awesomeness of this WooCommerce and Tickera fusion!

= Create Tickets Inside WooCommerce =

Creating tickets for an event is as easy as creating any other WooCommerce product. Each ticket can have its own SKU, price, stock, and even multiple variations! In addition, you can control sale price date easily and offer early bird tickets with just one click. Set the different ticket template and make a new look of each ticket type with Tickera ticket drag & drop template builder. Control number of allowed check-ins per ticket easily.

= Sell tickets and other products at the same time =

Have a cool T-Shirt for your event or want to offer option for your visitors to have lunch? Why not generating extra revenue with that? Offer your customers all the perks you have, easy and straight away!

= Check in attendees with phone apps or barcode readers =

Use Tickera check-in [iOS](https://itunes.apple.com/us/app/ticket-checkin/id958838933) and [Android](https://play.google.com/store/apps/details?id=com.tickera.tickeraapp) apps or even a simple but efficient [Barcode Reader](https://tickera.com/addons/barcode-reader/). Just point your smartphone or barcode reader to the ticket, check-in and say "Welcome!"

= Extend WooCommerce order status e-mails =

Tickets in the mailbox! Right after purchasing a ticket, your customers will get the standard WooCommerce "Order Completed" e-mail with the download link to your ticket(s). Pretty cool, right?

= Create Custom Order Fields =

Need to know more than just name and email of you customers? Use [Tickera Custom Forms](https://tickera.com/addons/custom-forms/) and set everything up! Input fields, checkboxes, radio buttons, dropdown menus... all at your disposal, any way you need, any way you like.

= Supported Tickera Addons =

* [Custom Forms](https://tickera.com/addons/custom-forms/)
* [Events Calendar](https://tickera.com/addons/tickera-event-calendar/)
* [Barcode Reader](https://tickera.com/addons/barcode-reader/)
* [CSV Export](https://tickera.com/addons/csv-export/)
* [Custom Ticket Template Font](https://tickera.com/addons/custom-ticket-template-fonts/)
* [Check-in Notifications](https://tickera.com/addons/check-in-notifications/)
* [Check-in Translate](https://tickera.com/addons/check-in-app-translation/)

= Feature List =

* Leverage the power of both WooCommerce and Tickera to deliver the best experience for your event
* No additional fees
* Manage events and sell tickets directly on your website
* Check-in attendees with mobile apps or barcode reader
* Create different ticket templates with drag & drop template builder
* Add multiple ticket types and variations (both paid and free)
* Control number of available check-ins per each ticket type
* Sell tickets and other products at the same time
* Schedule sale price date
* Limit how many tickets of a given ticket type are available
* Optionally hide tickets from the WooCommerce product archive
* Redirect single ticket products to associated event page
* Create Custom Order Fields and display them in the check-in mobile apps and on the tickets
* Export attendee list (as a PDF document or in the CSV format)

= Requirements = 

* [Tickera](https://wordpress.org/plugins/tickera-event-ticketing-system/ "WordPress Event Ticketing System") Plugin (version 3.2.0.9 and up)
* WooCommerce Plugin (version 2.3.13 and up)

== Installation ==

1. Upload this add-on zip to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Install and activate Tickera plugin https://wordpress.org/plugins/tickera-event-ticketing-system/
4. Install and activate WooCommerce plugin https://wordpress.org/plugins/woocommerce/ 
5. Create a new Event (http://yoursite.com/wp-admin/post-new.php?post_type=tc_events)
6. Create a new WooCommerce product (http://yoursite.com/wp-admin/post-new.php?post_type=product) - select "Product is a Ticket"
7. Sell your event tickets and enjoy!

== Changelog ==

= 1.1.5.9 = 
* Fixed calling of the product id

= 1.1.5.8 = 
* Changed position of one of the developer hooks

= 1.1.5.7 = 
* Added support for "WooCommerce One Page Checkout" plugin (https://woocommerce.com/products/woocommerce-one-page-checkout/)
* Fixed support for "WooCommerce POS" plugin 

= 1.1.5.6 = 
* Added refunded order status to the attendees filter

= 1.1.5.5 = 
* Added new product option "Available dates for tickets selling"

= 1.1.5.4 =
* Fixed issue with sale price shown in seating charts (requires at least 0.25v seatings charts)

= 1.1.5.3 =
* Fixed issues with billing and shipping addressed for ticket template elements

= 1.1.5.2 =
* Fixed conflict with WooCommerce Subscription plugin (renewal process)
* Order creation improvements (for seating charts add-on)

= 1.1.5.1 =
* Fixed bug with number of tickets created when placing an order with two different ticket types in cart and having specific number of items for both ticket types (11)

= 1.1.5 = 
* Fix for order ID on the order details page

= 1.1.4.9 = 
* Added Order Total column for CSV export

= 1.1.4.8 =
* Added new option "Within following time after order" under "Available dates / times for check-in" for ticket type checkins

= 1.1.4.7 =
* Added fix for the zero subtotal in confirmation email when order status is on-hold

= 1.1.4.6 =
* Added fix for WooCommerce 3.0.3 version

= 1.1.4.5 =
* Added new hook "tc_bridge_for_woocommerce_content_order_table_is_after" (which controls position of the tickets table in the email)

= 1.1.4.4 =
* Fixed issue with download table (not showing) when "Cash on Delivery Ticket Download" option is set to "yes"

= 1.1.4.3 =
* Update for WooCommerce 3.0 (fixed issues with variable names, notices etc)

= 1.1.4.2 =
* Added order by menu_order in the products query
* Added filter tc_wc_modify_get_event_ticket_types_args for developers

= 1.1.4.1 =
* Small Shortcode Builder admin performance improvements
* Fixed issue with saving extra values with custom forms add-on

= 1.1.4 =
* Fix for hiding tickets from the store

= 1.1.3.9 =
* Added new hooks for developers and fix for possible compatibility issues with future version of Tickera add-ons
* Fix for tickets quantity (WooCommerce POS plugin)

= 1.1.3.8 =
* Fixed issue with bundle plugins, because bundle and single ticket could not be added to the cart

= 1.1.3.7 =
* Fixed issue with admin order placed e-mail

= 1.1.3.6 =
* Added hooks and filters for other add-ons
* Added event name under product name
* Added support for Tickera 3.2.5.3

= 1.1.3.5 =
* Code improvements for WooCommerce ticket templates

= 1.1.3.4 =
* Fixed ticket element WordPress multisite issues

= 1.1.3.3 =
* Added new hooks for developers (and the physical tickets integration)
* Allow products with private status to show tickets via API

= 1.1.3.2 =
* Added shipping and billing info ticket template elements
* Code improvements

= 1.1.3.1 =
* Fixed issue with shortcodes

= 1.1.3 =
* Added WooCommerce order fields to CSV Export add-on
* Added WooCommerce order fields to the ticket template builder

= 1.1.2.9 =
* Added compatibility for Tickera 3.2.5

= 1.1.2.8 =
* Fixed issue with "Redirect product single post to an event" option
* Fixed notices

= 1.1.2.7 =
* Hide check-ins options by default
* Removed duplicated code (bridge-for-woocommerce.php)

= 1.1.2.6 =
* Removed duplicated code (bridge-for-woocommerce.php)

= 1.1.2.5 =
* Fixed issue when counting number of tickets sold when a product has more than 5 variations

= 1.1.2.4 =
* Added fix for clashes with some third-party themes
* Added plugin updater support for new licensing server

= 1.1.2.3 =
* Added "Ticket Check-in Availability Dates" functionality

= 1.1.2.2 =
* Added support for WooCommerce POS extension

= 1.1.2.1 =
* Removed "Client Order Placed E-Mail" (used in standard Tickera) section when Bridge is active

= 1.1.1 =
* Fixed issue with showing WooCommerce order number in the CSV export

= 1.1 =
* Fixed issue with showing tickets table on the order details page and confirmation e-mail when order doesn't contain ticket product

= 1.0.9 =
* Added new hooks for developers

= 1.0.8 =
* Added method to control resuming Woo orders

= 1.0.7 =
* Added support for the custom forms addon 1.2
* Small CSS changes in the admin area
* Replaced event shortcode on the events page in the admin (works with Tickera 3.2.2.9 version)

= 1.0.6 =
* Added functionality for trashing, deleting and untrashing associated tickets and attendee(s) data when parent order is deleted, trashed or changed status to cancelled

= 1.0.5 =
* Added support for Tickera 3.2.2.2

= 1.0.4 =
* Added option for controlling ticket delivery for "Cash on Delivery" payment gateway

= 1.0.3 =
* Added support for Tickera 3.2.1.9
* Updated fields and added tooltips

= 1.0.2 =
* Fixed issue with Order Processing and Order Completed ON/OFF email controls

= 1.0.1 =
* Removed Global Fee Scope option from the main plugin when this add-on is active
* Added additional hooks for developers

= 1.0 =
* First Release