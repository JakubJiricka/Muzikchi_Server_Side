<?php
/*
  Plugin Name: Tickera Seating Charts
  Plugin URI: http://tickera.com/
  Description: Create seating charts for your event
  Author: Tickera.com
  Author URI: http://tickera.com/
  Version: 0.39
  Text Domain: tcsc
  Domain Path: /languages
  Copyright 2017 Tickera (http://tickera.com/)
 */

if (!class_exists('TC_Seat_Chart')) {

    class TC_Seat_Chart {

        var $version = '0.39';
        var $tc_version_required = '3.2.8.5';
        var $title = 'Seating Charts';
        var $name = 'tc-seat-charts';
        var $dir_name = 'seating-charts';
        var $location = 'plugins';
        var $plugin_dir = '';
        var $plugin_url = '';

        function __construct() {

            $this->maybe_create_html_dir();

            require_once( $this->plugin_dir . 'includes/php-html-css-js-minifier.php' );
            require_once( $this->plugin_dir . 'includes/class.tc_firebase.php' );

            add_action('init', array($this, 'register_custom_posts'), 0);
            add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts_and_styles'));
            add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts_and_styles'));
            add_action('save_post', 'TC_Seat_Chart::save_admin'); //save_metabox_values

            add_action('wp_ajax_tc_get_event_ticket_types', array($this, 'get_event_ticket_types_select'));
            add_action('wp_ajax_nopriv_tc_get_event_ticket_types', array($this, 'get_event_ticket_types_select'));

            add_action('wp_ajax_nopriv_tc_add_seat_to_cart', array($this, 'tc_add_seat_to_cart'));
            add_action('wp_ajax_tc_add_seat_to_cart', array($this, 'tc_add_seat_to_cart'));

            add_action('wp_ajax_nopriv_tc_add_seat_to_cart_woo', array($this, 'tc_add_seat_to_cart_woo'));
            add_action('wp_ajax_tc_add_seat_to_cart_woo', array($this, 'tc_add_seat_to_cart_woo'));

            add_action('wp_ajax_nopriv_tc_add_seat_to_cart_woo_variation', array($this, 'tc_add_seat_to_cart_woo_variation'));
            add_action('wp_ajax_tc_add_seat_to_cart_woo_variation', array($this, 'tc_add_seat_to_cart_woo_variation'));

            add_action('wp_ajax_tc_wc_get_cart_info', array($this, 'tc_wc_get_cart_info'));

            add_action('wp_ajax_nopriv_tc_remove_seat_from_cart', array($this, 'tc_remove_seat_from_cart'));
            add_action('wp_ajax_tc_remove_seat_from_cart', array($this, 'tc_remove_seat_from_cart'));

            add_filter('tc_ticket_fields', array($this, 'add_color_picker_field'), 10, 1);

            add_filter('tc_settings_new_menus', array($this, 'tc_settings_new_menus'), 10, 1);
            add_action('tc_settings_menu_seat_charts', array($this, 'tc_settings_menu_seat_charts'));

            add_action('wp_ajax_tc_remove_order_session_data', array($this, 'ajax_remove_order_session_data'));
            add_action('wp_ajax_nopriv_tc_remove_order_session_data', array($this, 'ajax_remove_order_session_data'));

            add_action('tc_cart_before_attendee_info_wrap', array($this, 'tc_add_cart_seat_info'), 10, 2);

            add_filter('tc_cart_attendee_info_caption', array($this, 'tc_add_seat_info_to_attendee_caption'), 10, 3);

            add_filter('tc_checkout_owner_info_ticket_title', array($this, 'tc_maybe_add_seat_info_to_ticket_type'), 10, 4);
            add_filter('post_row_actions', array($this, 'post_row_actions'), 10, 2);

            add_filter('the_content', array($this, 'modify_the_content'));

            add_action('admin_notices', array($this, 'admin_notices'));

            add_filter('tc_editable_quantity', array($this, 'tc_editable_quantity'), 10, 3);

            add_action('tc_cart_before_error_pass_check', array($this, 'check_if_seats_are_available_before_cart_error_check'), 0, 2);

            add_filter('tc_shortcodes', array($this, 'tc_modify_shortcode_builder_list'));

            require_once( $this->plugin_dir . 'includes/functions.php' );

            require_once( $this->plugin_dir . 'includes/class.shortcodes.php' );

            add_action('wp_ajax_nopriv_tc_seat_chart_get_wc_variations', array($this, 'tc_seat_chart_get_wc_variations'));
            add_action('wp_ajax_tc_seat_chart_get_wc_variations', array($this, 'tc_seat_chart_get_wc_variations'));

            add_filter('woocommerce_quantity_input_args', array($this, 'tc_seat_chart_maybe_modify_woocommerce_quantity_input_args'), 10, 2);
            add_action('woocommerce_after_checkout_validation', array($this, 'woo_check_if_seats_are_available_before_cart_error_check'), 10, 1);

            add_filter('tc_has_cart_or_payment_errors', array($this, 'tc_has_cart_or_payment_errors'), 10, 2);

            add_action('tc_woo_show_if_tc_ticket_before', array($this, 'woo_ticket_type_additional_meta'), 10, 1);

            add_action('woocommerce_process_product_meta', array($this, 'woo_ticket_type_additional_meta_save'), 10, 1);
            add_action('woocommerce_remove_cart_item', array($this, 'woo_cart_item_remove_seat'), 10, 1);
            //add_action('tc_woo_bridge_after_order_completed', array($this, 'delete_order_cookie'), 10, 1);

            add_action('init', array($this, 'load_plugin_textdomain'), 11);

            add_filter('post_row_actions', array($this, 'duplicate_seating_chart_row_action'), 10, 2);
            add_action('admin_action_tc_duplicate_seating_chart', array($this, 'duplicate_seating_chart_action'));

            add_action('admin_footer', array($this, 'admin_footer_styles'));

            add_action('edit_form_after_editor', array($this, 'seating_charts_admin'), 10, 1);

            add_filter('post_updated_messages', array($this, 'post_updated_messages'));

            add_filter('admin_body_class', array($this, 'add_body_class'));

            add_action('delete_post', array($this, 'delete_chart_html'), 10, 1);
            add_action('wp_trash_post', array($this, 'trash_chart'), 10, 1);

            add_action('woocommerce_cart_emptied', array($this, 'delete_cart_seats_persistent'), 10);

            add_action('tc_cart_col_after_ticket_type', array($this, 'tc_list_out_single_tickets'), 10, 1);
            add_action('woocommerce_cart_item_name', array($this, 'tc_list_out_single_tickets_woo'), 10, 3);
            //add_action('wp_logout', array($this, 'destroy_cookies'), 10);
            //add_action('edit_form_after_editor', array($this, 'maybe_save_duplicated_chart_values'), 99);

            add_filter('tc_delete_info_plugins_list', array($this, 'tc_delete_info_plugins_list'), 10, 1);
            add_action('tc_delete_plugins_data', array($this, 'tc_delete_plugins_data'), 10, 1);
        }

        function tc_delete_info_plugins_list($plugins) {
            $plugins[$this->name] = $this->title;
            return $plugins;
        }

        function tc_delete_plugins_data($submitted_data) {
            if (array_key_exists($this->name, $submitted_data)) {
                global $wpdb;

                //Delete posts and post metas
                $wpdb->query("
                DELETE 
                p, pm
                FROM $wpdb->posts p 
                JOIN $wpdb->postmeta pm on pm.post_id = p.id
		 WHERE p.post_type IN ('tc_seat_charts')
		"
                );

                //Delete options
                $options = array('tc_seat_charts_settings');

                foreach ($options as $option) {
                    delete_option($option);
                }

                //Delete directories and files
                $upload = wp_upload_dir();
                $upload_dir = $upload['basedir'];
                $upload_dir = $upload_dir . '/tc-seating-charts';

                TC::rrmdir($upload_dir);
            }
        }

        function trash_chart($post_id = false) {
            if ($post_id && get_post_type($post_id) == 'tc_seat_charts') {

                $post = array(
                    'ID' => $post_id,
                    'post_status' => 'trash'
                );

                wp_update_post($post);
            }
        }

        function tc_list_out_single_tickets_woo($tc_product_name, $cart_item, $cart_item_key) {
            $cart_seats = TC_Seat_Chart::get_cart_seats_cookie();
            $chart_id = $cart_seats[$ticket_type->details->ID][0][2];
            echo $tc_product_name;
            echo '<br /><div class="tc-cart-seat-wrap">';

            if ($cart_item['variation_id'] !== 0) {

                foreach (array_keys($cart_seats) as $key) {
                    if ($cart_item['variation_id'] == $key) {

                        foreach ($cart_seats[$cart_item['variation_id']] as $single_cart_seat) {
                            ?>
                            <span class="tc-single-cart-seat">
                                <?php
                                if (!empty($single_cart_seat[1])) {
                                    $remove_icon = '<span class="tc_cart_remove_icon tc_cart_seat_remove" title="' . __('Remove from Cart', 'tcsc') . '" data-ticket-type-id="' . (int) $cart_item['variation_id'] . '" data-seat-sign="' . $single_cart_seat[1] . '" data-seat-id="' . $single_cart_seat[0] . '" data-chart-id="' . (int) $single_cart_seat[2] . '"><i class="fa fa-times" aria-hidden="true"></i></span>';
                                    echo $remove_icon;
                                    echo $single_cart_seat[1];
                                }
                                ?>
                            </span>
                            <?php
                        }
                    }
                }
            } else {

                foreach (array_keys($cart_seats) as $key) {
                    if ($cart_item['product_id'] == $key) {

                        foreach ($cart_seats[$cart_item['product_id']] as $single_cart_seat) {
                            ?>
                            <span class="tc-single-cart-seat">
                                <?php
                                if (!empty($single_cart_seat[1])) {
                                    $remove_icon = '<span class="tc_cart_remove_icon tc_cart_seat_remove" title="' . __('Remove from Cart', 'tcsc') . '" data-ticket-type-id="' . (int) $cart_item['product_id'] . '" data-seat-sign="' . $single_cart_seat[1] . '" data-seat-id="' . $single_cart_seat[0] . '" data-chart-id="' . (int) $single_cart_seat[2] . '"><i class="fa fa-times" aria-hidden="true"></i></span>';
                                    echo $remove_icon;
                                    echo $single_cart_seat[1];
                                }
                                ?>
                            </span>
                            <?php
                        }
                    }
                }
            }

            echo "</div>";
        }

        function tc_list_out_single_tickets($ticket_type) {

            global $tc;
            $cart_seats = TC_Seat_Chart::get_cart_seats_cookie();
            $tc_cart_cookie = $tc->get_cart_cookie(true);

            $chart_id = $cart_seats[$ticket_type->details->ID][0][2];
            echo '<br /><div class="tc-cart-seat-wrap">';
            foreach (array_keys($cart_seats) as $key) {

                if ($ticket_type->id == $key) {
                    foreach ($cart_seats[$ticket_type->id] as $single_cart_seat) {
                        ?>
                        <span class="tc-single-cart-seat">
                            <?php
                            if (!empty($single_cart_seat[1])) {
                                $remove_icon = '<span class="tc_cart_remove_icon tc_cart_seat_remove" title="' . __('Remove from Cart', 'tcsc') . '" data-ticket-type-id="' . (int) $ticket_type->details->ID . '" data-seat-sign="' . $single_cart_seat[1] . '" data-seat-id="' . $single_cart_seat[0] . '" data-chart-id="' . (int) $single_cart_seat[2] . '"><i class="fa fa-times" aria-hidden="true"></i></span>';
                                echo $remove_icon;
                                echo $single_cart_seat[1];
                            }
                            ?>
                        </span>
                        <?php
                    }
                }
            }
            echo "</div>";
        }

        function delete_chart_html($post_id = false) {

            if ($post_id && get_post_type($post_id) == 'tc_seat_charts') {
                try {
                    $upload = wp_upload_dir();
                    $upload_dir = $upload['basedir'];
                    $upload_dir = $upload_dir . '/tc-seating-charts';

                    $filename_front = $post_id . '-front.tcsm';
                    $filename_admin = $post_id . '.tcsm';

                    $path_front = $upload_dir . '/' . $filename_front;
                    $path_admin = $upload_dir . '/' . $filename_admin;

                    if (file_exists($path_front)) {
                        try {
                            unlink($path_front);
                        } catch (Exception $e) {
                            
                        }
                    }
                    if (file_exists($path_admin)) {
                        try {
                            unlink($path_admin);
                        } catch (Exception $e) {
                            
                        }
                    }
                } catch (Exception $e) {
                    
                }
            }
        }

        function maybe_create_html_dir() {
            try {
                $upload = wp_upload_dir();
                $upload_dir = $upload['basedir'];
                $upload_dir = $upload_dir . '/tc-seating-charts';

                if (!is_dir($upload_dir)) {
                    @mkdir($upload_dir, 0755);

                    $filename = '.htaccess';
                    $path = $upload_dir . '/' . $filename;

                    if (!file_exists($path)) {
                        $htaccess = @fopen($path, "w");
                        $content = "Deny from all";
                        @fwrite($htaccess, $content);
                        @fclose($htaccess);
                        @chmod($path, 0644);
                    }
                }
            } catch (Exception $e) {
//we can't make a directory
            }
        }

        function add_body_class($classes) {
            if (get_post_type() == 'tc_seat_charts' && !isset($_GET['post_type'])) {
                $classes .= ' tc-seat-chart-single ';
                return $classes;
            }
        }

        function post_updated_messages($messages) {
            global $post;
            $class = 'updated notice notice-success is-dismissible tc-tickera-show';
            $permalink = get_permalink($post->ID);

            $upload = wp_upload_dir();
            $upload_dir = $upload['basedir'];
            $upload_dir = $upload_dir . '/tc-seating-charts';

            $messages['tc_seat_charts'] = array(
                0 => sprintf(__('<div id="message" class="%1$s tc-donothide"><p>Cannot save! Directory %2$s is not writable.</p></div> ', 'tcsc'), $class, '<strong>' . $upload_dir . '</strong>'),
                1 => sprintf(__('<div id="message" class="%1$s"><p>%2$s</p> </div> ', 'tcsc'), $class, __('Seating Chart updated.', 'tcsc')),
                4 => sprintf(__('<div id="message" class="%1$s"><p>%2$s</p> </div> ', 'tcsc'), $class, __('Seating Chart updated.', 'tcsc')),
                /* translators: %s: date and time of the revision */
                5 => isset($_GET['revision']) ? sprintf(__('<div id="message" class="%1$s">Seating Chart restored to revision from %s</div>'), $class, wp_post_revision_title((int) $_GET['revision'], false)) : false,
                6 => sprintf(__('<div id="message" class="%1$s"><p>%2$s</p> </div> ', 'tcsc'), $class, __('Seating Chart published.', 'tcsc')),
                7 => sprintf(__('<div id="message" class="%1$s"><p>%2$s</p> </div> ', 'tcsc'), $class, __('Seating Chart saved.', 'tcsc')),
                8 => sprintf(__('<div id="message" class="%1$s"><p>Seating Chart submitted. <a target="_blank" href="%s">View Preview</a></p></div>'), $class, esc_url(add_query_arg('preview', 'true', isset($permalink) ? $permalink : ''))),
                9 => sprintf(
                        __('<div id="message" class="%1$s">Seating Chart post scheduled for: <strong>%1$s</strong>.</div>'), $class, date_i18n(__('M j, Y @ G:i'), strtotime($post->post_date))
                ),
                10 => sprintf(__('<div id="message" class="%1$s"><p>%2$s</p> </div> ', 'tcsc'), $class, __('Seating Chart draft updated.', 'tcsc'))
            );
            return $messages;
        }

        function seating_charts_admin($post) {
            global $post_type;

            if ($post_type == 'tc_seat_charts') {
                $this->admin_enqueue_scripts_and_styles();
                include($this->plugin_dir . 'includes/admin-pages/seat_charts_admin.php');
            }
        }

        /**
         * Enqueue admin scripts and styles
         * @global type $post
         * @global type $post_type
         */
        function admin_enqueue_scripts_and_styles() {
            global $post, $post_type;

            if ($post_type == 'tc_seat_charts') {

                $tc_seat_charts_settings = TC_Seat_Chart::get_settings();

                wp_enqueue_script('jquery-pan', plugins_url('js/jquery.pan.js', __FILE__), array('jquery'), $this->version, true);
                wp_enqueue_style('tc-seat-charts-assets-admin', plugins_url('assets/style-admin.css', __FILE__));

                if (isset($post->ID)) {
                    wp_enqueue_style('tc-seat-charts-assets-admin-single', plugins_url('assets/style-admin-single.css', __FILE__));
                }

                wp_enqueue_script('tc-tinymce', plugins_url('assets/js/admin/wordpress-tinymce.js', __FILE__));

                wp_enqueue_style('tc-seat-charts-jquery-ui', plugins_url('assets/js/jquery-ui/jquery-ui.css', __FILE__));

                wp_enqueue_style('jquery-ui-rotatable', plugins_url('assets/jquery.ui.rotatable.css', __FILE__));
                wp_enqueue_script('tc-jquery-ui-rotatable', plugins_url('assets/js/tc.jquery.ui.rotatable.js', __FILE__));

                wp_enqueue_script('tc-seats-controls-admin', plugins_url('assets/js/admin/controls.js', __FILE__), array('jquery'), $this->version, true);
                wp_localize_script('tc-seats-controls-admin', 'tc_controls_vars', array(
                    'ajaxUrl' => admin_url('admin-ajax.php', (is_ssl() ? 'https' : 'http')),
                    'are_you_sure' => __('Are you sure?', 'tcsc'),
                    'label_error_message' => __('Label error message', 'tcsc'),
                    'yes' => __('Yes', 'tcsc'),
                    'ok' => __('OK', 'tcsc'),
                    'no' => __('No', 'tcsc'),
                    'tc_reserved_seat_color' => isset($tc_seat_charts_settings['reserved_seat_color']) ? $tc_seat_charts_settings['reserved_seat_color'] : '#DCCBCB',
                ));

                wp_enqueue_script('tc-seats-admin', plugins_url('assets/js/admin/seats.js', __FILE__), array('jquery', 'tc-seats-controls-admin', 'jquery-ui-selectable', 'jquery-ui-draggable', 'tc-jquery-ui-rotatable'), $this->version, true);
                wp_enqueue_script('tc-text-admin', plugins_url('assets/js/admin/text.js', __FILE__), array('jquery', 'jquery-ui-selectable', 'jquery-ui-draggable', 'tc-jquery-ui-rotatable'), $this->version, true);
                wp_enqueue_script('tc-elements-admin', plugins_url('assets/js/admin/elements.js', __FILE__), array('jquery', 'jquery-ui-selectable', 'jquery-ui-draggable', 'tc-jquery-ui-rotatable', 'jquery-ui-resizable'), $this->version, true);
                wp_enqueue_script('tc-standing-admin', plugins_url('assets/js/admin/standing.js', __FILE__), array('jquery', 'jquery-ui-selectable', 'jquery-ui-draggable', 'tc-jquery-ui-rotatable', 'jquery-ui-resizable'), $this->version, true);
                wp_localize_script('tc-standing-admin', 'standing_translation', array(
                    'edit' => __('Edit', 'tcsc'),
                    'Create' => __('Create', 'tcsc'),
                ));

                wp_enqueue_script('tc-tables-admin', plugins_url('assets/js/admin/tables.js', __FILE__), array('tc-elements-admin'), $this->version, true);

                wp_enqueue_script('tc-unslider', plugins_url('assets/js/unslider/src/js/unslider.js', __FILE__), false, $this->version, true);

                wp_enqueue_script('tc-seats-keypress-admin', plugins_url('assets/js/admin/keypress-2.1.3.min.js', __FILE__), array('jquery'), $this->version, true);
                wp_enqueue_script('tc-settings-admin', plugins_url('assets/js/admin/settings.js', __FILE__), array('jquery', 'jquery-ui-selectable', 'jquery-ui-draggable', 'tc-jquery-ui-rotatable', 'jquery-ui-resizable', 'jquery-pan', 'tc-seats-admin', 'tc-text-admin', 'tc-elements-admin', 'jquery-ui-tabs', 'jquery-ui-dialog'), $this->version, true);

                wp_enqueue_script('tc-seat-labels-admin', plugins_url('assets/js/admin/labels.js', __FILE__), array('tc-settings-admin'), $this->version, true);
                wp_enqueue_script('tc-seats-common-admin', plugins_url('assets/js/admin/common.js', __FILE__), array('jquery', 'jquery-ui-selectable', 'jquery-ui-draggable', 'tc-jquery-ui-rotatable', 'jquery-ui-resizable', 'jquery-pan', 'tc-seats-admin', 'tc-text-admin', 'tc-elements-admin', 'jquery-ui-tabs', 'jquery-ui-dialog'), $this->version, true);

                wp_enqueue_style('tc-unslider', plugins_url('assets/js/unslider/src/scss/unslider.css', __FILE__));

                wp_enqueue_script('jquery-ui-selectable');

                wp_enqueue_script('tc-seats-tooltips-admin', plugins_url('assets/js/admin/tooltips.js', __FILE__), array('tc-seats-common-admin'), $this->version, true);
                wp_localize_script('tc-seats-tooltips-admin', 'tc_seatings_tooltips', array(
                    'pan_wrapper' => __('Left click and drag to pan (or use arrow keys). Use mouse wheel to zoom', 'tcsc'),
                    'draggable' => __('Click and drag to move', 'tcsc'),
                    'rotate' => __('Click and drag to rotate. Hold SHIFT key to rotate it in precise steps.', 'tcsc'),
                    'delete' => __('Click to remove the object permanently', 'tcsc'),
                    'copy' => __('Click to copy the object', 'tcsc'),
                    'edit' => __('Click to edit the object', 'tcsc'),
                    'resizable' => __('Click and drag to resize. Hold SHIFT key to resize the object proportionally.', 'tcsc'),
                    'drag_slider' => __('Drag to change the value', 'tcsc'),
                    'click_option' => __('Click to change the value', 'tcsc'),
                    'selectable' => __('Click to select / deselect seats. Use CTRL / CMD keys or mouse lasso to mark multiple desired seats at once.', 'tcsc'),
                    'save' => __('Click to save changes', 'tcsc'),
                ));
            }



            wp_enqueue_style('wp-color-picker');
            wp_enqueue_script('tc-ticket-type-color-picker-admin', plugins_url('js/ticket-type-color-picker.js', __FILE__), array('jquery', 'wp-color-picker'), $this->version, true);
            wp_enqueue_script('tc-admin-js', plugins_url('assets/js/admin/admin.js', __FILE__), '', $this->version, true);
        }

        function admin_footer_styles() {
            ?>
            <style type="text/css" id="tc_seating_chart_footer_style">
                /*Some style here*/
            </style>
            <?php
        }

        /**
         * Load Localisation files (first translation file found will be loaded, others will be ignored)
         */
        public function load_plugin_textdomain() {
            $locale = apply_filters('plugin_locale', get_locale(), 'tcsc');
            load_textdomain('tcsc', WP_LANG_DIR . '/' . $locale . '.mo');
            load_textdomain('tcsc', WP_LANG_DIR . '/tc-seat-charts/' . $locale . '.mo');
            load_plugin_textdomain('tcsc', false, plugin_basename(dirname(__FILE__)) . "/languages");
        }

        function duplicate_seating_chart_row_action($actions, $post) {
            if (current_user_can('edit_posts') && $post->post_type == 'tc_seat_charts') {
                unset($actions['inline hide-if-no-js']);

                $duplicate_url = add_query_arg(array(
                    'post_type' => 'tc_seat_charts',
                    'action' => 'tc_duplicate_seating_chart',
                    'post' => $post->ID
                        ), admin_url('edit.php'));
                $actions['duplicate'] = '<a href="' . $duplicate_url . '" title="' . esc_attr(__('Duplicate this Seating Chart', 'tcsc')) . '" rel="permalink">' . __('Duplicate', 'tcsc') . '</a>';
            }
            return $actions;
        }

        function maybe_save_duplicated_chart_values() {
            if (isset($_GET['state']) && $_GET['state'] == 'tcclnd') {
                /* if ($post_type == 'tc_seat_charts') {
                  $this->admin_enqueue_scripts_and_styles();
                  } */
                ?>
                <script type="text/javascript">
                    jQuery(document).ready(function ($) {
                        //$('.tc-save-button').click();
                        //window.tc_controls.save();
                    });
                </script>
                <?php
            }
        }

        public static function maybe_duplicate_chart() {
            global $post;
            if (isset($_GET['state']) && $_GET['state'] == 'duplicated' && isset($_GET['from_post'])) {
                $from_post_id = $_GET['from_post'];

                $upload = wp_upload_dir();
                $upload_dir = $upload['basedir'];
                $upload_dir = $upload_dir . '/tc-seating-charts';

                //ADMIN file initial
                $filename = $from_post_id . '.tcsm';
                $path_admin = $upload_dir . '/' . $filename;

                //ADMIN: Current file contents
                $handle = fopen($path_admin, "r");
                $file_content = fread($handle, filesize($path_admin));

                //ADMIN file NEW
                $filename = $post->ID . '.tcsm';
                $path_admin_new = $upload_dir . '/' . $filename;

                $file_admin_new = @fopen($path_admin_new, "w");

                @fwrite($file_admin_new, $file_content);
                @fclose($file_admin_new);
                @chmod($path_admin_new, 0644);

                //FRONT file initial
                $filename = $from_post_id . '-front.tcsm';
                $path_front = $upload_dir . '/' . $filename;

                //FRONT: Current file contents
                $handle = fopen($path_front, "r");
                $file_content = fread($handle, filesize($path_front));

                //FRONT file NEW
                $filename = $post->ID . '-front.tcsm';
                $path_front_new = $upload_dir . '/' . $filename;

                $file_front_new = @fopen($path_front_new, "w");

                @fwrite($file_front_new, $file_content);
                @fclose($file_front_new);
                @chmod($path_front_new, 0644);

                //tc_seat_reserved

                wp_redirect(admin_url('post.php?post=' . $post->ID . '&action=edit&state=tcclnd'));
            }
        }

        function duplicate_seating_chart_action($post_id = false) {
            global $wpdb;

            $duplicate_title_extension = apply_filters('tc_seating_charts_duplicate_title_extension', __(' [duplicate]', 'tcsc'));

            if ($post_id !== false) {
                if (!( isset($_GET['post']) || isset($_POST['post']) || ( isset($_REQUEST['action']) && 'tc_duplicate_seating_chart' == $_REQUEST['action'] ) )) {
                    wp_die(__('No seating chart to duplicate has been supplied!', 'tcsc'));
                }
            }

            /*
             * get the original post id
             */
            $post_id = $post_id ? $post_id : (isset($_GET['post']) ? absint($_GET['post']) : absint($_POST['post']) );
            /*
             * and all the original post data then
             */
            $post = get_post($post_id);

            /*
             * if you don't want current user to be the new post author,
             * then change next couple of lines to this: $new_post_author = $post->post_author;
             */
            $current_user = wp_get_current_user();
            $new_post_author = $current_user->ID;

            /*
             * if post data exists, create the post duplicate
             */
            if (isset($post) && $post != null) {

                /*
                 * new post data array
                 */

                $new_post_author = wp_get_current_user();
                $new_post_date = current_time('mysql');
                $new_post_date_gmt = get_gmt_from_date($new_post_date);

                $args = apply_filters('tc_duplicate_seating_chart_args', array(
                    'post_author' => $new_post_author->ID,
                    'post_date' => $new_post_date,
                    'post_date_gmt' => $new_post_date_gmt,
                    'post_content' => $post->post_content,
                    'post_content_filtered' => $post->post_content_filtered,
                    'post_title' => $post->post_title . $duplicate_title_extension,
                    'post_excerpt' => $post->post_excerpt,
                    'post_status' => 'draft',
                    'post_type' => $post->post_type,
                    'comment_status' => $post->comment_status,
                    'ping_status' => $post->ping_status,
                    'post_password' => $post->post_password,
                    'to_ping' => $post->to_ping,
                    'pinged' => $post->pinged,
                    'post_modified' => $new_post_date,
                    'post_modified_gmt' => $new_post_date_gmt,
                    'menu_order' => $post->menu_order,
                    'post_mime_type' => $post->post_mime_type,
                        ), $post_id);

                /*
                 * insert the post by wp_insert_post() function
                 */
                $new_post_id = wp_insert_post($args);

                /*
                 * get all current post terms ad set them to the new post draft
                 */
                $taxonomies = get_object_taxonomies($post->post_type); // returns array of taxonomy names for post type, ex array("category", "post_tag");

                foreach ($taxonomies as $taxonomy) {
                    $post_terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs'));
                    wp_set_object_terms($new_post_id, $post_terms, $taxonomy, false);
                }

                /*
                 * duplicate all post meta
                 */
                $post_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$post_id");

                if (count($post_meta_infos) != 0) {
                    $sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
                    foreach ($post_meta_infos as $meta_info) {
                        $meta_key = $meta_info->meta_key;
                        $meta_value = addslashes($meta_info->meta_value);
                        $sql_query_sel[] = "SELECT $new_post_id, '$meta_key', '$meta_value'";
                    }
                    $sql_query .= implode(" UNION ALL ", $sql_query_sel);
                    $wpdb->query($sql_query);
                }

                /* $event_id = get_post_meta($post_id, 'event_name', true);

                  if (is_numeric($event_id)) {
                  TC_Better_Events::tc_duplicate_event_as_draft($event_id, ' [duplicate]', 'tc_seating_chart', $new_post_id, $post_id, false);
                  } */

                do_action('tc_after_seat_chart_duplication', $new_post_id, $post_id);
                /*
                 * finally, redirect to the edit post screen for the new draft
                 */
                $new_post_url = add_query_arg(array(
                    'post' => $new_post_id,
                    'action' => 'edit',
                    'post' => $new_post_id,
                    'state' => 'duplicated',
                    'from_post' => $post_id
                        ), admin_url('post.php'));

                wp_redirect($new_post_url);
                exit;
            } else {
                wp_die('Post creation failed, could not find original post: ' . $post_id);
            }
        }

        /**
         * Delete seat in-cart cookie after order completion
         * @global type $tc
         */
        function delete_order_cookie() {
            global $tc;
            $tc->remove_order_session_data(apply_filters('tc_seating_charts_remove_order_session_data_js_fallback', false));
        }

        /**
         * Adds Type select box on the product edit screen
         */
        function woo_ticket_type_additional_meta() {
            ?>
            <div class="hide_if_grouped hide_if_external">
                <?php
                woocommerce_wp_text_input(
                        array(
                            'id' => '_seat_color',
                            'label' => __('Seat Color', 'tcsc'),
                            'desc_tip' => 'true',
                            'description' => __('Color which will be visible on a seating chart(s)', 'tcsc'),
                            'class' => 'tc-color-picker'
                        )
                );
                ?>
            </div>
            <?php
        }

        /**
         * Saves type of ticket for Woo products
         * @param type $post_id
         */
        function woo_ticket_type_additional_meta_save($post_id) {
            $post_id = (int) $post_id;
// Check if product is a ticket
            $_tc_is_ticket = isset($_POST['_tc_is_ticket']) ? 'yes' : 'no';

            if ($_tc_is_ticket == 'yes') {
                if ($_POST['product-type'] == 'simple' || $_POST['product-type'] == 'variable') {
                    update_post_meta($post_id, '_seat_color', $_POST['_seat_color']);
                } else {
                    delete_post_meta($post_id, '_seat_color');
                }
            } else {
                delete_post_meta($post_id, '_seat_color');
            }
        }

        /**
         * Disable quantity change for products (and variations) in the cart if the product is a seat
         * @param type $args
         * @param type $product
         * @return type
         */
        function tc_seat_chart_maybe_modify_woocommerce_quantity_input_args($args, $product) {
            $cart_seats = TC_Seat_Chart::get_cart_seats_cookie();

            $product_id = $product->is_type('variation') ? $product->get_parent_id() : $product->get_id();
            $variation_id = $product->is_type('variation') ? $product->get_id() : 0;

            $pid = $variation_id > 0 ? $variation_id : $product_id;

            if (isset($cart_seats) && (isset($cart_seats[$variation_id]) || isset($cart_seats[$product_id]))) {//(isset($cart_seats[$product->variation_id]) || isset($cart_seats[$product->id]))) {
                if (isset($cart_seats[$pid][0]) && isset($cart_seats[$pid][0][1]) && $cart_seats[$pid][0][1] !== '') {
                    $args['max_value'] = $args['input_value'];
                    $args['min_value'] = $args['input_value'];
                }
            }

            return $args;
        }

        /**
         * Loads variations form into seat maps popup
         * Called from ajax action tc_seat_chart_get_wc_variations
         */
        function tc_seat_chart_get_wc_variations() {
            if (isset($_REQUEST['seat_ticket_type_id'])) {
                $ticket_type_id = $_REQUEST['seat_ticket_type_id'];
                echo $this->wc_get_product_variations_form($ticket_type_id);
                exit;
            }
        }

        /**
         * Variations data shown in the seat map popup when varible product is selected
         * @global type $product
         * @param type $id
         * @return type
         */
        function wc_get_product_variations_form($id) {
            global $product;

            ob_start();

            $product = wc_get_product((int) $id);
            $get_variations = sizeof($product->get_children()) <= apply_filters('woocommerce_ajax_variation_threshold', 30, $product);
            $available_variations = $get_variations ? $product->get_available_variations() : false;

            $attributes = $product->get_variation_attributes();
            $attribute_keys = array_keys($attributes);
            ?>

            <form class="variations_form cart" method="post" enctype='multipart/form-data' data-product_id="<?php echo absint($product->get_id()); ?>" data-product_variations="<?php echo htmlspecialchars(json_encode($available_variations)) ?>">
                <?php if (empty($available_variations) && false !== $available_variations) : ?>
                    <p class="stock out-of-stock"><?php _e('This product is currently out of stock and unavailable.', 'tcsc'); ?></p>
                <?php else : ?>
                    <table class="variations" cellspacing="0">
                        <tbody>
                            <?php foreach ($attributes as $attribute_name => $options) : ?>
                                <tr>
                                    <td class="label"><label for="<?php echo sanitize_title($attribute_name); ?>"><?php echo wc_attribute_label($attribute_name); ?></label></td>
                                    <td class="value">
                                        <?php
                                        $selected = isset($_REQUEST['attribute_' . sanitize_title($attribute_name)]) ? wc_clean(urldecode($_REQUEST['attribute_' . sanitize_title($attribute_name)])) : $product->get_variation_default_attribute($attribute_name);
                                        wc_dropdown_variation_attribute_options(array('options' => $options, 'attribute' => $attribute_name, 'product' => $product, 'selected' => $selected));
                                        echo end($attribute_keys) === $attribute_name ? apply_filters('woocommerce_reset_variations_link', '<a class="reset_variations" href="#">' . __('Clear', 'tcsc') . '</a>') : '';
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div class="single_variation_wrap">
                        <?php
                        /**
                         * woocommerce_before_single_variation Hook.
                         */
                        do_action('woocommerce_before_single_variation');

                        /**
                         * woocommerce_single_variation hook. Used to output the cart button and placeholder for variation data.
                         * @since 2.4.0
                         * @hooked woocommerce_single_variation - 10 Empty div for variation data.
                         * @hooked woocommerce_single_variation_add_to_cart_button - 20 Qty and cart button.
                         */
                        do_action('woocommerce_single_variation');

                        /**
                         * woocommerce_after_single_variation Hook.
                         */
                        do_action('woocommerce_after_single_variation');
                        ?>
                    </div>
                <?php endif; ?>

            </form>

            <?php
            return ob_get_clean();
        }

        /**
         * Checks if a chart has orders (so we can disable chart editing etc)
         * @param type $chart_id
         * @return boolean
         */
        public static function is_chart_has_orders($chart_id) {
            $chart_ticket_instances_args = array(
                'posts_per_page' => 1,
                'post_status' => 'publish',
                'post_type' => 'tc_tickets_instances',
                'meta_key' => 'chart_id',
                'meta_value' => $chart_id,
                'no_found_rows' => true
            );

            $chart_ticket_instances = get_posts($chart_ticket_instances_args);

            if (count($chart_ticket_instances) > 0) {
                return true;
            } else {
                return false;
            }
        }

        /**
         * Add tc_seat_chart shortcode to the shortcode builder list
         * @param array $shortcodes
         * @return type
         */
        function tc_modify_shortcode_builder_list($shortcodes) {
            $shortcodes['tc_seat_chart'] = __('Seating Chart', 'tcsc');
            return $shortcodes;
        }

        /**
         * Get reserved seats based on cart contents
         * @return type
         */
        function get_cart_reserved_seats($excluded_order_id = false) {
            $reserved_order_statuses = TC_Seat_Chart::get_reserved_order_statuses();
            $in_cart_seats = TC_Seat_Chart::get_cart_seats_cookie();

            $reserved_seats = array();

            foreach ($in_cart_seats as $seat_ticket_type_id => $ticket_type_seats_in_carts) {

                foreach ($ticket_type_seats_in_carts as $ticket_type_seats_in_cart) {

                    $seat_id = $ticket_type_seats_in_cart[0];
                    $chart_id = (int) $ticket_type_seats_in_cart[2];

                    $meta_query = array('relation' => 'AND');

                    $meta_query[] = array(
                        'key' => 'seat_id',
                        'value' => $seat_id,
                    );
                    $meta_query[] = array(
                        'key' => 'chart_id',
                        'value' => $chart_id,
                    );

                    if ($excluded_order_id) {
                        $maybe_reserved_seat_args = array(
                            'posts_per_page' => 1,
                            'post_status' => 'publish',
                            'post_type' => 'tc_tickets_instances',
                            'meta_query' => $meta_query,
                            'no_found_rows' => true,
                            'post_parent__not_in' => array($excluded_order_id)
                        );
                    } else {
                        $maybe_reserved_seat_args = array(
                            'posts_per_page' => 1,
                            'post_status' => 'publish',
                            'post_type' => 'tc_tickets_instances',
                            'meta_query' => $meta_query,
                            'no_found_rows' => true
                        );
                    }

                    $maybe_reserved_seats = get_posts($maybe_reserved_seat_args);

                    foreach ($maybe_reserved_seats as $maybe_reserved_seat) {
                        $order_id = $maybe_reserved_seat->post_parent;
                        $order_status = get_post_status($order_id);
                        if (in_array($order_status, $reserved_order_statuses)) {
                            $reserved_seat_sign = TC_Seat_Chart::get_ticket_instance_seat_sign($maybe_reserved_seat->ID);
                            if (!empty($reserved_seat_sign) && $reserved_seat_sign !== '') {
                                $reserved_seats[] = $reserved_seat_sign;
                            }
                        }
                    }
                }
            }

            return $reserved_seats;
        }

        function tc_has_cart_or_payment_errors($has, $cart_contents) {
            $reserved_seats = $this->get_cart_reserved_seats();

            if (count($reserved_seats) > 0) {
                $has = true;
                add_action('tc_has_cart_or_payment_errors_action', array($this, 'tc_has_cart_or_payment_errors_action'), 10, 1);
            }

            return $has;
        }

        function tc_has_cart_or_payment_errors_action($cart_contents) {
            global $tc;
            $reserved_seats = $this->get_cart_reserved_seats();

            if (count($reserved_seats) > 0) {
                if (count($reserved_seats) == 1) {
                    $error = (sprintf(__('Seat %s is already booked. Please remove it from the %scart%s.', 'tcsc'), '<strong>' . $reserved_seats[0] . '</strong>', '<a href="' . $tc->get_cart_slug(true) . '">', '</a>'));
                } else {
                    $error = (sprintf(__('Seats %s are already booked. Please remove them from the %scart%s.', 'tcsc'), '<strong>' . implode(',', $reserved_seats) . '</strong>', '<a href="' . $tc->get_cart_slug(true) . '">', '</a>'));
                }
            }

            echo $error;
        }

        /**
         * Checks if a seat is available upon placing an order (for WooCommerce version)
         * @param type $posted
         */
        function woo_check_if_seats_are_available_before_cart_error_check($data) {

            $order_id = absint(WC()->session->get('order_awaiting_payment'));
            $cart_hash = md5(json_encode(wc_clean(WC()->cart->get_cart_for_session())) . WC()->cart->total);

            if ($order_id && ( $order = wc_get_order($order_id) ) && $order->has_cart_hash($cart_hash) && $order->has_status(array('pending', 'failed'))) {
                //wc_add_notice('continuing order, maybe we should skip the check? Or check for all reserverd seats except for this order', 'error');
                $reserved_seats = $this->get_cart_reserved_seats($order_id);
            } else {
                $reserved_seats = $this->get_cart_reserved_seats();
            }


            if (count($reserved_seats) > 0) {
                if (count($reserved_seats) == 1) {
                    wc_add_notice(sprintf(__('Seat %s is already booked. Please remove it from the cart.', 'tcsc'), '<strong>' . $reserved_seats[0] . '</strong>'), 'error');
                } else {
                    wc_add_notice(sprintf(__('Seats %s are already booked. Please remove them from the cart.', 'tcsc'), '<strong>' . implode(',', $reserved_seats) . '</strong>'), 'error');
                }
            }
        }

        /**
         * Checks if a seat is available upon placing an order (for Tickera standalone version)
         * @global type $tc
         * @global type $tc_cart_errors
         * @global type $cart_error_number
         * @global type $wpdb
         * @param type $cart_error_number_orig
         * @param type $tc_cart_errors_orig
         */
        function check_if_seats_are_available_before_cart_error_check($cart_error_number_orig, $tc_cart_errors_orig) {
            global $tc, $tc_cart_errors, $cart_error_number;

            $reserved_seats = $this->get_cart_reserved_seats();

            if (count($reserved_seats) > 0) {
                if (count($reserved_seats) == 1) {
                    $cart_error_number++;
                    $tc_cart_errors .= '<li>' . sprintf(__('Seat %s is already booked. Please remove it from the cart.', 'tcsc'), '<strong>' . $reserved_seats[0] . '</strong>') . '</li>';
                } else {
                    $cart_error_number++;
                    $tc_cart_errors .= '<li>' . sprintf(__('Seats %s are already booked. Please remove them from the cart.', 'tcsc'), '<strong>' . implode(',', $reserved_seats) . '</strong>') . '</li>';
                }
            }
        }

        /**
         * Gets add-on settings
         * @return type
         */
        public static function get_settings() {
            $tc_seat_charts_settings = get_option('tc_seat_charts_settings');
            return $tc_seat_charts_settings;
        }

        /**
         * Adds new admin menu item for the add-on
         * @param array $menus
         * @return type
         */
        function tc_settings_new_menus($menus) {
            $menus['seat_charts'] = __('Seating Charts', 'cp');
            return $menus;
        }

        /**
         * Loads admin settings page for the add-on
         */
        function tc_settings_menu_seat_charts() {
            include($this->plugin_dir . 'includes/admin-pages/seat_charts_settings.php');
        }

        /**
         * Adds admin notices if needed (for admins only if the add-on is not compatible with the parent plugin)
         * @global type $tc
         */
        function admin_notices() {
            global $tc;

            if (current_user_can('manage_options')) {
                if (isset($tc->version) && version_compare($tc->version, $this->tc_version_required, '<')) {
                    ?>
                    <div class="notice notice-error">
                        <p><?php printf(__('%s add-on requires at least %s version of %s plugin. Your current version of %s is %s. Please update it.', 'tcsc'), $this->title, $this->tc_version_required, $tc->title, $tc->title, $tc->version); ?></p>
                    </div>
                    <?php
                }
            }
        }

        /**
         * Adds tc_seat_chart shortcode to the content on the single page of the tc_seat_charts post type (so we can preview a seat map)
         * @global type $post
         * @global type $post_type
         * @param type $content
         * @return type
         */
        function modify_the_content($content) {
            global $post, $post_type;
            if (!is_admin() && isset($post_type) && $post_type == 'tc_seat_charts') {
                $content .= do_shortcode('[tc_seat_chart id="' . (int) $post->ID . '" show_legend="true"]');
            }

            return $content;
        }

        /**
         * Hides unendeed actions from the admin
         * @param type $actions
         * @param type $post
         * @return type
         */
        function post_row_actions($actions, $post) {
            if ($post->post_type == 'tc_seat_charts') {
                unset($actions['inline hide-if-no-js']);
            }
            return $actions;
        }

        /**
         * Remove order session data (after completed payment for instance)
         */
        function ajax_remove_order_session_data() {
            ob_start();
            $cookie_id = 'tc_cart_seats_' . COOKIEHASH;
            @setcookie($cookie_id, null, time() - 1, COOKIEPATH, COOKIE_DOMAIN);
            $this->set_cart_seats_persistent(null);
            ob_end_flush();
            exit;
        }

        /**
         * Disable quantity change for seats (for standalone version)
         * @param boolean $value
         * @param type $ticket_type_id
         * @param type $ordered_count
         * @return boolean
         */
        function tc_editable_quantity($value, $ticket_type_id, $ordered_count) {
            global $tc;

            $cart_seats = TC_Seat_Chart::get_cart_seats_cookie();
            if (isset($cart_seats) && isset($cart_seats[$ticket_type_id])) {
                if (isset($cart_seats[$ticket_type_id][0]) && isset($cart_seats[$ticket_type_id][0][1]) && $cart_seats[$ticket_type_id][0][1] !== '') {
                    $value = false;
                } else {
                    $value = true;
                }
            }
            return $value;
        }

        /**
         * Add seats info neeeded for the database on the cart page / attendee form
         * @param type $ticket_type
         * @param type $attendee_index
         */
        function tc_add_cart_seat_info($ticket_type, $attendee_index) {
            $cart_seats = TC_Seat_Chart::get_cart_seats_cookie();

            if (isset($cart_seats[$ticket_type->details->ID][$attendee_index])) {
                $chart_id = isset($cart_seats[$ticket_type->details->ID][$attendee_index][2]) ? $cart_seats[$ticket_type->details->ID][$attendee_index][2] : '';
                $seat_label = isset($cart_seats[$ticket_type->details->ID][$attendee_index][1]) ? $cart_seats[$ticket_type->details->ID][$attendee_index][1] : '';
                $seat_id = isset($cart_seats[$ticket_type->details->ID][$attendee_index][0]) ? $cart_seats[$ticket_type->details->ID][$attendee_index][0] : '';
                ?>
                <input type="hidden" name="<?php echo esc_attr('owner_data_seat_label_post_meta[' . $ticket_type->details->ID . '][' . $attendee_index . ']'); ?>" value="<?php echo esc_attr($seat_label); ?>" /><input type="hidden" name="<?php echo esc_attr('owner_data_chart_id_post_meta[' . $ticket_type->details->ID . '][' . $attendee_index . ']'); ?>" value="<?php echo esc_attr($chart_id); ?>" /><input type="hidden" name="<?php echo esc_attr('owner_data_seat_id_post_meta[' . $ticket_type->details->ID . '][' . $attendee_index . ']'); ?>" value="<?php echo esc_attr($seat_id); ?>" />
                <?php
            }
        }

        /**
         * Get seat sign (A1, A2...) for a specified ticket instance id / ticket
         * @param type $ticket_instance_id
         * @return boolean
         */
        public static function get_ticket_instance_seat_sign($ticket_instance_id) {
            $seat_label = get_post_meta($ticket_instance_id, 'seat_label', true);

            if ($seat_label !== '' && !empty($seat_label)) {
                return $seat_label;
            }
            return false;
        }

        /**
         * Add seat sign in the ticket title after purchase
         * @param string $ticket_type_title
         * @param type $ticket_type_id
         * @param type $array
         * @param type $ticket_instance_id
         * @return string
         */
        function tc_maybe_add_seat_info_to_ticket_type($ticket_type_title, $ticket_type_id, $array = array(), $ticket_instance_id = false) {
            if ($ticket_instance_id) {
                $has_seat_sign = TC_Seat_Chart::get_ticket_instance_seat_sign($ticket_instance_id);
                if ($has_seat_sign) {
                    $ticket_type_title = $ticket_type_title . ' (' . $has_seat_sign . ')';
                }
            }
            return $ticket_type_title;
        }

        /**
         * Add remove from cart "X" next to an attendee form
         * @param string $attendee_caption
         * @param type $ticket_type
         * @param type $attendee_index
         * @return string
         */
        function tc_add_seat_info_to_attendee_caption($attendee_caption, $ticket_type, $attendee_index) {
            $cart_seats = TC_Seat_Chart::get_cart_seats_cookie();
            if (isset($cart_seats[$ticket_type->details->ID][$attendee_index])) {
                $seat_id = $cart_seats[$ticket_type->details->ID][$attendee_index][0];
                $seat_sign = isset($cart_seats[$ticket_type->details->ID][$attendee_index][1]) ? $cart_seats[$ticket_type->details->ID][$attendee_index][1] : '';
                $chart_id = $cart_seats[$ticket_type->details->ID][$attendee_index][2];

                $remove_icon = '<span class="tc_cart_remove_icon" title="' . __('Remove from Cart', 'tcsc') . '" data-ticket-type-id="' . (int) $ticket_type->details->ID . '" data-seat-sign="' . $seat_sign . '" data-seat-id="' . $seat_id . '" data-chart-id="' . (int) $chart_id . '"><i class="fa fa-times" aria-hidden="true"></i></span>';
                $attendee_caption = $attendee_caption . (!empty($seat_sign) ? apply_filters('tc_attendee_info_has_seat_sign_caption', ' (' . $seat_sign . ')', $seat_sign) : '') . $remove_icon;
            }

            return $attendee_caption;
        }

        /**
         * Get seats cookie
         * @return array
         */
        public static function get_cart_seats_cookie() {

            $cookie_id = 'tc_cart_seats_' . COOKIEHASH;

            $seats = array();

            if (isset($_COOKIE[$cookie_id])) {

                $seats_obj = json_decode(stripslashes($_COOKIE[$cookie_id]), true);

                foreach ($seats_obj as $ticket_type_id => $position) {
                    $seats[(int) $ticket_type_id] = $position;
                }
            } else {
                $saved_cart = TC_Seat_Chart::get_cart_seats_persistant();
                if (is_null($saved_cart)) {
                    $seats = array();
                } else {
                    $seats = $saved_cart;
                }
            }

            if (isset($seats)) {
                return $seats;
            } else {
                return array();
            }
        }

        /**
         * Set seats cookie
         * @param type $seats_info
         */
        function set_cart_seats_cookie($seats_info) {
            $seats = array();

            $old_seats = TC_Seat_Chart::get_cart_seats_cookie();

            foreach ($old_seats as $old_ticket_type_id => $position) {
                $seats[(int) $old_ticket_type_id] = $position;
            }

            foreach ($seats_info as $seat_info) {
                $ticket_type_id = $seat_info[0];
                $seat_id = $seat_info[1];
                $seat_label = $seat_info[2];
                $chart_id = $seat_info[3];
                $seats[(int) $ticket_type_id][] = array($seat_id, $seat_label, (int) $chart_id);
            }

            $cookie_id = 'tc_cart_seats_' . COOKIEHASH;

            unset($_COOKIE[$cookie_id]);
            setcookie($cookie_id, null, -1, '/');

//set cookie
            $expire = time() + apply_filters('tc_cart_cookie_expiration', 172800); //72 hrs expire by default
            setcookie($cookie_id, json_encode($seats), $expire, COOKIEPATH, COOKIE_DOMAIN);

            $_COOKIE[$cookie_id] = json_encode($seats);
            $this->set_cart_seats_persistent($seats);
        }

        public static function get_cart_seats_persistant() {
            $cart = array();
            if (get_current_user_id()) {
                if ($saved_cart = get_user_meta(get_current_user_id(), '_seatings_persistent_cart', true)) {
                    $cart = json_decode(stripslashes($saved_cart['seats_cart']), true);
                } elseif (is_null($cart) || empty($cart)) {
                    $cart = array();
                }
            } else {
                $cart = array();
            }

            return $cart;
        }

        function set_cart_seats_persistent($seats) {
            if (get_current_user_id()) {
                foreach($seats as $seat_key => $seat_value){
                    if(empty($seats[$seat_key])){
                        unset($seats[$seat_key]);
                    }
                }
                update_user_meta(get_current_user_id(), '_seatings_persistent_cart', array(
                    'seats_cart' => json_encode($seats),
                ));
            }
        }

        function delete_cart_seats_persistent() {
            delete_user_meta(get_current_user_id(), '_seatings_persistent_cart');
            $this->delete_order_cookie();
            //$this->destroy_cookies();
        }

        function destroy_cookies() {
            ob_start();
            $cookie_id = 'tc_cart_seats_' . COOKIEHASH;
            unset($_COOKIE[$cookie_id]);
            setcookie($cookie_id, null, -1, '/');
            ob_end_flush();
        }

        /**
         * Remove a product or product variation from a WooCommerce cart
         * @param type $product_id
         */
        function wc_remove_product_from_cart($product_id) {

            $WC = WC();
// Set the product ID to remove
            $prod_to_remove = intval($product_id);
// Cycle through each product in the cart
            foreach ($WC->cart->get_cart() as $cart_item_key => $cart_item) {
// Get the Variation or Product ID
                $prod_id = get_post_type($prod_to_remove) == 'product_variation' ? $cart_item['variation_id'] : $cart_item['product_id'];

// Check to see if IDs match
                if ($prod_to_remove == $prod_id) {
                    $WC->cart->set_quantity($cart_item_key, ((int) $cart_item['quantity']) - 1, true);
                    $new_quant = (int)$cart_item['quantity'] - 1;
                    if( $new_quant == 0) {          
                        $WC->cart->remove_cart_item($cart_item_key);
                    }
                    break;
                }
            }
        }

        /**
         * Remove seats from cart when removing an item (pr multiple items from cart via "X" button)
         * @param type $cart_item_key
         */
        function woo_cart_item_remove_seat($cart_item_key) {
            ob_start();

            $cart_item = WC()->cart->get_cart_item($cart_item_key);

            $seat_ticket_type_id = (isset($cart_item['variation_id']) && is_int($cart_item['variation_id']) && $cart_item['variation_id'] > 0) ? $cart_item['variation_id'] : $cart_item['product_id'];
            $in_cart_seats = TC_Seat_Chart::get_cart_seats_cookie();

            if (isset($in_cart_seats) && isset($in_cart_seats[$seat_ticket_type_id])) {
                foreach ($in_cart_seats[$seat_ticket_type_id] as $index => $ticket_type_seats_in_cart) {
                    $seat_id = $ticket_type_seats_in_cart[0];
                    $chart_id = (int) $ticket_type_seats_in_cart[2];

                    do_action('tc_seat_chart_woo_cart_item_remove_seat', $seat_id, $chart_id);
                    unset($in_cart_seats[$seat_ticket_type_id][$index]);
                }
            }

            $cookie_id = 'tc_cart_seats_' . COOKIEHASH;

            unset($_COOKIE[$cookie_id]);
            setcookie($cookie_id, null, -1, '/');
            //set cookie
            $expire = time() + apply_filters('tc_cart_cookie_expiration', 172800); //72 hrs expire by default
            setcookie($cookie_id, json_encode($in_cart_seats), $expire, COOKIEPATH, COOKIE_DOMAIN);

            $_COOKIE[$cookie_id] = json_encode($in_cart_seats);
            $this->set_cart_seats_persistent($in_cart_seats);

            ob_end_flush();
        }

        /**
         * Remove a product or product variation from a WooCommerce cart
         * @global type $tc
         */
        function tc_remove_seat_from_cart_woo() {
            global $tc;
            $in_cart_seats = TC_Seat_Chart::get_cart_seats_cookie();

            $seat_id = $_POST['seat_id'];
            //$seat_sign = $_POST['seat_sign'];
            $chart_id = (int) $_POST['chart_id'];

            foreach ($in_cart_seats as $seat_ticket_type_id => $seat_ticket_type_index) {

                $orig_seat_id = 0;
                $orig_chart_id = 0;
                
                foreach ($in_cart_seats[$seat_ticket_type_id] as $index => $ticket_type_seats_in_cart) {

                    $orig_seat_id = $ticket_type_seats_in_cart[0];
                    $orig_chart_id = (int) $ticket_type_seats_in_cart[2];

                    if ($orig_seat_id == $seat_id && $orig_chart_id == $chart_id) {// && $orig_seat_sign == $seat_sign
                        unset($in_cart_seats[$seat_ticket_type_id][$index]);
                        $in_cart_seats[$seat_ticket_type_id] = array_values($in_cart_seats[$seat_ticket_type_id]);
                        
                        $this->wc_remove_product_from_cart($seat_ticket_type_id);
                    }
                }
            }

            $cookie_id = 'tc_cart_seats_' . COOKIEHASH;

            unset($_COOKIE[$cookie_id]);
            setcookie($cookie_id, null, -1, '/');

            //set cookie
            $expire = time() + apply_filters('tc_cart_cookie_expiration', 172800); //72 hrs expire by default
            setcookie($cookie_id, json_encode($in_cart_seats), $expire, COOKIEPATH, COOKIE_DOMAIN);

            $_COOKIE[$cookie_id] = json_encode($in_cart_seats);
            
            $this->set_cart_seats_persistent($in_cart_seats);

            if (ob_get_length() > 0) {
                ob_end_clean();
            }

            $cart_subtotal = WC()->cart->get_cart_total();
            $in_cart_count = apply_filters('tc_seat_chart_in_cart_count', $in_cart_count);

            $response = array();
            $response['link'] = sprintf('<span class="tc_in_cart">%s <a href="%s">%s</a></span>', apply_filters('tc_ticket_added_to_message', __('Ticket added to', 'tcsc')), $tc->get_cart_slug(true), apply_filters('tc_ticket_added_to_cart_message', __('Cart', 'tcsc')));
            $response['subtotal'] = __('Subtotal: ', 'tcsc');
            $response['total'] = $cart_subtotal; //apply_filters('tc_cart_currency_and_format', $cart_subtotal);
            $response['in_cart_count'] = $in_cart_count;
            $response['cart_link'] = add_query_arg(array(
                'tcrft' => time(),
                    ), apply_filters('tc_seat_chart_checkout_url', $tc->get_cart_page(true)));

            echo json_encode($response);
            ob_end_flush();
            exit;
        }

        /**
         * Remove a product or product variation from a WooCommerce cart (if the Bridge is active) or remove a ticket from a standalone version
         * @global type $tc
         */
        function tc_remove_seat_from_cart() {
            global $tc;

            ob_start();

            $seat_ticket_type_id = (int) $_POST['seat_ticket_type_id'];

            if (apply_filters('tc_is_woo', false) == true) {
                $this->tc_remove_seat_from_cart_woo();
                exit;
            }

            $old_cart = $tc->get_cart_cookie(true);
            $cart = $old_cart;

            $in_cart_seats = TC_Seat_Chart::get_cart_seats_cookie();

            $seat_id = $_POST['seat_id'];
            $seat_sign = $_POST['seat_sign'];
            $chart_id = (int) $_POST['chart_id'];

            foreach ($in_cart_seats[$seat_ticket_type_id] as $index => $ticket_type_seats_in_cart) {

                $orig_seat_id = $ticket_type_seats_in_cart[0];
                $orig_chart_id = (int) $ticket_type_seats_in_cart[2];

                if ($orig_seat_id == $seat_id && $orig_chart_id == $chart_id) {

                    unset($in_cart_seats[$seat_ticket_type_id][$index]);
                    $in_cart_seats[$seat_ticket_type_id] = array_values($in_cart_seats[$seat_ticket_type_id]);
                    $cart[(int) $_POST['seat_ticket_type_id']] = ($cart[(int) $_POST['seat_ticket_type_id']] - 1);
                }
            }

            $tc->update_cart_cookie($cart);

            $cookie_id = 'tc_cart_seats_' . COOKIEHASH;

            unset($_COOKIE[$cookie_id]);
            setcookie($cookie_id, null, -1, '/');

//set cookie
            $expire = time() + apply_filters('tc_cart_cookie_expiration', 172800); //72 hrs expire by default
            setcookie($cookie_id, json_encode($in_cart_seats), $expire, COOKIEPATH, COOKIE_DOMAIN);

            $_COOKIE[$cookie_id] = json_encode($in_cart_seats);
            
            $this->set_cart_seats_persistent($in_cart_seats);

            if (ob_get_length() > 0) {
                ob_end_clean();
            }

            $cart_contents = $cart; //$tc->get_cart_cookie();

            $cart_subtotal = 0;
            $in_cart_count = 0;

            foreach ($cart_contents as $ticket_type => $ordered_count) {
                $ticket = new TC_Ticket($ticket_type);
                $cart_subtotal = $cart_subtotal + (tc_get_ticket_price($ticket->details->ID) * $ordered_count);
                if ($ordered_count > 0) {
                    $in_cart_count++;
                }
            }

            $response = array();
            $response['link'] = sprintf('<span class="tc_in_cart">%s <a href="%s">%s</a></span>', apply_filters('tc_ticket_added_to_message', __('Ticket removed from', 'tcsc')), $tc->get_cart_slug(true), apply_filters('tc_ticket_removed_from_cart_message', __('Cart', 'tcsc')));
            $response['subtotal'] = __('Subtotal: ', 'tcsc');
            $response['total'] = apply_filters('tc_cart_currency_and_format', $cart_subtotal);
            $response['in_cart_count'] = $in_cart_count;
            $response['cart_link'] = add_query_arg(array(
                'tcrft' => time(),
                    ), $tc->get_cart_page(true));

            echo json_encode($response);
            ob_end_flush();
            exit;
        }

        function tc_wc_get_cart_info() {
            ob_start();
            global $tc;
            $response = array();

            $response['error'] = false;

            $cart_subtotal = 0;
            $in_cart_count = apply_filters('tc_seat_chart_in_cart_count', $in_cart_count);

            $response['link'] = sprintf('<span class="tc_in_cart">%s <a href="%s">%s</a></span>', apply_filters('tc_ticket_added_to_message', __('Ticket added to', 'tcsc')), $tc->get_cart_slug(true), apply_filters('tc_ticket_added_to_cart_message', __('Cart', 'tcsc')));
            $response['subtotal'] = __('Subtotal: ', 'tcsc');
            $response['total'] = apply_filters('tc_seat_chart_cart_subtotal', apply_filters('tc_cart_currency_and_format', isset($cart_subtotal) ? $cart_subtotal : 0));
            $response['in_cart_count'] = $in_cart_count;

            echo json_encode($response);
            ob_end_flush();
            exit;
        }

        /**
         * Add a product variation to the cart
         * @global type $tc
         */
        function tc_add_seat_to_cart_woo_variation() {
            global $tc;

            $response = array();
            ob_start();

            if (isset($_POST['tc_seat_cart_items'])) {
                $tc_seat_cart_items = $_POST['tc_seat_cart_items'];
                $tc_seat_cart_items_exploded = array();

                foreach ($tc_seat_cart_items as $tc_seat_cart_item) {
                    $tc_seat_cart_item = explode('-', $tc_seat_cart_item);

                    if (isset($_POST['variation_id'])) {
                        $tc_seat_cart_item[0] = absint($_POST['variation_id']);
                    }

                    $tc_seat_cart_items_exploded[] = $tc_seat_cart_item;
                }

                $response['error'] = false; //we'll assume it

                $this->set_cart_seats_cookie($tc_seat_cart_items_exploded);

                $cart_subtotal = 0;
                $in_cart_count = apply_filters('tc_seat_chart_in_cart_count', $in_cart_count);

                $response['link'] = sprintf('<span class="tc_in_cart">%s <a href="%s">%s</a></span>', apply_filters('tc_ticket_added_to_message', __('Ticket added to', 'tcsc')), $tc->get_cart_slug(true), apply_filters('tc_ticket_added_to_cart_message', __('Cart', 'tcsc')));
                $response['subtotal'] = __('Subtotal: ', 'tcsc');
                $response['total'] = apply_filters('tc_seat_chart_cart_subtotal', apply_filters('tc_cart_currency_and_format', isset($cart_subtotal) ? $cart_subtotal : 0));
                $response['in_cart_count'] = $in_cart_count;

                echo json_encode($response);
                ob_end_flush();
                exit;
            }
        }

        /**
         * Add simple WooCommerce to the cart
         * @global type $tc
         */
        function tc_add_seat_to_cart_woo() {
            global $tc;

            $response = array();
            ob_start();

            if (isset($_POST['tc_seat_cart_items'])) {
                $tc_seat_cart_items = $_POST['tc_seat_cart_items'];
                $tc_seat_cart_items_exploded = array();

                foreach ($tc_seat_cart_items as $tc_seat_cart_item) {
                    $tc_seat_cart_item = explode('-', $tc_seat_cart_item);
                    $tc_seat_cart_items_exploded[] = $tc_seat_cart_item;
                }

                foreach ($tc_seat_cart_items_exploded as $tc_seat_cart_item_exploded) {
                    $product_id = absint($tc_seat_cart_item_exploded[0]);
                    $quantity = 1;
                    $passed_validation = apply_filters('woocommerce_add_to_cart_validation', true, $product_id, $quantity);
                    $product_status = get_post_status($product_id);

                    if ($passed_validation && false !== WC()->cart->add_to_cart($product_id, $quantity) && 'publish' === $product_status) {
                        $response['error'] = false;
                    } else {
                        $response['error'] = true;
                        $data = array(
                            'error' => true,
                            'product_url' => apply_filters('woocommerce_cart_redirect_after_error', get_permalink($product_id), $product_id)
                        );
                    }
                }

                $this->set_cart_seats_cookie($tc_seat_cart_items_exploded);

                $cart_subtotal = 0;
                $in_cart_count = apply_filters('tc_seat_chart_in_cart_count', $in_cart_count);

                $response['link'] = sprintf('<span class="tc_in_cart">%s <a href="%s">%s</a></span>', apply_filters('tc_ticket_added_to_message', __('Ticket added to', 'tcsc')), $tc->get_cart_slug(true), apply_filters('tc_ticket_added_to_cart_message', __('Cart', 'tcsc')));
                $response['subtotal'] = __('Subtotal: ', 'tcsc');
                $response['total'] = apply_filters('tc_seat_chart_cart_subtotal', apply_filters('tc_cart_currency_and_format', isset($cart_subtotal) ? $cart_subtotal : 0));
                $response['in_cart_count'] = $in_cart_count;

                echo json_encode($response);
                ob_end_flush();
                exit;
            }
        }

        /**
         * Add (standalone) ticket to the cart
         * @global type $tc
         */
        function tc_add_seat_to_cart() {
            global $tc;
            if (isset($_POST['tc_seat_cart_items'])) {
                $tc_seat_cart_items = $_POST['tc_seat_cart_items'];
                $tc_seat_cart_items_exploded = array();

                foreach ($tc_seat_cart_items as $tc_seat_cart_item) {
                    $tc_seat_cart_item = explode('-', $tc_seat_cart_item);
                    $tc_seat_cart_items_exploded[] = $tc_seat_cart_item;
                }

                $old_cart = $tc->get_cart_cookie(true);

                foreach ($old_cart as $old_ticket_id => $old_quantity) {
                    $cart[(int) $old_ticket_id] = (int) $old_quantity;
                }

                foreach ($tc_seat_cart_items_exploded as $tc_seat_cart_item_exploded) {
                    if (isset($cart[$tc_seat_cart_item_exploded[0]])) {
                        $cart[(int) $tc_seat_cart_item_exploded[0]] = $cart[$tc_seat_cart_item_exploded[0]] + 1;
                    } else {
                        $cart[(int) $tc_seat_cart_item_exploded[0]] = 1;
                    }
                }

                $tc->set_cart_cookie($cart);
                $this->set_cart_seats_cookie($tc_seat_cart_items_exploded);

                if (ob_get_length() > 0) {
                    ob_end_clean();
                }

                ob_start();

                $cart_contents = $tc->get_cart_cookie();
                $in_cart_count = 0;
                foreach ($cart_contents as $ticket_type => $ordered_count) {
                    $ticket = new TC_Ticket($ticket_type);
                    $cart_subtotal = $cart_subtotal + (tc_get_ticket_price($ticket->details->ID) * $ordered_count);

                    if ($ordered_count > 0) {
                        $in_cart_count++;
                    }
                }

                $response = array();
                $response['link'] = sprintf('<span class="tc_in_cart">%s <a href="%s">%s</a></span>', apply_filters('tc_ticket_added_to_message', __('Ticket added to', 'tcsc')), $tc->get_cart_slug(true), apply_filters('tc_ticket_added_to_cart_message', __('Cart', 'tcsc')));
                $response['subtotal'] = __('Subtotal: ', 'tcsc');
                $response['total'] = apply_filters('tc_cart_currency_and_format', $cart_subtotal);
                $response['in_cart_count'] = $in_cart_count;

                echo json_encode($response);
                ob_end_flush();
                exit;
            }
        }

        /**
         * Register tc_seat_charts custom post type
         */
        function register_custom_posts() {
            $args = array(
                'labels' => array('name' => __('Seating Charts', 'tcsc'),
                    'singular_name' => __('Seating Chart', 'tcsc'),
                    'add_new' => __('Create New', 'tcsc'),
                    'add_new_item' => __('Create New Seating Chart', 'tcsc'),
                    'edit_item' => __('Edit Seating Chart', 'tcsc'),
                    'edit' => __('Edit', 'tcsc'),
                    'new_item' => __('New Seating Chart', 'tcsc'),
                    'view_item' => __('View Seating Chart', 'tcsc'),
                    'search_items' => __('Search Seating Charts', 'tcsc'),
                    'not_found' => __('No Seating Charts Found', 'tcsc'),
                    'not_found_in_trash' => __('No Seating Charts found in Trash', 'tcsc'),
                    'view' => __('View Seating Chart', 'tcsc')
                ),
                'public' => false,
                'show_ui' => true,
                'publicly_queryable' => true,
                'hierarchical' => false,
                'query_var' => true,
                'show_in_menu' => 'edit.php?post_type=tc_events',
                'supports' => array('title', 'editor')
            );

            register_post_type('tc_seat_charts', apply_filters('tc_seat_charts_post_type_args', $args));
        }

        /**
         * Add ticket type color picker to the ticket type screen in the admin (for standalone version)
         * @param type $fields
         * @return string
         */
        function add_color_picker_field($fields) {
            $fields[] = array(
                'field_name' => '_seat_color',
                'field_title' => __('Seat Color', 'tcsc'),
                'field_type' => 'text',
                'field_description' => __('Color which will be visible on a seating chart', 'tcsc'),
                'table_visibility' => false,
                'post_field_type' => 'post_meta'
            );
            return $fields;
        }

        /**
         * Get seats selected in the admin (just a raw seat map)
         * @param type $seat_map_post_id
         */
        public static function get_occupied_seats($seat_map_post_id) {
            if (is_object($seat_map_post_id)) {
                $seat_map_post_id = $seat_map_post_id->ID;
            }

//if (false === ( $content = get_option('tc_get_occupied_seats_' . $seat_map_post_id, false) )) {
//  ob_start();
            $seats = get_post_meta($seat_map_post_id, 'tc_seat_cords', true);
            $seats = explode('|', $seats);

            $seat_ticket_types = get_post_meta($seat_map_post_id, 'tc_seat_ticket_types', true);
            $seat_ticket_types = explode('|', $seat_ticket_types);

            $seat_signs = get_post_meta($seat_map_post_id, 'tc_seat_signs', true);
            $seat_signs = explode('|', $seat_signs);

            $seat_directions = get_post_meta($seat_map_post_id, 'tc_seat_directions', true);
            $seat_directions = explode('|', $seat_directions);
            ?>
            <script type="text/javascript">
                var tc_seats = new Array();
            <?php
            $i = 0;
            foreach ($seats as $seat) {
                if (!empty($seat)) {
                    $seat_sign_val = (isset($seat_signs) && is_array($seat_signs) && count($seat_signs) > 0 && !empty($seat_signs) && isset($seat_signs[$i])) ? $seat_signs[$i] : '';
                    $seat_direction_val = (isset($seat_directions) && is_array($seat_directions) && count($seat_directions) > 0 && !empty($seat_directions) && isset($seat_directions[$i])) ? $seat_directions[$i] : '';
                    ?>
                        tc_seats[ '<?php echo $seat; ?>' ] = new Array(<?php echo $seat_ticket_types[$i]; ?>, <?php echo '"' . $seat_sign_val . '"'; ?>, <?php echo '"' . $seat_direction_val . '"'; ?>);
                    <?php
                    $i++;
                }
            }
            ?>
                var tc_seat_colors = new Array();
            <?php
            $unique_ticket_types = array_unique($seat_ticket_types);

            $i = 0;
            foreach ($unique_ticket_types as $unique_ticket_type) {
                if (!empty($unique_ticket_type)) {
                    $seat_color_default = apply_filters('tc_seat_color_default', '#6b5f89');
                    $seat_color = get_post_meta($unique_ticket_type, '_seat_color', true);

                    if (empty($seat_color)) {
                        $seat_color = $seat_color_default;
                    }
                    ?>
                        tc_seat_colors[ '<?php echo $unique_ticket_type; ?>' ] = "<?php echo $seat_color; ?>";
                    <?php
                    $i++;
                }
            }
            ?>
            </script>
            <?php
//$content = ob_get_clean();
//set_option('tc_get_occupied_seats_' . $seat_map_post_id, base64_decode($content));
//echo $content;
            /* } else {
              echo base64_encode($content);
              } */
        }

        public static function get_seating_chart_html($post_id, $front = false) {

            try {
                $content = '';
                $upload = wp_upload_dir();
                $upload_dir = $upload['basedir'];
                $upload_dir = $upload_dir . '/tc-seating-charts';

                if ($front) {
                    $filename = $post_id . '-front.tcsm';
                } else {
                    $filename = $post_id . '.tcsm';
                }
                $path = $upload_dir . '/' . $filename;

                if (file_exists($path)) {
                    try {
                        $content = file_get_contents($path);
                    } catch (Exception $e) {
                        $myfile = fopen($path, "r");
                        $content = fread($myfile, filesize($path));
                        fclose($myfile);
                    }
                }

                return $content;
            } catch (Exception $e) {
                return '';
            }
        }

        public static function save_admin($post_id) {
            if (get_post_type($post_id) == 'tc_seat_charts' && isset($_POST['tc_chart_content'])) {

                if (wp_is_post_revision($post_id) && !current_user_can(apply_filters('tc_seating_chart_save_post_capability', 'publish_post'))) {
                    return;
                }

                remove_action('save_post', 'TC_Seat_Chart::save_admin', 10, 1);

                $upload = wp_upload_dir();
                $upload_dir = $upload['basedir'];
                $upload_dir = $upload_dir . '/tc-seating-charts';

                if (!is_writable($upload_dir)) {
                    wp_redirect(admin_url('post.php?post=' . $post_id . '&action=edit&message=0'));
                    exit;
                }

                $chart_post = array(
                    'ID' => $post_id,
                    'post_status' => 'publish',
                    'post_content' => '',
                    'post_name' => isset($permalink[1]) ? $permalink[1] : false
                );

                if (isset($_POST['tc_chart_title'])) {
                    $permalink = get_sample_permalink($post_id, $_POST['tc_chart_title'], sanitize_title($_POST['tc_chart_title']));
                    $chart_post['post_name'] = isset($permalink[1]) ? $permalink[1] : false;
                }

                $upload = wp_upload_dir();
                $upload_dir = $upload['basedir'];
                $upload_dir = $upload_dir . '/tc-seating-charts';

//admin file
                $filename = $post_id . '.tcsm';
                $path = $upload_dir . '/' . $filename;

                $file = @fopen($path, "w");
                @fwrite($file, isset($_POST['tc_chart_content']) ? stripslashes(minify_output($_POST['tc_chart_content'])) : '');
                @fclose($file);
                @chmod($path, 0644);

//front file
                $filename_front = $post_id . '-front.tcsm';
                $path_front = $upload_dir . '/' . $filename_front;

                $tc_chart_content_front_minified = stripslashes(minify_output(isset($_POST['tc_chart_content_front']) ? $_POST['tc_chart_content_front'] : ''));
                $tc_chart_content_front = preg_replace('/<div class=\"tc-group-controls\">.*?<\/div>/', '', $tc_chart_content_front_minified);
                $tc_chart_content_front = preg_replace('/<!--.*?-->/', '', $tc_chart_content_front);

                $file_front = @fopen($path_front, "w");
                @fwrite($file_front, isset($_POST['tc_chart_content_front']) ? $tc_chart_content_front : '');
                @fclose($file_front);
                @chmod($path_front, 0644);

// Update the post into the database
                wp_update_post($chart_post);

                $metas = array();

                foreach ($_POST as $field_name => $field_value) {
                    if (preg_match('/_post_meta/', $field_name)) {
                        $metas[str_replace('_post_meta', '', $field_name)] = $field_value;
                    }

                    $metas = apply_filters('tc_seat_charts_metas', $metas);

                    if (isset($metas)) {
                        foreach ($metas as $key => $value) {
                            update_post_meta($post_id, $key, $value);
                        }
                    }
                }
            }
        }

        /**
         * Save metabox values for the seat map
         * @param type $post_id
         * @return type
         */
        public static function save_metabox_values($post_id) {

            if (get_post_type($post_id) == 'tc_seat_charts') {

                if (wp_is_post_revision($post_id) || !isset($_POST['tc_seat_cords_post_meta'])) {
                    return;
                }

                delete_transient('tc_seat_chart_floor_html_' . $post_id);
                delete_transient('tc_seat_chart_floor_html_front_' . $post_id);

                remove_action('save_post', 'TC_Seat_Chart::save_metabox_values', 10, 1);

                $metas = array();

                foreach ($_POST as $field_name => $field_value) {
                    if (preg_match('/_post_meta/', $field_name)) {
                        $metas[
                                str_replace('_post_meta', '', $field_name)] = $field_value;
                    }

                    $metas = apply_filters('tc_seat_charts_metas', $metas);

                    if (isset($metas
                            )) {
                        foreach ($metas as $key => $value) {
                            update_post_meta($post_id, $key, $value);
                        }
                    }
                }
            }
        }

        /**
         * Enqueue front-end scripts
         * @global TC_Seat_Chart $TC_Seat_Chart
         */
        function enqueue_scripts_and_styles() {
            global $TC_Seat_Chart;
            
            $tc_seat_charts_settings = TC_Seat_Chart::get_settings();

            $use_firebase_integration = isset($tc_seat_charts_settings['user_firebase_integration']) ? $tc_seat_charts_settings['user_firebase_integration'] : '0';
            
            wp_enqueue_script('tc-seat-charts-cart-front', plugins_url('/js/tc-seat-charts-cart-front.js', __FILE__), array('jquery'), $TC_Seat_Chart->version, true);
            wp_enqueue_script('tc-seat-charts-documentsize', plugins_url('/assets/js/front/jquery.documentsize.min.js', __FILE__), array('jquery'), $TC_Seat_Chart->version, true);
            wp_localize_script('tc-seat-charts-cart-front', 'tc_seat_chart_cart_ajax', array(
                'ajaxUrl' => admin_url('admin-ajax.php', (is_ssl() ? 'https' : 'http')),
                'firebase_integration' => $use_firebase_integration              
            ));

            wp_enqueue_style('tc-seatings-front', plugins_url('assets/seatings-default.css', __FILE__));
        }

        /*
         * Render fields by type (function, text, textarea, etc)
         */

        public static function render_field($field, $show_title = true) {
            global $post;

            $seat_chart = get_post($post->ID);
            $seat_chart_fields = get_post_custom($post->ID);

            if ($show_title) {
                ?>
                <label id="<?php echo esc_attr($field['field_name'] . '_label'); ?>" class="tc_seat_chart_label"><?php echo isset($field['field_title']) ? $field['field_title'] : ''; ?>
                    <?php
                }
                //Button
                if ($field['field_type'] == 'button') {
                    submit_button($field['text'], $field['type'], $field['field_name'], false);
                }

                // Function
                if ($field['field_type'] == 'function') {
                    eval($field['function'] . '("' . $field['field_name'] . '"' . (isset($post->ID) ? ',' . $post->ID : '') . ');');
                    ?>
                    <span class="description"><?php echo isset($field['field_description']) ? $field['field_description'] : ''; ?></span>
                    <?php
                }
                //Text
                if ($field['field_type'] == 'text') {
                    $class = isset($field['class']) ? $field['class'] : '';
                    ?>
                    <input type="text" <?php echo isset($field['disabled']) ? 'disabled' : ''; ?> class="regular-<?php echo $field['field_type'] . ' ' . $class; ?>" value="<?php
                    if (isset($post->ID)) {
                        if ($field['post_field_type'] == 'post_meta') {
                            echo esc_attr(isset($seat_chart_fields[$field['field_name']][0]) ? $seat_chart_fields[$field['field_name']][0] : (isset($field['default_value']) ? $field['default_value'] : '') );
                        } else {
                            echo esc_attr($seat_chart_fields[$field['post_field_type']][0]);
                        }
                    }
                    ?>" id="<?php echo $field['field_name']; ?>" name="<?php echo $field['field_name'] . '_' . $field['post_field_type']; ?>">
                    <span class="description"><?php echo isset($field['field_description']) ? $field['field_description'] : ''; ?></span>
                    <?php
                }
                //Textare
                if ($field['field_type'] == 'textarea') {
                    ?>
                    <textarea <?php echo isset($field['disabled']) ? 'disabled' : ''; ?> class="regular-<?php echo $field['field_type']; ?>" id="<?php echo $field['field_name']; ?>" name="<?php echo $field['field_name'] . '_' . $field['post_field_type']; ?>"><?php
                        if (isset($post->ID)) {
                            if ($field['post_field_type'] == 'post_meta') {
                                echo esc_textarea(isset($seat_chart_fields[$field['field_name']][0]) ? $seat_chart_fields[$field['field_name']][0] : (isset($field['default_value']) ? $field['default_value'] : '') );
                            } else {
                                echo esc_textarea($seat_chart_fields[$field['post_field_type']]);
                            }
                        }
                        ?></textarea>
                    <span class="description"><?php echo $field['field_description']; ?></span>
                    <?php
                }
                //Editor
                if ($field['field_type'] == 'textarea_editor') {
                    ?>
                    <?php
                    if (isset($post->ID)) {
                        if ($field['post_field_type'] == 'post_meta') {
                            $editor_content = ( isset($seat_chart_fields[$field['field_name']][0]) ? $seat_chart_fields[$field['field_name']][0] : (isset($field['default_value']) ? $field['default_value'] : '') );
                        } else {
                            $editor_content = ( $seat_chart_fields[$field['post_field_type']] );
                        }
                    } else {
                        $editor_content = '';
                    }
                    wp_editor(html_entity_decode(stripcslashes($editor_content)), $field['field_name'], array('textarea_name' => $field['field_name'] . '_' . $field['post_field_type'], 'textarea_rows' => 5));
                    ?>
                    <span class="description"><?php echo $field['field_description']; ?></span>
                    <?php
                }
                //Image
                if ($field['field_type'] == 'image') {
                    ?>
                    <div class="file_url_holder">
                        <label>
                            <input class="file_url" type="text" size="36" name="<?php echo $field['field_name'] . '_file_url_' . $field['post_field_type']; ?>" value="<?php
                            if (isset($post->ID)) {
                                echo esc_attr(isset($seat_chart_fields[$field['field_name'] . '_file_url']) ? $seat_chart_fields[$field['field_name'] . '_file_url'] : '' );
                            }
                            ?>" />
                            <input class="file_url_button button-secondary" type="button" value="<?php _e('Browse', 'tcsc'); ?>" />
                            <span class="description"><?php echo $field['field_description']; ?></span>
                        </label>
                    </div>
                    <?php
                }
                if ($show_title) {
                    ?>
                </label>
                <?php
            }
        }

        /**
         * Get unique ticket types 
         * Used in tc_seat_chart shortcode
         * @param type $seat_map_post_id
         * @return type
         */
        public static function get_unique_ticket_types($seat_map_post_id) {

            $seat_ticket_types = get_post_meta($seat_map_post_id, 'tc_ticket_types', true);

            $seat_ticket_types = explode(',', $seat_ticket_types);
            return array_filter($seat_ticket_types);
        }

        /**
         * General settings metabox input fields
         */
        public static function show_general_settings_metabox() {

            $is_chart_has_orders = isset($_REQUEST['post']) ? TC_Seat_Chart::is_chart_has_orders($_REQUEST['post']) : false;

            if ($is_chart_has_orders) {
                ?>
                <input type="hidden" id="tc_chart_has_orders" value="1" />
                <?php
            }
            ?>
            <input type="hidden" id="tc_square_size" value="<?php echo esc_attr(TC_Seat_Chart::chart_measure()); ?>" />
            <?php
            if (isset($_REQUEST['post'])) {
                TC_Seat_Chart::get_reserved_seats($_REQUEST['post']);
            }

            TC_Seat_Chart::render_field(array(
                'field_title' => __('Seating Chart Rows', 'tcsc'),
                'field_type' => 'text',
                'post_field_type' => 'post_meta',
                'field_name' => 'seat_chart_rows',
                'post_field_type' => 'post_meta',
                'field_description' => __('Set a number of rows (including the empty ones)', 'tcsc'),
                'default_value' => 20,
            ));

            TC_Seat_Chart::render_field(array(
                'field_title' => __('Seating Chart Columns', 'tcsc'),
                'field_type' => 'text',
                'post_field_type' => 'post_meta',
                'field_name' => 'seat_chart_cols',
                'post_field_type' => 'post_meta',
                'field_description' => __('Set a number of columns (including the empty ones)', 'tcsc'),
                'default_value' => 20,
            ));

            TC_Seat_Chart::render_field(array(
                'field_name' => 'event_name',
                'field_title' => __('Event Name', 'tcsc'),
                'placeholder' => '',
                'field_type' => 'function',
                'function' => 'tc_get_events',
                'field_description' => '',
                'table_visibility' => true,
                'post_field_type' => 'post_meta',
            ));

            if (!$is_chart_has_orders) {
                TC_Seat_Chart::render_field(array(
                    'field_type' => 'button',
                    'field_name' => 'tc_seat_chart_change_settings_button',
                    'text' => __('Change & Save', 'tcsc'),
                    'type' => 'primary'
                ));
            }
            ?>
            <br clear="all" />
            <?php
        }

        public static function chart_measure($data = 'size') {
            $size = apply_filters('tc_chart_measure_size', 35);
            $font = apply_filters('tc_chart_measure_size', 35);

            switch ($data) {
                case 'size':
                    $value = $size;
                    break;
                case 'font':
                    $value = $font;
                    break;

                default:
                    $value = $size;
            }
            return $value;
        }

        public static function show_seat_controls_metabox() {
            ?>
            <div class="tc_seating_chart_canvas_controls">
                <span class="zoom-in">Zoom In</span>
                <span class="zoom-out">Zoom Out</span>
                <input type="range" class="zoom-range">
                <span class="reset">Reset</span>
            </div>
            <?php
        }

        /**
         * Seating chart metabox / floor plan
         * @global type $post
         * @param type $id
         */
        public static function show_seat_chart_metabox() {//$id = false
            global $post;

            $id = $post->ID;

            $seat_chart_rows = get_post_meta($id, 'seat_chart_rows', true);
            if (empty($seat_chart_rows) || !is_numeric($seat_chart_rows)) {
                $seat_chart_rows = 30;
            }

            $seat_chart_cols = get_post_meta($id, 'seat_chart_cols', true);
            if (empty($seat_chart_cols) || !is_numeric($seat_chart_cols)) {
                $seat_chart_cols = 30;
            }

            $canvas_width = $seat_chart_cols * TC_Seat_Chart::chart_measure();
            $canvas_height = $seat_chart_rows * TC_Seat_Chart::chart_measure();

            $selectable_row_html = '';

            /* for ($i = 1; $i < ($seat_chart_rows + 1); $i++) {
              $selectable_row_html .= '<div class="tc_seat_row_row">';

              for ($j = 1; $j < ($seat_chart_cols + 1); $j++) {
              //$selectable_row_html .= '<div class="tc_seat_unit tc_seat_' . $i . '_' . $j . '" data-seat-r="' . $i . '" data-seat-c="' . $j . '"><i class="fa fa-times tc_sc_rfc" aria-hidden="true" style="display:none;"></i></div>';
              $selectable_row_html .= '<div class="tc_seat_unit" id="tc_seat_' . $i . '_' . $j . '"><span class="tc-add-font"></span></div>';
              }

              $selectable_row_html .= '</div>';
              } */
            ?>

            <div class="selectable_row tc_seating_chart_canvas" style="width:<?php echo $canvas_width; ?>px;height:<?php echo $canvas_height; ?>px;">
                <!--SEATING CHART HERE ADMIN-->
                <?php //echo $selectable_row_html;                     ?>
            </div>
            <br clear="all" />
            <p class="description"><?php _e('HINT: use SHIFT and CTRL / CMD keys or mouse (lasso) in order to select or deselect multiple seats at once.', 'tcsc'); ?></p>
            <style type="text/css">
                .tc_seating_selectable_group .tc_seat_unit{
                    width: <?php echo TC_Seat_Chart::chart_measure(); ?>px;
                    height: <?php echo TC_Seat_Chart::chart_measure(); ?>px;
                }

                .tc_seating_selectable_group .tc_seat_unit .tc-add-font{
                    font-size: <?php echo TC_Seat_Chart::chart_measure('font'); ?>px;
                }
            </style>
            <?php
            TC_Seat_Chart::render_field(array(
                'field_title' => __('Seat Cords', 'tcsc'),
                'field_type' => 'text',
                'post_field_type' => 'post_meta',
                'field_name' => 'tc_seat_cords',
                'post_field_type' => 'post_meta',
            ));

            TC_Seat_Chart::render_field(array(
                'field_title' => __('Seat Ticket Types', 'tcsc'),
                'field_type' => 'text',
                'post_field_type' => 'post_meta',
                'field_name' => 'tc_seat_ticket_types',
                'post_field_type' => 'post_meta',
            ));
            TC_Seat_Chart::render_field(array(
                'field_title' => __('Seat Signs', 'tcsc'),
                'field_type' => 'text',
                'post_field_type' => 'post_meta',
                'field_name' => 'tc_seat_signs',
                'post_field_type' => 'post_meta',
            ));
            TC_Seat_Chart::render_field(array(
                'field_title' => __('Seat Directions', 'tcsc'),
                'field_type' => 'text',
                'post_field_type' => 'post_meta',
                'field_name' => 'tc_seat_directions',
                'post_field_type' => 'post_meta',
            ));

            TC_Seat_Chart::get_occupied_seats($id);
        }

        /**
         * Show seating chart on the front-end
         * @global type $post
         * @param type $id
         */
        public static function show_seat_chart_front($id = false) {
            global $post;

            $id = $id ? $id : $post->ID;

            $seat_chart_rows = get_post_meta($id, 'seat_chart_rows', true);
            if (empty($seat_chart_rows) || !is_numeric($seat_chart_rows)) {
                $seat_chart_rows = 20;
            }

            $seat_chart_cols = get_post_meta($id, 'seat_chart_cols', true);
            if (empty($seat_chart_cols) || !is_numeric($seat_chart_cols)) {
                $seat_chart_cols = 20;
            }

            $seat_data = TC_Seat_Chart::get_occupied_seats_front_php($id);
            $tc_seats = $seat_data[0];
            $tc_seat_colors = $seat_data[1];

            $selectable_row_html = '';

            for ($i = 1; $i < ($seat_chart_rows + 1); $i++) {
                $selectable_row_html .= '<div class="tc_seat_row_row">';

                for ($j = 1; $j < ($seat_chart_cols + 1); $j++) {
                    $k = $i . '_' . $j; //key made of row and col number
                    $attributes = '';
                    $tc_add_font_class = '';
                    $seat_classes = '';
                    $seat_color = 'style="color: #0085BA;"';

                    if (isset($tc_seats[$k])) {
                        if (isset($tc_seat_colors[$tc_seats[$k][0]])) {
                            $seat_color = 'style="color:' . $tc_seat_colors[$tc_seats[$k][0]] . ';"';
                        }
                        $attributes .= ' data-ticket-type-id="' . $tc_seats[$k][0] . '"'; //ticket type id
                        $attributes .= ' data-seat-sign="' . $tc_seats[$k][1] . '"'; //seat sign
                        $attributes .= ' data-has-variations="' . $tc_seats[$k][2] . '"'; //is woocommerce product variation
                        $seat_classes = 'tc_set_seat tc_seat_as';
                        $tc_add_font_class = $tc_seats[$k][3]; //rotation
                    }
//Seat seat sign attribute
//Create row html
                    $selectable_row_html .= '<div class="tc_seat_unit tc_seat_' . $k . ' ' . $seat_classes . '" data-seat-r="' . $i . '" data-seat-c="' . $j . '" ' . $attributes . '><span class="tc-add-font ' . $tc_add_font_class . '" ' . $seat_color . '></span><i class="fa fa-times tc_sc_rfc" aria-hidden="true" style="display:none;"></i></div>';
                }

                $selectable_row_html .= '</div>';
            }
            ?>
            <div class="tc_selectable_holder">
                <div class="selectable_row selectable_row_<?php echo esc_attr($id); ?>" data-seat-chart-id="<?php echo esc_attr($id); ?>" data-seat-chart-rows="<?php echo esc_attr($seat_chart_rows); ?>" data-seat-chart-cols="<?php echo esc_attr($seat_chart_cols); ?>">
                    <!--SEATING CHART HERE-->
                    <?php echo $selectable_row_html; ?>
                </div>
            </div>
            <?php
            TC_Seat_Chart::get_occupied_seats_front($id);
        }

        public static function get_reserved_order_statuses() {
            $order_statuses = apply_filters('tc_seat_charts_get_reserved_seats_order_statuses', array('order_received', 'order_paid'));
            return $order_statuses;
        }

        /**
         * Get all reserved seats
         * @param type $chart_id
         * @param type $post_status
         * @return type
         */
        public static function get_reserved_seats($chart_id = false) {

            $post_status = TC_Seat_Chart::get_reserved_order_statuses();

            if (!is_array($post_status)) {
                $post_status = array($post_status);
            }

            if (!$chart_id) {
                return;
            }

            $args = array(
                'post_type' => 'tc_tickets_instances',
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'meta_key' => 'chart_id',
                'meta_value' => (string) $chart_id,
                'no_found_rows' => true
            );

            $tickets_instances = get_posts($args);
            ?>
            <script type="text/javascript">
                if (typeof tc_reserved_seats == "undefined") {
                    var tc_reserved_seats = new Array();
                }
                tc_reserved_seats[<?php echo $chart_id; ?>] = new Array();
            <?php
            $i = 0;
            foreach ($tickets_instances as $ticket_instance) {
                if (in_array(get_post_status($ticket_instance->post_parent), $post_status)) {
                    $seat_id = get_post_meta($ticket_instance->ID, 'seat_id', true);
                    ?>tc_reserved_seats[<?php echo $chart_id; ?>][ '<?php echo $seat_id; ?>' ] = 1;<?php
                    $i++;
                }
            }
            ?>
            </script>
            <?php
        }

        /**
         * Get seats which are in the cart of current user
         * @global type $tc
         * @param type $chart_id
         * @return type
         */
        public static function get_in_cart_seats($chart_id = false) {
            global $tc;

            if (!$chart_id) {
                return;
            }

            $in_cart_seats = TC_Seat_Chart::get_cart_seats_cookie();
            ?>
            <script type="text/javascript">
                if (typeof tc_in_cart_seats == "undefined") {
                    var tc_in_cart_seats = new Array();
                }
                tc_in_cart_seats[<?php echo $chart_id; ?>] = new Array();
            <?php
            $i = 0;
            foreach ($in_cart_seats as $in_cart_ticket_type) {
                foreach ($in_cart_ticket_type as $in_cart_seat) {
                    if ($in_cart_seat[2] == $chart_id) {//check if any of the ticket types which bellong to the current chart_id is in the cart
                        $seat_id = $in_cart_seat[0];
                        ?>tc_in_cart_seats[<?php echo $chart_id; ?>][ '<?php echo $seat_id; ?>' ] = 1;<?php
                        $i++;
                    }
                }
            }
            ?>
            </script>
            <?php
        }

        /**
         * Checks if ticket type is product or product variation (WooCommerce)
         * @param type $ticket_type_id
         * @return boolean
         */
        public static function is_woo_variable_product($ticket_type_id) {
            if (function_exists('get_product')) {
                $product = get_product($ticket_type_id);

                if ($product && $product->is_type('variable')) {
                    return true;
                } else {
                    return false;
                }
            }
        }

        public static function qty_left($ticket_type_id) {
            if (function_exists('get_product')) {
                $product = get_product($ticket_type_id);
                if ($product) {
                    $qty = $product->get_stock_quantity();
                    if (is_numeric($qty)) {
                        return $product->get_stock_quantity();
                    } else {
                        return 999;
                    }
                } else {
                    if (function_exists('tc_get_tickets_count_left')) {
                        $qty = tc_get_tickets_count_left($ticket_type_id);
                        if (!is_numeric($qty)) {
                            $qty = 1;
                        }
                        return $qty;
                    }
                }
            } else {
                if (function_exists('tc_get_tickets_count_left')) {
                    $qty = tc_get_tickets_count_left($ticket_type_id);
                    if (!is_numeric($qty)) {
                        $qty = 1;
                    }
                    return $qty;
                }
            }
            return 1;
        }

        /**
         * Shows floor map on the front-end
         * @global boolean $tc_seats_defined
         * @param type $seat_map_post_id
         */
        public static function get_occupied_seats_front($seat_map_post_id) {

            $seats = get_post_meta($seat_map_post_id, 'tc_seat_cords', true);
            $seats = explode('|', $seats);

            $seat_ticket_types = get_post_meta($seat_map_post_id, 'tc_seat_ticket_types', true);
            $seat_ticket_types = explode('|', $seat_ticket_types);

            $seat_signs = get_post_meta($seat_map_post_id, 'tc_seat_signs', true);
            $seat_signs = explode('|', $seat_signs);

            $seat_directions = get_post_meta($seat_map_post_id, 'tc_seat_directions', true);
            $seat_directions = explode('|', $seat_directions);
            ?>
            <script type="text/javascript" >
                if (typeof tc_seats == "undefined") {
                    var tc_seats = new Array();
                }
                tc_seats[<?php echo $seat_map_post_id; ?>] = new Array();
            <?php
            $i = 0;

            $has_seat_signs = (is_array($seat_signs) && count($seat_signs) > 0 && !empty($seat_signs)) ? true : false;
            $has_seat_direction = (is_array($seat_directions) && count($seat_directions) > 0 && !empty($seat_directions)) ? true : false;
            foreach ($seats as $seat) {
                if (!empty($seat)) {
                    $seat_sign_val = $has_seat_signs && isset($seat_signs[$i]) ? $seat_signs[$i] : '';
                    $seat_direction_val = $has_seat_direction && isset($seat_directions[$i]) ? $seat_directions[$i] : 'bc';
                    ?>tc_seats[<?php echo $seat_map_post_id; ?>][ '<?php echo $seat; ?>' ] = new Array(<?php echo $seat_ticket_types[$i]; ?>, <?php echo '"' . $seat_sign_val . '"'; ?>, <?php echo TC_Seat_Chart::is_woo_variable_product($seat_ticket_types[$i]) ? 1 : 0; ?>, <?php echo '"' . $seat_direction_val . '"'; ?>);<?php
                    $i++;
                }
            }
            ?>
                        if (typeof tc_seat_colors == "undefined") {
                            var tc_seat_colors = new Array();
                        }
            <?php
            $unique_ticket_types = array_unique($seat_ticket_types);

            $i = 0;
            foreach ($unique_ticket_types as $unique_ticket_type) {
                if (!empty($unique_ticket_type)) {
                    $seat_color_default = apply_filters('tc_seat_color_default', '#6b5f89');
                    $seat_color = get_post_meta($unique_ticket_type, '_seat_color', true);

                    if (empty($seat_color)) {
                        $seat_color = $seat_color_default;
                    }
                    ?>
                                tc_seat_colors[ '<?php echo $unique_ticket_type; ?>' ] = "<?php echo $seat_color; ?>";
                    <?php
                    $i++;
                }
            }
            ?>
            </script>
            <?php
            /* } else {
              echo base64_decode($content);
              } */
        }

        public static function get_occupied_seats_front_php($seat_map_post_id) {

            $seats = get_post_meta($seat_map_post_id, 'tc_seat_cords', true);
            $seats = explode('|', $seats);

            $seat_ticket_types = get_post_meta($seat_map_post_id, 'tc_seat_ticket_types', true);
            $seat_ticket_types = explode('|', $seat_ticket_types);

            $seat_signs = get_post_meta($seat_map_post_id, 'tc_seat_signs', true);
            $seat_signs = explode('|', $seat_signs);

            $seat_directions = get_post_meta($seat_map_post_id, 'tc_seat_directions', true);
            $seat_directions = explode('|', $seat_directions);

            $tc_seats = array();

            $i = 0;

            $has_seat_signs = (is_array($seat_signs) && count($seat_signs) > 0 && !empty($seat_signs)) ? true : false;
            $has_seat_direction = (is_array($seat_directions) && count($seat_directions) > 0 && !empty($seat_directions)) ? true : false;
            foreach ($seats as $seat) {
                if (!empty($seat)) {
                    $seat_sign_val = $has_seat_signs && isset($seat_signs[$i]) ? $seat_signs[$i] : '';
                    $seat_direction_val = $has_seat_direction && isset($seat_directions[$i]) ? $seat_directions[$i] : 'bc';
                    $tc_seats[$seat] = array($seat_ticket_types[$i], $seat_sign_val, TC_Seat_Chart::is_woo_variable_product($seat_ticket_types[$i]) ? 1 : 0, $seat_direction_val);
                    $i++;
                }
            }

            $tc_seat_colors = array();

            $unique_ticket_types = array_unique($seat_ticket_types);

            $i = 0;

            foreach ($unique_ticket_types as $unique_ticket_type) {
                if (!empty($unique_ticket_type)) {
                    $seat_color_default = apply_filters('tc_seat_color_default', '#6b5f89');
                    $seat_color = get_post_meta($unique_ticket_type, '_seat_color', true);

                    if (empty($seat_color)) {
                        $seat_color = $seat_color_default;
                    }

                    $tc_seat_colors[$unique_ticket_type] = $seat_color;
                    $i++;
                }
            }

            return array($tc_seats, $tc_seat_colors);
        }

        /**
         * Seat Sign metabox
         */
        public static function show_seat_sign_settings_metabox() {
            ?>
            <div class="tc_box_overlay"><span><?php _e('Select assigned seats and add seat labels.', 'tcsc'); ?></span></div>
            <div class="tc_seat_sign_settings_single_holder">
                <label><?php _e('Label', 'tcsc'); ?>
                    <input type="text" value="" id="tc_seat_sign_settings_single_seat_row_sign">
                </label>
                <?php
                TC_Seat_Chart::render_field(array(
                    'field_type' => 'button',
                    'field_name' => 'tc_seat_sign_settings_single_set_button',
                    'text' => __('Set', 'tcsc'),
                    'type' => 'primary'
                ));
                ?>
            </div>
            <div class="tc_seat_sign_settings_multi_holder">
                <label class="tc_seat_label_one_third"><?php _e('Row Label', 'tcsc'); ?>
                    <input type="text" value="" id="tc_seat_sign_settings_multi_seat_row_sign">
                </label>
                <label class="tc_seat_label_two_third"><?php _e('Col Label (from - to)', 'tcsc'); ?><br />
                    <input type="text" value="" placeholder="<?php _e('From', 'tcsc'); ?>" id="tc_seat_sign_settings_multi_seat_col_sign_from">
                    <span class="tc_col_label_invert">↔</span>
                    <input type="text" value="" placeholder="<?php _e('To', 'tcsc'); ?>" id="tc_seat_sign_settings_multi_seat_col_sign_to">
                </label>
                <?php
                TC_Seat_Chart::render_field(array(
                    'field_type' => 'button',
                    'field_name' => 'tc_seat_sign_settings_multi_set_button',
                    'text' => __('Set', 'tcsc'),
                    'type' => 'primary'
                ));
                ?>
            </div>
            <br clear="all" />
            <?php
        }

        /**
         * Seat Sign metabox
         */
        public static function show_seat_direction_settings_metabox() {
            ?>
            <div class="tc_box_overlay"><span><?php _e('Select assigned seats and set their direction.', 'tcsc'); ?></span></div>

            <div class="tc_seat_direction_holder">
                <div class="tc_seat_direction_box tl"><span>↑</span></div>
                <div class="tc_seat_direction_box tc"><span>↑</span></div>
                <div class="tc_seat_direction_box tr"><span>↑</span></div>

                <div class="tc_seat_direction_box lc"><span>←</span></div>
                <div class="tc_seat_direction_box blank"><span>☼</span></div>
                <div class="tc_seat_direction_box rc"><span>→</span></div>

                <div class="tc_seat_direction_box bl"><span>↓</span></div>
                <div class="tc_seat_direction_box bc tc-selected-direction"><span>↓</span></div>
                <div class="tc_seat_direction_box br"><span>↓</span></div>
            </div>
            <input type="hidden" value="bc" id="tc_seat_direction_settings_value">

            <?php
            /* TC_Seat_Chart::render_field(array(
              'field_type' => 'button',
              'field_name' => 'tc_seat_direction_settings_single_set_button',
              'text' => __('Set', 'tcsc'),
              'type' => 'primary'
              )); */
            ?>                                                                                                                                                       <!--<p class="tc_seat_sign_settings_message"><?php _e('Select one or more seats which have assigned ticket type in order to set seat(s) sign.', 'tcsc'); ?></p>-->
            <br clear="all" />
            <?php
        }

        public static function show_add_seats_metabox() {
            ?>
            <label class="tc_seat_label_two_third">
                <input type="text" value="10" placeholder="<?php _e('Rows', 'tcsc'); ?>" id="tc_seat_add_seats_rows">
                <input type="text" value="10" placeholder="<?php _e('Cols', 'tcsc'); ?>" id="tc_seat_add_seats_cols">
            </label>
            <?php
            TC_Seat_Chart::render_field(array(
                'field_type' => 'button',
                'field_name' => 'tc_add_seats_button',
                'text' => __('Create', 'tcsc'),
                'type' => 'primary'
            ));
        }

        /**
         * Ticket type settings metabox
         */
        public static function show_ticket_type_settings_metabox() {
            ?>
            <div class="ticket_type_id_wrapper">
                <label><?php _e('Ticket Type', 'tcsc'); ?>
                    <select name="ticket_type_id" id="ticket_type_id">
                        <option value=""><?php _e('Loading...', 'tcsc'); ?></option>
                    </select>
                </label>
            </div>
            <?php
            TC_Seat_Chart::render_field(array(
                'field_type' => 'button',
                'field_name' => 'tc_seat_chart_unset_ticket_type_settings_button',
                'text' => __('Unset', 'tcsc'),
                'type' => 'secondary'
            ));

            TC_Seat_Chart::render_field(array(
                'field_type' => 'button',
                'field_name' => 'tc_seat_chart_change_ticket_type_settings_button',
                'text' => __('Set', 'tcsc'),
                'type' => 'primary'
            ));
            ?>
            <br clear="all" />
            <?php
        }

        public static function set_event_ticket_types_colors($event_id = false, $front = false) {
            if ($event_id && !empty($event_id)) {
                $event_id = get_post_meta($event_id, 'event_name', true);

                $args = array(
                    'post_type' => 'tc_tickets',
                    'post_status' => 'any',
                    'posts_per_page' => -1,
                    'meta_key' => 'event_name',
                    'meta_value' => (string) $event_id,
                    'orderby' => 'post_title',
                    'order' => 'ASC',
                    'no_found_rows' => true
                );

                $args = apply_filters('tc_get_event_ticket_types_args', $args);
                $ticket_types = apply_filters('tc_get_event_ticket_types', get_posts($args), $event_id, false);

                $ticket_type_colors = '';
                $ticket_types_colors_script = '';

                foreach ($ticket_types as $ticket_type) {
                    $seat_color_default = apply_filters('tc_seat_color_default', '#6b5f89');
                    $seat_color = get_post_meta($ticket_type->ID, '_seat_color', true);

                    if (empty($seat_color)) {
                        $seat_color = $seat_color_default;
                    }

                    $ticket_type_colors .= 'tc_seat_default_colors[' . $ticket_type->ID . '] = "' . $seat_color . '";';
                }

                $ticket_types_colors_script .= '<script type="text/javascript">
				var tc_seat_default_colors = new Array();';

                $ticket_types_colors_script .= $ticket_type_colors;

                if ($front) {
                    $ticket_types_colors_script .= ''
                            . 'if (window.tc_controls !== null || window.tc_controls !== "undefined") {'
                            . 'window.tc_controls.set_default_colors();'
                            . '}';
                }

                $ticket_types_colors_script .= '</script>';

                echo $ticket_types_colors_script;
            }
        }

        /**
         * Shows select box with ticket types
         * @return type
         */
        function get_event_ticket_types_select() {

            if (!isset($_POST['event_id'])) {
                return;
            }

            $event_id = $_POST['event_id'];

            $args = array(
                'post_type' => 'tc_tickets',
                'post_status' => 'any',
                'posts_per_page' => -1,
                'meta_key' => 'event_name',
                'meta_value' => (string) $event_id,
                'orderby' => 'post_title',
                'order' => 'ASC',
                'no_found_rows' => true
            );

            $args = apply_filters('tc_get_event_ticket_types_args', $args);

            $ticket_types = apply_filters('tc_get_event_ticket_types', get_posts($args), $event_id, false);

            $ticket_type_colors = '';
            $ticket_types_html = '';

            $ticket_types_html .= '<select name="ticket_type_id" id="ticket_type_id" class="ticket_type_id">';

            foreach ($ticket_types as $ticket_type) {

                $ticket_type_title = apply_filters('tc_checkout_owner_info_ticket_title', $ticket_type->post_title, $ticket_type->ID);

                $ticket_types_html .= '<option value="' . esc_attr($ticket_type->ID) . '">' . esc_attr($ticket_type_title) . '</option>';

                $seat_color_default = apply_filters('tc_seat_color_default', '#6b5f89');
                $seat_color = get_post_meta($ticket_type->ID, '_seat_color', true);

                if (empty($seat_color)) {
                    $seat_color = $seat_color_default;
                }

                $ticket_type_colors .= 'tc_seat_colors[' . $ticket_type->ID . '] = "' . $seat_color . '";';
            }

            $ticket_types_html .= '</select>';

            $ticket_types_html .= '<script type="text/javascript">
				var tc_seat_colors = new Array();';

            $ticket_types_html .= $ticket_type_colors;

            $ticket_types_html .= '</script>';

            echo $ticket_types_html;
            exit;
        }

    }

    $TC_Seat_Chart = new TC_Seat_Chart();
}

//Addon updater 
if (function_exists('tc_plugin_updater')) {
    tc_plugin_updater('seating-charts', __FILE__);
}
?>