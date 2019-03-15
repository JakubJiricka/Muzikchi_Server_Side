<?php
/*
  Plugin Name: Tickera
  Plugin URI: https://tickera.com/
  Description: Simple event ticketing system
  Author: Tickera.com
  Author URI: https://tickera.com/
  Version: 3.2.9.1
  TextDomain: tc
  Domain Path: /languages/

  Copyright 2018 Tickera (https://tickera.com/)
 */

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

if (!class_exists('TC')) {

    class TC {

        var $version = '3.2.9.1';
        var $title = 'Tickera';
        var $name = 'tc';
        var $dir_name = 'tickera';
        var $location = 'plugins';
        var $plugin_dir = '';
        var $plugin_url = '';
        var $global_cart = false;
        var $checkout_error = false;
        var $admin_menu_position = 1000;

        function __construct() {

            $this->init_vars();

            $this->maybe_set_session_path();

            $this->maybe_make_writtable_tcpdf_directory();

            require_once($this->plugin_dir . 'includes/classes/class.fields.php');

            require_once($this->plugin_dir . 'includes/classes/class.form_fields_api.php');

//load checkin api class
            require_once( $this->plugin_dir . 'includes/classes/class.checkin_api.php' );

//load sales api class
            require_once( $this->plugin_dir . 'includes/classes/class.sales_api.php' );

//load event class
            require_once( $this->plugin_dir . 'includes/classes/class.cart_form.php' );

//load event class
            require_once( $this->plugin_dir . 'includes/classes/class.event.php' );

//load events class
            require_once( $this->plugin_dir . 'includes/classes/class.events.php' );

//load general functions
            require_once( $this->plugin_dir . 'includes/general-functions.php' );

//load events search class
            require_once( $this->plugin_dir . 'includes/classes/class.events_search.php' );

//load api key class
            require_once( $this->plugin_dir . 'includes/classes/class.api_key.php' );

//load api keys class
            require_once( $this->plugin_dir . 'includes/classes/class.api_keys.php' );

//load api keys search class
            require_once( $this->plugin_dir . 'includes/classes/class.api_keys_search.php' );

//load ticket class
            require_once( $this->plugin_dir . 'includes/classes/class.ticket.php' );

//load tickets class
            require_once( $this->plugin_dir . 'includes/classes/class.tickets.php' );

//load ticket instance class
            require_once( $this->plugin_dir . 'includes/classes/class.ticket_instance.php' );

//load tickets instances class
            require_once( $this->plugin_dir . 'includes/classes/class.tickets_instances.php' );

//load tickets instances search class
            require_once( $this->plugin_dir . 'includes/classes/class.tickets_instances_search.php' );

//load tickets search class
            require_once( $this->plugin_dir . 'includes/classes/class.tickets_search.php' );

//load order class
            require_once( $this->plugin_dir . 'includes/classes/class.order.php' );

//load orders class
            require_once( $this->plugin_dir . 'includes/classes/class.orders.php' );

//load orders search class
            require_once( $this->plugin_dir . 'includes/classes/class.orders_search.php' );

//load discount class
            require_once( $this->plugin_dir . 'includes/classes/class.discount.php' );

//load discounts class
            require_once( $this->plugin_dir . 'includes/classes/class.discounts.php' );

//load discounts search class
            require_once( $this->plugin_dir . 'includes/classes/class.discounts_search.php' );

//load template class
            require_once( $this->plugin_dir . 'includes/classes/class.ticket_template.php' );

//load templates class
            require_once( $this->plugin_dir . 'includes/classes/class.ticket_templates.php' );

//load templates search class
            require_once( $this->plugin_dir . 'includes/classes/class.ticket_templates_search.php' );

//load admin pagination class
            require_once( $this->plugin_dir . 'includes/classes/class.pagination.php' );

//load general functions
            require_once( $this->plugin_dir . 'includes/classes/class.shortcodes.php' );

//load general settings class
            require_once( $this->plugin_dir . 'includes/classes/class.settings_general.php' );

//load email settings class
            require_once( $this->plugin_dir . 'includes/classes/class.settings_email.php' );

            require_once ( $this->plugin_dir . 'includes/classes/class.shortcode_builder.php' );

//Loading config first
            if (defined('TICKET_PLUGIN_TITLE')) {
                $this->title = TICKET_PLUGIN_TITLE;
            }

            if (defined('TICKET_PLUGIN_NAME')) {
                $this->name = TICKET_PLUGIN_NAME;
            }

            if (defined('TICKET_PLUGIN_DIR_NAME')) {
                $this->plugin_dir = TICKET_PLUGIN_DIR_NAME;
            }

            $this->admin_menu_position = apply_filters('tc_menu_position', 1000);

            $this->title = apply_filters('tc_plugin_title', $this->title);

            $this->name = apply_filters('tc_plugin_name', $this->name);

            $this->plugin_dir = apply_filters('tc_plugin_dir', $this->plugin_dir);

//admin css and scripts
            add_action('admin_enqueue_scripts', array(&$this, 'admin_header'));

//Add plugin admin menu
            add_action('admin_menu', array(&$this, 'add_admin_menu'));

//Add plugin newtork admin menu
            add_action('network_admin_menu', array(&$this, 'add_network_admin_menu'));

//Add plugin Settings link
            add_filter('plugin_action_links_' . plugin_basename(__FILE__), array(&$this, 'plugin_action_link'), 10, 2);

//localize the plugin
            add_action('plugins_loaded', array(&$this, 'localization'), 9);

//load add-ons
            add_action('plugins_loaded', array(&$this, 'load_addons'));

//Payment gateway returns
            add_action('pre_get_posts', array(&$this, 'handle_gateway_returns'), 1);

//Add additional rewrite rules
            add_filter('rewrite_rules_array', array(&$this, 'add_rewrite_rules'));

//Add additional query vars
            add_filter('query_vars', array($this, 'filter_query_vars'));

//Parse requests
            add_action('parse_request', array($this, 'action_parse_request'));

// Create virtual pages
            require_once( $this->plugin_dir . 'includes/classes/class.virtualpage.php' );

// Include Visual Composer shortcode
//require_once( $this->plugin_dir . 'includes/classes/class.visual_composer_shortcodes.php' );
//Register post types
            add_action('init', array(&$this, 'register_custom_posts'), 0);

            add_action('admin_init', array(&$this, 'generate_ticket_preview'), 11);

            add_action('init', array(&$this, 'checkin_api'), 0);

            add_action('init', array(&$this, 'sales_api'), 0);

            add_action('init', array(&$this, 'start_session'), 0);

            add_action('template_redirect', array(&$this, 'load_cart_scripts'));

            add_action('template_redirect', array(&$this, 'non_visible_post_types_404'));

            add_action('template_redirect', array($this, 'maybe_cancel_order'));

            add_action('init', array(&$this, 'update_cart'), 0);

            add_action('wp_enqueue_scripts', array(&$this, 'front_scripts_and_styles'));

            add_action('wp_ajax_nopriv_add_to_cart', array(&$this, 'add_to_cart'));

            add_action('wp_ajax_add_to_cart', array(&$this, 'add_to_cart'));

            add_action('wp_ajax_nopriv_update_cart_widget', array(&$this, 'update_cart_widget'));

            add_action('wp_ajax_update_cart_widget', array(&$this, 'update_cart_widget'));

            add_action('wp_ajax_change_order_status', array(&$this, 'change_order_status_ajax'));

            add_action('wp_ajax_change_event_status', array(&$this, 'change_event_status'));
            add_action('wp_ajax_change_ticket_status', array(&$this, 'change_ticket_status'));

            add_action('wp_ajax_save_attendee_info', array(&$this, 'save_attendee_info'));

            add_action('wp_ajax_tc_remove_order_session_data', array(&$this, 'ajax_remove_order_session_data'));
            add_action('wp_ajax_nopriv_tc_remove_order_session_data', array(&$this, 'ajax_remove_order_session_data'));

            add_action('wp_ajax_tc_remove_order_session_data_only', array(&$this, 'ajax_remove_order_session_data_only'));
            add_action('wp_ajax_nopriv_tc_remove_order_session_data_only', array(&$this, 'ajax_remove_order_session_data_only'));

            add_action('wp_ajax_tc_update_widget_cart', 'tc_update_widget_cart');
            add_action('wp_ajax_nopriv_tc_update_widget_cart', 'tc_update_widget_cart');

            add_filter('tc_cart_currency_and_format', array(&$this, 'get_cart_currency_and_format'));

            register_activation_hook(__FILE__, array($this, 'activation'));

            add_action("admin_init", array(&$this, "activation"));

            add_action("activated_plugin", array(&$this, "load_this_plugin_first"));

            add_filter('tc_order_confirmation_message_content', array(&$this, 'tc_order_confirmation_message_content'), 10, 2);

            add_action('admin_notices', array(&$this, 'admin_permalink_message'));

            add_action('admin_notices', array(&$this, 'admin_debug_notices_message'));

            add_action('tc_before_cart_submit', array(&$this, 'tc_add_age_check'));

            $tc_general_settings = get_option('tc_general_setting', false);

            $tc_email_settings = get_option('tc_email_setting', false);

            if ((isset($tc_general_settings['show_cart_menu_item']) && $tc_general_settings['show_cart_menu_item'] == 'yes')) {
                add_filter('wp_nav_menu_objects', array(&$this, 'main_navigation_links'), 10, 2);
            }

            if ((isset($tc_general_settings['show_cart_menu_item']) && $tc_general_settings['show_cart_menu_item'] == 'yes')) {

                $theme_location = 'primary';

                if (!has_nav_menu($theme_location)) {
                    $theme_locations = get_nav_menu_locations();
                    foreach ((array) $theme_locations as $key => $location) {
                        $theme_location = $key;
                        break;
                    }
                }

                if (!has_nav_menu($theme_location)) {
                    add_filter('wp_page_menu', array(&$this, 'main_navigation_links_fallback'), 20, 2);
                }
            }

            add_filter('comments_open', array(&$this, 'comments_open'), 10, 2);

            add_filter("comments_template", array(&$this, 'no_comments_template'));

//load cart widget
            require_once( $this->plugin_dir . 'includes/widgets/cart-widget.php' );
            require_once( $this->plugin_dir . 'includes/widgets/upcoming-events-widget.php' );

            add_action('admin_init', array(&$this, 'generate_pdf_ticket'), 0);

            add_action('init', array(&$this, 'generate_pdf_ticket_front'), 11);

            add_action('admin_print_styles', array(&$this, 'add_notices'));

            add_action('admin_init', array(&$this, 'install_actions'));

            add_filter('wp_get_nav_menu_items', array(&$this, 'remove_unnecessary_plugin_menu_items'), 10, 1);
            add_filter('wp_page_menu_args', array(&$this, 'remove_unnecessary_plugin_menu_items_wp_page_menu_args'), 10, 1);

            add_action('pre_get_posts', array(&$this, 'tc_events_front_page'), 10, 1);

//Update permissions if new version of the plugin is installed / updated
            /* if ( is_admin() ) {
              $current_version = get_option( 'tc_version', false );

              if ( version_compare( $current_version, $this->version, '=' ) ) {

              } else { */


            add_action('admin_init', array(&$this, 'add_required_capabilities'));

            add_action('tc_delete_plugins_data', array($this, 'tc_delete_plugins_data'), 10, 1);
            /* }
              } */
        }

        public static function rrmdir($dir) {
            if (is_dir($dir)) {
                $objects = scandir($dir);
                foreach ($objects as $object) {
                    if ($object != "." && $object != "..") {
                        if (filetype($dir . "/" . $object) == "dir")
                            @rrmdir($dir . "/" . $object);
                        else
                            @unlink($dir . "/" . $object);
                    }
                }
                @reset($objects);
                @rmdir($dir);
            }
        }

        function tc_delete_plugins_data($submitted_data) {
            if (array_key_exists('tickera', $submitted_data)) {
                global $wpdb;

                //Delete posts and post metas
                $wpdb->query("
                DELETE 
                p, pm
                FROM $wpdb->posts p 
                JOIN $wpdb->postmeta pm on pm.post_id = p.id
		 WHERE p.post_type IN ('tc_events', 'tc_tickets', 'tc_api_keys', 'tc_tickets_instances', 'tc_templates', 'tc_orders', 'tc_discounts')
		"
                );

                //Delete options
                $options = array('tc_wizard_step', 'tc_wizard_mode', 'tc_email_setting', 'tc_settings', 'tc_general_setting', 'tc_cart_page_id', 'tc_payment_page_id', 'tc_confirmation_page_id', 'tc_order_page_id', 'tc_process_payment_page_id', 'tc_process_payment_use_virtual', 'tc_ipn_page_id', 'tc_ipn_use_virtual', 'tc_needs_pages', 'tc_version');

                foreach ($options as $option) {
                    delete_option($option);
                }

                //Delete directories and files
                $upload = wp_upload_dir();
                $upload_dir = $upload['basedir'];
                $upload_dir = $upload_dir . '/sessions';

                TC::rrmdir($upload_dir);
            }
        }

        function tc_add_age_check() {

            $tc_general_settings = get_option('tc_general_setting', false);
            $tc_age_check = isset($tc_general_settings['show_age_check']) ? $tc_general_settings['show_age_check'] : 'no';
            $tc_age_text = isset($tc_general_settings['age_text']) ? $tc_general_settings['age_text'] : 'I hereby declare that I am 16 years or older';

            if ($tc_age_check == 'yes') {
                echo '<label class="tc-age-check-label"><input type="checkbox" id="tc_age_check" />' . $tc_age_text . '</label>';
            }
        }

        function tc_events_front_page($query) {
            $tc_general_settings = get_option('tc_general_setting', false);
            $show_events_as_front_page = isset($tc_general_settings['show_events_as_front_page']) ? $tc_general_settings['show_events_as_front_page'] : 'no';

            if ($show_events_as_front_page == 'no' || 'posts' !== get_option('show_on_front')) {
                return $query; //do not modify the query
            }

// Only filter the main query on the front-end
            if (is_admin() || !$query->is_main_query()) {
                return;
            }

            global $wp;
            $front = false;

// If the latest posts are showing on the home page
//if ( ( is_home() && empty( $wp->query_string ) ) ) {
            if (( is_home() || is_front_page() && empty($wp->query_string))) {
                $front = true;
            }

// If a static page is set as the home page
            if (( $query->get('page_id') == get_option('page_on_front') && get_option('page_on_front') ) || empty($wp->query_string)) {
                $front = true;
            }

            if ($front) :

                $query->set('post_type', 'tc_events');
                $query->set('page_id', '');

// Set properties to match an archive
                $query->is_page = 0;
                $query->is_singular = 0;
                $query->is_post_type_archive = 1;
                $query->is_archive = 1;

            endif;
        }

        function maybe_make_writtable_tcpdf_directory() {
            try {
                $tcpdf_cache_directory = $this->plugin_dir . 'includes/tcpdf/cache/';
                if (!@is_writable($tcpdf_cache_directory)) {

                    if (!is_dir($tcpdf_cache_directory)) {
                        @mkdir($tcpdf_cache_directory, 0755);
                    } else {
                        @chmod($tcpdf_cache_directory, 0755);
                    }

                    $filename = '.htaccess';
                    $path = $tcpdf_cache_directory . '/' . $filename;
                    if (!file_exists($path)) {
                        $htaccess = @fopen($path, "w");
                        $content = "Deny from all";
                        @fwrite($htaccess, $content);
                        @fclose($htaccess);
                        @chmod($path, 0644);
                    }
                }
            } catch (Exception $e) {
//tcpdf directory cannot be created or permissions cannot set to 0777
            }
        }

        function maybe_set_session_path() {
            $session_save_path = session_save_path();

            $tc_general_settings = get_option('tc_general_setting', false);
            $create_and_force_new_session_path = isset($tc_general_settings['create_and_force_new_session_path']) ? $tc_general_settings['create_and_force_new_session_path'] : 'no';

            if ($create_and_force_new_session_path == 'no') {
                $create_and_force_new_session_path = false;
            } else {
                $create_and_force_new_session_path = true;
            }

            if (substr($session_save_path, 0, 6) == 'tcp://') {//for memcache (memcacheD shoult be without tcp://)
//skip the check, sessions are saved in memory and we'll assume that is properly configured
            } else {
//Check for file-based sessions
                try {
                    if (!@is_writable(session_save_path()) || $create_and_force_new_session_path) {
                        $upload = wp_upload_dir();
                        $upload_dir = $upload['basedir'];
                        $upload_dir = $upload_dir . '/sessions';

                        if (!is_dir($upload_dir)) {
                            @mkdir($upload_dir, 0755);
                        }

                        $filename = '.htaccess';
                        $path = $upload_dir . '/' . $filename;
                        if (!file_exists($path)) {
                            $htaccess = @fopen($path, "w");
                            $content = "Deny from all";
                            @fwrite($htaccess, $content);
                            @fclose($htaccess);
                            @chmod($path, 0644);
                        }

                        @ini_set("session.save_handler", "files");
                        @session_save_path($upload_dir);
                    }
                } catch (Exception $e) {
//sessions don't work, save path is not writable
                }
            }
        }

        function save_attendee_info() {
            if (isset($_POST['post_id']) && isset($_POST['meta_name']) && isset($_POST['meta_value'])) {
                update_post_meta((int) $_POST['post_id'], sanitize_text_field($_POST['meta_name']), sanitize_text_field($_POST['meta_value']));
            }
        }

        /**
         * Install actions such as installing pages when a button is clicked.
         */
        function install_actions() {
// Install - Add pages button
            if (!empty($_GET['install_tickera_pages'])) {

                self::create_pages();

// Settings redirect
                wp_redirect(admin_url('edit.php?post_type=tc_events&page=tc_settings'));
                exit;
            }
        }

        function create_pages() {
            $pages = apply_filters('tc_create_pages', array(
                'cart' => array(
                    'name' => _x('tickets-cart', 'Page slug', 'tc'),
                    'title' => _x('Cart', 'Page title', 'tc'),
                    'content' => '[' . apply_filters('tc_cart_shortcode_tag', 'tc_cart') . ']'
                ),
                'payment' => array(
                    'name' => _x('tickets-payment', 'Page slug', 'tc'),
                    'title' => _x('Payment', 'Page title', 'tc'),
                    'content' => '[' . apply_filters('tc_payment_shortcode_tag', 'tc_payment') . ']'
                ),
                'confirmation' => array(
                    'name' => _x('tickets-order-confirmation', 'Page slug', 'tc'),
                    'title' => _x('Payment Confirmation', 'Page title', 'tc'),
                    'content' => '[' . apply_filters('tc_order_confirmation_shortcode_tag', 'tc_order_confirmation') . ']'
                ),
                'order' => array(
                    'name' => _x('tickets-order-details', 'Page slug', 'tc'),
                    'title' => _x('Order Details', 'Page title', 'tc'),
                    'content' => '[' . apply_filters('tc_order_details_shortcode_tag', 'tc_order_details') . ']'
                ),
                'process_payment' => array(
                    'name' => _x('tickets-process-payment', 'Page slug', 'tc'),
                    'title' => _x('Process Payment', 'Page title', 'tc'),
                    'content' => '[' . apply_filters('tc_process_payment_shortcode_tag', 'tc_process_payment') . ']'
                ),
                'ipn' => array(
                    'name' => _x('tickets-ipn-payment', 'Page slug', 'tc'),
                    'title' => _x('IPN', 'Page title', 'tc'),
                    'content' => '[' . apply_filters('tc_ipn_shortcode_tag', 'tc_ipn') . ']'
                ),
            ));

            foreach ($pages as $key => $page) {
                tc_create_page(esc_sql($page['name']), 'tc_' . $key . '_page_id', $page['title'], $page['content'], '');
            }

            update_option('tc_needs_pages', 0);

            flush_rewrite_rules();
        }

        function add_notices() {
            if (get_option('tc_needs_pages', 1) == 1) {
                add_action('admin_notices', array($this, 'install_notice'));
            }
        }

        function install_notice() {
            global $tc;
// If we have just installed, show a message with the install pages button
            if (get_option('tc_needs_pages', 1) == 1) {
                include( 'includes/install-notice.php' );
            }
        }

        function generate_pdf_ticket_front() {

            $order_key = isset($_GET['order_key']) ? sanitize_key($_GET['order_key']) : '';
            $ticket = isset($_GET['download_ticket']) ? (int) $_GET['download_ticket'] : '';
            $template_id = isset($_GET['template_id']) ? (int) $_GET['template_id'] : false;
            $not_forced_output = isset($_GET['not_forced_output']) ? true : false;

//if ( isset( $_GET[ 'download_ticket_nonce' ] ) && wp_verify_nonce( $_GET[ 'download_ticket_nonce' ], 'download_ticket_' . (int) $_GET[ 'download_ticket' ] . '_' . $order_key ) ) {
            if (!empty($order_key) && !empty($ticket)) {

                $tc_general_settings = get_option('tc_general_setting', false);
                $order_id = wp_get_post_parent_id($ticket);
                $post_author = get_post_field('post_author', $order_id);


                if (isset($tc_general_settings['force_login']) && $tc_general_settings['force_login'] == 'yes' && (!is_user_logged_in() || (is_user_logged_in() && $post_author != get_current_user_id()))) {
                    if (!current_user_can('manage_options')) {
                        $redirect_url = (is_ssl() ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                        $tc_force_login_url = apply_filters('tc_force_login_download_url', wp_login_url($redirect_url), wp_login_url($redirect_url));
                        wp_redirect(wp_login_url($tc_force_login_url));
                        exit;
                    }
                }

                $order = new TC_Order($order_id);
                $order_date = strtotime($order->details->post_date);
                $order_modified = strtotime($order->details->post_modified);
                $tc_order_date = $order->details->tc_order_date;
                $alt_paid_date = $order->details->_tc_paid_date;

                if ($order_key == $order_date || $order_key == $order_modified || $order_key == $tc_order_date || $alt_paid_date == $order_key) {
                    $templates = new TC_Ticket_Templates();
                    if ($not_forced_output) {
                        $force_download = false;
                    } else {
                        $force_download = true;
                    }
                    $templates->generate_preview((int) $_GET['download_ticket'], $force_download, $template_id);
                }
            }
//}
        }

        function generate_pdf_ticket() {
            if (isset($_GET['action']) && $_GET['action'] == 'preview' && isset($_GET['page']) && $_GET['page'] == 'tc_ticket_templates') {
                if (isset($_GET['ID'])) {
                    $templates = new TC_Ticket_Templates();
                    $templates->generate_preview(false, false, (int) $_GET['ID']);
                }
                if (isset($_GET['ticket_type_id'])) {
                    $templates = new TC_Ticket_Templates();
                    $templates->generate_preview(false, false, (int) $_GET['template_id'], (int) $_GET['ticket_type_id']);
                }
            }
        }

        function no_comments_template($template) {
            global $post;

            if ('virtual_page' == $post->post_type) {
                $template = $this->plugin_dir . 'includes/templates/no-comments.php';
            }

            return $template;
        }

        function comments_open($open, $post_id) {
            global $wp;


            $cart_page_id = get_option('tc_cart_page_id', false);
            $payment_page_id = get_option('tc_payment_page_id', false);
            $confirmation_page_id = get_option('tc_confirmation_page_id', false);
            $order_page_id = get_option('tc_order_page_id', false);

            $current_post = get_post($post_id);

            if ($current_post && ($current_post->post_type == 'virtual_page' || $post_id == $cart_page_id || $post_id == $payment_page_id || $post_id == $confirmation_page_id || $post_id == $order_page_id)) {
                $open = false;
            }

            return $open;
        }

        function activation() {
            global $pagenow, $wp_rewrite;

            if ($pagenow == 'plugins.php') {//add caps on plugin page so other plugins can hook and add their own caps if needed
                $this->add_default_posts_and_metas();
                $this->add_required_capabilities();
                $wp_rewrite->flush_rules();

                if (current_user_can('manage_options')) {//show wizard only to admins
                    if (get_option('tc_wizard_step', false) == false) {
                        if (get_option('tc_general_setting', false) == false) {
                            wp_redirect(admin_url('index.php?page=tc-installation-wizard'));
                            tc_js_redirect(admin_url('index.php?page=tc-installation-wizard')); //Fallback to JS redirect if something goes wrong
                        }
                    }
                }
            }
        }

        function add_required_capabilities() {

            $admin_role = get_role('administrator');

            $admin_capabilities = array_keys($this->admin_capabilities());

            foreach ($admin_capabilities as $cap) {
                if (!isset($admin_role->capabilities[$cap])) {
                    if ($admin_role) {
                        $admin_role->add_cap($cap);
                    } else {
                        //do nothing
                    }
                }
            }

            $staff_role = get_role('staff');

            if ($staff_role == null) {
                add_role('staff', 'Staff');
            }

            foreach ($this->staff_capabilities() as $cap => $value) {
                if ($value == 1) {
                    if (!isset($staff_role->capabilities[$cap])) {
                        if ($staff_role) {
                            $staff_role->add_cap($cap);
                        } else {
                            //do nothing
                        }
                    }
                }
            }
        }

        function add_default_posts_and_metas() {
            global $wpdb;

            $template_count = (int) $wpdb->get_var("SELECT COUNT(ID) FROM $wpdb->posts WHERE post_type = 'tc_templates' AND post_status = 'publish'");

            /* Add Default Ticket Template */
            if ($template_count == 0) {
                $post = array(
                    'post_content' => '',
                    'post_status' => 'publish',
                    'post_title' => __('Default', 'tc'),
                    'post_type' => 'tc_templates',
                );

                $post = apply_filters('tc_template_post', $post);
                $post_id = wp_insert_post($post);

                /* Add post metas for the template */
                if ($post_id != 0) {
                    update_post_meta($post_id, 'tc_event_logo_element_cell_alignment', 'left');
                    update_post_meta($post_id, 'tc_event_logo_element_top_padding', '0');
                    update_post_meta($post_id, 'tc_event_logo_element_bottom_padding', '3');
                    update_post_meta($post_id, 'tc_event_terms_element_font_size', '12');
                    update_post_meta($post_id, 'tc_event_terms_element_font_style', '');
                    update_post_meta($post_id, 'tc_event_terms_element_font_color', '#7a7a7a');
                    update_post_meta($post_id, 'tc_event_terms_element_cell_alignment', 'left');
                    update_post_meta($post_id, 'tc_event_terms_element_top_padding', '1');
                    update_post_meta($post_id, 'tc_event_terms_element_bottom_padding', '1');
                    update_post_meta($post_id, 'tc_ticket_qr_code_element_qr_code_size', '50');
                    update_post_meta($post_id, 'tc_ticket_qr_code_element_cell_alignment', 'center');
                    update_post_meta($post_id, 'tc_ticket_qr_code_element_top_padding', '1');
                    update_post_meta($post_id, 'tc_ticket_qr_code_element_bottom_padding', '1');
                    update_post_meta($post_id, 'tc_event_location_element_font_size', '16');
                    update_post_meta($post_id, 'tc_event_location_element_font_style', '');
                    update_post_meta($post_id, 'tc_event_location_element_font_color', '#000000');
                    update_post_meta($post_id, 'tc_event_location_element_cell_alignment', 'center');
                    update_post_meta($post_id, 'tc_event_location_element_top_padding', '0');
                    update_post_meta($post_id, 'tc_event_location_element_bottom_padd', '0');
                    update_post_meta($post_id, 'tc_ticket_type_element_font_size', '18');
                    update_post_meta($post_id, 'tc_ticket_type_element_font_style', 'B');
                    update_post_meta($post_id, 'tc_ticket_type_element_font_color', '#e54c2d');
                    update_post_meta($post_id, 'tc_ticket_type_element_cell_alignment', 'right');
                    update_post_meta($post_id, 'tc_ticket_type_element_top_padding', '1');
                    update_post_meta($post_id, 'tc_ticket_type_element_bottom_padding', '3');

                    update_post_meta($post_id, 'rows_1', 'tc_event_logo_element,tc_ticket_type_element');
                    update_post_meta($post_id, 'tc_event_date_time_element_font_size', '16');
                    update_post_meta($post_id, 'tc_event_date_time_element_font_style', '');
                    update_post_meta($post_id, 'tc_event_date_time_element_font_color', '#000000');
                    update_post_meta($post_id, 'tc_event_date_time_element_cell_alignment', 'center');
                    update_post_meta($post_id, 'tc_event_date_time_element_top_padding', '2');
                    update_post_meta($post_id, 'tc_event_date_time_element_bottom_padding', '0');

                    update_post_meta($post_id, 'rows_2', 'tc_event_name_element');
                    update_post_meta($post_id, 'tc_event_name_element_font_size', '60');
                    update_post_meta($post_id, 'tc_event_name_element_font_style', '');
                    update_post_meta($post_id, 'tc_event_name_element_font_color', '#000000');
                    update_post_meta($post_id, 'tc_event_name_element_cell_alignment', 'center');
                    update_post_meta($post_id, 'tc_event_name_element_top_padding', '0');
                    update_post_meta($post_id, 'tc_event_name_element_bottom_padding', '0');

                    update_post_meta($post_id, 'rows_3', 'tc_event_date_time_element');
                    update_post_meta($post_id, 'tc_ticket_owner_name_element_font_size', '20');
                    update_post_meta($post_id, 'tc_ticket_owner_name_element_font_color', '#e54c2d');
                    update_post_meta($post_id, 'tc_ticket_owner_name_element_cell_alignment', 'center');
                    update_post_meta($post_id, 'tc_ticket_owner_name_element_top_padding', '3');
                    update_post_meta($post_id, 'tc_ticket_owner_name_element_bottom_padding', '3');

                    update_post_meta($post_id, 'rows_4', 'tc_event_location_element');

                    update_post_meta($post_id, 'rows_5', 'tc_ticket_owner_name_element');

                    update_post_meta($post_id, 'rows_6', 'tc_ticket_description_element');

                    update_post_meta($post_id, 'rows_7', 'tc_ticket_qr_code_element');

                    update_post_meta($post_id, 'rows_8', 'tc_event_terms_element');

                    update_post_meta($post_id, 'rows_9', '');

                    update_post_meta($post_id, 'rows_10', '');

                    update_post_meta($post_id, 'rows_number', '10');

                    update_post_meta($post_id, 'document_font', 'helvetica');
                    update_post_meta($post_id, 'document_ticket_size', 'A4');
                    update_post_meta($post_id, 'document_ticket_orientation', 'P');
                    update_post_meta($post_id, 'document_ticket_top_margin', '10');
                    update_post_meta($post_id, 'document_ticket_right_margin', '10');
                    update_post_meta($post_id, 'document_ticket_left_margin', '10');
                    update_post_meta($post_id, 'document_ticket_background_image', '');

                    update_post_meta($post_id, 'tc_ticket_barcode_element_barcode_type', 'C128');
                    update_post_meta($post_id, 'tc_ticket_barcode_element_barcode_text_visibility', 'visible');
                    update_post_meta($post_id, 'tc_ticket_barcode_element_1d_barcode_size', '50');
                    update_post_meta($post_id, 'tc_ticket_barcode_element_font_size', '8');
                    update_post_meta($post_id, 'tc_ticket_barcode_element_cell_alignment', 'left');
                    update_post_meta($post_id, 'tc_ticket_barcode_element_top_padding', '0');
                    update_post_meta($post_id, 'tc_ticket_barcode_element_bottom_padding', '0');

                    update_post_meta($post_id, 'tc_ticket_description_element_font_size', '12');
                    update_post_meta($post_id, 'tc_ticket_description_element_font_style', '');
                    update_post_meta($post_id, 'tc_ticket_description_element_font_color', '#0a0a0a');
                    update_post_meta($post_id, 'tc_ticket_description_element_cell_alignment', 'left');
                    update_post_meta($post_id, 'tc_ticket_description_element_top_padding', '0');
                    update_post_meta($post_id, 'tc_ticket_description_element_bottom_padding', '2');
                    update_post_meta($post_id, 'tc_event_location_element_bottom_padding', '0');
                    update_post_meta($post_id, 'tc_ticket_owner_name_element_font_style', '');
                }
            }

            /* Add random default API Key */

            $api_key_count = (int) $wpdb->get_var("SELECT COUNT(ID) FROM $wpdb->posts WHERE post_type = 'tc_api_keys' AND post_status = 'publish'");

            if ($api_key_count == 0) {
                $post = array(
                    'post_content' => '',
                    'post_status' => 'publish',
                    'post_title' => __('Default', 'tc'),
                    'post_type' => 'tc_api_keys',
                );

                $post = apply_filters('tc_api_key_default_post', $post);
                $post_id = wp_insert_post($post);

                /* Add post metas for the API Key */
                $api_keys = new TC_API_Keys();

                if ($post_id != 0) {
                    update_post_meta($post_id, 'event_name', 'all');
                    update_post_meta($post_id, 'api_key_name', 'Default - All Events');
                    update_post_meta($post_id, 'api_key', $api_keys->get_rand_api_key());
                    update_post_meta($post_id, 'api_username', '');
                }
            }
        }

        function admin_permalink_message() {
            if (current_user_can('manage_options') && !get_option('permalink_structure')) {
                echo '<div class="error"><p>';
                echo '<strong>' . $this->title . '</strong>';
                _e(' is almost ready. ', 'tc');
                printf(__('You must %s to something other than the default for it to work.', 'tc'), '<a href="options-permalink.php">' . __('update your permalink structure', 'tc') . '</a>');
                echo '</p></div>';
            }
        }

        function is_wp_debug_enabled() {
            if (((defined('WP_DEBUG') && WP_DEBUG) && (defined('WP_DEBUG_DISPLAY') && WP_DEBUG_DISPLAY)) || ((defined('WP_DEBUG') && WP_DEBUG) && !defined('WP_DEBUG_DISPLAY'))) {
                return true;
            } else {
                return false;
            }
        }

        function is_tc_debug_enabled() {
            if (defined('TC_DEBUG')) {
                return true;
            } else {
                return false;
            }
        }

        /**
         * Make sure that debug messages are not shown
         */
        function admin_debug_notices_message() {
            if (current_user_can('manage_options')) {

                if ($this->is_tc_debug_enabled() || $this->is_wp_debug_enabled()) {
                    echo '<div class="notice notice-warning"><p>';
                    if ($this->is_tc_debug_enabled() && $this->is_wp_debug_enabled()) {//both are enabled
                        printf(__('It is recommended to turn off both %s and %s on a production site.', 'tc'), '<strong>TC_DEBUG</strong>', '<strong>WP_DEBUG</strong>');
                        printf(__(' Remove %s line from wp-config.php file.', 'tc'), '<i><strong>define(\'TC_DEBUG\', true);</strong></i>');
                        printf(__(' Edit wp-config.php file and set the the WP_DEBUG value like this: %s or add additional line %s to the wp-config.php', 'tc'), '<strong>define(\'WP_DEBUG\', false);</strong>', '<strong><i>define( \'WP_DEBUG_DISPLAY\', false );</i></strong>');
                    } else if ($this->is_tc_debug_enabled() && !$this->is_wp_debug_enabled()) {//Only TC_DEBUG is enabled
                        printf(__('It is recommended to turn off %s on a production site.', 'tc'), '<strong>TC_DEBUG</strong>');
                        printf(__(' Remove %s line from wp-config.php file.', 'tc'), '<i><strong>define(\'TC_DEBUG\', true);</strong></i>');
                    } else if (!$this->is_tc_debug_enabled() && $this->is_wp_debug_enabled()) {//Only WP_DEBUG is enabled
                        printf(__('It is recommended to turn off %s on a production site.', 'tc'), '<strong>WP_DEBUG</strong>');
                        printf(__(' Edit wp-config.php file and set the the WP_DEBUG value like this: %s or add additional line %s to the wp-config.php', 'tc'), '<strong>define(\'WP_DEBUG\', false);</strong>', '<strong><i>define( \'WP_DEBUG_DISPLAY\', false );</i></strong>');
                    } else {
                        //all good, do nothing
                    }
                    echo '</p></div>';
                }
            }
        }

        function get_license_key() {
            $tc_general_settings = get_option('tc_general_setting', false);
            $license_key = (defined('TC_LCK') && TC_LCK !== '') ? TC_LCK : (isset($tc_general_settings['license_key']) && $tc_general_settings['license_key'] !== '' ? $tc_general_settings['license_key'] : '');
            return $license_key;
        }

        function admin_capabilities() {
            $capabilities = array(
                'manage_events_cap' => 1, //for older versions
                'manage_ticket_types_cap' => 1, //for older versions
                'manage_discount_codes_cap' => 1,
                'manage_orders_cap' => 1,
                'manage_attendees_cap' => 1,
                'manage_ticket_templates_cap' => 1,
                'delete_checkins_cap' => 1,
                'delete_attendees_cap' => 1,
                'manage_ticket_templates_cap' => 1,
                'manage_settings_cap' => 1,
                'save_ticket_cap' => 1, //for older versions
                'add_discount_cap' => 1,
//For events
                'publish_tc_events' => 1,
                'edit_tc_events' => 1,
                'edit_others_tc_events' => 1,
                'delete_tc_events' => 1,
                'delete_others_tc_events' => 1,
                'read_private_tc_events' => 1,
                'edit_tc_event' => 1,
                'delete_tc_event' => 1,
                'read_tc_event' => 1,
                'edit_published_tc_events' => 1,
                'edit_private_tc_events' => 1,
                'delete_private_tc_events' => 1,
                'delete_published_tc_events' => 1,
                'create_tc_events' => 1,
                //for ticket types
                'publish_tc_tickets' => 1,
                'edit_tc_tickets' => 1,
                'edit_tc_ticket' => 1,
                'edit_others_tc_tickets' => 1,
                'delete_tc_tickets' => 1,
                'delete_others_tc_tickets' => 1,
                'read_private_tc_tickets' => 1,
                'delete_tc_ticket' => 1,
                'read_tc_ticket' => 1,
                'edit_published_tc_tickets' => 1,
                'edit_private_tc_tickets' => 1,
                'delete_private_tc_tickets' => 1,
                'delete_published_tc_tickets' => 1,
                'create_tc_tickets' => 1,
                //for ticket instances (attendees & tickets)
                'edit_tc_tickets_instance' => 1,
                'read_tc_tickets_instance' => 1,
                'delete_tc_tickets_instance' => 1,
                'create_tc_tickets_instances' => 1,
                'edit_tc_tickets_instances' => 1,
                'edit_others_posts_tc_tickets_instances' => 1,
                'publish_tc_tickets_instances' => 1,
                'read_private_tc_tickets_instances' => 1,
                'delete_tc_tickets_instances' => 1,
                'delete_private_tc_tickets_instances' => 1,
                'delete_published_tc_tickets_instances' => 1,
                'delete_others_tc_tickets_instances' => 1,
                'edit_private_tc_tickets_instances' => 1,
                'edit_published_tc_tickets_instances' => 1,
                //for orders
                'edit_tc_order' => 1,
                'read_tc_order' => 1,
                'delete_tc_order' => 1,
                'create_tc_orders' => 1,
                'edit_tc_orders' => 1,
                'edit_others_posts_tc_orders' => 1,
                'publish_tc_orders' => 1,
                'read_private_tc_orders' => 1,
                'delete_tc_orders' => 1,
                'delete_private_tc_orders' => 1,
                'delete_published_tc_orders' => 1,
                'delete_others_tc_orders' => 1,
                'edit_private_tc_orders' => 1,
                'edit_published_tc_orders' => 1,
                //general
                'read' => 1,
            );

            $role = get_role('administrator');

            return apply_filters('tc_admin_capabilities', array_merge($capabilities, $role->capabilities));
        }

        function staff_capabilities() {
            $capabilities = array(
                'manage_events_cap' => 0, //for older versions
                'manage_ticket_types_cap' => 0, //for older versions
                'manage_discount_codes_cap' => 0,
                'manage_orders_cap' => 0,
                'manage_attendees_cap' => 0,
                'manage_ticket_templates_cap' => 0,
                'delete_checkins_cap' => 0,
                'delete_attendees_cap' => 0,
                'manage_ticket_templates_cap' => 0,
                'manage_settings_cap' => 0,
                'save_ticket_cap' => 0, //for older versions
                'add_discount_cap' => 0,
//For events
                'publish_tc_events' => 0,
                'edit_tc_events' => 1,
                'edit_others_tc_events' => 0,
                'delete_tc_events' => 0,
                'delete_others_tc_events' => 0,
                'read_private_tc_events' => 0,
                'edit_tc_event' => 0,
                'delete_tc_event' => 0,
                'read_tc_event' => 0,
                'edit_published_tc_events' => 0,
                'edit_private_tc_events' => 0,
                'delete_private_tc_events' => 0,
                'delete_published_tc_events' => 0,
                'create_tc_events' => 0,
                //for ticket types
                'publish_tc_tickets' => 0,
                'edit_tc_tickets' => 0,
                'edit_tc_ticket' => 0,
                'edit_others_tc_tickets' => 0,
                'delete_tc_tickets' => 0,
                'delete_others_tc_tickets' => 0,
                'read_private_tc_tickets' => 0,
                'delete_tc_ticket' => 0,
                'read_tc_ticket' => 0,
                'edit_published_tc_tickets' => 0,
                'edit_private_tc_tickets' => 0,
                'delete_private_tc_tickets' => 0,
                'delete_published_tc_tickets' => 0,
                'create_tc_tickets' => 0,
                //for ticket instances (attendees & tickets)
                'edit_tc_tickets_instance' => 1,
                'read_tc_tickets_instance' => 1,
                'delete_tc_tickets_instance' => 0,
                'create_tc_tickets_instances' => 1,
                'edit_tc_tickets_instances' => 1,
                'edit_others_posts_tc_tickets_instances' => 1,
                'publish_tc_tickets_instances' => 1,
                'read_private_tc_tickets_instances' => 1,
                'delete_tc_tickets_instances' => 0,
                'delete_private_tc_tickets_instances' => 0,
                'delete_published_tc_tickets_instances' => 0,
                'delete_others_tc_tickets_instances' => 0,
                'edit_private_tc_tickets_instances' => 1,
                'edit_published_tc_tickets_instances' => 1,
                //for orders
                'edit_tc_order' => 0,
                'read_tc_order' => 0,
                'delete_tc_order' => 0,
                'create_tc_orders' => 0,
                'edit_tc_orders' => 0,
                'edit_others_posts_tc_orders' => 0,
                'publish_tc_orders' => 0,
                'read_private_tc_orders' => 0,
                'delete_tc_orders' => 0,
                'delete_private_tc_orders' => 0,
                'delete_published_tc_orders' => 0,
                'delete_others_tc_orders' => 0,
                'edit_private_tc_orders' => 0,
                'edit_published_tc_orders' => 0,
                //Required because WooCommerce doesn't allow access to admin otherwise
                'edit_posts' => 1,
                'read' => 1,
            );

            $role = get_role('staff');

            return apply_filters('tc_staff_capabilities', array_merge($capabilities, $role->capabilities));
        }

//adds plugin links to custom theme nav menus using wp_nav_menu()
        function main_navigation_links($sorted_menu_items, $args) {
            if (!is_admin()) {

                $theme_location = 'primary';

                if (!has_nav_menu($theme_location)) {
                    $theme_locations = get_nav_menu_locations();
                    foreach ((array) $theme_locations as $key => $location) {
                        $theme_location = $key;
                        break;
                    }
                }

                if ($args->theme_location == $theme_location) {//put extra menu items only in primary menu
                    $cart_link = new stdClass;

                    $cart_link->title = apply_filters('tc_cart_page_link_title', __('Cart', 'tc'));
                    $cart_link->menu_item_parent = 0;
                    $cart_link->ID = 'tc_cart';
                    $cart_link->db_id = '';
                    $cart_link->url = $this->get_cart_slug(true);

                    if (tc_current_url() == $cart_link->url) {
                        $cart_link->classes[] = 'current_page_item';
                    }

                    $sorted_menu_items[] = $cart_link;

                    return $sorted_menu_items;
                }
            }
        }

        function main_navigation_links_fallback($current_menu) {

            if (!is_admin()) {

                $cart_link = new stdClass;

                $cart_link->title = apply_filters('tc_cart_page_link_title', __('Cart', 'tc'));
                $cart_link->menu_item_parent = 0;
                $cart_link->ID = 'tc_cart';
                $cart_link->db_id = '';
                $cart_link->url = $this->get_cart_slug(true);

                if (tc_current_url() == $cart_link->url) {
                    $cart_link->classes[] = 'current_page_item';
                }

                $main_sorted_menu_items[] = $cart_link;
                ?>
                <div class="menu">
                    <ul class='nav-menu'>
                        <?php
                        foreach ($main_sorted_menu_items as $menu_item) {
                            ?>
                            <li class='menu-item-<?php echo $menu_item->ID; ?>'><a id="<?php echo $menu_item->ID; ?>" href="<?php echo $menu_item->url; ?>"><?php echo $menu_item->title; ?></a>
                                <?php if ($menu_item->db_id !== '') { ?>
                                    <ul class="sub-menu dropdown-menu">
                                        <?php
                                        foreach ($sub_sorted_menu_items as $menu_item) {
                                            ?>
                                            <li class='menu-item-<?php echo $menu_item->ID; ?>'><a id="<?php echo $menu_item->ID; ?>" href="<?php echo $menu_item->url; ?>"><?php echo $menu_item->title; ?></a></li>
                                        <?php } ?>
                                    </ul>
                                <?php } ?>
                            </li>
                            <?php
                        }
                        ?>
                    </ul>
                </div>

                <?php
            }
        }

        function checkin_api() {
            if (get_option('tc_version', false) == false) {
                global $wp_rewrite;
                $wp_rewrite->flush_rules();
                update_option('tc_version', $this->version);
            }
            if (isset($_REQUEST['tickera']) && trim($_REQUEST['tickera']) != '' && isset($_REQUEST['api_key'])) {//api is called
                $api_call = new TC_Checkin_API(sanitize_text_field($_REQUEST['api_key']), sanitize_text_field($_REQUEST['tickera']));
                exit;
            }
        }

        function sales_api() {
            if (get_option('tc_version', false) == false || get_option('tc_version', false) !== $this->version) {
                global $wp_rewrite;
                $wp_rewrite->flush_rules();
                update_option('tc_version', $this->version);
            }
            if (isset($_REQUEST['tickera_sales']) && trim($_REQUEST['tickera_sales']) != '' && isset($_REQUEST['api_key'])) {//api is called
                $api_call = new TC_Sales_API(sanitize_text_field($_REQUEST['api_key']), sanitize_text_field($_REQUEST['tickera_sales']));
                exit;
            }
        }

        function generate_ticket_preview() {
            if (isset($_GET['tc_preview']) || isset($_GET['tc_download'])) {
                $templates = new TC_Ticket_Templates();
                $templates->generate_preview((int) $_GET['ticket_instance_id'], (isset($_GET['tc_download']) ? true : false));
            }
        }

        function start_session() {
//start the session
            if (!session_id()) {
                @session_start();
            }
        }

        function get_tax_value() {
            $tc_general_settings = get_option('tc_general_setting', false);
            $tax_rate = (isset($tc_general_settings['tax_rate']) && is_numeric($tc_general_settings['tax_rate']) ? $tc_general_settings['tax_rate'] : 0);
            return $tax_rate; //%
        }

//Get currency
        function get_cart_currency() {
            $tc_general_settings = get_option('tc_general_setting', false);
            return apply_filters('tc_cart_currency', (isset($tc_general_settings['currency_symbol']) && $tc_general_settings['currency_symbol'] != '' ? $tc_general_settings['currency_symbol'] : (isset($tc_general_settings['currencies']) ? $tc_general_settings['currencies'] : 'USD')));
        }

//Get currency and set amount format in cart form
        function get_cart_currency_and_format($amount) {

            if (empty($amount) || !is_numeric($amount)) {
                $amount = 0;
            }

            $amount = apply_filters('tc_cart_currency_amount', $amount);

            $tc_general_settings = get_option('tc_general_setting', false);

            if ((int) ( $amount ) == (float) $amount) {
                $int_decimals = 0;
            } else {
                $int_decimals = 2;
            }

            $decimals = apply_filters('tc_cart_amount_decimals', $int_decimals);


            $price_format = (isset($tc_general_settings['price_format']) ? $tc_general_settings['price_format'] : 'us');
            $currency_position = (isset($tc_general_settings['currency_position']) ? $tc_general_settings['currency_position'] : 'pre_nospace');

            if ($price_format == 'us') {
                $price = number_format($amount, $decimals, $dec_point = ".", $thousands_sep = ",");
            }

            if ($price_format == 'eu') {
                $price = number_format($amount, $decimals, $dec_point = ",", $thousands_sep = ".");
            }

            if ($price_format == 'french_comma') {
                $price = number_format($amount, $decimals, $dec_point = ",", $thousands_sep = " ");
            }

            if ($price_format == 'french_dot') {
                $price = number_format($amount, $decimals, $dec_point = ".", $thousands_sep = " ");
            }

            do_action('tc_price_format_check');

            if ($currency_position == 'pre_space') {
                return $this->get_cart_currency() . ' ' . $price;
            }

            if ($currency_position == 'pre_nospace') {
                return $this->get_cart_currency() . '' . $price;
            }

            if ($currency_position == 'post_nospace') {
                return $price . '' . $this->get_cart_currency();
            }

            if ($currency_position == 'post_space') {
                return $price . ' ' . $this->get_cart_currency();
            }

            do_action('tc_currency_position_check');
        }

        function save_cart_post_data() {
            if (isset($_POST)) {

                $buyer_data = array();
                $owner_data = array();

                if (!session_id()) {
                    session_start();
                }

                $_SESSION['cart_info']['coupon_code'] = sanitize_text_field($_POST['coupon_code']);
                $_SESSION['cart_info']['total'] = $_SESSION['discounted_total'];
                $_SESSION['cart_info']['currency'] = $this->get_cart_currency();

                foreach ($_POST as $field => $value) {

                    if (preg_match('/buyer_data_/', $field)) {
                        $buyer_data[str_replace('buyer_data_', '', $field)] = $value;
                    }

                    if (preg_match('/owner_data_/', $field)) {
                        $owner_data[str_replace('owner_data_', '', $field)] = $value;
                    }
                }

                $_SESSION['cart_info']['buyer_data'] = $buyer_data;
                $_SESSION['cart_info']['owner_data'] = $owner_data;

                do_action('tc_cart_post_data_check');
            }
        }

        function cart_checkout_error($msg, $context = 'checkout') {
            $msg = str_replace('"', '\"', $msg);
            $content = 'return "<div class=\"tc_cart_errors\">' . $msg . '</div>";';
            add_action('tc_checkout_error_' . $context, create_function('', $content));
            $this->checkout_error = true;
        }

        /* payment gateway form */

        function cart_payment($echo = false) {
            global $blog_id, $tc_gateway_active_plugins;

            if (!session_id()) {
                session_start();
            }

            $cart_total = isset($_SESSION['tc_cart_total']) ? $_SESSION['tc_cart_total'] : 0;

            $blog_id = (is_multisite()) ? $blog_id : 1;

            $cart = $this->get_cart_cookie();

            $content = '';

            $content = '<div class="tickera"><form id="tc_payment_form" method="post" action="' . $this->get_process_payment_slug(true) . '">';

            if ($cart_total == 0) {
                $tc_gateway_active_plugins = array();
                $free_orders = new TC_Gateway_Free_Orders();
                $tc_gateway_active_plugins[0] = $free_orders;
            }

            if (isset($_SESSION['tc_gateway_error']) && !empty($_SESSION['tc_gateway_error'])) {
                $content .= '<div class="tc_cart_errors"><ul><li>' . $_SESSION['tc_gateway_error'] . '</li></ul></div>';
            }

            $content .= $this->tc_checkout_payment_form('', $cart);

            $content .= '</form></div>';

            if ($echo) {
                echo $content;
            } else {
                return $content;
            }
        }

        function active_payment_gateways() {
            global $tc, $tc_gateway_active_plugins, $tc_gateway_plugins;
            if ($tc_gateway_active_plugins !== NULL) {
                return count($tc_gateway_active_plugins);
            } else {
                return NULL;
            }
        }

        function tc_checkout_payment_form($content, $cart) {
            global $tc, $tc_gateway_active_plugins, $tc_gateway_plugins;
            $settings = get_option('tc_settings');
            $tc_general_settings = get_option('tc_general_setting', false);

            if (!session_id()) {
                session_start();
            }

            $cart_total = isset($_SESSION['tc_cart_total']) ? $_SESSION['tc_cart_total'] : null;

            if ($cart_total == 0) {
                $tc_gateway_plugins = array();
                $free_orders = new TC_Gateway_Free_Orders();
                $tc_gateway_plugins[0] = $free_orders;
            }

            $active_gateways_num = 0;
            $skip_payment_screen = false;

            foreach ((array) $tc_gateway_plugins as $code => $plugin) {

                if ($this->gateway_is_network_allowed($code)) {
                    if ($cart_total == 0) {
                        $gateway = new $plugin;
                    } else {
                        $gateway = new $plugin[0];
                    }

                    $plugin_name = $gateway->plugin_name == 'checkout' ? '2checkout' : $gateway->plugin_name;

                    if ($plugin_name == 'custom_offline_payments') {
                        $show_gateway_admin = $settings['gateways']['custom_offline_payments']['admin_gateway'];
                    } else {
                        $show_gateway_admin = '';
                    }

                    $gateway_show_priority = (isset($settings['gateways'][$plugin_name]['gateway_show_priority']) && is_numeric($settings['gateways'][$plugin_name]['gateway_show_priority'])) ? $settings['gateways'][$plugin_name]['gateway_show_priority'] : '30';

                    if (isset($settings['gateways']['active'])) {
                        if (in_array($code, $settings['gateways']['active'])) {
                            $visible = true;
                            $active_gateways_num++;
                        } else {
                            $visible = false;
                        }
                    } elseif (isset($gateway->automatically_activated) && $gateway->automatically_activated) {
                        $visible = true;
                        $active_gateways_num++;
                    } else {
                        $visible = false;
                    }

                    if ($plugin_name == 'custom_offline_payments' && in_array($code, $settings['gateways']['active']) == true) {

                        if (apply_filters('tc_change_user_role_offline_payment', current_user_can('administrator'))) {
                            if ($show_gateway_admin == 'yes') {
                                $visible = true;
                            }
                        } else {
                            if ($show_gateway_admin == 'yes') {
                                $visible = false;
                            } else {
                                $visible = true;
                            }
                        }
                    }

                    if ($visible) {

                        if (count((array) $tc_gateway_active_plugins) == 1) {
                            $tickera_max_height = 'tickera-height';
                        } else {
                            $tickera_max_height = '';
                        }

                        $skip_payment_screen = $gateway->skip_payment_screen;
                        $content .= '<div class="tickera tickera-payment-gateways" data-gateway_show_priority="' . $gateway_show_priority . '">'
                                . '<div class="' . $gateway->plugin_name . ' plugin-title">'
                                . '<label>';

                        if (count((array) $tc_gateway_active_plugins) == 1) {
                            $content .= '<input type="radio" class="tc_choose_gateway tickera-hide-button" id="' . $gateway->plugin_name . '" name="tc_choose_gateway" value="' . $gateway->plugin_name . '" checked ' . checked(isset($_SESSION['tc_payment_method']) ? $_SESSION['tc_payment_method'] : '', $gateway->plugin_name, false) . '/>';
                        } else {
                            $content .= '<input type="radio" class="tc_choose_gateway" id="' . $gateway->plugin_name . '" name="tc_choose_gateway" value="' . $gateway->plugin_name . '" ' . checked(isset($_SESSION['tc_payment_method']) ? $_SESSION['tc_payment_method'] : '', $gateway->plugin_name, false) . '/>';
                        }

                        $content .= $gateway->public_name
                                . '<img src="' . $gateway->method_img_url . '" class="tickera-payment-options" alt="' . $gateway->plugin_name . '" /></label>'
                                . '</label>'
                                . '</div>'
                                . '<div class="tc_gateway_form ' . $tickera_max_height . '" id="' . $gateway->plugin_name . '">';
                        $content .= $gateway->payment_form($cart);
                        $content .= '<p class="tc_cart_direct_checkout">';

                        $content .= '<div class="tc_redirect_message">' . apply_filters('tc_redirect_gateway_message', sprintf(__('Redirecting to %s payment page...', 'tc'), $gateway->public_name), $gateway->public_name) . '</div>';
                        if ($gateway->plugin_name == 'free_orders') {
                            $content .= '<input type="submit" name="tc_payment_submit" id="tc_payment_confirm" class="tickera-button tc_payment_confirm" value="' . __('Continue &raquo;', 'tc') . '" />';
                        } else {


                            $content .= '<input type="submit" name="tc_payment_submit" id="tc_payment_confirm" class="tickera-button tc_payment_confirm" data-tc-check-value="tc-check-' . $plugin_name . '" value="' . __('Continue Checkout &raquo;', 'tc') . '" />';
                        }
                        $content .= '</p></div></div>';
                    }
                }
            }

            if ($active_gateways_num == 1) {
                if ($skip_payment_screen) {
                    ?>
                    <script>
                        jQuery(document).ready(function ($) {
                    <?php
                    global $tc;
                    if ((isset($tc->checkout_error) && $tc->checkout_error == true) || (isset($_SESSION['tc_gateway_error']) && $_SESSION['tc_gateway_error'] !== '')) {
//don't redirect, there is an error on the checkout.
                    } else {
//Redirect, everything is OK
                        ?>
                                $("#tc_payment_form").submit();
                        <?php
                    }
                    ?>
                            $('#tc_payment_confirm').css('display', 'none');
                            $('.tc_redirect_message').css('display', 'block');
                        });
                    </script>
                    <?php
                }
            }

            return $content;
        }

        function action_parse_request(&$wp) {

            /* Check for new TC Checkin API calls */
            if (array_key_exists('tickera', $wp->query_vars)) {
                if (isset($wp->query_vars['tickera']) && $wp->query_vars['api_key']) {
                    $api_call = new TC_Checkin_API($wp->query_vars['api_key'], $wp->query_vars['tickera']);
                    exit;
                }
            }


            /* Show Cart page */
            if (array_key_exists('page_cart', $wp->query_vars)) {

                $vars = array();
                $theme_file = locate_template(array('page-cart.php'));

                if ($theme_file != '') {
                    require_once($theme_file);
                    exit;
                } else {

                    $args = array(
                        'slug' => $wp->request,
                        'title' => __('Cart', 'tc'),
                        'content' => $this->get_template_details($this->plugin_dir . 'includes/templates/page-cart.php', $vars),
                        'type' => 'virtual_page',
                        'is_page' => TRUE,
                        'is_singular' => TRUE,
                        'is_archive' => FALSE
                    );

                    $page = new Virtual_Page($args);
                }
            }

            /* Show Payment Methods page */
            if (array_key_exists('page_payment', $wp->query_vars)) {

                $vars = array();
                $theme_file = locate_template(array('page-payment.php'));

                if ($theme_file != '') {
                    require_once($theme_file);
                    exit;
                } else {

                    $args = array(
                        'slug' => $wp->request,
                        'title' => __('Payment', 'tc'),
                        'content' => $this->get_template_details($this->plugin_dir . 'includes/templates/page-payment.php', $vars),
                        'type' => 'virtual_page',
                        'is_page' => TRUE,
                        'is_singular' => TRUE,
                        'is_archive' => FALSE
                    );

                    $page = new Virtual_Page($args);
                }

                global $tc_gateway_plugins;

                $settings = get_option('tc_settings');

// Redirect to https if force SSL is choosen
                $gateway_force_ssl = false;

                foreach ((array) $tc_gateway_plugins as $code => $plugin) {

                    if (is_array($plugin)) {
                        $gateway = new $plugin[0];
                    } else {
                        $gateway = new $plugin;
                    }

                    if (isset($settings['gateways']['active'])) {
                        if (in_array($code, $settings['gateways']['active']) || (isset($gateway->automatically_activated) && $gateway->automatically_activated)) {
                            if ($gateway->force_ssl) {
                                $gateway_force_ssl = true;
                            }
                        }
                    } else if (isset($gateway->automatically_activated) && $gateway->automatically_activated) {
                        if ($gateway->force_ssl) {
                            $gateway_force_ssl = true;
                        }
                    }
                }

                if (!is_ssl() && $gateway_force_ssl) {
                    wp_redirect('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
                    exit();
                }
            }

            /* Process payment page */
            if (array_key_exists('page_process_payment', $wp->query_vars)) {
                $vars = array();
                $theme_file = locate_template(array('page-process-payment.php'));

                if ($theme_file != '') {
                    require_once($theme_file);
                    exit;
                } else {

                    $args = array(
                        'slug' => $wp->request,
                        'title' => __('Process Payment', 'tc'),
                        'content' => $this->get_template_details($this->plugin_dir . 'includes/templates/page-process-payment.php', $vars),
                        'type' => 'virtual_page',
                        'is_page' => TRUE,
                        'is_singular' => TRUE,
                        'is_archive' => FALSE
                    );

                    $page = new Virtual_Page($args);
                }
            }

            /* Order status page and ticket downloads */

            if ((array_key_exists('page_order', $wp->query_vars) ) && (array_key_exists('tc_order_key', $wp->query_vars))) {
                $vars = array();
                $theme_file = locate_template(array('page-order.php'));

                if ($theme_file != '') {
                    require_once($theme_file);
                    exit;
                } else {
                    $args = array(
                        'slug' => $wp->request,
                        'title' => __('Order', 'tc'),
                        'content' => $this->get_template_details($this->plugin_dir . 'includes/templates/page-order.php', $vars),
                        'type' => 'virtual_page',
                        'is_page' => TRUE,
                        'is_singular' => TRUE,
                        'is_archive' => FALSE
                    );

                    $page = new Virtual_Page($args);
                }
            }

            /* Payment confirmation page */
            if (array_key_exists('page_confirmation', $wp->query_vars)) {
                $vars = array();
                $theme_file = locate_template(array('page-confirmation.php'));

                if ($theme_file != '') {
                    require_once($theme_file);
                    exit;
                } else {

                    $args = array(
                        'slug' => $wp->request,
                        'title' => __('Confirmation', 'tc'),
                        'content' => $this->get_template_details($this->plugin_dir . 'includes/templates/page-confirmation.php', $vars),
                        'type' => 'virtual_page',
                        'is_page' => TRUE,
                        'is_singular' => TRUE,
                        'is_archive' => FALSE
                    );

                    $page = new Virtual_Page($args);
                }
            }
        }

        function get_template_details($template, $args = array()) {
            ob_start();
            extract($args);
            require_once($template);
            return ob_get_clean();
        }

        function filter_query_vars($query_vars) {
            $query_vars[] = 'page_cart';
            $query_vars[] = 'page_payment';
            $query_vars[] = 'page_process_payment';
            $query_vars[] = 'page_confirmation';
            $query_vars[] = 'payment_gateway_return';
            $query_vars[] = 'page_order';
            $query_vars[] = 'tc_order';
            $query_vars[] = 'tc_order_return';
            $query_vars[] = 'tc_order_key';

            $query_vars[] = 'tickera';
            $query_vars[] = 'api_key';
            $query_vars[] = 'checksum';
            $query_vars[] = 'check_in';
            $query_vars[] = 'results_per_page';
            $query_vars[] = 'page_number';
            $query_vars[] = 'keyword';

            $query_vars[] = 'tickera_tickera';
            $query_vars[] = 'period';
            $query_vars[] = 'order_id';
            $query_vars[] = 'event_id';

            return $query_vars;
        }

        function add_rewrite_rules($rules) {
            $new_rules['^' . $this->get_payment_gateway_return_slug() . '/(.+)'] = 'index.php?page_id=-1&payment_gateway_return=$matches[1]';

            //if (!$this->cart_has_custom_url()) {
            //$new_rules[ '^' . $this->get_cart_slug() ] = 'index.php?page_id=-1&page_cart';
            //}

            if (!$this->get_payment_page()) {
                $new_rules['^' . $this->get_payment_slug()] = 'index.php?page_id=-1&page_payment';
            }

            if (!$this->get_confirmation_page()) {
                $new_rules['^' . $this->get_confirmation_slug() . '/(.+)'] = 'index.php?page_id=-1&page_confirmation&tc_order_return=$matches[1]';
            } else {
                $page_id = get_option('tc_confirmation_page_id', false);
                $page = get_post($page_id, OBJECT);
                $parent_page_id = wp_get_post_parent_id($page_id);
                $parent_page = get_post($parent_page_id, OBJECT);

                if ($parent_page) {
                    $page_slug = $parent_page->post_name . '/' . $page->post_name;
                } else {
                    $page_slug = $page->post_name;
                }
                $new_rules['^' . $page_slug . '/(.+)'] = 'index.php?pagename=' . $page_slug . '&tc_order_return=$matches[1]';
            }

            if (!$this->get_order_page()) {
                $new_rules['^' . $this->get_order_slug() . '/(.+)/(.+)'] = 'index.php?page_id=-1&page_order&tc_order=$matches[1]&tc_order_key=$matches[2]';
            } else {
                $page_id = get_option('tc_order_page_id', false);
                $page = get_post($page_id, OBJECT);
                $parent_page_id = wp_get_post_parent_id($page_id);
                $parent_page = get_post($parent_page_id, OBJECT);

                if ($parent_page) {
                    $page_slug = $parent_page->post_name . '/' . $page->post_name;
                } else {
                    $page_slug = $page->post_name;
                }
                $new_rules['^' . $page_slug . '/(.+)/(.+)'] = 'index.php?pagename=' . $page_slug . '&tc_order=$matches[1]&tc_order_key=$matches[2]';
            }

            $new_rules['^' . $this->get_process_payment_slug()] = 'index.php?page_id=-1&page_process_payment';


            /* Check-in API */
            $new_rules['^tc-api/(.+)/translation'] = 'index.php?tickera=tickera_translation&api_key=$matches[1]';
            $new_rules['^tc-api/(.+)/check_credentials'] = 'index.php?tickera=tickera_check_credentials&api_key=$matches[1]';
            $new_rules['^tc-api/(.+)/event_essentials'] = 'index.php?tickera=tickera_event_essentials&api_key=$matches[1]';
            $new_rules['^tc-api/(.+)/ticket_checkins/(.+)'] = 'index.php?tickera=tickera_checkins&api_key=$matches[1]&checksum=$matches[2]';

            if (isset($_GET['timestamp'])) {
                $new_rules['^tc-api/(.+)/check_in/(.+)'] = 'index.php?tickera=tickera_scan&api_key=$matches[1]&checksum=$matches[2]&timestamp=' . (int) $_GET['timestamp'];
            } else {
                $new_rules['^tc-api/(.+)/check_in/(.+)'] = 'index.php?tickera=tickera_scan&api_key=$matches[1]&checksum=$matches[2]';
            }

            $new_rules['^tc-api/(.+)/tickets_info/(.+)/(.+)/(.+)'] = 'index.php?tickera=tickera_tickets_info&api_key=$matches[1]&results_per_page=$matches[2]&page_number=$matches[3]&keyword=$matches[4]';
            $new_rules['^tc-api/(.+)/tickets_info/(.+)/(.+)'] = 'index.php?tickera=tickera_tickets_info&api_key=$matches[1]&results_per_page=$matches[2]&page_number=$matches[3]';


            $new_rules['^tc-api/(.+)/sales_check_credentials'] = 'index.php?tickera_sales=sales_check_credentials&api_key=$matches[1]';
            $new_rules['^tc-api/(.+)/sales_stats_general/(.+)/(.+)/(.+)'] = 'index.php?tickera_sales=sales_stats_general&api_key=$matches[1]&period=$matches[2]&results_per_page=$matches[3]&page_number=$matches[4]';
            $new_rules['^tc-api/(.+)/sales_stats_event/(.+)/(.+)/(.+)/(.+)'] = 'index.php?tickera_sales=sales_stats_event&api_key=$matches[1]&event_id=$matches[2]&period=$matches[3]&results_per_page=$matches[4]&page_number=$matches[5]';
            $new_rules['^tc-api/(.+)/sales_stats_order/(.+)'] = 'index.php?tickera_sales=sales_stats_order&api_key=$matches[1]&order_id=$matches[2]';

            return array_merge($new_rules, $rules);
        }

        function get_cart_cookie() {
            $cookie_id = 'tc_cart_' . COOKIEHASH;

            $cart = array();

            if (isset($_COOKIE[$cookie_id])) {
                $cart_obj = json_decode(stripslashes($_COOKIE[$cookie_id]));
                foreach ($cart_obj as $ticket_id => $qty) {
                    if ($qty > 0) {
                        $cart[(int) $ticket_id] = $qty;
                    } else {
                        unset($cart[(int) $ticket_id]);
                    }
                }
            } else {
                $cart = array();
            }
            if (isset($cart)) {
                return $cart;
            } else {
                return array();
            }
        }

//saves cart array to cookie
        function set_cart_cookie($cart) {
            $cookie_id = 'tc_cart_' . COOKIEHASH;

            unset($_COOKIE[$cookie_id]);
            setcookie($cookie_id, null, -1, '/');

//set cookie
            $expire = time() + apply_filters('tc_cart_cookie_expiration', 172800); //72 hrs expire by default
            setcookie($cookie_id, json_encode((array) $cart), $expire, COOKIEPATH, COOKIE_DOMAIN);

            $_COOKIE[$cookie_id] = json_encode((array) $cart);
        }

        function add_to_cart() {
            if (isset($_POST['ticket_id'])) {
                $ticket_id = (int) $_POST['ticket_id'];

                $new_quantity = isset($_POST['tc_qty']) && !empty($_POST['tc_qty']) ? (int) $_POST['tc_qty'] : 1;

                $old_cart = $this->get_cart_cookie(true);

                foreach ($old_cart as $old_ticket_id => $old_quantity) {
                    $cart[(int) $old_ticket_id] = (int) $old_quantity;
                }

                if (isset($cart[$ticket_id])) {
                    $cart[(int) $ticket_id] = $cart[$ticket_id] + $new_quantity;
                } else {
                    $cart[(int) $ticket_id] = $new_quantity;
                }

                $this->set_cart_cookie($cart);
                if (ob_get_length() > 0) {
                    ob_end_clean();
                }
                ob_start();
                echo sprintf('<span class="tc_in_cart">%s <a href="%s">%s</a></span>', apply_filters('tc_ticket_added_to_message', __('Ticket added to', 'tc')), $this->get_cart_slug(true), apply_filters('tc_ticket_added_to_cart_message', __('Cart', 'tc')));
                ob_end_flush();
                exit;
            }
        }

        function update_cart_widget() {
            $cart_contents = $this->get_cart_cookie();

            if (!empty($cart_contents)) {
                do_action('tc_cart_before_ul', $cart_contents);
                ?>
                <ul class='tc_cart_ul'>
                    <?php
                    foreach ($cart_contents as $ticket_type => $ordered_count) {
                        $ticket = new TC_Ticket($ticket_type);
                        ?>
                        <li id='tc_ticket_type_<?php echo $ticket_type; ?>'>
                            <?php echo apply_filters('tc_cart_widget_item', ($ordered_count . ' x ' . $ticket->details->post_title . ' (' . apply_filters('tc_cart_currency_and_format', tc_get_ticket_price($ticket->details->ID) * $ordered_count) . ')'), $ordered_count, $ticket->details->post_title, tc_get_ticket_price($ticket->details->ID)); ?>
                        </li>
                        <?php
                    }
                    ?>
                </ul><!--tc_cart_ul-->

                <?php
                do_action('tc_cart_after_ul', $cart_contents);
            } else {
                do_action('tc_cart_before_empty');
                ?>
                <span class='tc_empty_cart'><?php _e('The cart is empty', 'tc'); ?></span>
                <?php
                do_action('tc_cart_after_empty');
            }
            exit;
        }

        function update_cart() {
            global $tc_cart_errors, $cart_error_number;

            $cart_error_number = 0;
            $required_fields_error_count = 0;

            if ((isset($_POST['cart_action']) && $_POST['cart_action'] == 'update_cart') || (isset($_POST['cart_action']) && $_POST['cart_action'] == 'apply_coupon') || (isset($_POST['cart_action']) && $_POST['cart_action'] == 'proceed_to_checkout')) {

                $discount = new TC_Discounts();
                $discount->discounted_cart_total($_SESSION['cart_subtotal_pre']);

                $cart = array();

                $ticket_type_count = 0;
                $ticket_quantity = $_POST['ticket_quantity'];

                if (isset($_POST['cart_action'])) {

                    if ($_POST['cart_action'] == 'update_cart' || $_POST['cart_action'] == 'proceed_to_checkout') {

                        $tc_cart_errors .= '<ul>';

                        foreach ($_POST['ticket_cart_id'] as $ticket_id) {

                            $ticket = new TC_Ticket($ticket_id);

                            if ($ticket_quantity[$ticket_type_count] <= 0) {
                                unset($cart[$ticket_id]); //remove from cart
                            } else {

                                $tc_is_seatings_active = apply_filters('is_seatings_chart_addon_active', is_plugin_active('seating-charts/seating-charts.php'));

                                //do_action('tc_add_cart_errors', $ticket);
                                
                                if ($ticket->details->min_tickets_per_order != 0 && $ticket->details->min_tickets_per_order !== '') {
                                    if ($ticket_quantity[$ticket_type_count] < $ticket->details->min_tickets_per_order) {
                                        if (!$tc_is_seatings_active) {
                                            $cart[$ticket_id] = (int) $ticket->details->min_tickets_per_order;
                                            $ticket_quantity[$ticket_type_count] = (int) $ticket->details->min_tickets_per_order;
                                        }
                                        $tc_cart_errors .= '<li>' . sprintf(__('Minimum order quantity for "%s" is %d', 'tc'), $ticket->details->post_title, $ticket->details->min_tickets_per_order) . '</li>';
                                        $cart_error_number++;
                                    }
                                }

                                if ($ticket->details->max_tickets_per_order != 0 && $ticket->details->max_tickets_per_order !== '') {
                                    if ($ticket_quantity[$ticket_type_count] > $ticket->details->max_tickets_per_order) {
                                        if (!$tc_is_seatings_active) {
                                            $cart[$ticket_id] = (int) $ticket->details->max_tickets_per_order;
                                            $ticket_quantity[$ticket_type_count] = (int) $ticket->details->max_tickets_per_order;
                                        }
                                        $tc_cart_errors .= '<li>' . sprintf(__('Maximum order quantity for "%s" is %d', 'tc'), $ticket->details->post_title, $ticket->details->max_tickets_per_order) . '</li>';
                                        $cart_error_number++;
                                    }
                                }

                                $quantity_left = $ticket->get_tickets_quantity_left();

                                if ($quantity_left >= $ticket_quantity[$ticket_type_count]) {
                                    $cart[$ticket_id] = (int) $ticket_quantity[$ticket_type_count];
                                } else {
                                    if ($quantity_left > 0) {
                                        $tc_cart_errors .= '<li>' . sprintf(__('Only %d "%s" %s left', 'tc'), $quantity_left, $ticket->details->post_title, ($quantity_left > 1 ? __('tickets', 'tc') : __('ticket', 'tc'))) . '</li>';
                                        $cart_error_number++;
                                    } else {
                                        $tc_cart_errors .= '<li>' . sprintf(__('"%s" tickets are sold out', 'tc'), $ticket->details->post_title) . '</li>';
                                        $cart_error_number++;
                                    }
                                    $cart[$ticket_id] = (int) $quantity_left;
                                }
                            }
                            $ticket_type_count++;
                        }
                        
                        $tc_cart_errors = apply_filters('tc_add_cart_errors',$tc_cart_errors, $ticket);

                        $cart_error_number = apply_filters('tc_cart_error_number', $cart_error_number);
                        
                        $tc_cart_errors .= '</ul>';
                        add_filter('tc_cart_errors', array(&$this, 'tc_cart_errors'), 10, 1);

                        //$this->update_discount_code_cookie(sanitize_text_field($_POST['coupon_code']));
                        $this->update_cart_cookie($cart);

                        $cart_contents = $this->get_cart_cookie();

                        $discount = new TC_Discounts();
                        $discount->discounted_cart_total();

                        if (empty($cart)) {
                            $this->remove_order_session_data(false);
                        }
                    }

                    if ($_POST['cart_action'] == 'apply_coupon') {
                        $discount = new TC_Discounts();
                        $discount->discounted_cart_total();
                        add_filter('tc_discount_code_message', array('TC_Discounts', 'discount_code_message'), 11, 1);
                    }

                    if ($_POST['cart_action'] == 'proceed_to_checkout') {

                        $required_fields = $_POST['tc_cart_required']; //array of required field names

                        foreach ($_POST as $key => $value) {
                            if ($key !== 'tc_cart_required') {

                                if (in_array($key, $required_fields)) {

                                    if (!is_array($value)) {

                                        if (trim($value) == '') {

                                            $required_fields_error_count++;
                                        }
                                    } else {

                                        foreach ($_POST[$key] as $val) {
                                            if (!is_array($val)) {

                                                if (trim($val) == '') {

                                                    $required_fields_error_count++;
                                                }
                                            } else {

                                                foreach ($val as $val_str) {
                                                    if (trim($val_str) == '') {
                                                        $required_fields_error_count++;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        if ($required_fields_error_count > 0) {
                            $tc_cart_errors .= '<li>' . __('All fields marked with * are required.', 'tc') . '</li>';
                        }

                        do_action('tc_cart_before_error_pass_check', $cart_error_number, $tc_cart_errors);

                        if ($cart_error_number == 0 && $required_fields_error_count == 0) {
                            $this->save_cart_post_data();
                            do_action('tc_cart_passed_successfully');
                            if (apply_filters('tc_can_redirect_to_payment_page', true)) {
                                wp_redirect($this->get_payment_slug(true));
                                exit;
                            }
                        }
                    }
                }
            }
        }

        function tc_cart_errors($errors) {
            global $tc_cart_errors;
            $errors = $errors . $tc_cart_errors;
            return $errors;
        }

        function create_unique_id() {
            $tuid = '';

            $uid = uniqid("", true);

            $data = '';
            $data .= isset($_SERVER['REQUEST_TIME']) ? $_SERVER['REQUEST_TIME'] : rand(1, 999);
            $data .= isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : rand(1, 999);
            $data .= isset($_SERVER['LOCAL_ADDR']) ? $_SERVER['LOCAL_ADDR'] : rand(1, 999);
            $data .= isset($_SERVER['LOCAL_PORT']) ? $_SERVER['LOCAL_PORT'] : rand(1, 999);
            $data .= isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : rand(1, 999);
            $data .= isset($_SERVER['REMOTE_PORT']) ? $_SERVER['REMOTE_PORT'] : rand(1, 999);

            $tuid = substr(strtoupper(hash('ripemd128', $uid . md5($data))), 0, apply_filters('tc_unique_id_length', 10));

            if (apply_filters('tc_use_only_digit_order_number', false) == true) {
                $tuid_array = tc_unistr_to_ords($tuid);
                $tuid = '';
                foreach ($tuid_array as $tuid_array_key => $val) {
                    $tuid = $tuid .= $val;
                }
            }

            return $tuid;
        }

        function maybe_skip_confirmation_screen($gateway_class, $order) {
            $settings = get_option('tc_settings');
            $skip_confirmation_screen = isset($settings['gateways'][$gateway_class->plugin_name]['skip_confirmation_page']) ? $settings['gateways'][$gateway_class->plugin_name]['skip_confirmation_page'] : 'no';

            if ($skip_confirmation_screen == 'yes') {
//Fallback to JS redirection if headers are already sent
                ?>
                <script type="text/javascript">
                    jQuery(document).ready(function ($) {
                        jQuery('body').hide();
                    });
                    window.location = "<?php echo $this->tc_order_status_url($order, $order->details->tc_order_date, '', false); ?>";</script>
                <?php
                ob_start();
                wp_redirect($this->tc_order_status_url($order, $order->details->tc_order_date, '', false));
                ob_end_clean();
            }
        }

        function tc_order_status_url($order, $order_key, $link_title, $link = true) {
            $tc_general_settings = get_option('tc_general_setting', false);
            $use_order_details_pretty_links = isset($tc_general_settings['use_order_details_pretty_links']) ? $tc_general_settings['use_order_details_pretty_links'] : 'yes';

            if ($link) {
                if ($use_order_details_pretty_links == 'no') {
                    return '<a href="' . trailingslashit($this->get_order_slug(true)) . '?tc_order=' . $order->details->post_title . '&tc_order_key=' . get_post_meta($order->details->ID, 'tc_order_date', true) . '">' . $link_title . '</a>';
                } else {
                    return '<a href="' . trailingslashit($this->get_order_slug(true)) . $order->details->post_title . '/' . get_post_meta($order->details->ID, 'tc_order_date', true) . '/">' . $link_title . '</a>';
                }
            } else {
                if ($use_order_details_pretty_links == 'no') {
                    return trailingslashit($this->get_order_slug(true)) . '?tc_order=' . $order->details->post_title . '&tc_order_key=' . get_post_meta($order->details->ID, 'tc_order_date', true);
                } else {
                    return trailingslashit($this->get_order_slug(true)) . $order->details->post_title . '/' . get_post_meta($order->details->ID, 'tc_order_date', true) . '/';
                }
            }
        }

        function tc_order_confirmation_message_content($content, $order) {

            $order_status_url = $this->tc_order_status_url($order, $order->details->tc_order_date, __('here', 'tc'));

            if ($order->details->post_status == 'order_received') {
                __('You can check your order status here: ', 'tc');
                $content .= sprintf(__('You can check your order status %s.', 'tc'), $order_status_url);
            }
            if ($order->details->post_status == 'order_fraud') {
                $content .= sprintf(__('You can check your order status %s.', 'tc'), $order_status_url);
            }
            if ($order->details->post_status == 'order_cancelled') {
                $content .= sprintf(__('You can check your order status %s.', 'tc'), $order_status_url);
            }
            if ($order->details->post_status == 'order_paid') {
                $content .= sprintf(__('You can check your order status and download tickets %s.', 'tc'), $order_status_url);
            }

            return $content;
        }

        function generate_order_id() {
            global $wpdb;

            $count = true;
            while ($count) {
                $order_id = $this->create_unique_id();
                $count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . $wpdb->posts . " WHERE post_title = %s AND post_type = 'tc_orders'", $order_id));
            }

            $order_id = apply_filters('tc_order_id', $order_id);

            if (!isset($_SESSION['tc_order'])) {
                $_SESSION['tc_order'] = $order_id;
            } else {
                $order_id = $_SESSION['tc_order'];
            }

            return $order_id;
        }

        /**
         * Deprecated
         * @param type $discount_code
         */
        function update_discount_code_cookie($discount_code) {
            $cookie_id = 'tc_discount_code_' . COOKIEHASH;

//put discount code in a cookie
            $expire = time() + apply_filters('tc_discount_cookie_expiration', 172800); //72 hrs expire by default
            setcookie($cookie_id, $discount_code, $expire, COOKIEPATH, COOKIE_DOMAIN);
        }

        function update_cart_cookie($cart) {
            $cookie_id = 'tc_cart_' . COOKIEHASH;

//set cookie
            $expire = time() + apply_filters('tc_cart_cookie_expiration', 172800); //72 hrs expire by default
            setcookie($cookie_id, json_encode((array) $cart), $expire, COOKIEPATH, COOKIE_DOMAIN);

// Set the cookie variable as well, just in case something goes wrong ;)
            $_COOKIE[$cookie_id] = json_encode((array) $cart);
        }

        function front_scripts_and_styles() {
            if (apply_filters('tc_use_default_front_css', true) == true) {
                wp_enqueue_style($this->name . '-front', $this->plugin_url . 'css/front.css', array(), $this->version);
                wp_enqueue_script('tc-jquery-validate', $this->plugin_url . 'js/jquery.validate.min.js', array('jquery'), $this->version);
                if (apply_filters('tc-load-font-awesome', true) == true) {
                    wp_enqueue_style('font-awesome', $this->plugin_url . 'css/font-awesome.min.css', array(), $this->version);
                }
            }
        }


        function load_cart_scripts() {
            
            if (apply_filters('tc_use_cart_scripts', true) == true) {

                $tc_general_settings = get_option('tc_general_setting', false);

                $tc_error_message = isset($tc_general_settings['age_error_text']) ? $tc_general_settings['age_error_text'] : 'Only customers aged 16 or older are permitted for purchase on this website';
                $tc_age_checkbox = isset($tc_general_settings['show_age_check']) ? $tc_general_settings['show_age_check'] : 'no';

                $tc_collection_data_text = isset($tc_general_settings['tc_collection_data_text']) ? $tc_general_settings['tc_collection_data_text'] : '';
                $tc_gateway_collection_data = isset($tc_general_settings['tc_gateway_collection_data']) ? $tc_general_settings['tc_gateway_collection_data'] : '';


                if (empty($tc_collection_data_text)) {
                    $tc_collection_data_text = 'In order to continue you need to agree to provide your details.';
                }

                
                
                wp_enqueue_script('tc-cart', $this->plugin_url . 'js/cart.js', array('jquery'), $this->version);
                wp_localize_script('tc-cart', 'tc_ajax', array(
                    'ajaxUrl' => apply_filters('tc_ajaxurl', admin_url('admin-ajax.php', (is_ssl() ? 'https' : 'http'))),
                    'emptyCartMsg' => __('Are you sure you want to remove all tickets from your cart?', 'tc'),
                    'success_message' => __('Ticket Added!', 'tc'),
                    'imgUrl' => $this->plugin_url . 'images/ajax-loader.gif',
                    'addingMsg' => __('Adding ticket to cart...', 'tc'),
                    'outMsg' => __('In Your Cart', 'tc'),
                    'cart_url' => $this->get_cart_slug(true),
                    'update_cart_message' => __('Please update your cart before proceeding.', 'tc'),
                    'tc_provide_your_details' => $tc_collection_data_text,
                    'tc_gateway_collection_data' => $tc_gateway_collection_data,
                    'tc_error_message' => $tc_error_message,
                    'tc_show_age_check' => $tc_age_checkbox,
                        )
                );
            }
        }

        function get_front_end_invisible_post_types() {
            $post_types = array(
                'tc_templates',
                'tc_api_keys',
                'tc_tickets',
                'tc_tickets_instances',
                'tc_orders',
                'tc_forms',
                'tc_form_fields',
                'tc_custom_fonts'
            );
            return apply_filters('tc_get_front_end_invisible_post_types', $post_types);
        }

        function non_visible_post_types_404() {
            global $post;
            if (is_single($post) && in_array(get_post_type($post), $this->get_front_end_invisible_post_types())) {
                global $wp_query;
                $wp_query->set_404();
                status_header(404);
            }
        }

        function init_vars() {
            global $tc_plugin_dir, $tc_plugin_url;
//setup proper directories

            if (defined('WP_PLUGIN_URL') && defined('WP_PLUGIN_DIR') && file_exists(WP_PLUGIN_DIR . '/' . $this->dir_name . '/' . basename(__FILE__))) {
                $this->location = 'subfolder-plugins';
                $this->plugin_dir = WP_PLUGIN_DIR . '/' . $this->dir_name . '/';
                $this->plugin_url = plugins_url('/', __FILE__);
            } else if (defined('WP_PLUGIN_URL') && defined('WP_PLUGIN_DIR') && file_exists(WP_PLUGIN_DIR . '/' . basename(__FILE__))) {
                $this->location = 'plugins';
                $this->plugin_dir = WP_PLUGIN_DIR . '/';
                $this->plugin_url = plugins_url('/', __FILE__);
            } else if (is_multisite() && defined('WPMU_PLUGIN_URL') && defined('WPMU_PLUGIN_DIR') && file_exists(WPMU_PLUGIN_DIR . '/' . basename(__FILE__))) {
                $this->location = 'mu-plugins';
                $this->plugin_dir = WPMU_PLUGIN_DIR;
                $this->plugin_url = WPMU_PLUGIN_URL;
            } else {
                wp_die(sprintf(__('There was an issue determining where %s is installed. Please reinstall it.', 'tc'), $this->title));
            }

            $tc_plugin_dir = $this->plugin_dir;
            $tc_plugin_url = $this->plugin_url;
        }

        function load_this_plugin_first() {
            $path = $this->dir_name . '/' . basename(__FILE__);
            if ($plugins = get_option('active_plugins')) {
                if ($key = array_search($path, $plugins)) {
                    array_splice($plugins, $key, 1);
                    array_unshift($plugins, $path);
                    update_option('active_plugins', $plugins);
                }
            }
        }

//Add plugin admin menu items
        function add_admin_menu() {
            global $first_tc_menu_handler;

            add_dashboard_page('', '', 'manage_options', 'tc-installation-wizard', '');

            $plugin_admin_menu_items = array(
                'events' => __('Events', 'tc'), //invisible
                'ticket_templates' => __('Ticket Templates', 'tc'),
                'discount_codes' => __('Discount Codes', 'tc'),
                'settings' => __('Settings', 'tc'),
            );

            if ($this->title == 'Tickera') {//Do not show addons for (assumed) white-labeled plugin 
                $plugin_admin_menu_items['addons'] = __('Add-ons', 'tc');
            }

            $plugin_admin_menu_items = apply_filters('tc_plugin_admin_menu_items', $plugin_admin_menu_items);

// Add the sub menu items
            $number_of_sub_menu_items = 0;
            $first_tc_menu_handler = '';

            foreach ($plugin_admin_menu_items as $handler => $value) {
                if ($number_of_sub_menu_items == 0) {

                    $first_tc_menu_handler = apply_filters('first_tc_menu_handler', $this->name . '_' . $handler);

                    do_action($this->name . '_add_menu_items_up');
                } else {
                    if ($handler == 'addons') {
                        $capability = 'manage_options';
                    } else {
                        $capability = 'manage_' . $handler . '_cap';
                    }

                    add_submenu_page($first_tc_menu_handler, $value, $value, $capability, $this->name . '_' . $handler, $this->name . '_' . $handler . '_admin');
                    do_action($this->name . '_add_menu_items_after_' . $handler);
                }

                $number_of_sub_menu_items++;
            }

            do_action($this->name . '_add_menu_items_down');
        }

        function add_network_admin_menu() {
            if (!apply_filters('tc_add_network_admin_menu', true)) {
                return;
            }

            global $first_tc_network_menu_handler;

            $plugin_admin_menu_items = array(
                'network_settings' => 'Settings',
            );

            apply_filters('tc_plugin_network_admin_menu_items', $plugin_admin_menu_items);

// Add the sub menu items
            $number_of_sub_menu_items = 0;
            $first_tc_network_menu_handler = '';

            foreach ($plugin_admin_menu_items as $handler => $value) {
                if ($number_of_sub_menu_items == 0) {

                    $first_tc_network_menu_handler = $this->name . '_' . $handler;

                    add_menu_page($this->name, $this->title, 'manage_' . $handler . '_cap', $this->name . '_' . $handler, $this->name . '_' . $handler . '_admin');
                    do_action($this->name . '_add_menu_items_up');

                    add_submenu_page($this->name . '_' . $handler, __($value, 'tc'), __($value, 'tc'), 'manage_' . $handler . '_cap', $this->name . '_' . $handler, $this->name . '_' . $handler . '_admin');
                    do_action($this->name . '_add_menu_items_after_' . $handler);
                } else {
                    add_submenu_page($first_tc_network_menu_handler, __($value, 'tc'), __($value, 'tc'), 'manage_' . $handler . '_cap', $this->name . '_' . $handler, $this->name . '_' . $handler . '_admin');
                    do_action($this->name . '_add_menu_items_after_' . $handler);
                }

                $number_of_sub_menu_items++;
            }

            do_action($this->name . '_add_menu_items_down');
        }

//Function for adding plugin Settings link
        function plugin_action_link($links, $file) {
            global $first_tc_menu_handler;
            $settings_link = '<a href = "' . admin_url('edit.php?post_type=tc_events') . '">' . __('Settings', 'tc') . '</a>';

// add the link to the list
            array_unshift($links, $settings_link);
            return $links;
        }

//Plugin localization function
        function localization() {
// Load up the localization file if we're using WordPress in a different language
// Place it in this plugin's "languages" folder and name it "tc-[value in wp-config].mo"
            if ($this->location == 'mu-plugins') {
                load_muplugin_textdomain('tc', 'languages/');
            } else if ($this->location == 'subfolder-plugins') {
//load_plugin_textdomain( 'tc', false, $this->plugin_dir . '/languages/' );
                load_plugin_textdomain('tc', false, dirname(plugin_basename(__FILE__)) . '/languages/');
            } else if ($this->location == 'plugins') {
                load_plugin_textdomain('tc', false, 'languages/');
            } else {
                
            }

            $temp_locales = explode('_', get_locale());
            $this->language = ($temp_locales[0]) ? $temp_locales[0] : 'en';
        }

//Load payment gateways
        function load_addons() {
            require_once( $this->plugin_dir . 'includes/classes/class.payment_gateways.php' );
            $this->load_payment_gateway_addons();

//Load Ticket Template Elements
            require_once($this->plugin_dir . 'includes/classes/class.ticket_template_elements.php');
            $this->load_ticket_template_elements();

            $this->load_tc_addons();
            do_action('tc_load_addons');

            if (!function_exists('activate_plugin'))
                require_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }

        function load_ticket_template_elements() {
//get ticket elements dir
            $dir = $this->plugin_dir . 'includes/ticket-elements/';

            $ticket_template_elements = array();

            if (!is_dir($dir))
                return;
            if (!$dh = opendir($dir))
                return;
            while (( $plugin = readdir($dh) ) !== false) {
                if (substr($plugin, -4) == '.php')
                    $ticket_template_elements[] = $dir . '/' . $plugin;
            }
            closedir($dh);
            sort($ticket_template_elements);

            foreach ($ticket_template_elements as $file)
                include( $file );

            do_action('tc_load_ticket_template_elements');
        }

        function load_tc_addons() {
            $dir = $this->plugin_dir . 'includes/addons/';
            if (!is_dir($dir))
                return;
            if (!$dh = opendir($dir))
                return;
            while (( $plugin_dir = readdir($dh) ) !== false) {
                if ($plugin_dir !== '.' && $plugin_dir !== '..') {
                    include($dir . $plugin_dir . '/index.php');
                }
            }
        }

        function gateways_require_53php() {
            $gateways = apply_filters('tc_gateways_require_53php', array(
                'beanstream.php',
                'netbanx.php'
            ));

            return $gateways;
        }

        function load_payment_gateway_addons() {
            global $tc_gateways_currencies;

            if (!is_array($tc_gateways_currencies)) {
                $tc_gateways_currencies = array();
            }

            if (isset($_POST['gateway_settings'])) {
                $settings = get_option('tc_settings');

                if (isset($_POST['tc']['gateways']['active'])) {
                    $settings['gateways']['active'] = $_POST['tc']['gateways']['active'];
                } else {
                    $settings['gateways']['active'] = array();
                }

                update_option('tc_settings', tc_sanitize_array($settings));
            }

//get gateways dir
            $dir = $this->plugin_dir . 'includes/gateways/';

            $gateway_plugins = array();
            $gateway_plugins_originals = array();

            if (!is_dir($dir))
                return;
            if (!$dh = opendir($dir))
                return;
            while (( $plugin = readdir($dh) ) !== false) {
                if (version_compare(phpversion(), '5.3', '<')) {
                    if (in_array($plugin, $this->gateways_require_53php())) {
                        $plugin = str_replace('.php', '.53', $plugin);
                    }
                }
                if (substr($plugin, -4) == '.php') {
                    $gateway_plugins[] = trailingslashit($dir) . $plugin;
                    $gateway_plugins_originals[] = $plugin;
                }
            }

            closedir($dh);

            $gateway_plugins = apply_filters('tc_gateway_plugins', $gateway_plugins, $gateway_plugins_originals);

            sort($gateway_plugins);

            foreach ($gateway_plugins as $file)
                include( $file );

            do_action('tc_load_gateway_plugins');

            global $tc_gateway_plugins, $tc_gateway_active_plugins;
            $gateways = $this->get_setting('gateways');

            foreach ((array) $tc_gateway_plugins as $code => $plugin) {
                $class = $plugin[0];

                if (isset($gateways['active']) && in_array($code, (array) $gateways['active']) && class_exists($class) && !$plugin[3])
                    $tc_gateway_active_plugins[] = new $class;
                $gateway = new $class;

                if (isset($gateway->currencies) && is_array($gateway->currencies)) {
                    $tc_gateways_currencies = array_merge($gateway->currencies, $tc_gateways_currencies);
                }
            }

            $settings = get_option('tc_settings');
            $settings['gateways']['currencies'] = apply_filters('tc_gateways_currencies', $tc_gateways_currencies);
            update_option('tc_settings', $settings);
        }

        function show_page_tab($tab) {
            do_action('tc_show_page_tab_' . $tab);
            require_once( $this->plugin_dir . 'includes/admin-pages/settings-' . $tab . '.php' );
        }

        function show_network_page_tab($tab) {
            do_action('tc_show_network_page_tab_' . $tab);
            require_once( $this->plugin_dir . 'includes/network-admin-pages/network_settings-' . $tab . '.php' );
        }

        function get_setting($key, $default = null) {
            $settings = get_option('tc_settings');
            $keys = explode('->', $key);
            array_map('trim', $keys);
            if (count($keys) == 1)
                $setting = isset($settings[$keys[0]]) ? $settings[$keys[0]] : $default;
            else if (count($keys) == 2)
                $setting = isset($settings[$keys[0]][$keys[1]]) ? $settings[$keys[0]][$keys[1]] : $default;
            else if (count($keys) == 3)
                $setting = isset($settings[$keys[0]][$keys[1]][$keys[2]]) ? $settings[$keys[0]][$keys[1]][$keys[2]] : $default;
            else if (count($keys) == 4)
                $setting = isset($settings[$keys[0]][$keys[1]][$keys[2]][$keys[3]]) ? $settings[$keys[0]][$keys[1]][$keys[2]][$keys[3]] : $default;

            return apply_filters("tc_setting_" . implode('', $keys), $setting, $default);
        }

        function get_network_setting($key, $default = null) {
            $settings = get_site_option('tc_network_settings');
            $keys = explode('->', $key);
            array_map('trim', $keys);
            if (count($keys) == 1)
                $setting = isset($settings[$keys[0]]) ? $settings[$keys[0]] : $default;
            else if (count($keys) == 2)
                $setting = isset($settings[$keys[0]][$keys[1]]) ? $settings[$keys[0]][$keys[1]] : $default;
            else if (count($keys) == 3)
                $setting = isset($settings[$keys[0]][$keys[1]][$keys[2]]) ? $settings[$keys[0]][$keys[1]][$keys[2]] : $default;
            else if (count($keys) == 4)
                $setting = isset($settings[$keys[0]][$keys[1]][$keys[2]][$keys[3]]) ? $settings[$keys[0]][$keys[1]][$keys[2]][$keys[3]] : $default;

            return apply_filters("tc_network_setting_" . implode('', $keys), $setting, $default);
        }

        function gateway_is_network_allowed($gateway) {
            $settings = get_site_option('tc_network_settings', '');
            if (in_array($gateway, $this->get_network_setting('gateways->active', array())) || $gateway == 'free_orders') {
                return true;
            } else {
                if ($settings == '') {//not set by the network admin and every gateway is available by default
                    return true;
                } else {
                    return false;
                }
            }
        }

        function handle_gateway_returns($wp_query) {

            global $wp;
            if (is_admin())
                return;

//listen for gateway IPN returns and tie them in to proper gateway plugin
            if (!empty($wp_query->query_vars['payment_gateway_return'])) {
                $vars = array();
                $theme_file = locate_template(array('page-ipn.php'));

                if ($theme_file != '') {
                    require_once($theme_file);
                    exit;
                } else {

                    $args = array(
                        'slug' => $wp->request,
                        'title' => __('IPN', 'tc'),
                        'content' => $this->get_template_details($this->plugin_dir . 'includes/templates/page-ipn.php', $vars),
                        'type' => 'virtual_page',
                        'is_page' => TRUE,
                        'is_singular' => TRUE,
                        'is_archive' => FALSE
                    );

                    $page = new Virtual_Page($args);
                }
            }

            if (isset($wp_query->query_vars['payment_gateway_return']) && isset($_GET['payment_gateway_return'])) {
                if (isset($wp_query->query_vars['payment_gateway_return'])) {
                    $payment_gateway = $wp_query->query_vars['payment_gateway_return'];
                }
                if (isset($_GET['payment_gateway_return'])) {
                    $payment_gateway = sanitize_key($_GET['payment_gateway_return']);
                }

                do_action('tc_handle_payment_return_' . $payment_gateway);
            }
        }

        function get_order_payment_status($order_id) {
            $order = $this->get_order($order_id);
            return $order->post_status;
        }

//called by payment gateways to update order statuses
        function update_order_payment_status($order_id, $paid) {
//get the order

            $order = $this->get_order($order_id);
            if (!$order) {
                return false;
            }

            if ($paid) {

                $current_payment_status = $this->get_order_payment_status($order_id);
                $this->update_order_status($order->ID, 'order_paid');

                if ($current_payment_status !== 'order_paid') {
                    $cart_contents = get_post_meta($order->ID, 'tc_cart_contents', false);
                    $cart_info = get_post_meta($order->ID, 'tc_cart_info', false);
                    $payment_info = get_post_meta($order->ID, 'tc_payment_info', false);
                    do_action('tc_order_updated_status_to_paid', $order->ID, 'order_paid', $cart_contents, $cart_info, $payment_info);
                    tc_order_created_email($order->post_name, 'order_paid', false, false, false, true);
                }
            }
        }

//returns the full order details as an object
        function get_order($order_id) {
            $id = (is_int($order_id)) ? $order_id : $this->order_to_post_id($order_id);

            if (empty($id))
                return false;

            $order = get_post($id);
            if (!$order)
                return false;

            $meta = get_post_custom($id);

            foreach ($meta as $key => $val)
                $order->{$key} = maybe_unserialize($meta[$key][0]);

            return $order;
        }

        function get_cart_event_tickets($cart_contents, $event_id) {
            $ticket_count_global = 0;

            foreach ($cart_contents as $ticket_type => $ticket_count) {
                $event = get_post_meta($ticket_type, 'event_name', true);
                if ($event == $event_id) {
                    $ticket_count_global = $ticket_count_global + $ticket_count;
                }
            }
            return $ticket_count_global;
        }

//returns all event ids based on the cart contents
        function get_cart_events($cart_contents) {
            $event_ids = array();
            foreach ($cart_contents as $ticket_type => $ordered_count) {
                $ticket = new TC_Ticket($ticket_type);

                $event_id = $ticket->get_ticket_event($ticket_type);

                if (!in_array($event_id, $event_ids)) {
                    $event_ids[] = $event_id;
                }
            }
            return $event_ids;
        }

        function get_events_creators($cart_contents) {
            $event_ids = $this->get_cart_events($cart_contents);
            foreach ($event_ids as $event_id) {
                $event = new TC_Event($event_id);
                $promoter_ids[] = $event->details->post_author;
            }
            return $promoter_ids;
        }

        function check_for_total_paid_fraud($total_paid, $total_needed) {
            if (apply_filters('tc_compare_total_needed', true) == true) {
                if ($total_paid == $total_needed) {
                    return false; //not fraud
                } else {
                    return true;
                }
            } else {
                return false;
            }
        }

//called on checkout to create a new order
        function create_order($order_id, $cart_contents, $cart_info, $payment_info, $paid) {
            global $wpdb;
            tc_final_cart_check($cart_contents);
            
            if (!session_id()) {
                session_start();
            }

            //make sure buyer data is available
            if (!isset($cart_info['buyer_data'])) {
                $_SESSION['tc_gateway_error'] = __('Something went wrong. Cart data is not available', 'tc');
                ob_start();
                $this->remove_order_session_data(true);
                @wp_redirect($tc->get_payment_slug(true));
                tc_js_redirect($tc->get_payment_slug(true));
                exit;
                return false;
            }

            //Make sure the order id doesn't exists
            if (empty($order_id) || $this->get_order($order_id)) {//do not continue if order exists or order_id is not supplied
                $_SESSION['tc_gateway_error'] = __('Something went wrong. The order with the same ID already exists. Please try again.', 'tc');
                ob_start();
                $this->remove_order_session_data(true);
                @wp_redirect($tc->get_payment_slug(true));
                tc_js_redirect($tc->get_payment_slug(true));
                exit;
                return false;
            }

            $this->set_cart_info_cookie($cart_info);
            $this->set_order_cookie($order_id);

            if (!isset($_SESSION['cart_info']['total']) || is_null($_SESSION['cart_info']['total'])) {
                $cart_total = $_SESSION['cart_total_pre'];
                $_SESSION['cart_info']['total'] = $_SESSION['tc_cart_total'];
                $cart_info = $_SESSION['cart_info'];
            } else {
                $cart_total = $_SESSION['cart_info']['total'];
            }

            $fraud = $this->check_for_total_paid_fraud($payment_info['total'], $cart_total);

            $user_id = get_current_user_id();

//insert post type
            $status = ($paid ? ($fraud ? 'order_fraud' : 'order_paid') : 'order_received');

            $order = array();
            $order['post_title'] = sanitize_text_field($order_id);
            $order['post_name'] = sanitize_text_field($order_id);
            $order['post_content'] = serialize($cart_contents);
            $order['post_status'] = sanitize_key($status);
            $order['post_type'] = 'tc_orders';

            if ($user_id != 0) {
                $order['post_author'] = $user_id;
            }

            $post_id = wp_insert_post($order);

            /* add post meta */

//Cart Contents
            add_post_meta($post_id, 'tc_cart_contents', $cart_contents);

//Cart Info
            add_post_meta($post_id, 'tc_cart_info', $cart_info); //save row data - buyer and ticket owners data, gateway, total, currency, coupon code, etc.
//Payment Info
            add_post_meta($post_id, 'tc_payment_info', $payment_info); //transaction_id, total, currency, method
//Order Date & Time
            add_post_meta($post_id, 'tc_order_date', time());

//Discount code
            if (isset($_SESSION['tc_discount_code'])) {
                add_post_meta($post_id, 'tc_discount_code', $_SESSION['tc_discount_code']);
            }

//Order Paid Time
            add_post_meta($post_id, 'tc_paid_date', ($paid) ? time() : '' ); //empty means not yet paid
//Event(s) - could be more events at once since customer may have tickets from more than one event in the cart
            add_post_meta($post_id, 'tc_parent_event', $this->get_cart_events($cart_contents));

            add_post_meta($post_id, 'tc_event_creators', $this->get_events_creators($cart_contents));

//Discount Code
            add_post_meta($post_id, 'tc_paid_date', ($paid) ? time() : '' );

//Save Ticket Owner(s) data
            $owner_data = $_SESSION['cart_info']['owner_data'];

            $owner_records = array();

            $different_ticket_types = array_keys($owner_data['ticket_type_id_post_meta']);

            $n = 0;
            $i = 1;

            foreach ($different_ticket_types as $different_ticket_type) {
                $i = $i + 10;
                foreach ($owner_data as $field_name => $field_values) {

                    $inner_count = count($field_values[$different_ticket_type]);

                    foreach ($field_values[$different_ticket_type] as $field_value) {
                        $owner_records[$n . '-' . $inner_count . '-' . $i][$field_name] = $field_value;
                        $inner_count = $inner_count + 1;
                    }
                }
                $n++;
            }

            $owner_record_num = 1;


            foreach ($owner_records as $owner_record) {
                if (isset($owner_record['ticket_type_id_post_meta'])) {
                    $metas = array();

                    foreach ($owner_record as $owner_field_name => $owner_field_value) {

                        if (preg_match('/_post_title/', $owner_field_name)) {
                            $title = sanitize_text_field($owner_field_value);
                        }

                        if (preg_match('/_post_excerpt/', $owner_field_name)) {
                            $excerpt = sanitize_text_field($owner_field_value);
                        }

                        if (preg_match('/_post_content/', $owner_field_name)) {
                            $content = sanitize_text_field($owner_field_value);
                        }

                        if (preg_match('/_post_meta/', $owner_field_name)) {
                            $metas[sanitize_key(str_replace('_post_meta', '', $owner_field_name))] = sanitize_text_field($owner_field_value);
                        }
                    }

                    if (apply_filters('tc_use_only_digit_order_number', false) == true) {
                        $metas['ticket_code'] = apply_filters('tc_ticket_code', $order_id . '' . $owner_record_num, $owner_record['ticket_type_id_post_meta']);
                    } else {
                        $metas['ticket_code'] = apply_filters('tc_ticket_code', $order_id . '-' . $owner_record_num, $owner_record['ticket_type_id_post_meta']);
                    }

                    do_action('tc_after_owner_post_field_type_check');

                    $arg = array(
                        'post_author' => isset($user_id) ? $user_id : '',
                        'post_parent' => $post_id,
                        'post_excerpt' => (isset($excerpt) ? $excerpt : ''),
                        'post_content' => (isset($content) ? $content : ''),
                        'post_status' => 'publish',
                        'post_title' => (isset($title) ? $title : ''),
                        'post_type' => 'tc_tickets_instances',
                    );

                    $owner_record_id = @wp_insert_post($arg, true);

                    $ticket_type_id = 0;

                    foreach ($metas as $meta_name => $mata_value) {
                        update_post_meta($owner_record_id, $meta_name, $mata_value);
                        if ($meta_name == 'ticket_type_id') {
                            $ticket_type_id = $mata_value;
                        }
                    }

                    if ($ticket_type_id == 0 || empty($ticket_type_id)) {
                        $ticket_type_id = get_post_meta($owner_record_id, 'ticket_type_id', true);
                    }

                    $ticket_type = new TC_Ticket($ticket_type_id);
                    $event_id = $ticket_type->get_ticket_event($ticket_type_id);

                    update_post_meta($owner_record_id, 'event_id', (int) $event_id);

                    $owner_record_num++;
                }
            }

            /* foreach ($metas as $meta_name => $mata_value) {
              update_post_meta($owner_record_id, $meta_name, $mata_value);
              } */

//Send order status email to the customer

            $payment_class_name = $_SESSION['cart_info']['gateway_class'];

            $payment_gateway = new $payment_class_name;

            do_action('tc_order_created', $order_id, $status, $cart_contents, $cart_info, $payment_info);
            return $order_id;
        }

        function change_event_status() {
            if (isset($_POST['event_id'])) {
                $event_id = (int) $_POST['event_id'];
                $post_status = sanitize_key($_POST['event_status']);

                $post_data = array(
                    'ID' => (int) $event_id,
                    'post_status' => sanitize_key($post_status)
                );

                wp_update_post($post_data);
                exit;
            } else {
                echo 'error';
                exit;
            }
        }

        function change_ticket_status() {
            if (isset($_POST['ticket_id'])) {
                $ticket_id = (int) $_POST['ticket_id'];
                $post_status = sanitize_key($_POST['ticket_status']);

                $post_data = array(
                    'ID' => (int) $ticket_id,
                    'post_status' => sanitize_key($post_status)
                );

                wp_update_post($post_data);
                exit;
            } else {
                echo 'error';
                exit;
            }
        }

        function change_order_status_ajax() {

            if (isset($_POST['order_id'])) {
                $order_id = (int) $_POST['order_id'];
                $post_status = sanitize_key($_POST['new_status']);

                $post_data = array(
                    'ID' => $order_id,
                    'post_status' => $post_status
                );

//$order = get_post( $order_id );

                $order = new TC_Order($order_id);
                $old_post_status = $order->details->post_status;

                if ($post_status == 'trash') {
                    $order->delete_order(false);
                } else {
                    if ($old_post_status == 'trash') { //untrash attendees and tickets only if the order was in the trash
                        $order->untrash_order();
                    }

                    if (wp_update_post($post_data)) {
                        echo 'updated';
                    } else {
                        echo 'error';
                    }
                }

                if ($post_status == 'order_paid') {
//echo 'calling function to send an notification email for order:'.$order->post_name;
                    tc_order_created_email($order->details->post_name, $post_status, false, false, false, false);
                    $payment_info = get_post_meta($order_id, 'tc_payment_info', true);
                    do_action('tc_order_paid_change', $order_id, $post_status, '', '', $payment_info);
                } else {
//echo 'post status is not order_paid';
                }


                exit;
            } else {
                echo 'error';
                exit;
            }
        }

//saves cart info array to cookie
        function set_order_cookie($order) {
            ob_start();
            $cookie_id = 'tc_order_' . COOKIEHASH;

            unset($_COOKIE[$cookie_id]);
            @setcookie($cookie_id, null, -1, '/');

//set cookie
            $expire = time() + apply_filters('tc_cart_cookie_expiration', 172800); //72 hrs expire by default
            @setcookie($cookie_id, $order, $expire, COOKIEPATH, COOKIE_DOMAIN);

            $_COOKIE[$cookie_id] = $order;
            ob_end_flush();
        }

        function get_order_cookie() {
            ob_start();
            $cookie_id = 'tc_order_' . COOKIEHASH;

            $order = isset($_COOKIE[$cookie_id]) ? $_COOKIE[$cookie_id] : null;

            if (isset($order)) {
                return $order;
            } else {
                return false;
            }
            ob_end_flush();
        }

//saves cart info array to cookie
        function set_cart_info_cookie($cart_info) {
            ob_start();
            $cookie_id = 'cart_info_' . COOKIEHASH;

            unset($_COOKIE[$cookie_id]);
            @setcookie($cookie_id, null, -1, '/');

//set cookie
            $expire = time() + apply_filters('tc_cart_cookie_expiration', 172800); //72 hrs expire by default
            @setcookie($cookie_id, json_encode((array) $cart_info), $expire, COOKIEPATH, COOKIE_DOMAIN);

            $_COOKIE[$cookie_id] = json_encode((array) $cart_info);

            ob_end_flush();
        }

        function get_cart_info_cookie() {

            $cookie_id = 'cart_info_' . COOKIEHASH;

            if (isset($_COOKIE[$cookie_id])) {
                $cart_obj = json_decode(stripslashes($_COOKIE[$cookie_id]));
                foreach ($cart_obj as $ticket_id => $qty) {
                    $cart[(int) $ticket_id] = $qty;
                }
            } else {
                $cart = array();
            }

            if (isset($cart)) {
                return $cart;
            } else {
                return array();
            }
        }

        function ajax_remove_order_session_data_only() {
            unset($_SESSION['tc_order']);
        }

        function ajax_remove_order_session_data() {
            ob_start();

            do_action('tc_remove_order_session_data_ajax');

            unset($_SESSION['tc_discount_code']);
            unset($_SESSION['discounted_total']);
            unset($_SESSION['tc_payment_method']);
            unset($_SESSION['cart_info']);
            unset($_SESSION['tc_order']);
            unset($_SESSION['tc_payment_info']);
            unset($_SESSION['cart_subtotal_pre']);
            unset($_SESSION['tc_total_fees']);
            unset($_SESSION['discount_value_total']);
            unset($_SESSION['tc_cart_subtotal']);
            unset($_SESSION['tc_cart_total']);
            unset($_SESSION['tc_tax_value']);
            unset($_SESSION['tc_gateway_error']);

            @setcookie('tc_cart_' . COOKIEHASH, null, time() - 1, COOKIEPATH, COOKIE_DOMAIN);
            @setcookie('tc_cart_seats_' . COOKIEHASH, null, time() - 1, COOKIEPATH, COOKIE_DOMAIN);
            @setcookie('cart_info_' . COOKIEHASH, null, time() - 1, COOKIEPATH, COOKIE_DOMAIN);
            @setcookie('tc_order_' . COOKIEHASH, null, time() - 1, COOKIEPATH, COOKIE_DOMAIN);
            //@setcookie('tc_discount_code_' . COOKIEHASH, null, time() - 1, COOKIEPATH, COOKIE_DOMAIN);

            ob_end_flush();
        }

        function remove_order_session_data_only($js_fallback = true) {
            ob_start();
            unset($_SESSION['tc_order']);
            ob_end_flush();
            if ($js_fallback) {
                ?>
                <script type="text/javascript">
                    jQuery(document).ready(function ($) {
                        $.post(tc_ajax.ajaxUrl, {action: "tc_remove_order_session_data_only"}, function (data) {
                        });
                    });
                </script>
                <?php
            }
        }

        function remove_order_session_data($js_fallback = true) {
            ob_start();

            do_action('tc_remove_order_session_data', $js_fallback);

            unset($_SESSION['tc_discount_code']);
            unset($_SESSION['discounted_total']);
            unset($_SESSION['tc_payment_method']);
            unset($_SESSION['cart_info']);
            unset($_SESSION['tc_order']);
            unset($_SESSION['tc_payment_info']);
            unset($_SESSION['cart_subtotal_pre']);
            unset($_SESSION['tc_total_fees']);
            unset($_SESSION['discount_value_total']);
            unset($_SESSION['tc_cart_subtotal']);
            unset($_SESSION['tc_cart_total']);
            unset($_SESSION['tc_tax_value']);
            unset($_SESSION['tc_gateway_error']);

            @setcookie('tc_cart_' . COOKIEHASH, null, time() - 1, COOKIEPATH, COOKIE_DOMAIN);
            @setcookie('tc_cart_seats_' . COOKIEHASH, null, time() - 1, COOKIEPATH, COOKIE_DOMAIN);
            @setcookie('cart_info_' . COOKIEHASH, null, time() - 1, COOKIEPATH, COOKIE_DOMAIN);
            @setcookie('tc_order_' . COOKIEHASH, null, time() - 1, COOKIEPATH, COOKIE_DOMAIN);
            //@setcookie('tc_discount_code_' . COOKIEHASH, null, time() - 1, COOKIEPATH, COOKIE_DOMAIN);

            ob_end_flush();
            if ($js_fallback) {
                ?>
                <script type="text/javascript">
                    jQuery(document).ready(function ($) {
                        $.post(tc_ajax.ajaxUrl, {action: "tc_remove_order_session_data"}, function (data) {
                            //console.log( data );
                        });
                    });
                </script>
                <?php
            }
        }

        function update_order_status($order_id, $new_status) {
            $order = array(
                'ID' => (int) $order_id,
                'post_status' => sanitize_key($new_status)
            );

            $order_object = new TC_Order($order_id);
            $old_post_status = $order_object->details->post_status;

            if ($old_post_status == 'trash') {
                $order_object->untrash_order(); //untrash order if it's in trash
            }

            wp_update_post($order);
        }

//converts the pretty order id to an actual post ID
        function order_to_post_id($order_id) {
            global $wpdb;
            return $wpdb->get_var($wpdb->prepare("SELECT ID FROM " . $wpdb->posts . " WHERE post_name = %s AND post_type = 'tc_orders'", $order_id));
        }

        function get_order_slug($url = false) {
            $tc_general_settings = get_option('tc_general_setting', false);
            if (isset($tc_general_settings['ticket_order_slug'])) {
                $default_slug_value = $tc_general_settings['ticket_order_slug'];
            } else {
                $default_slug_value = 'order';
            }

            if ($url) {
                if ($this->get_order_page()) {
                    return trailingslashit($this->get_order_page(true));
                } else {
                    return trailingslashit(home_url()) . get_option('ticket_order_slug', $default_slug_value);
                }
            }

            return $default_slug_value;
        }

        function cart_has_custom_url() {
            $tc_general_settings = get_option('tc_general_setting', false);
            if (isset($tc_general_settings['ticket_custom_cart_url']) && $tc_general_settings['ticket_custom_cart_url'] !== '') {
                return true;
            } else {
                return false;
            }
        }

        function get_cart_page($url = false) {
            $page = get_option('tc_cart_page_id', false);
            if ($page) {
                if ($url) {
                    return get_permalink($page);
                } else {
                    return $page;
                }
            } else {
                return false;
            }
        }

        function get_payment_page($url = false) {
            $page = get_option('tc_payment_page_id', false);
            if ($page) {
                if ($url) {
                    return get_permalink($page);
                } else {
                    return $page;
                }
            } else {
                return false;
            }
        }

        function get_process_payment_page($url = false) {
            $page = get_option('tc_process_payment_page_id', false);
            if ($page) {
                if ($url) {
                    return get_permalink($page);
                } else {
                    return $page;
                }
            } else {
                return false;
            }
        }

        function get_ipn_page($url = false) {
            global $wp_rewrite;
            $page = get_option('tc_ipn_page_id', false);

            if ($page) {
                if ($url && isset($wp_rewrite)) {
                    return get_permalink((int) $page);
                } else {
                    return $page;
                }
            } else {
                return false;
            }
        }

        function get_confirmation_page($url = false) {
            $page = get_option('tc_confirmation_page_id', false);
            if ($page) {
                if ($url) {
                    return get_permalink($page);
                } else {
                    return $page;
                }
            } else {
                return false;
            }
        }

        function get_order_page($url = false) {
            $page = get_option('tc_order_page_id', false);
            if ($page) {
                if ($url) {
                    return get_permalink($page);
                } else {
                    return $page;
                }
            } else {
                return false;
            }
        }

        function get_cart_slug($url = false) {
            $tc_general_settings = get_option('tc_general_setting', false);
            if (isset($tc_general_settings['ticket_cart_slug'])) {
                $default_slug_value = $tc_general_settings['ticket_cart_slug'];
            } else {
                $default_slug_value = 'cart';
            }
            if ($url) {
                if ($this->get_cart_page()) {
                    return $this->get_cart_page(true);
                } else {
                    if (isset($tc_general_settings['ticket_custom_cart_url']) && $tc_general_settings['ticket_custom_cart_url'] !== '') {
                        return $tc_general_settings['ticket_custom_cart_url'];
                    } else {
                        return trailingslashit(home_url()) . get_option('ticket_cart_slug', $default_slug_value);
                    }
                }
            }
            return $default_slug_value;
        }

        function get_payment_slug($url = false) {
            $tc_general_settings = get_option('tc_general_setting', false);

            if (isset($tc_general_settings['ticket_payment_slug'])) {
                $default_slug_value = $tc_general_settings['ticket_payment_slug'];
            } else {
                $default_slug_value = 'payment';
            }

            if ($url) {
                if ($this->get_payment_page()) {
                    return $this->get_payment_page(true);
                } else {
                    return trailingslashit(home_url()) . get_option('ticket_payment_slug', $default_slug_value);
                }
            }

            return $default_slug_value;
        }

        function get_process_payment_slug($url = false) {
            $tc_general_settings = get_option('tc_general_setting', false);

            if (isset($tc_general_settings['ticket_payment_process_slug'])) {
                $default_slug_value = $tc_general_settings['ticket_payment_process_slug'];
            } else {
                $default_slug_value = 'process-payment';
            }

            if ($url) {
                $tc_process_payment_use_virtual = isset($tc_general_settings['tc_process_payment_use_virtual']) ? $tc_general_settings['tc_process_payment_use_virtual'] : 'no';
                if ($this->get_process_payment_page() && $tc_process_payment_use_virtual == 'no') {
                    return trailingslashit($this->get_process_payment_page(true));
                } else {
                    return trailingslashit(home_url()) . get_option('ticket_payment_process_slug', $default_slug_value);
                }
            }

            return $default_slug_value;
        }

        function get_cancel_url($order_id = false) {
            if ($order_id) {
                if ($this->active_payment_gateways() == 1) {
                    $cancel_url = $this->get_cart_slug(true) . '?tc_cancel_order=' . $order_id;
                } else {
                    $cancel_url = $this->get_payment_slug(true) . '?tc_cancel_order=' . $order_id;
                }
            } else {
                $cancel_url = $this->get_cart_slug(true);
            }
            return $cancel_url;
        }

        function maybe_cancel_order($redirect = false) {
            if (isset($_GET['tc_cancel_order']) && !empty($_GET['tc_cancel_order'])) {

                $order_id = $_GET['tc_cancel_order'];
                $order = tc_get_order_id_by_name($order_id);

                $order_status = get_post_status($order->ID);

                if (($order_status == 'order_received' || $order_status == 'order_paid') || current_user_can('manage_options')) {//cancel order if it's received / pending only (administrator can cancel other order statuses as well)
                    $this->update_order_status($order->ID, 'order_cancelled');
                }

                if ($redirect !== false) {
                    ob_start();

                    $_SESSION['tc_gateway_error'] = __('Your transaction has been canceled.', 'tc');

                    if ($this->active_payment_gateways() == 1) {
                        @wp_redirect($this->get_cart_slug(true));
                        tc_js_redirect($this->get_cart_slug(true));
                    } else {
                        @wp_redirect($this->get_payment_slug(true));
                        tc_js_redirect($this->get_payment_slug(true));
                    }
                    exit;
                }
            }
        }

        function get_confirmation_slug($url = false, $order_id = false) {
            $tc_general_settings = get_option('tc_general_setting', false);
            if (isset($tc_general_settings['ticket_confirmation_slug'])) {
                $default_slug_value = $tc_general_settings['ticket_confirmation_slug'];
            } else {
                $default_slug_value = 'confirmation';
            }

            $use_order_details_pretty_links = isset($tc_general_settings['use_order_details_pretty_links']) ? $tc_general_settings['use_order_details_pretty_links'] : 'yes';

            if ($url) {
                if ($this->get_confirmation_page()) {
                    if ($use_order_details_pretty_links == 'yes') {
                        return trailingslashit($this->get_confirmation_page(true)) . trailingslashit($order_id);
                    } else {
                        return trailingslashit($this->get_confirmation_page(true)) . '?tc_order_return=' . $order_id;
                    }
                } else {
                    return trailingslashit(home_url()) . trailingslashit(get_option('ticket_confirmation_slug', $default_slug_value)) . trailingslashit($order_id);
                }
            }

            return $default_slug_value;
        }

        function get_payment_gateway_return_slug($url = false) {
            $tc_general_settings = get_option('tc_general_setting', false);
            if (isset($tc_general_settings['ticket_payment_gateway_return_slug'])) {
                $default_slug_value = $tc_general_settings['ticket_payment_gateway_return_slug'];
            } else {
                $default_slug_value = 'payment-gateway-ipn';
            }

            if ($url) {
                $tc_ipn_use_virtual = isset($tc_general_settings['tc_ipn_use_virtual']) ? $tc_general_settings['tc_ipn_use_virtual'] : 'no';
                if ($this->get_ipn_page() && $tc_ipn_use_virtual == 'no') {
                    return trailingslashit($this->get_ipn_page(true));
                } else {
                    return trailingslashit(home_url()) . get_option('ticket_payment_gateway_return_slug', $default_slug_value);
                }
            }

            return $default_slug_value;
        }

        function register_custom_posts() {
            $args = array(
                'labels' => array('name' => __('Events', 'tc'),
                    'singular_name' => __('Events', 'tc'),
                    'add_new' => __('Create New', 'tc'),
                    'add_new_item' => __('Create New Event', 'tc'),
                    'edit_item' => __('Edit Events', 'tc'),
                    'edit' => __('Edit', 'tc'),
                    'new_item' => __('New Event', 'tc'),
                    'view_item' => __('View Event', 'tc'),
                    'search_items' => __('Search Events', 'tc'),
                    'not_found' => __('No Events Found', 'tc'),
                    'not_found_in_trash' => __('No Events found in Trash', 'tc'),
                    'view' => __('View Event', 'tc')
                ),
                'public' => true,
                'show_ui' => false,
                'publicly_queryable' => true,
                'capability_type' => 'tc_events',
                'map_meta_cap' => true,
                'capabilities' => array(
                    'publish_posts' => 'publish_tc_events',
                    'edit_posts' => 'edit_tc_events',
                    'edit_others_posts' => 'edit_others_tc_events',
                    'delete_posts' => 'delete_tc_events',
                    'delete_others_posts' => 'delete_others_tc_events',
                    'read_private_posts' => 'read_private_tc_events',
                    'edit_post' => 'edit_tc_event',
                    'delete_post' => 'delete_tc_event',
                    'read' => 'read_tc_event',
                    'edit_published_posts' => 'edit_published_tc_events',
                    'edit_private_posts' => 'edit_private_tc_events',
                    'delete_private_posts' => 'delete_private_tc_events',
                    'delete_published_posts' => 'delete_published_tc_events',
                    'create_posts' => 'create_tc_events',
                ),
                'hierarchical' => false,
                'query_var' => true,
            );

            register_post_type('tc_events', apply_filters('tc_events_post_type_args', $args));

            $args = array(
                'labels' => array('name' => __('Ticket Types', 'tc'),
                    'singular_name' => __('Ticket', 'tc'),
                    'add_new' => __('Create New', 'tc'),
                    'add_new_item' => __('Create New Ticket', 'tc'),
                    'edit_item' => __('Edit Ticket', 'tc'),
                    'edit' => __('Edit', 'tc'),
                    'new_item' => __('New Ticket', 'tc'),
                    'view_item' => __('View Ticket', 'tc'),
                    'search_items' => __('Search Tickets', 'tc'),
                    'not_found' => __('No Tickets Found', 'tc'),
                    'not_found_in_trash' => __('No Tickets found in Trash', 'tc'),
                    'view' => __('View Ticket', 'tc')
                ),
                'public' => false,
                'show_ui' => false,
                'publicly_queryable' => true,
                'capability_type' => 'tc_tickets',
                'map_meta_cap' => true,
                'capabilities' => array(
                    'publish_posts' => 'publish_tc_tickets',
                    'edit_posts' => 'edit_tc_tickets',
                    'edit_others_posts' => 'edit_others_tc_tickets',
                    'delete_posts' => 'delete_tc_tickets',
                    'delete_others_posts' => 'delete_others_tc_tickets',
                    'read_private_posts' => 'read_private_tc_tickets',
                    'edit_post' => 'edit_tc_ticket',
                    'delete_post' => 'delete_tc_ticket',
                    'read_post' => 'read_tc_ticket',
                    'edit_published_posts' => 'edit_published_tc_tickets',
                    'edit_private_posts' => 'edit_private_tc_tickets',
                    'delete_private_posts' => 'delete_private_tc_tickets',
                    'delete_published_posts' => 'delete_published_tc_tickets',
                    'create_posts' => 'create_tc_tickets',
                ),
                'hierarchical' => true,
                'query_var' => true,
            );

            register_post_type('tc_tickets', apply_filters('tc_ticket_type_post_type_args', $args));

            $args = array(
                'labels' => array('name' => __('API Keys', 'tc'),
                    'singular_name' => __('API Keys', 'tc'),
                    'add_new' => __('Create New', 'tc'),
                    'add_new_item' => __('Create New API Keys', 'tc'),
                    'edit_item' => __('Edit API Keys', 'tc'),
                    'edit' => __('Edit', 'tc'),
                    'new_item' => __('New API Key', 'tc'),
                    'view_item' => __('View API Key', 'tc'),
                    'search_items' => __('Search API Keys', 'tc'),
                    'not_found' => __('No API Keys Found', 'tc'),
                    'not_found_in_trash' => __('No API Keys found in Trash', 'tc'),
                    'view' => __('View API Key', 'tc')
                ),
                'public' => true,
                'show_ui' => false,
                'publicly_queryable' => true,
                'capability_type' => 'page', //page
                'hierarchical' => false,
                'query_var' => true,
            );

            register_post_type('tc_api_keys', $args);

            $args = array(
                'labels' => array('name' => __('Attendees & Tickets', 'tc'),
                    'singular_name' => __('Attendee', 'tc'),
                    'add_new' => __('Create Attendee', 'tc'),
                    'add_new_item' => __('Create New Attendee', 'tc'),
                    'edit_item' => __('Check-in Details', 'tc'),
                    'edit' => __('Edit', 'tc'),
                    'new_item' => __('New Attendee', 'tc'),
                    'view_item' => __('View Attendee', 'tc'),
                    'search_items' => __('Search Attendees', 'tc'),
                    'not_found' => __('No Attendees Found', 'tc'),
                    'not_found_in_trash' => __('No Attendee records found in Trash', 'tc'),
                    'view' => __('View Attendee', 'tc')
                ),
                'public' => false,
                'show_ui' => false,
                'publicly_queryable' => true,
                'capability_type' => 'tc_tickets_instances',
                'map_meta_cap' => true,
                'capabilities' => array(
// meta caps (don't assign these to roles)
                    'edit_post' => 'edit_tc_tickets_instance',
                    'read_post' => 'read_tc_tickets_instance',
                    'delete_post' => 'delete_tc_tickets_instance',
                    // primitive/meta caps
                    'create_posts' => 'create_tc_tickets_instances',
                    'edit_posts' => 'edit_tc_tickets_instances',
                    'edit_others_posts' => 'edit_others_posts_tc_tickets_instances',
                    'publish_posts' => 'publish_tc_tickets_instances',
                    'read_private_posts' => 'read_private_tc_tickets_instances',
                    'read' => 'read',
                    'delete_posts' => 'delete_tc_tickets_instances',
                    'delete_private_posts' => 'delete_private_tc_tickets_instances',
                    'delete_published_posts' => 'delete_published_tc_tickets_instances',
                    'delete_others_posts' => 'delete_others_tc_tickets_instances',
                    'edit_private_posts' => 'edit_private_tc_tickets_instances',
                    'edit_published_posts' => 'edit_published_tc_tickets_instances'
                ),
                'hierarchical' => true,
                'query_var' => true,
            );

            register_post_type('tc_tickets_instances', apply_filters('tc_tickets_instances_post_type_args', $args));

            register_post_type('tc_orders', apply_filters('tc_orders_post_type_args', array(
                'labels' => array('name' => __('Orders', 'tc'),
                    'singular_name' => __('Order', 'tc'),
                    'edit' => __('Edit', 'tc'),
                    'view_item' => __('View Order', 'tc'),
                    'search_items' => __('Search Orders', 'tc'),
                    'not_found' => __('No Orders Found', 'tc')
                ),
                'public' => false,
                'show_ui' => false,
                'hierarchical' => false,
                'rewrite' => false,
                'query_var' => false,
                'supports' => array(),
                'capability_type' => 'tc_orders',
                'map_meta_cap' => true,
                'capabilities' => array(
// meta caps (don't assign these to roles)
                    'edit_post' => 'edit_tc_order',
                    'read_post' => 'read_tc_order',
                    'delete_post' => 'delete_tc_order',
                    // primitive/meta caps
                    'create_posts' => 'create_tc_orders',
                    'edit_posts' => 'edit_tc_orders',
                    'edit_others_posts' => 'edit_others_posts_tc_orders',
                    'publish_posts' => 'publish_tc_orders',
                    'read_private_posts' => 'read_private_tc_orders',
                    'read' => 'read',
                    'delete_posts' => 'delete_tc_orders',
                    'delete_private_posts' => 'delete_private_tc_orders',
                    'delete_published_posts' => 'delete_published_tc_orders',
                    'delete_others_posts' => 'delete_others_tc_orders',
                    'edit_private_posts' => 'edit_private_tc_orders',
                    'edit_published_posts' => 'edit_published_tc_orders'
                ),
            )));

            register_post_status('order_received', array(
                'label' => __('Received', 'tc'),
                'label_count' => _n_noop('Received <span class="count">(%s)</span>', 'Received <span class="count">(%s)</span>', 'tc'),
                'post_type' => 'tc_orders',
                'public' => true
            ));

            register_post_status('order_paid', array(
                'label' => __('Paid', 'tc'),
                'label_count' => _n_noop('Paid <span class="count">(%s)</span>', 'Paid <span class="count">(%s)</span>', 'tc'),
                'post_type' => 'tc_orders',
                'public' => true
            ));

            register_post_status('order_cancelled', array(
                'label' => __('Cancelled', 'tc'),
                'label_count' => _n_noop('Cancelled <span class="count">(%s)</span>', 'Cancelled <span class="count">(%s)</span>', 'tc'),
                'post_type' => 'tc_orders',
                'public' => true
            ));

            register_post_status('order_fraud', array(
                'label' => __('Fraud', 'tc'),
                'label_count' => _n_noop('Fraud <span class="count">(%s)</span>', 'Fraud <span class="count">(%s)</span>', 'tc'),
                'post_type' => 'tc_orders',
                'public' => true
            ));

            $args = array(
                'labels' => array('name' => __('Templates', 'tc'),
                    'singular_name' => __('Templates', 'tc'),
                    'add_new' => __('Create New', 'tc'),
                    'add_new_item' => __('Create New Template', 'tc'),
                    'edit_item' => __('Edit Templates', 'tc'),
                    'edit' => __('Edit', 'tc'),
                    'new_item' => __('New Template', 'tc'),
                    'view_item' => __('View Template', 'tc'),
                    'search_items' => __('Search Templates', 'tc'),
                    'not_found' => __('No Templates Found', 'tc'),
                    'not_found_in_trash' => __('No Templates found in Trash', 'tc'),
                    'view' => __('View Template', 'tc')
                ),
                'public' => true,
                'show_ui' => false,
                'publicly_queryable' => true,
                'capability_type' => 'page',
                'hierarchical' => false,
                'query_var' => true,
            );

            register_post_type('tc_templates', apply_filters('tc_templates_post_type_args', $args));
        }

        function remove_unnecessary_plugin_menu_items($items) {

            $i = 0;
            foreach ($items as $item) {
                if ($item->url == $this->get_payment_page(true) || $item->url == $this->get_confirmation_page(true) || $item->url == $this->get_order_page(true)) {
                    unset($items[$i]);
                }
                $i++;
            }

            return $items;
        }

        function remove_unnecessary_plugin_menu_items_wp_page_menu_args($args) {

            $exlude_plugin_pages[] = $this->get_payment_page();
            $exlude_plugin_pages[] = $this->get_confirmation_page();
            $exlude_plugin_pages[] = $this->get_order_page();

            $args['exclude'] = implode(',', $exlude_plugin_pages);

            return $args;
        }

        function in_pages_doesnt_require_media() {
            $page = isset($_GET['page']) ? sanitize_key($_GET['page']) : '';

            $pages_doesnt_requires_media = array(
                'tc_discount_codes',
                'tc_orders',
                'tc_attendees',
                'tc_addons'
            );
            if (in_array($page, $pages_doesnt_requires_media)) {
                return true;
            } else {
                return false;
            }
        }

        function get_current_post_type() {
            global $post, $typenow, $current_screen;

            //we have a post so we can just get the post type from that
            if ($post && $post->post_type)
                return $post->post_type;

            //check the global $typenow - set in admin.php
            elseif ($typenow)
                return $typenow;

            //check the global $current_screen object - set in sceen.php
            elseif ($current_screen && $current_screen->post_type)
                return $current_screen->post_type;

            //lastly check the post_type querystring
            elseif (isset($_REQUEST['post_type']))
                return sanitize_key($_REQUEST['post_type']);

            //we do not know the post type!
            return null;
        }

        function in_admin_pages_require_admin_styles() {
            $post_type = $this->get_current_post_type();

            /* if (is_null($post_type)) {
              return true;
              } */

            $post_types_require_admin_styles = array(
                'tc_forms',
                'tc_form_fields',
                'tc_custom_fonts',
                'tc_seat_charts',
                'tc_events',
                'tc_tickets',
                'tc_api_keys',
                'tc_tickets_instances',
                'tc_orders',
                'tc_templates',
                'tc_volume_discount',
                'product',
                'product_variation'
            );

            if (isset($_GET['page'])) {
                $tc_get_page = $_GET['page'];
            } else {
                $tc_get_page = '';
            }
            if (in_array($post_type, $post_types_require_admin_styles) || $tc_get_page == 'tc_ticket_templates') {
                return true;
            } else {
                return false;
            }
        }

        function admin_header() {
            global $wp_version, $post_type;

            /* menu icon */
            if ($wp_version >= 3.8) {
                wp_register_style('tc-admin-menu-icon', $this->plugin_url . 'css/admin-icon.css');
                wp_enqueue_style('tc-admin-menu-icon');
            }

            wp_enqueue_style($this->name . '-admin', $this->plugin_url . 'css/admin.css', array(), $this->version);

            if ($this->in_admin_pages_require_admin_styles()) {

                wp_enqueue_style($this->name . '-admin-jquery-ui', '//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css', array(), $this->version);
                wp_enqueue_style($this->name . '-chosen', $this->plugin_url . 'css/chosen.min.css', array(), $this->version);
                wp_enqueue_style($this->name . '-simple-dtpicker', $this->plugin_url . 'css/jquery.simple-dtpicker.css', array(), $this->version);
                wp_enqueue_style('font-awesome', $this->plugin_url . 'css/font-awesome.min.css', array(), $this->version);

                if (apply_filters('tc_use_admin_colors_css', true) == true) {
                    wp_enqueue_style($this->name . '-colors', $this->plugin_url . 'css/colors.css', array(), $this->version);
                }
            }


            if (!$this->in_pages_doesnt_require_media()) {
                wp_enqueue_style('thickbox');
                wp_enqueue_script('thickbox');
                wp_enqueue_media();
                wp_enqueue_script('media-upload');
                wp_enqueue_style('wp-color-picker');
            }

            if ((isset($post_type) && $post_type == 'tc_events' || $post_type == 'tc_tickets' || $post_type == 'tc_speakers' || $post_type == 'shop_order' || $post_type == 'tc_orders' || $post_type == 'product' || $post_type == 'tc_seat_charts') || isset($_REQUEST['post_type']) && $_REQUEST['post_type'] == 'tc_events' || (isset($_REQUEST['page']) && preg_match("/tc_/", $_REQUEST['page']))) {
                wp_enqueue_script('tc-jquery-admin-validate', $this->plugin_url . 'js/jquery.validate.min.js', array('jquery'), $this->version);
                wp_enqueue_script('tc-jquery-validate-additional-methods', $this->plugin_url . 'js/additional-methods.min.js', array('tc-jquery-admin-validate'), $this->version);
            }


            wp_enqueue_script($this->name . '-admin', $this->plugin_url . 'js/admin.js', array('jquery', 'tc-jquery-admin-validate', 'tc-jquery-validate-additional-methods', 'jquery-ui-tooltip', 'jquery-ui-core', 'jquery-ui-sortable', 'jquery-ui-draggable', 'jquery-ui-droppable', 'jquery-ui-accordion', 'wp-color-picker'), false, false);
            wp_localize_script($this->name . '-admin', 'tc_vars', array(
                'ajaxUrl' => apply_filters('tc_ajaxurl', admin_url('admin-ajax.php', (is_ssl() ? 'https' : 'http'))),
                'animated_transitions' => apply_filters('tc_animated_transitions', true),
                'delete_confirmation_message' => __('Please confirm that you want to delete it permanently?', 'tc'),
                'order_status_changed_message' => __('Order status changed successfully.', 'tc'),
                'order_confirmation_email_resent_message' => __('Order confirmation e-mail resent successfully.', 'tc'),
                'order_confirmation_email_resending_message' => __('Sending...', 'tc'),
            ));

            wp_enqueue_script($this->name . '-chosen', $this->plugin_url . 'js/chosen.jquery.min.js', array($this->name . '-admin'), false, false);
            wp_enqueue_script($this->name . '-simple-dtpicker', $this->plugin_url . 'js/jquery.simple-dtpicker.js', array('jquery'), $this->version);
//}
        }

    }

    global $tc, $license_key;
    $tc = new TC();
}

if (!function_exists('tc_multiple_plugin_versions_active_check')) {
    add_action('admin_notices', 'tc_multiple_plugin_versions_active_check');

    function tc_multiple_plugin_versions_active_check() {
        global $tc;
        if (current_user_can('manage_options')) {//show warning to the admin only
            if (is_plugin_active('tickera-event-ticketing-system/tickera.php') && is_plugin_active('tickera/tickera.php')) {
                echo '<div class="error"><p>';
                echo '<strong>' . __('You have both FREE and PREMIUM version of Tickera plugin activated. In order to avoid conflicts, please deactivate one of them. </br> Once premium version is installed, free version is no longer needed and can be removed. Leaving free version active will block premium features of Tickera!', 'tc') . '</strong>';
                echo '</p></div>';
            }
        }
    }

}

if (!function_exists('tc_is_json')) {

    function tc_is_json($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

}

if (!function_exists('tc_plugin_updater')) {

    function tc_plugin_updater($plugin_slug = '', $plugin_file = '') {
        global $tc;

        if (!defined('TC_NU')) {//updates are allowed
            $updater_file = $tc->plugin_dir . 'includes/plugin-update-checker/plugin-update-checker.php';

            if (file_exists($updater_file)) {
                $tc_general_settings = get_option('tc_general_setting', false);
                $license_key = (defined('TC_LCK') && TC_LCK !== '') ? TC_LCK : (isset($tc_general_settings['license_key']) && $tc_general_settings['license_key'] !== '' ? $tc_general_settings['license_key'] : '');

                require_once($updater_file);
                $tc_plugin_update_checker = PucFactory::buildUpdateChecker('http://update.tickera.com/?action=get_metadata&slug=' . $plugin_slug . '&license_key=' . $license_key, $plugin_file, $plugin_slug, 1);
            }
        }
    }

}

tc_plugin_updater('tickera', __FILE__);
?>