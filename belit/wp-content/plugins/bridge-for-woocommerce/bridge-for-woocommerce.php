<?php
/**
 * Plugin Name: Tickera Bridge for WooCommerce
 * Plugin URI: https://tickera.com/
 * Description: Leverage the power of both WooCommerce and Tickera to manage events and sell tickets
 * Version: 1.1.5.9
 * Author: Tickera
 * Author URI: https://tickera.com/
 * Developer: Tickera
 * Developer URI: https://tickera.com/
 * Text Domain: woocommerce-tickera-bridge
 * Domain Path: /languages
 */
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly


if (class_exists('TC')) {
    
} else {
    add_action('admin_notices', 'tc_tickera_plugin_installation_message');
}

if (class_exists('TC') && class_exists('WooCommerce')) {
    
} else {
    add_action('admin_notices', 'tc_tickera_plugin_installation_message');
}

/**
 * Show admin notices if Tickera of WooCommerce are not installed
 */
function tc_tickera_plugin_installation_message() {
    $url_tickera = add_query_arg(
            array('tab' => 'plugin-information',
        'plugin' => 'tickera-event-ticketing-system',
        'TB_iframe' => 'true'), network_admin_url('plugin-install.php'));

    $url_woocommerce = add_query_arg(
            array('tab' => 'plugin-information',
        'plugin' => 'woocommerce',
        'TB_iframe' => 'true'), network_admin_url('plugin-install.php'));

    $title_tickera = __('Tickera', 'woocommerce-tickera-bridge');
    $title_woocommerce = __('WooCommerce', 'woocommerce-tickera-bridge');

    if (!class_exists('TC')) {
        echo '<div class="error"><p>' . sprintf(__('To begin using WooCommerce Bridge for Tickera, please install and activate the latest version of <a href="%s" class="thickbox" title="%s">%s</a>.', 'woocommerce-tickera-bridge'), esc_url($url_tickera), $title_tickera, $title_tickera) . '</p></div>';
    }

    if (!class_exists('WooCommerce')) {
        echo '<div class="error"><p>' . sprintf(__('To begin using WooCommerce Bridge for Tickera, please install and activate the latest version of <a href="%s" class="thickbox" title="%s">%s</a>.', 'woocommerce-tickera-bridge'), esc_url($url_woocommerce), $title_woocommerce, $title_woocommerce) . '</p></div>';
    }
}

if (!class_exists('TC_WooCommerce_Bridge')) {

    class TC_WooCommerce_Bridge {

        var $version = '1.1.5.9';
        var $tc_version_required = '3.2.7.1';
        var $title = 'Bridge for WooCommerce';
        var $name = 'tc_woobridge';
        var $dir_name = 'bridge-for-woocommerce';
        var $location = 'plugins';
        var $plugin_dir = '';
        var $plugin_url = '';

        function __construct() {

            $this->init_vars();
            global $tc;

            require_once($this->plugin_dir . 'functions.php');
            include_once( 'class-admin-duplicate-product.php' );

            add_action('admin_notices', array(&$this, 'admin_notices'));
            add_action('woocommerce_product_options_general_product_data', array(&$this, 'tc_add_custom_settings'));
            add_action('woocommerce_process_product_meta', array(&$this, 'tc_custom_settings_fields_save'), 10, 1);
            //TO DO add_action('woocommerce_save_product_variation', array(&$this, 'tc_custom_additional_meta_save_variations'), 10, 2);
            add_action('woocommerce_checkout_after_customer_details', array(&$this, 'add_standard_tc_fields_to_checkout'));
            add_action('woocommerce_new_order', array(&$this, 'tc_order_created'), 10, 1);
            add_action('woocommerce_resume_order', array(&$this, 'tc_order_created'), 10, 1);
            add_action('woocommerce_api_create_order', array(&$this, 'tc_api_create_order'), 10, 2);

            add_action('woocommerce_order_details_after_order_table', array(&$this, 'tc_add_tickets_table_on_woo_order_details_page'), 10, 2);
            add_action('woocommerce_view_order', array(&$this, 'tc_add_tickets_table_on_woo_order_details_page'), 10, 2);

            if (apply_filters('tc_bridge_for_woocommerce_content_order_table_is_after', true) == true) {
                add_action('woocommerce_email_after_order_table', array(&$this, 'tc_add_content_email_after_order_table'), 99, 3);
            } else {
                add_action('woocommerce_email_before_order_table', array(&$this, 'tc_add_content_email_after_order_table'), 99, 3);
            }

            /* if (defined('tc_woo_has_manual_tickets') && tc_woo_has_manual_tickets == true) {
              add_action('woocommerce_process_shop_order_meta', array(&$this, 'tc_maybe_add_additional_tickets'), 11, 2);
              } */

            add_action('woocommerce_checkout_process', array(&$this, 'tc_validate_tickera_fields'), 10, 1);
            add_action('woocommerce_admin_order_data_after_order_details', array(&$this, 'tc_woocommerce_admin_order_data_after_order_details'));
            add_action('woocommerce_order_status_changed', array(&$this, 'check_tickets_action'), 10, 3);

            add_filter('tc_bridge_for_woocommerce_is_active', array(&$this, 'tc_bridge_for_woocommerce_is_active'));

            add_filter('tc_event_shortcode_column', array(&$this, 'tc_event_shortcode_column_modify'), 10, 2);
            add_filter('tc_plugin_admin_menu_items', array(&$this, 'tc_modify_plugin_admin_menu_items'), 10, 1);
            add_filter('tc_ticket_instance_order_admin_url', array(&$this, 'tc_modify_ticket_instance_order_admin_url'), 10, 3);
            add_filter('tc_cart_contents', array(&$this, 'tc_modify_cart_contents'), 10, 1);
            add_filter('tc_event_name_field_name', array(&$this, 'tc_modify_event_name_field_name'), 10, 1);
            add_filter('tc_ticket_template_field_name', array(&$this, 'tc_modify_ticket_template_field_name'), 99, 1);
            add_filter('tc_order_is_paid', array(&$this, 'tc_modify_order_is_paid'), 10, 2);
            add_filter('tc_paid_post_statuses', array(&$this, 'tc_modify_order_paid_statuses'), 10, 1);
            add_filter('tc_order_post_type_name', array(&$this, 'tc_modify_order_post_type_name'), 10, 1);
            add_filter('tc_download_ticket_url_front', array(&$this, 'tc_modify_download_ticket_url_front'), 10, 3);
            add_filter('tc_available_checkins_per_ticket_field_name', array(&$this, 'tc_modify_available_checkins_per_ticket_field_name'), 10, 1);
            add_filter('tc_ticket_checkin_order_date', array(&$this, 'modify_checkin_order_date'), 10, 2);
            add_filter('tc_ticket_checkin_buyer_first_name', array(&$this, 'modify_checkin_buyer_first_name'), 10, 2);
            add_filter('tc_ticket_checkin_buyer_last_name', array(&$this, 'modify_checkin_buyer_last_name'), 10, 2);
            add_filter('tc_ticket_checkin_buyer_full_name', array(&$this, 'modify_checkin_buyer_full_name'), 10, 2);
            add_filter('tc_ticket_buyer_name_element', array(&$this, 'modify_checkin_buyer_full_name'), 10, 2);
            add_filter('tc_ticket_checkin_buyer_email', array(&$this, 'modify_checkin_buyer_email'), 10, 2);
            add_filter('tc_general_settings_store_fields', array(&$this, 'modify_general_settings_store_fields'), 10, 1);
            add_filter('tc_get_event_ticket_types', array(&$this, 'modify_get_event_ticket_types'), 10, 3);
            add_filter('tc_settings_new_menus', array(&$this, 'tc_settings_new_menus'));
            add_filter('tc_settings_general_sections', array(&$this, 'modify_settings_general_sections'));
            add_filter('tc_general_settings_miscellaneous_fields', array(&$this, 'modify_settings_miscellaneous_fields'));
            add_filter('tc_settings_email_sections', array(&$this, 'modify_settings_email_sections'));
            add_filter('tc_settings_email_fields', array(&$this, 'modify_settings_email_fields'));
            add_filter('tc_add_network_admin_menu', array(&$this, 'modify_add_network_admin_menu'));
            add_filter('tc_custom_forms_owner_form_template_meta', array(&$this, 'modify_custom_forms_owner_form_template_meta'), 10, 1);
            add_filter('tc_order_fields', array(&$this, 'modify_order_fields'), 9, 1);
            add_filter('tc_buyer_info_fields', array(&$this, 'modify_buyer_info_fields'), 9, 1);
            add_filter('tc_custom_forms_show_custom_fields_as_order_columns', array(&$this, 'modify_show_custom_fields_as_order_columns'), 10, 1);
            add_filter('tc_ticket_type_id', array(&$this, 'modify_ticket_type_id'), 10, 1);
            add_filter('tc_checkout_owner_info_ticket_title', array(&$this, 'modify_checkout_owner_info_ticket_title'), 10, 2);

            add_action('woocommerce_pos_process_payment', array(&$this, 'tc_create_tickets_for_order'), 10, 2);
            
            add_filter('tc_csv_payment_statuses', array(&$this, 'modify_csv_payment_statuses'), 10, 1);
            add_filter('tc_export_order_number_column_value', array(&$this, 'modify_export_order_number_column_value'), 10, 2);
            add_filter('tc_order_payment_gateway_name', array(&$this, 'modify_order_payment_gateway_name'), 10, 2);
            add_filter('tc_csv_admin_fields', array(&$this, 'modify_tc_csv_admin_fields'), 10, 2);
            add_filter('tc_order_status_title', array(&$this, 'modify_order_status_title'), 10, 3);
            add_filter('tc_shortcodes', array(&$this, 'modify_shortcode_builder_list'), 10, 1);
            add_filter('tc_event_shortcode', array(&$this, 'modify_event_shortcode'), 10, 1);
            add_filter('tc_order_found', array(&$this, 'modify_order_found'), 10, 2);
            add_filter('tc_ticket_type_admin_url', array(&$this, 'modify_ticket_type_admin_url'), 10, 1);

            add_filter('tc_csv_admin_fields', array(&$this, 'tc_csv_admin_fields_woo_billing'));
            add_filter('tc_csv_array', array(&$this, 'tc_csv_array_woo_billing'), 10, 4);

            add_action('admin_enqueue_scripts', array(&$this, 'admin_header'));
            add_action('wp_enqueue_scripts', array(&$this, 'front_header'));

            add_action('add_meta_boxes', array(&$this, 'add_meta_boxes'), 30);
            add_action('pre_get_posts', array(&$this, 'tc_custom_pre_get_posts_query'), 10);
            add_action('template_redirect', array(&$this, 'tc_redirect_ticket_single_to_event'));
            add_action('init', array(&$this, 'replace_shortcodes'), 11);
            add_action('init', array(&$this, 'load_plugin_textdomain'), 11);

            add_action('admin_init', array(&$this, 'check_order_deletion'));

            add_action('plugins_loaded', array(&$this, 'load_ticket_template_elements'));

//Seat Charts
            add_filter('tc_seat_chart_add_to_cart_url', array(&$this, 'modify_seat_chart_add_to_cart_url'));
            add_filter('tc_seat_chart_checkout_url', array(&$this, 'modify_seat_chart_checkout_url'));
            add_filter('tc_seat_chart_in_cart_count', array(&$this, 'modify_seat_chart_in_cart_count'));
            add_filter('tc_seat_chart_cart_subtotal', array(&$this, 'modify_seat_chart_cart_subtotal'));
            add_filter('tc_is_woo', array(&$this, 'modify_tc_seat_charts_is_woo'));
            add_filter('tc_seat_charts_get_reserved_seats_order_statuses', array(&$this, 'modify_seat_charts_get_reserved_seats_post_status'));
            add_filter('tc_seat_chart_shortcode_price', array(&$this, 'modify_price_per_ticket'), 10, 4);


            add_action('manage_product_posts_custom_column', array(&$this, 'change_product_post_link_in_the_admin'), 10, 2);

            add_filter('woocommerce_is_purchasable', array(&$this, 'maybe_modify_product_is_purchasable'), 10, 2);
            //add_filter('woocommerce_product_is_visible', array(&$this, 'maybe_modify_product_is_visible'), 10, 2);

            add_action('woocommerce_update_order_review_fragments', array(&$this, 'update_order_review_fragments'), 10, 1);

            //TO DO add_action('woocommerce_product_after_variable_attributes', array($this, 'woo_ticket_type_additional_meta_variations'), 10, 3);
        }

        function woo_ticket_type_additional_meta_variations($loop, $variation_data, $variation) {
            $variation_id = $variation->ID;
            $_ticket_type_variation_override_checkins = get_post_meta($variation_id, '_ticket_type_variation_override_checkins', true);
            $_ticket_type_variation_override_checkins = !empty($_ticket_type_variation_override_checkins) ? $_ticket_type_variation_override_checkins : 'no';

            $parent_product_id = wp_get_post_parent_id($variation_id);

            $is_ticket_meta = get_post_meta(TC_WooCommerce_Bridge::maybe_get_original_wpml_product_id((int) $parent_product_id), '_tc_is_ticket', true);
            $is_ticket = $is_ticket_meta == 'yes' ? true : false;

            if ($is_ticket) {
                $display = 'style="display: block"';
            } else {
                $display = 'style="display: none"';
            }
            ?>

            <div class="tc_ticket_type_pd_variation_holder show_if_tc_ticket" <?php echo $display; ?>>
                <p class="form-field form-row form-row-first form-row-first tc_ticket_type_pd_variation">
                    <label><?php _e('Override "Check-ins per ticket" count', 'tc'); ?></label>
                    <span class="woocommerce-help-tip" data-tip="<?php echo esc_attr(__('Set "Yes" if number of check-ins per ticket variation is different from the parent ticket product', 'tc')); ?>"></span>
                    <br /><input type="radio" name="_ticket_type_variation_override_checkins[<?php echo esc_attr($loop); ?>]" value="no" <?php checked($_ticket_type_variation_override_checkins, 'no', true); ?>  /><?php _e('No', 'tc'); ?>
                    <input type="radio" name="_ticket_type_variation_override_checkins[<?php echo esc_attr($loop); ?>]" value="yes" <?php checked($_ticket_type_variation_override_checkins, 'yes', true); ?>  /><?php _e('Yes', 'tc'); ?>
                </p>
                <p class="form-field form-row form-row-last tc_ticket_type_pd_variation_available_checkins_per_ticket_field">
                    <label for="tc_ticket_type_pd_variation_available_checkins_per_ticket_field"><?php _e('Check-ins per ticket', 'tc') ?></label>
                    <span class="woocommerce-help-tip" data-tip="<?php echo esc_attr(__('Set number of check-ins allowed per ticket variation', 'tc')); ?>"></span>
                    <input type="number" class="short" style="" name="tc_ticket_type_pd_variation_available_checkins_per_ticket_field" id="_available_checkins_per_ticket" value="" placeholder="<?php echo esc_attr(__('Unlimited', 'tc')); ?>">
                </p>
            </div>

            <?php
        }

        function update_order_review_fragments($fragments) {
            ob_start();
            $this->add_standard_tc_fields_to_checkout();
            $fragments['.tickera_additional_info'] = ob_get_clean();
            return $fragments;
        }

        /* function maybe_modify_product_is_visible($visible, $product_id) {
          return true;
          } */

        function maybe_modify_product_is_purchasable($status, $object) {
            if (!TC_Ticket::is_sales_available($object->get_id())) {
                $status = false;
            }
            return $status;
        }

        function change_product_post_link_in_the_admin($column, $product_id) {
            if ($column == 'name') {
                $is_ticket_meta = get_post_meta($product_id, '_tc_is_ticket', true);
                $is_ticket = $is_ticket_meta == 'yes' ? true : false;

                if ($is_ticket) {
                    $event_id = get_post_meta($product_id, '_event_name', true);
                    echo '<span class="tc_wc_product_title_event"><a href="' . admin_url('post.php?post=' . $event_id . '&action=edit') . '">' . get_the_title($event_id) . '</a></span>';
                }
            }
        }

        function modify_price_per_ticket($price, $id, $formatted = true, $semi_formatted = false) {
            $id = TC_WooCommerce_Bridge::maybe_get_original_wpml_product_id($id);
            $post_type = get_post_type($id);
            if ($post_type == 'product' || $post_type == 'product_variation') {
                $product = wc_get_product($id);
                if ($formatted) {
                    $price = $product->get_price_html();
                } else {
                    if ($semi_formatted) {
                        $price = strip_tags($product->get_price_html(), '<del>');
                    } else {
                        $price = strip_tags($product->get_price_html());
                    }
                }
            }
            return $price;
        }

        function modify_seat_chart_checkout_url() {
            return wc_get_checkout_url();
        }

        function modify_seat_chart_add_to_cart_url() {
            return wc_get_cart_url();
        }

        function modify_seat_chart_in_cart_count() {
            return WC()->cart->get_cart_contents_count();
        }

        function modify_seat_chart_cart_subtotal() {
            return WC()->cart->get_cart_total();
        }

        function modify_tc_seat_charts_is_woo() {
            return true;
        }

        function admin_notices() {
            global $tc;

            if (current_user_can('manage_options')) {
                if (isset($tc->version) && version_compare($tc->version, $this->tc_version_required, '<')) {
                    ?>
                    <div class="notice notice-error">
                        <p><?php printf(__('%s add-on requires at least %s version of %s plugin. Your current version of %s is %s. Please update it.', 'tc'), $this->title, $this->tc_version_required, $tc->title, $tc->title, $tc->version); ?></p>
                    </div>
                    <?php
                }

                /* if (!$this->is_3_0_version()) {
                  ?>
                  <div class="notice notice-error">
                  <p><?php printf(__('%s add-on requires at least 3.0 version of WooCommerce plugin. Please update it.', 'tc'), $this->title); ?></p>
                  </div>
                  <?php
                  } */
            }
        }

        function tc_bridge_for_woocommerce_is_active() {
            return true;
        }

        function check_tickets_action($post_id, $old_status, $new_status) {
            if ($new_status == 'cancelled') {
                $this->trash_associated_tickets($post_id);
            } else {
                $this->untrash_associated_tickets($post_id);
            }

            $wc_order = new WC_Order($post_id);

            if ($this->allowed_tickets_download($wc_order)) {
                do_action('tc_wb_allowed_tickets_access', $wc_order);
            }
        }

        function check_order_deletion() {
            add_action('delete_post', array(&$this, 'delete_associated_tickets'), 10, 1);
            add_action('wp_trash_post', array(&$this, 'trash_associated_tickets'), 10, 1);
            add_action('untrashed_post', array(&$this, 'untrash_associated_tickets'), 10, 1);
        }

        function untrash_associated_tickets($order_id) {
            if (get_post_type($order_id) == 'shop_order') {
                $args = array(
                    'post_type' => 'tc_tickets_instances',
                    'post_status' => 'trash',
                    'post_parent' => $order_id,
                    'posts_per_page' => -1,
                );

                $ticket_instances = get_posts($args);

                foreach ($ticket_instances as $ticket_instance) {
                    wp_untrash_post($ticket_instance->ID);
                }
            }
        }

        function trash_associated_tickets($order_id) {
            if (get_post_type($order_id) == 'shop_order') {
                $args = array(
                    'post_type' => 'tc_tickets_instances',
                    'post_status' => 'any',
                    'post_parent' => $order_id,
                    'posts_per_page' => -1,
                );

                $ticket_instances = get_posts($args);

                foreach ($ticket_instances as $ticket_instance) {
                    $ticket_instance_instance = new TC_Ticket_Instance($ticket_instance->ID);
                    $ticket_instance_instance->delete_ticket_instance(false);
                }
            }
        }

        function delete_associated_tickets($order_id) {
            if (get_post_type($order_id) == 'shop_order') {
                $args = array(
                    'post_type' => 'tc_tickets_instances',
                    'post_status' => 'any',
                    'post_parent' => $order_id,
                    'limit' => -1,
                );

                $ticket_instances = get_posts($args);

                foreach ($ticket_instances as $ticket_instance) {
                    $ticket_instance_instance = new TC_Ticket_Instance($ticket_instance->ID);
                    $ticket_instance_instance->delete_ticket_instance(true);
                }
            }
        }

        /**
         * Load Localisation files (first translation file found will be loaded, others will be ignored)
         *
         * Frontend/global Locales found in:
         * 		- WP_LANG_DIR/woocommerce-tickera-bridge-LOCALE.mo
         * 		- WP_LANG_DIR/woocommerce-tickera-bridge/woocommerce-tickera-bridge-LOCALE.mo
         * 	 	- woocommerce-tickera-bridge/languages/woocommerce-tickera-bridge-LOCALE.mo (which if not found falls back to:)
         */
        public function load_plugin_textdomain() {
            $locale = apply_filters('plugin_locale', get_locale(), 'woocommerce-tickera-bridge');
            load_textdomain('woocommerce-tickera-bridge', WP_LANG_DIR . '/woocommerce-tickera-bridge-' . $locale . '.mo');
            load_textdomain('woocommerce-tickera-bridge', WP_LANG_DIR . '/woocommerce-tickera-bridge/woocommerce-tickera-bridge-' . $locale . '.mo');
            load_plugin_textdomain('woocommerce-tickera-bridge', false, plugin_basename(dirname(__FILE__)) . "/languages");
        }

        
        public static function tc_create_tickets_for_order($payment_details, $order){
                           
        global $woocommerce;
        $items = $order->get_items();
        $order_id = $order->get_order_number();
        $owner_record_num = 1;
         
         foreach ( $order->get_items() as $item_id => $item ) {
             
            $product_id = $item->get_product_id();
            $variation_id = $item->get_variation_id(); 
            $is_ticket_meta = get_post_meta($product_id, '_tc_is_ticket', true);
            $is_ticket = $is_ticket_meta == 'yes' ? true : false;
            $item_quantity = $item->get_quantity();
            $metas = array();
            if ($is_ticket) {
                
                $metas['ticket_type_id'] = $product_id; //CHECK FOR VARIATIONS HERE!!!//////////////////////
                $qty = $item_quantity;
                
                for ($i = 1; $i <= $qty; $i++) {

                        if (apply_filters('tc_use_only_digit_order_number', false) == true) {
                            $tc_ticket_code = apply_filters('tc_ticket_code', $order_id . '' . $owner_record_num);
                        } else {
                            $tc_ticket_code = apply_filters('tc_ticket_code', $order_id . '-' . $owner_record_num);
                        }

                        $user_id = get_current_user_id();

                        $arg = array(
                            'post_author' => isset($user_id) ? $user_id : '',
                            'post_parent' => (int) $order_id,
                            'post_excerpt' => '',
                            'post_content' => '',
                            'post_status' => 'publish',
                            'post_title' => '',
                            'post_type' => 'tc_tickets_instances',
                        );

                        $owner_record_id = @wp_insert_post($arg, true);
                        update_post_meta($owner_record_id, 'ticket_code', sanitize_text_field($tc_ticket_code));
                        
                        if($variation_id == 0 || $variation_id == ''){
                            update_post_meta($owner_record_id, 'ticket_type_id', sanitize_text_field($product_id));
                        } else {
                            update_post_meta($owner_record_id, 'ticket_type_id', sanitize_text_field($variation_id));
                        }

                        if($variation_id !== 0 || $variation_id !== ''){
                            $ticket_type = $variation_id;
                        } else {
                            $ticket_type = new TC_Ticket($product_id);

                        }
                        
                        $event_id = get_post_meta($product_id, '_event_name', true);
                        
                        update_post_meta($owner_record_id, 'event_id', $event_id);

                        $owner_record_num++;
                    }
                
            }

         }
         
            
            
            
        }
        
        /**
         * @param type $url
         * @return type
         */
        function modify_ticket_type_admin_url($url) {
            return admin_url('edit.php?post_type=product');
        }

        /**
         * Modify shortcode which is appended automatically on the event post single content page if "Show Tickets Automatically" is checked 
         * 
         * @param string $shortcode
         * @return string
         */
        function modify_event_shortcode($shortcode) {
            return '[tc_wb_event]';
        }

        /**
         * Check if order post type is valid and return wheter the order is found or not based on that
         * 
         * @param boolean $value
         * @param int $order_id
         */
        function modify_order_found($value, $order_id) {
            if (get_post_type($order_id) == 'tc_orders' || get_post_type($order_id) == 'shop_order') {
                $value = true;
            }
            return $value;
        }

        /**
         * Remove unneeded shortcode and add new one
         */
        function replace_shortcodes() {
            remove_shortcode('tc_event');
            remove_shortcode('tc_ticket');

            add_shortcode('tc_wb_event', array(&$this, 'tc_wb_event'));
        }

        /**
         * Shortcode for showing event tickets
         * 
         * @global object $post
         * @param array $atts
         * @return mixed $content event tickets
         */
        function tc_wb_event($atts) {
            global $post;
            ob_start();
            extract(shortcode_atts(array(
                'id' => $post->ID,
                'event_table_class' => 'event_tickets tickera',
                'ticket_type_title' => __('Ticket Type', 'woocommerce-tickera-bridge'),
                'price_title' => __('Price', 'woocommerce-tickera-bridge'),
                'cart_title' => __('Cart', 'woocommerce-tickera-bridge'),
                'wrapper' => ''
                            ), $atts));

            $args = array(
                'post_type' => 'product',
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'meta_query' => array(
                    'relation' => 'AND',
                    array(
                        'key' => '_tc_is_ticket',
                        'compare' => '=',
                        'value' => 'yes'
                    ),
                    array(
                        'key' => '_event_name',
                        'compare' => '=',
                        'value' => $id
                    ),
                )
            );

            $ticket_types = get_posts($args);
            ?>

            <div class="tickera">
                <table class="<?php echo esc_attr($event_table_class); ?>">
                    <tr>
                        <?php do_action('tc_wb_event_col_title_before_ticket_title'); ?>
                        <th><?php echo $ticket_type_title; ?></th>
                        <?php do_action('tc_wb_event_col_title_before_ticket_price'); ?>
                        <th><?php echo $price_title; ?></th>
                        <?php do_action('tc_wb_event_col_title_before_cart_title'); ?>
                        <th><?php echo $cart_title; ?></th>
                    </tr>
                    <?php
                    foreach ($ticket_types as $ticket_type) {
                        $product = wc_get_product($ticket_type->ID);
                        ?>
                        <tr>
                            <?php do_action('tc_wb_event_col_value_before_ticket_type', $ticket_type->ID); ?>
                            <td><?php echo $ticket_type->post_title; ?></td>
                            <?php do_action('tc_wb_event_col_value_before_ticket_price', $ticket_type->ID); ?>
                            <td><?php echo $product->get_price_html(); ?></td>
                            <?php do_action('tc_wb_event_col_value_before_cart_title', $ticket_type->ID); ?>
                            <td><?php echo do_shortcode('[add_to_cart id="' . $ticket_type->ID . '" style="" show_price="false"]'); ?></td>
                        </tr>
                        <?php
                    }
                    ?>
                </table>
            </div><!-- tickera -->

            <?php
            $content = ob_get_clean();

            return $content;
        }

        /**
         * Remove uneeded shortcodes from the Tickera's shortcode builder
         * 
         * @param array $shortcodes
         * @return array $shortcodes modified shortcode list
         */
        function modify_shortcode_builder_list($shortcodes) {

            unset($shortcodes['tc_ticket']);
            unset($shortcodes['tc_event']);
            unset($shortcodes['event_tickets_sold']);
            unset($shortcodes['event_tickets_left']);
            unset($shortcodes['tickets_sold']);
            unset($shortcodes['tickets_left']);
            unset($shortcodes['tc_order_history']);

            $ticket_shortcode['add_to_cart'] = __('Ticket / Add to cart button', 'woocommerce-tickera-bridge');
            $event_shortcode['tc_wb_event'] = __('Table with Event tickets', 'woocommerce-tickera-bridge');

            $shortcodes = array_merge($ticket_shortcode, $event_shortcode, $shortcodes);

            return $shortcodes;
        }

        function modify_seat_charts_get_reserved_seats_post_status($status) {
            $status = apply_filters('tc_seat_charts_get_reserved_seats_order_statuses_woo', array('wc-completed', 'wc-processing', 'wc-pending', 'wc-on-hold'));
            return $status;
        }

        public static function maybe_get_original_wpml_product_id($product_id) {
            global $sitepress;
            if (function_exists('icl_object_id') && is_object($sitepress)) {
                $product_id = icl_object_id($product_id, get_post_type($product_id), true, $sitepress->get_default_language());
            }

            return $product_id;
        }

        /**
         * Allow / Dissalow download of tickets (based on the order status and / or payment gateway)
         * @global array $tc_general_settings
         * @param type $order
         * @return boolean
         */
        function allowed_tickets_download($order) {

            $has_ticket = false;

            if (method_exists($order, 'get_items')) {
                $items = $order->get_items();
                if (empty($items)) {
                    $items = WC()->cart->get_cart();
                }
            } else {
                $items = WC()->cart->get_cart();
            }


            foreach ($items as $item) {
                $product_id = $item['product_id'];

                $is_ticket_meta = get_post_meta(TC_WooCommerce_Bridge::maybe_get_original_wpml_product_id($product_id), '_tc_is_ticket', true);
                $is_ticket = $is_ticket_meta == 'yes' ? true : false;
                if ($is_ticket) {
                    $has_ticket = true;
                }
            }

//var_dump($has_ticket);

            if ($has_ticket) {

                $tc_general_settings = get_option('tc_general_setting', false);

                $tc_woo_allow_cash_on_delivery_processing = isset($tc_general_settings['tc_woo_allow_cash_on_delivery_processing']) ? $tc_general_settings['tc_woo_allow_cash_on_delivery_processing'] : 'no';

                $allowed_tickets_download = true;

                if (is_object($order)) {

                    $order_id = method_exists($order, 'get_id') ? $order->get_id() : $order->id;
                    $order_payment_method = get_post_meta($order_id, '_payment_method', true);

                    if ($order->get_status() == 'processing' || $order->get_status() == 'completed') {
                        $allowed_tickets_download = true;
                        if ($order_payment_method == 'cod') {//if payment gateway is Cash on Delivery
                            if ($tc_woo_allow_cash_on_delivery_processing == 'yes') {//If the processing payment status is allowed
                                $allowed_tickets_download = true; //Allow tickets download and sending
                            } else {
                                if ($order->get_status() == 'completed') {//If the order status is completed allow download anyway
                                    $allowed_tickets_download = true;
                                } else {
                                    $allowed_tickets_download = false; //disallow download if it's processed and $tc_woo_allow_cash_on_delivery_processing is no
                                }
                            }
                        }
                    } else {
                        $allowed_tickets_download = false;
                    }
                }
            } else {
                $allowed_tickets_download = false;
            }

            return $allowed_tickets_download;
        }

        /**
         * Redirect WooCommerce ticket single post to its event / associated event single post
         * 
         * @global array $tc_general_settings
         * @global object $post
         */
        function tc_redirect_ticket_single_to_event() {
            global $post;

            $tc_general_settings = get_option('tc_general_setting', false);
            $tc_woo_redirect_single_post_to_event = isset($tc_general_settings['tc_woo_redirect_single_post_to_event']) ? $tc_general_settings['tc_woo_redirect_single_post_to_event'] : 'no';

            if (is_single()) {

                if ($tc_woo_redirect_single_post_to_event == 'yes') {
                    if (get_post_type($post->ID) == 'product') {

                        $product = wc_get_product($post->ID);
                        $is_ticket_meta = get_post_meta($post->ID, '_tc_is_ticket', true);
                        $is_ticket = $is_ticket_meta == 'yes' ? true : false;

                        if ($is_ticket && !$product->has_child()) {
                            $event_id = get_post_meta($post->ID, '_event_name', true);
                            wp_redirect(get_permalink($event_id));
                            exit;
                        }
                    }
                }
            }
        }

        /**
         * Modify order status title
         * 
         * @param string $status_title
         * @param int $order_id
         * @param string $status
         * @return string $status_title title of the WooCommerce order status
         */
        function modify_order_status_title($status_title, $order_id, $status) {
            $payment_statuses = array(
                'wc-completed' => __('Completed', 'woocommerce-tickera-bridge'),
                'wc-processing' => __('Processing', 'woocommerce-tickera-bridge'),
                'wc-on-hold' => __('On Hold', 'woocommerce-tickera-bridge'),
                'wc-pending' => __('Pending Payment', 'woocommerce-tickera-bridge')
            );

            if (array_key_exists($status, $payment_statuses)) {
                $status_title = $payment_statuses[$status];
            }

            return $status_title;
        }

        /**
         * Modify fields shown for export CSV
         * 
         * @param array $fields
         * @return array $fields modified list of CSV export fields
         */
        function modify_tc_csv_admin_fields($fields) {
            unset($fields['col_discount_code']);
            unset($fields['col_order_total']);
            $fields['col_woo_order_total'] = __('Order Total', 'woocommerce-tickera-bridge');
            return $fields;
        }

        /**
         * Modify Payment Gateway Name (get one from WooCommerce)
         * 
         * @param string $gateway_name
         * @param int $order_id
         * @return string $gateway_name WooCommerce payment method title
         */
        function modify_order_payment_gateway_name($gateway_name, $order_id) {
            if (get_post_type($order_id) == 'shop_order') {
                $gateway_name = get_post_meta($order_id, '_payment_method_title', true);
            }
            return $gateway_name;
        }

        function is_3_0_version($version = '3.0') {

            global $woocommerce;
            if (version_compare($woocommerce->version, $version, ">=")) {
                return true;
            }
            return false;
        }

        /**
         * Modify ticket type title for variations
         * 
         * @param string $title
         * @param int $ticket_type_id
         * @return string
         */
        function modify_checkout_owner_info_ticket_title($title, $ticket_type_id) {
            if (get_post_type($ticket_type_id) == 'product_variation') {
                $variation = wc_get_product($ticket_type_id);
//var_dump($variation);
                $title = $variation->get_title();

                if (!$this->is_3_0_version()) {
                    $variation_data = wc_get_formatted_variation($variation->variation_data, true);
                } else {// different args to reflect WooCommerce 3.0 changes
                    $variation_data = wc_get_formatted_variation($variation, true, false);
                }
                if (isset($variation_data)) {
                    $title .= ' (' . $variation_data . ')';
                }
            }
            return $title;
        }

        /**
         * Modify payment status select box and add WooCommerce payment statuses
         * 
         * @param array $payment_statuses
         * @return array $payment_statuses modified list of WooCommerce order payment statuses
         */
        function modify_csv_payment_statuses($payment_statuses) {
            $payment_statuses = array(
                'any' => __('Any', 'woocommerce-tickera-bridge'),
                'wc-completed' => __('Completed', 'woocommerce-tickera-bridge'),
                'wc-processing' => __('Processing', 'woocommerce-tickera-bridge'),
                'wc-on-hold' => __('On Hold', 'woocommerce-tickera-bridge'),
                'wc-pending' => __('Pending Payment', 'woocommerce-tickera-bridge'),
                'wc-refunded' => __('Refunded', 'woocommerce-tickera-bridge'),
            );

            return $payment_statuses;
        }

        function modify_export_order_number_column_value($order_number, $order_id) {
            if (get_post_type($order_id) == 'shop_order') {
                $order_number = '#' . $order_id;
            }
            return $order_number;
        }

        /**
         * Modify ticket type id
         * 
         * If the product is Variable / Variation, ticket type ID should be product's ID which is parent to the variation in order to get right custom form
         * 
         * @param int $ticket_type_id
         * @return int
         */
        public static function modify_ticket_type_id($ticket_type_id) {
            if (get_post_type($ticket_type_id) == 'product_variation') {
                $ticket_type_id = wp_get_post_parent_id($ticket_type_id);
            }
            return TC_WooCommerce_Bridge::maybe_get_original_wpml_product_id($ticket_type_id);
        }

        /**
         * Modify buyer info fields
         * 
         * Remove standard buyer fields (First Name, Last Name and E-mail) since we already have them in WooCommerce
         * 
         * @param array $fields
         * @return array
         */
        function modify_buyer_info_fields($fields) {
            return array();
        }

        /**
         * Modify order details
         * 
         * Don't show standard Tickera buyer fields, just extra custom fields (priority of 9 is required then)
         * 
         * @global object $post
         * @param array $fields
         * @return array
         */
        function modify_order_fields($fields) {
            global $post;

            if (isset($post) && isset($post->ID) && get_post_type($post->ID) == 'shop_order') {
                $fields = array();
            }
            return $fields;
        }

        /**
         * Show order details buyer custom fields on front-end
         * 
         * @global object $post
         * @param object $order
         */
        function tc_woocommerce_admin_order_data_after_order_details($order) {
            global $post;
            $tc_cart_info = get_post_meta($post->ID, 'tc_cart_info');

            if (count($tc_cart_info) > 0 && !empty($tc_cart_info[0]['buyer_data'])) {//Make sure that we have custom fields to show first
                $order_id = method_exists($order, 'get_id') ? $order->get_id() : $order->id;
                tc_get_order_details_buyer_custom_fields($order_id);
            }
        }

        /**
         * Indicator to show custom fields on the WooCommerce order page in the admin
         * 
         * @param boolean $value
         * @return boolean
         */
        function modify_show_custom_fields_as_order_columns($value) {
            return true;
        }

        /**
         * Remove tickets from shop page if needed (if it's set in Tickera > Settings > WooCommerce > Hide Tickets)
         * 
         * @global array $tc_general_settings
         * @param mixed $query
         */
        function tc_custom_pre_get_posts_query($query) {
            $tc_general_settings = get_option('tc_general_setting', false);

            if (!$query->is_main_query())
                return;
            if (!$query->is_post_type_archive())
                return;

            if (!is_admin() && is_shop()) {

                $tc_woo_hide_products = isset($tc_general_settings['tc_woo_hide_products']) ? $tc_general_settings['tc_woo_hide_products'] : 'no';

                if ($tc_woo_hide_products == 'yes') {
                    $query->set('meta_query', array(
                        array(
                            'key' => '_tc_is_ticket',
                            'value' => 'yes',
                            'compare' => 'NOT EXISTS',
                        ),
                    ));
                }
            }
        }

        /**
         * Validate Tickera fields
         */
        function tc_validate_tickera_fields() {

            if (isset($_POST['tc_cart_required'])) {

                $required_fields_error_count = 0;

                foreach ($_POST as $key => $value) {

                    if ($key !== 'tc_cart_required') {
                        if (in_array($key, $_POST['tc_cart_required'])) {
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
                    wc_add_notice(__('All fields marked with * are required.', 'woocommerce-tickera-bridge'), 'error');
                }
            }
        }

        /**
         * Change the name of the owner form template meta
         * 
         * @param string $meta_name
         * @return string
         */
        function modify_custom_forms_owner_form_template_meta($meta_name) {
            return '_owner_form_template';
        }

        /**
         * Disable Tickera gateways network menu item
         * 
         * @return boolean
         */
        function modify_add_network_admin_menu() {
            return false;
        }

        /**
         * Add additional content to the Woo emails
         * 
         * @global array $tc_general_settings
         * @param object $order
         * @param mixed $sent_to_admin
         * @param mixed $plain_text
         */
        function tc_add_content_email_after_order_table($order, $sent_to_admin, $plain_text) {
            global $tc_general_settings;

            $tc_email_settings = get_option('tc_email_setting', false);

            $processing_woo_order_message_content = '';
            $completed_woo_order_message_content = '';

            $send_processing_woo_order_message = isset($tc_email_settings['send_processing_woo_order_message']) ? $tc_email_settings['send_processing_woo_order_message'] : 'yes';
            $send_completed_woo_order_message = isset($tc_email_settings['send_completed_woo_order_message']) ? $tc_email_settings['send_completed_woo_order_message'] : 'yes';

            $order_id = method_exists($order, 'get_id') ? $order->get_id() : $order->id;
            $tickets_table = tc_get_tickets_table_email($order_id);

            if ($order->has_status('processing') && $this->allowed_tickets_download($order) && !$sent_to_admin) {
                if ($send_processing_woo_order_message == 'yes') {
                    $processing_woo_order_message_content = isset($tc_email_settings['processing_woo_order_message_content']) ? $tc_email_settings['processing_woo_order_message_content'] : '<strong>Tickets:</strong><br />TICKETS_TABLE';
                    $processing_woo_order_message_content = str_replace('TICKETS_TABLE', $tickets_table, $processing_woo_order_message_content);
                    echo '<br />' . isset($processing_woo_order_message_content) ? $processing_woo_order_message_content : '';
                }
            }

            if ($order->has_status('completed') && $this->allowed_tickets_download($order) && !$sent_to_admin) {
                if ($send_completed_woo_order_message == 'yes') {
                    $completed_woo_order_message_content = isset($tc_email_settings['completed_woo_order_message_content']) ? $tc_email_settings['completed_woo_order_message_content'] : '<strong>Tickets:</strong><br />TICKETS_TABLE';
                    $completed_woo_order_message_content = str_replace('TICKETS_TABLE', $tickets_table, $completed_woo_order_message_content);
                    echo '<br />' . isset($completed_woo_order_message_content) ? $completed_woo_order_message_content : '';
                }
            }
        }

        /**
         * Add extra email fields / options
         * 
         * @param array $fields
         * @return array additional settings fields for emails section
         */
        function modify_settings_email_fields($fields) {
            $fields[] = array(
                'field_name' => 'send_processing_woo_order_message',
                'field_title' => __('Append content to the order processing e-mails', 'woocommerce-tickera-bridge'),
                'field_type' => 'function',
                'function' => 'tc_yes_no_email',
                'default_value' => 'yes',
                'tooltip' => __('Whether to append content to the WooCommerce processing e-mails.', 'woocommerce-tickera-bridge'),
                'section' => 'woo_processing_email_content'
            );

            $fields[] = array(
                'field_name' => 'processing_woo_order_message_content',
                'field_title' => __('Message Content', 'woocommerce-tickera-bridge'),
                'field_type' => 'function',
                'function' => 'tc_get_admin_order_message',
                'default_value' => '<strong>Tickets:</strong><br />TICKETS_TABLE',
                'field_description' => __('Content of the message. You can use following placeholder: TICKETS_TABLE. This placeholder shows ordered tickets with the download links.', 'woocommerce-tickera-bridge'),
                'section' => 'woo_processing_email_content',
                'conditional' => array(
                    'field_name' => 'send_processing_woo_order_message',
                    'field_type' => 'radio',
                    'value' => 'no',
                    'action' => 'hide'
                )
            );

            $fields[] = array(
                'field_name' => 'send_completed_woo_order_message',
                'field_title' => __('Append content to the order completed e-mails', 'woocommerce-tickera-bridge'),
                'field_type' => 'function',
                'function' => 'tc_yes_no_email',
                'default_value' => 'yes',
                'tooltip' => __('Whether to append content to the WooCommerce completed e-mails.', 'woocommerce-tickera-bridge'),
                'section' => 'woo_completed_email_content'
            );

            $fields[] = array(
                'field_name' => 'completed_woo_order_message_content',
                'field_title' => __('Message Content', 'woocommerce-tickera-bridge'),
                'field_type' => 'function',
                'function' => 'tc_get_admin_order_message',
                'default_value' => '<strong>Tickets:</strong><br />TICKETS_TABLE',
                'field_description' => __('Content of the message. You can use following placeholder: TICKETS_TABLE. This placeholder shows ordered tickets with the download links.', 'woocommerce-tickera-bridge'),
                'section' => 'woo_completed_email_content',
                'conditional' => array(
                    'field_name' => 'send_completed_woo_order_message',
                    'field_type' => 'radio',
                    'value' => 'no',
                    'action' => 'hide'
                )
            );

            return $fields;
        }

        /**
         * Remove email settings sections and add new ones
         * 
         * @param array $sections
         * @return array
         */
        function modify_settings_email_sections($sections) {
            $array_fields_to_unset = array('client_order_completed_email', 'admin_order_completed_email', 'admin_order_placed_email', 'misc_email', 'client_order_placed_email');
            $index = 0;

            foreach ($sections as $section) {
                if (in_array($section['name'], $array_fields_to_unset)) {
                    unset($sections[$index]);
                }
                $index++;
            }

            $sections[] = array(
                'name' => 'woo_processing_email_content',
                'title' => __('Order Processing E-mail', 'woocommerce-tickera-bridge'),
                'description' => __('E-mail content shown after order details in the WooCommerce order processing e-mail.', 'woocommerce-tickera-bridge'),
            );

            $sections[] = array(
                'name' => 'woo_completed_email_content',
                'title' => __('Order Completed E-mail', 'woocommerce-tickera-bridge'),
                'description' => __('E-mail content shown after order details in the WooCommerce order completed e-mail.', 'woocommerce-tickera-bridge'),
            );

            return $sections;
        }

        /**
         * Remove miscellaneous fields from Tickera general settings
         * 
         * @param array $fields
         * @return array
         */
        function modify_settings_miscellaneous_fields($fields) {
            $array_fields_to_unset = array('use_order_details_pretty_links', 'delete_pending_orders');
            $index = 0;
            foreach ($fields as $field) {
                if (in_array($field['field_name'], $array_fields_to_unset)) {
                    unset($fields[$index]);
                }
                $index++;
            }
            return $fields;
        }

        /**
         * Remove unneeded general settings sections
         * 
         * @param array $sections
         * @return array
         */
        function modify_settings_general_sections($sections) {

            $sections[] = array(
                'name' => 'tc_woo_settings',
                'title' => __('WooCommerce', 'woocommerce-tickera-bridge'),
                'description' => '',
            );

            unset($sections[1]); //Remove Pages section from the general settings
            unset($sections[2]); //Remove Menu section from the general settings
            return $sections;
        }

        /**
         * Remove Tickera General Settings fields not needed for WooCommerce and add new ones
         * 
         * @param array $fields
         * @return array
         */
        function modify_general_settings_store_fields($fields) {
            $array_fields_to_unset = apply_filters('woobridge_hidden_fields', array('currencies', 'currency_symbol', 'currency_position', 'price_format', 'tax_rate', 'tax_inclusive', 'show_tax_rate', 'tax_label', 'use_global_fees', 'global_fee_type', 'global_fee_scope', 'global_fee_value', 'show_fees', 'fees_label', 'force_login', 'show_discount_field'));
            $index = 0;

            $fields[] = array(
                'field_name' => 'tc_woo_hide_products',
                'field_title' => __('Hide Tickets', 'woocommerce-tickera-bridge'),
                'field_type' => 'function',
                'function' => 'tc_yes_no',
                'default_value' => 'no',
                'tooltip' => __('Hide WooCommerce products / tickets from the Store page on the front-end.', 'woocommerce-tickera-bridge'),
                'section' => 'tc_woo_settings'
            );

            $fields[] = array(
                'field_name' => 'tc_woo_redirect_single_post_to_event',
                'field_title' => __('Redirect product single post to an event', 'woocommerce-tickera-bridge'),
                'field_type' => 'function',
                'function' => 'tc_yes_no',
                'default_value' => 'no',
                'tooltip' => sprintf(__('Whether to redirect ticket product single posts to associated event post. %sNOTE: Variable products won\'t be redirected.%s', 'woocommerce-tickera-bridge'), '<strong>', '</strong>'),
                'section' => 'tc_woo_settings'
            );

            $fields[] = array(
                'field_name' => 'tc_woo_allow_cash_on_delivery_processing',
                'field_title' => __('Cash on Delivery Ticket Download', 'woocommerce-tickera-bridge'),
                'field_type' => 'function',
                'function' => 'tc_yes_no',
                'default_value' => 'no',
                'tooltip' => sprintf(__('Allow ticket download for processing order status for "Cash on Delivery" gateway.', 'woocommerce-tickera-bridge'), '<strong>', '</strong>'),
                'section' => 'tc_woo_settings'
            );

            foreach ($fields as $field) {
                if (in_array($field['field_name'], $array_fields_to_unset)) {
                    unset($fields[$index]);
                }
                $index++;
            }
            return $fields;
        }

        /**
         * Modify event ticket types
         * 
         * @param object $ticket_types
         * @param int $event_id
         * @return object
         */
        function modify_get_event_ticket_types($ticket_types, $event_id, $show_variations, $post_status = array('publish', 'private')) {

            $args = array(
                'post_type' => array('product', 'tc_tickets'),
                'post_status' => $post_status,
                'posts_per_page' => -1,
                'meta_query' => array(
                    'relation' => 'OR',
                    array(
                        'key' => '_event_name',
                        'compare' => '=',
                        'value' => $event_id
                    ),
                    array(
                        'key' => 'event_name',
                        'compare' => '=',
                        'value' => $event_id
                    ),
                ),
                'orderby' => 'menu_order',
                'order' => 'ASC',
            );

            $ticket_types = get_posts(apply_filters('tc_wc_modify_get_event_ticket_types_args', $args));

            $ticket_type_index = 0;

            foreach ($ticket_types as $ticket_type) {
                if (get_post_type($ticket_type->ID) == 'product') {

                    $variations_args = array(
                        'posts_per_page' => -1,
                        'post_type' => 'product_variation',
                        'post_status' => $post_status,
                        'post_parent' => $ticket_type->ID
                    );

                    $ticket_type_variations = get_posts($variations_args);
                    if (count($ticket_type_variations)) {
//Product has variations
                        if (apply_filters('woo_modify_get_event_ticket_types_show_variations', $show_variations) == true) {
                            $ticket_types = array_merge($ticket_types, $ticket_type_variations);
                        }
                    }
                }
                $ticket_type_index++;
            }

            return $ticket_types;
        }

        /**
         * Modify check-in order date
         * 
         * Get the date from WooCommerce order post_date
         * 
         * @param string $date
         * @param int $order_id
         * @return string
         */
        function modify_checkin_order_date($date, $order_id) {
            if (get_post_type($order_id) == 'shop_order') {
                $order = new TC_Order($order_id);
                $date = strtotime($order->details->post_date);
            }
            return $date;
        }

        /**
         * Modify buyer first name 
         * 
         * Get the one associated to Woo order
         * 
         * @param string $buyer_first_name
         * @param int $order_id
         * @return string
         */
        function modify_checkin_buyer_first_name($buyer_first_name, $order_id) {
            if (get_post_type($order_id) == 'shop_order') {
                $buyer_first_name = get_post_meta($order_id, '_billing_first_name', true);
            }
            return $buyer_first_name;
        }

        /**
         * Modify buyer last name 
         * 
         * Get the one associated to Woo order
         * 
         * @param string $buyer_last_name
         * @param int $order_id
         * @return string
         */
        function modify_checkin_buyer_last_name($buyer_last_name, $order_id) {
            if (get_post_type($order_id) == 'shop_order') {
                $buyer_last_name = get_post_meta($order_id, '_billing_last_name', true);
            }
            return $buyer_last_name;
        }

        /**
         * Modify full name 
         * 
         * Get the one associated to Woo order
         * 
         * @param string $buyer_name
         * @param int $order_id
         * @return string
         */
        function modify_checkin_buyer_full_name($buyer_name, $order_id) {
            if (get_post_type($order_id) == 'shop_order') {
                $first_name = get_post_meta($order_id, '_billing_first_name', true);
                $last_name = get_post_meta($order_id, '_billing_last_name', true);
                $buyer_name = $first_name . ' ' . $last_name;
            }
            return $buyer_name;
        }

        /**
         * Modify email 
         * 
         * Get the one associated to Woo order
         * 
         * @param string $buyer_email
         * @param int $order_id
         * @return string
         */
        function modify_checkin_buyer_email($buyer_email, $order_id) {
            if (get_post_type($order_id) == 'shop_order') {
                $buyer_email = get_post_meta($order_id, '_billing_email', true);
            }
            return $buyer_email;
        }

        /**
         * Add "Tickets" meta box to WooCommerce orders where order contains tickets
         * 
         * @global object $post
         */
        function add_meta_boxes() {
            global $post;
            $tc_cart_contents = get_post_meta($post->ID, 'tc_cart_contents');

            if (count($tc_cart_contents) > 0) {
                add_meta_box('tc-order-details-tickets', __('Tickets', 'woocommerce-tickera-bridge'), 'TC_WooCommerce_Bridge::tc_get_order_tickets', 'shop_order', 'normal', 'default');
            }

            if (defined('tc_woo_has_manual_tickets') && tc_woo_has_manual_tickets == true) {
                add_meta_box('tc-order-details-tickets-edit', __('Book Tickets', 'woocommerce-tickera-bridge'), 'TC_WooCommerce_Bridge::tc_get_order_tickets_additional', 'shop_order', 'normal', 'default');
            }
        }

        /**
         * Retrieve ticket details for the current order 
         * 
         * Called from Woo admin Order page
         * 
         * @global object $post
         */
        public static function tc_get_order_tickets() {
            global $post;
//Add inline edit form
            ?>
            <form>
                <input type="hidden" name="hiddenField" />
            </form>

            <script type="text/javascript">
                jQuery(document).ready(function ($) {
                var replaceWith = $('<input name="temp" class="tc_temp_value" type="text" />'),
                        connectWith = $('input[name="hiddenField"]');
                $('td.first_name, td.last_name, td.owner_email').inlineEdit(replaceWith, connectWith);
                });</script>
            <?php
            do_action('tc_wb_get_order_tickets_before', $post->ID);
//Get tickets table / list
            tc_get_order_event('tc_cart_contents', $post->ID);
            do_action('tc_wb_get_order_tickets_after', $post->ID);
        }

        /**
         * Maybe add additional ticket / attendee
         */
        function tc_maybe_add_additional_tickets($order_id, $order) {
            if (isset($_POST['add-ticket-submit']) && isset($_POST['event_new']) && !empty($_POST['event_new']) && isset($_POST['ticket_type_new']) && !empty($_POST['ticket_type_new'])) {//make sure event and ticket type are selected
//Add ticket instance
                $arg = array(
                    'post_author' => get_current_user_id(),
                    'post_parent' => (int) $order_id,
                    'post_excerpt' => '',
                    'post_content' => '',
                    'post_status' => 'publish',
                    'post_title' => '',
                    'post_type' => 'tc_tickets_instances',
                );

                $owner_record_id = @wp_insert_post($arg, true);
                $attendees_count_args = array(
                    'post_parent' => (int) $order_id,
                    'post_status' => 'publish',
                    'post_type' => 'tc_tickets_instances',
                );

                $attendees_count = count(get_posts($attendees_count_args));

                $metas = array();
                if (apply_filters('tc_use_only_digit_order_number', false) == true) {
                    $metas['ticket_code'] = apply_filters('tc_ticket_code', $order_id . '' . $attendees_count, $owner_record_id);
                } else {
                    $metas['ticket_code'] = apply_filters('tc_ticket_code', $order_id . '-' . $attendees_count, $owner_record_id);
                }

                $metas['event_id'] = (int) $_POST['event_new'];
                $metas['ticket_type_id'] = (int) $_POST['ticket_type_new'];
                $metas['first_name'] = $_POST['first_name_new'];
                $metas['last_name'] = $_POST['last_name_new'];
                $metas['owner_email'] = $_POST['owner_email_new'];

//Add custom fields data if needed
                if (isset($_POST['tc_custom_field_owner_data'])) {
                    $custom_forms_data = $_POST['tc_custom_field_owner_data'][0];
                    foreach ($custom_forms_data as $meta_key_custom => $meta_value_custom) {
                        $metas[$meta_key_custom] = $meta_value_custom;
                    }
                }

                foreach ($metas as $meta_name => $mata_value) {
                    update_post_meta($owner_record_id, sanitize_text_field($meta_name), sanitize_text_field($mata_value));
                }

//Add additional WooCommerce product and metas
                $product = wc_get_product((int) $_POST['ticket_type_new']);
                wc_get_order($order_id)->add_product($product, 1);
                wc_get_order($order_id)->calculate_totals();

//Update order
                $tc_cart_info = get_post_meta($post->ID, 'tc_cart_info');

                $buyer_data = array();

                if (count($tc_cart_info) > 0 && !empty($tc_cart_info[0]['buyer_data'])) {
//do nothing, regular order update will change the buyer data
                } else {
//add some default values so we can see buyer fields and edit them via regular order update
                    if (class_exists('TC_Custom_Fields')) {//only add buyer data if custom forms is active
                        $forms = new TC_Forms();
                        $buyer_form = $forms->get_forms('buyer');

                        if (count($buyer_form) >= 1 && (isset($buyer_form[0]) && !is_null($buyer_form[0]))) {

                            $buyer_form = $buyer_form[0];

                            $args = array(
                                'post_type' => 'tc_form_fields',
                                'post_status' => 'publish',
                                'posts_per_page' => -1,
                                'post_parent' => $buyer_form->ID,
                                'meta_key' => 'row',
                                'orderby' => 'meta_value_num',
                                'order' => 'ASC',
                                'no_found_rows' => true,
                                'update_post_term_cache' => false,
                                'update_post_meta_cache' => false,
                                'cache_results' => false,
                                'fields' => array('ID', 'post_parent')
                            );

                            $custom_fields = get_posts($args);

                            if (count($custom_fields) > 0) {
                                foreach ($custom_fields as $custom_field) {
                                    $buyer_data['buyer_data'][get_post_meta($custom_field->ID, 'name', true)] = '';
                                }
                            }
                        }
                    }
                }

                if (!empty($buyer_data)) {
                    update_post_meta($order_id, 'tc_cart_info', $buyer_data);
                }

                $cart_contents = array();

                $order = wc_get_order($order_id);
                if (method_exists($order, 'get_items')) {
                    $items = $order->get_items();

                    foreach ($items as $item => $values) {
                        $is_ticket_meta = get_post_meta(TC_WooCommerce_Bridge::maybe_get_original_wpml_product_id((int) $values['product_id']), '_tc_is_ticket', true);
                        $is_ticket = $is_ticket_meta == 'yes' ? true : false;

                        if ($is_ticket) {
                            $cart_contents[(int) $values['product_id']] = (int) $values['quantity'];
                        }
                    }

                    if (!empty($cart_contents)) {
                        update_post_meta((int) $order_id, 'tc_cart_contents', $cart_contents);
                    }
                }
            }
        }

        public static function get_additional_tickets_tabs() {

            $tickets_content = '';
            ob_start();

            $has_custom_forms = class_exists('TC_Custom_Fields');
            ?>
            <label class="tc_book_tickets_label"><?php _e('Event', 'woocommerce-tickera-bridge'); ?><br />
                <select name="event_new" class="event_new">
                    <option value=""></option>
                    <?php
                    $events = tc_get_events_array();
                    foreach ($events as $event_id => $event_title) {
                        ?>
                        <option value="<?php echo esc_attr($event_id); ?>"><?php echo $event_title; ?></option>
                        <?php
                    }
                    ?>
                </select>
            </label>

            <script type="text/javascript">
                var ticket_types_list = new Array();
                var ticket_types_custom_forms = new Array();
            <?php
            $args = array(
                'post_type' => 'product',
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'meta_query' => array(
                    'relation' => 'AND',
                    array(
                        'key' => '_tc_is_ticket',
                        'compare' => '=',
                        'value' => 'yes'
                    ),
                )
            );

            $ticket_types = get_posts($args);

            foreach ($ticket_types as $ticket_type) {
                $id = $ticket_type->ID;
                $event_id = get_post_meta($id, '_event_name', true);

                $title = $ticket_type->post_title;

                $product = wc_get_product($id);
                ?>

                    if (typeof ticket_types_list['<?php echo $event_id; ?>'] == "undefined") {
                    ticket_types_list['<?php echo $event_id; ?>'] = new Array();
                    }
                <?php
                if ($product->is_type('variable')) {
                    $variations = $product->get_available_variations();
                    foreach ($variations as $variation) {
                        ?>if (typeof ticket_types_custom_forms['<?php echo $variation['variation_id']; ?>'] == "undefined") {
                                        ticket_types_custom_forms['<?php echo $variation['variation_id']; ?>'] = '-1';
                                        }
                                        ticket_types_custom_forms['<?php echo $variation['variation_id']; ?>'] = '<?php echo tc_get_order_details_custom_forms_fields_values_helper($id); ?>';
                                        ticket_types_list['<?php echo $event_id; ?>']['<?php echo $variation['variation_id']; ?>'] = '<?php echo get_the_title($variation['variation_id']); ?>';
                        <?php
                    }
                } else {
                    ?>if (typeof ticket_types_custom_forms['<?php echo $id; ?>'] == "undefined") {
                                ticket_types_custom_forms['<?php echo $id; ?>'] = '-1';
                                }
                                ticket_types_custom_forms['<?php echo $id; ?>'] = '<?php echo tc_get_order_details_custom_forms_fields_values_helper($id); ?>';
                                ticket_types_list['<?php echo $event_id; ?>']['<?php echo $id; ?>'] = '<?php echo $title; ?>';
                    <?php
                }
                ?>

                <?php
            }
            ?></script>

            <label class="tc_book_tickets_label"><?php _e('Ticket Type', 'woocommerce-tickera-bridge'); ?><br />
                <span id="ticket_type_new_holder">
                    <select name="ticket_type_new" id="ticket_type_new"></select>
                </span>
            </label>

            <?php
            $tickets_content = ob_get_clean();

            $seats_content = 'Seats content';

            $tabs = array(
                'tickets' => array(__('Book Tickets', 'woocommerce-tickera-bridge'), $tickets_content),
                    //'seats' => array(__('Book Seats', 'woocommerce-tickera-bridge'), $seats_content),
            );

            return apply_filters('tc_additional_tickets_tabs', $tabs);
        }

        public static function tc_get_order_tickets_additional() {
            $tabs = TC_WooCommerce_Bridge::get_additional_tickets_tabs();
            $tabs_count = count($tabs);
            ?>
            <div id="tc_tickets_additional_tabs" class="<?php echo 'tc_tickets_additional_tabs_' . (int) $tabs_count; ?>">
                <ul>
                    <?php
                    foreach ($tabs as $tab_key => $tab_info) {
                        ?>
                        <li class="tc_tickets_additional_tab_title"><a href="#<?php echo esc_attr($tab_key); ?>"><?php echo $tab_info[0]; ?></a></li>
                        <?php
                    }
                    ?>
                </ul>
                <?php foreach ($tabs as $tab_key => $tab_info) { ?>
                    <div id="<?php echo esc_attr($tab_key); ?>" class="tc_tickets_additional_tab_content">
                        <?php echo $tab_info[1]; ?>
                    </div>
                    <?php
                }
                ?>
            </div>

            <?php
        }

        /**
         * Get Add New Ticket metabox
         */
        public static function tc_get_order_tickets_additional_old() {
            global $pagenow;

            if ((isset($_GET['action']) && $_GET['action'] == 'edit') || in_array($pagenow, array('post-new.php'))) {

//$tc_orders = new TC_Orders();
//$fields = $tc_orders->get_owner_info_fields();
                $has_custom_forms = class_exists('TC_Custom_Fields');
                ?>
                <table class="order-details widefat shadow-table">
                    <tbody>
                        <tr>
                            <th><?php _e('Event', 'woocommerce-tickera-bridge'); ?></th>
                            <th><?php _e('Ticket Type', 'woocommerce-tickera-bridge'); ?></th>
                            <th><?php _e('First Name', 'woocommerce-tickera-bridge'); ?></th>
                            <th><?php _e('Last Name', 'woocommerce-tickera-bridge'); ?></th>
                            <th><?php _e('Attendee E-mail', 'woocommerce-tickera-bridge'); ?></th>
                            <?php if ($has_custom_forms) { ?>
                                <th class="custom_fields_col"><?php _e('Custom Fields', 'woocommerce-tickera-bridge'); ?></th>
                            <?php } ?>
                        </tr>
                        <tr class="alternate">
                            <td class="event_id">
                                <select name="event_new" class="event_new">
                                    <option value=""></option>
                                    <?php
                                    $events = tc_get_events_array();
                                    foreach ($events as $event_id => $event_title) {
                                        ?>
                                        <option value="<?php echo esc_attr($event_id); ?>"><?php echo $event_title; ?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
                            </td>
                            <td class="ticket_type_id">
                                <script type="text/javascript">
                            var ticket_types_list = new Array();
                            var ticket_types_custom_forms = new Array();
                <?php
                $args = array(
                    'post_type' => 'product',
                    'post_status' => 'publish',
                    'posts_per_page' => -1,
                    'meta_query' => array(
                        'relation' => 'AND',
                        array(
                            'key' => '_tc_is_ticket',
                            'compare' => '=',
                            'value' => 'yes'
                        ),
                    )
                );

                $ticket_types = get_posts($args);

                foreach ($ticket_types as $ticket_type) {
                    $id = $ticket_type->ID;
                    $event_id = get_post_meta($id, '_event_name', true);

                    $title = $ticket_type->post_title;

                    $product = wc_get_product($id);
                    ?>

                                if (typeof ticket_types_list['<?php echo $event_id; ?>'] == "undefined") {
                                ticket_types_list['<?php echo $event_id; ?>'] = new Array();
                                }
                    <?php
                    if ($product->is_type('variable')) {
                        $variations = $product->get_available_variations();
                        foreach ($variations as $variation) {
                            ?>if (typeof ticket_types_custom_forms['<?php echo $variation['variation_id']; ?>'] == "undefined") {
                                            ticket_types_custom_forms['<?php echo $variation['variation_id']; ?>'] = '-1';
                                            }
                                            ticket_types_custom_forms['<?php echo $variation['variation_id']; ?>'] = '<?php echo tc_get_order_details_custom_forms_fields_values_helper($id); ?>';
                                            ticket_types_list['<?php echo $event_id; ?>']['<?php echo $variation['variation_id']; ?>'] = '<?php echo get_the_title($variation['variation_id']); ?>';
                            <?php
                        }
                    } else {
                        ?>if (typeof ticket_types_custom_forms['<?php echo $id; ?>'] == "undefined") {
                                    ticket_types_custom_forms['<?php echo $id; ?>'] = '-1';
                                    }
                                    ticket_types_custom_forms['<?php echo $id; ?>'] = '<?php echo tc_get_order_details_custom_forms_fields_values_helper($id); ?>';
                                    ticket_types_list['<?php echo $event_id; ?>']['<?php echo $id; ?>'] = '<?php echo $title; ?>';
                        <?php
                    }
                    ?>

                    <?php
                }
                ?></script>
                                <div id="ticket_type_new_holder">
                                    <select name="ticket_type_new" id="ticket_type_new"></select>
                                </div>

                            </td>
                            <td class="first_name_new">
                                <input type="text" name="first_name_new" class="first_name_new" value="" />
                            </td>
                            <td class="last_name_new">
                                <input type="text" name="last_name_new" class="last_name_new" value="" />
                            </td>
                            <td class="owner_email_new">
                                <input type="text" name="owner_email_new" class="owner_email_new" value="" />
                            </td>
                            <?php if ($has_custom_forms) { ?>
                                <td class="custom_fields">
                                    -
                                </td>
                            <?php } ?>
                        </tr>
                    </tbody>
                </table>
                <?php
                submit_button(__('Add Ticket', 'woocommerce-tickera-bridge'), 'primary', 'add-ticket-submit', false);
                ?>
                <br clear="all" />
                <?php
            }
        }

        /**
         * Modify ticket download URL (Used on the front-end )
         * 
         * @param string $url
         * @param string $order_key
         * @param int $ticket_id
         * @return string
         */
        function tc_modify_download_ticket_url_front($url, $order_key, $ticket_id) {

            $ticket_instance = new TC_Ticket_Instance($ticket_id);
            $order_id = $ticket_instance->details->post_parent;
            $order = new TC_Order($order_id);
            $order_key = $order->details->_tc_paid_date;

            return wp_nonce_url(str_replace(' ', '', trailingslashit(site_url()) . '?download_ticket=' . $ticket_id . '&order_key=' . $order_key), 'download_ticket_' . $ticket_id . '_' . $order_key, 'download_ticket_nonce');
        }

        /**
         * Add tickets table on the Woo order details page
         * 
         * @param object $order
         */
        function tc_add_tickets_table_on_woo_order_details_page($order) {
            if (is_int($order)) {
                $order_id = $order;
            } else {
                $order_id = method_exists($order, 'get_id') ? $order->get_id() : $order->id;
            }

            $wc_order = new WC_Order((int) $order_id);

            if (is_object($wc_order)) {
                if ($this->allowed_tickets_download($order)) {
                    $order_id = method_exists($order, 'get_id') ? $order->get_id() : $order->id;

                    tc_order_details_table_front($order_id);
                }
            }
        }

        /**
         * Shows event shortcode on the events page in the admin
         * 
         * @param type $shortcode
         * @param type $event_id
         */
        function tc_event_shortcode_column_modify($shortcode, $event_id) {
            return '[tc_wb_event id="' . $event_id . '"]';
        }

        /**
         * Modify Tickera admin menu items
         * 
         * @param array $menu_items
         * @return array
         */
        function tc_modify_plugin_admin_menu_items($menu_items) {
            unset($menu_items['ticket_types']);
            unset($menu_items['discount_codes']);
            unset($menu_items['orders']);
            return $menu_items;
        }

        /**
         * Modify if order is paid based on Woo post status
         * 
         * @param boolean $is_paid
         * @param int $post_id
         * @return boolean
         */
        function tc_modify_order_is_paid($is_paid, $post_id) {

            if (get_post_type($post_id) == 'shop_order') {
                $post_status = get_post_status($post_id);

                if ($post_status == 'wc-completed' || $post_status == 'wc-processing') {
                    return true;
                } else {
                    return false;
                }
            }
            return $is_paid;
        }

        function tc_modify_order_paid_statuses($paid_statuses) {
            $paid_statuses = array('wc-completed', 'wc-processing', 'order_paid');
            return $paid_statuses;
        }

        function tc_modify_order_post_type_name($order_post_type) {
            $order_post_type = array('tc_orders', 'shop_order');
            return $order_post_type;
        }

        /**
         * Modify Order admin url if needed
         * 
         * @param string $url
         * @param int $order_id
         * @param string $order_title
         * @return string
         */
        function tc_modify_ticket_instance_order_admin_url($url, $order_id, $order_title) {
            if (get_post_type($order_id) == 'shop_order') {
                if (!current_user_can('edit_shop_orders')) {
                    $url = $order_id;
                } else {
                    $url = '<a href = "' . admin_url('post.php?post=' . (int) $order_id) . '&action=edit">' . esc_attr($order_id) . '</a>';
                }
            } else {
                $url = $order_title;
            }
            return $url;
        }

        /**
         * Modify post meta for getting available checkins number
         * 
         * @return string
         */
        function tc_modify_available_checkins_per_ticket_field_name() {
            return '_available_checkins_per_ticket';
        }

        /**
         * Modify event meta name
         * 
         * @return string
         */
        function tc_modify_event_name_field_name() {
            return '_event_name';
        }

        /**
         * Modify ticket template meta name
         * 
         * @return string
         */
        function tc_modify_ticket_template_field_name() {
            return '_ticket_template';
        }

        /**
         * Enqueue front-end styles and scripts
         */
        function front_header() {
            if (apply_filters('tc_woo_use_front_header', true) == true) {
                wp_enqueue_style($this->name . '-front', $this->plugin_url . 'css/front.css', array(), $this->version);
            }
        }

        /**
         * Enqueue admin styles and scripts
         */
        function admin_header() {
            global $post_type;
            if (isset($post_type) && ($post_type == 'product' || $post_type == 'shop_order' || $post_type == 'tc_events')) {
                wp_enqueue_script($this->name . '-admin', $this->plugin_url . 'js/admin.js', array('jquery', 'jquery-ui-core', 'jquery-ui-sortable', 'jquery-ui-draggable', 'jquery-ui-droppable', 'jquery-ui-accordion', 'wp-color-picker'), $this->version, false);
                wp_enqueue_style($this->name . '-admin', $this->plugin_url . 'css/admin.css', array(), $this->version);
            }

            if (defined('tc_woo_has_manual_tickets') && tc_woo_has_manual_tickets == true) {
                if (isset($post_type) && $post_type == 'shop_order') {
                    wp_enqueue_script($this->name . '-admin-ticket', $this->plugin_url . 'js/admin-ticket.js', array('jquery', 'jquery-ui-tabs'), $this->version, false);
                    wp_localize_script($this->name . '-admin-ticket', 'tc_woo_ajax', array(
                        'ajaxUrl' => admin_url('admin-ajax.php', (is_ssl() ? 'https' : 'http')),
                    ));
                }
            }
        }

        /**
         * Create new order via WooCommerce API
         * @global mixed $woocommerce
         * @param type $order_id
         * @param type $data
         */
        function tc_api_create_order($order_id, $data) {
            global $woocommerce;

            $tc_tickets_instances_arg = array(
                'post_parent' => $order_id,
                'post_type' => 'tc_tickets_instances',
                'posts_per_page' => -1,
            );

            $tc_tickets_instances = get_posts($tc_tickets_instances_arg);

            foreach ($tc_tickets_instances as $post) { //make sure to delete tickets if exist already (failed and pending orders)
                wp_delete_post($post->ID, true);
            }

            update_post_meta((int) $order_id, '_tc_paid_date', time());

            $owner_data = array();
            $buyer_data = array();

            $cart_info['buyer_data'] = $buyer_data;
            update_post_meta((int) $order_id, 'tc_cart_info', $cart_info);

            $items = $data['line_items'];

            $owner_record_num = 1;

            $cart_contents = array();

            foreach ($items as $item => $values) {

                $is_ticket_meta = get_post_meta(TC_WooCommerce_Bridge::modify_ticket_type_id((int) $values['product_id']), '_tc_is_ticket', true);
                $is_ticket = $is_ticket_meta == 'yes' ? true : false;

                $metas = array();

                if ($is_ticket) {
                    $metas['ticket_type_id'] = $values['product_id']; //CHECK FOR VARIATIONS HERE!!!//////////////////////
                    $cart_contents[(int) $values['product_id']] = (int) $values['quantity'];

                    $qty = (int) $values['quantity'];

                    for ($i = 1; $i <= $qty; $i++) {

                        if (apply_filters('tc_use_only_digit_order_number', false) == true) {
                            $metas['ticket_code'] = apply_filters('tc_ticket_code', $order_id . '' . $owner_record_num);
                        } else {
                            $metas['ticket_code'] = apply_filters('tc_ticket_code', $order_id . '-' . $owner_record_num);
                        }

                        $user_id = get_current_user_id();

                        $arg = array(
                            'post_author' => isset($user_id) ? $user_id : '',
                            'post_parent' => (int) $order_id,
                            'post_excerpt' => '',
                            'post_content' => '',
                            'post_status' => 'publish',
                            'post_title' => '',
                            'post_type' => 'tc_tickets_instances',
                        );

                        $owner_record_id = @wp_insert_post($arg, true);

                        foreach ($metas as $meta_name => $mata_value) {
                            update_post_meta($owner_record_id, sanitize_text_field($meta_name), sanitize_text_field($mata_value));
                        }

                        $ticket_type_id = $metas['ticket_type_id'];
                        $ticket_type = new TC_Ticket($ticket_type_id);
                        $event_id = $ticket_type->get_ticket_event(apply_filters('tc_ticket_type_id', (int) $ticket_type_id));

                        update_post_meta($owner_record_id, 'event_id', $event_id);

                        $owner_record_num++;
                    }
                }
            }

//Save tc_cart_contents meta for the order (used by Tickera)
            $items = WC()->cart->get_cart();

            if (!empty($cart_contents)) {
                update_post_meta((int) $order_id, 'tc_cart_contents', $cart_contents);
            }

            $wc_order = new WC_Order($order_id);

            if ($this->allowed_tickets_download($wc_order, $items)) {
                do_action('tc_wb_allowed_tickets_access', $wc_order);
            }
        }

        /**
         * Create tickets uppon creating a new order if needed
         * 
         * @global mixed $woocommerce
         * @param int $order_id
         */
        function tc_order_created($order_id) {
            global $woocommerce;

            $tc_tickets_instances_arg = array(
                'post_parent' => $order_id,
                'post_type' => 'tc_tickets_instances',
                'posts_per_page' => -1,
            );

            $tc_tickets_instances = get_posts($tc_tickets_instances_arg);

            foreach ($tc_tickets_instances as $post) { //make sure to delete tickets if exist already (failed and pending orders)
                wp_delete_post($post->ID, true);
            }

            update_post_meta((int) $order_id, '_tc_paid_date', time());

            $owner_data = array();
            $buyer_data = array();

            foreach ($_POST as $field => $value) {

                if (preg_match('/buyer_data_/', $field)) {
                    $buyer_data[str_replace('buyer_data_', '', $field)] = $value;
                }

                if (preg_match('/owner_data_/', $field)) {
                    $owner_data[str_replace('owner_data_', '', $field)] = $value;
                }
            }

            if (!empty($owner_data)) {//execute bellow code only if it's related to tickera
// Save buyer extra fields values
                $cart_info['buyer_data'] = $buyer_data;
                update_post_meta((int) $order_id, 'tc_cart_info', $cart_info);

// Save attendee / owner extra fields values
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
                                $title = isset($owner_field_value) ? $owner_field_value : '';
                            }

                            if (preg_match('/_post_excerpt/', $owner_field_name)) {
                                $excerpt = isset($owner_field_value) ? $owner_field_value : '';
                            }

                            if (preg_match('/_post_content/', $owner_field_name)) {
                                $content = isset($owner_field_value) ? $owner_field_value : '';
                            }

                            if (preg_match('/_post_meta/', $owner_field_name)) {
                                $metas[str_replace('_post_meta', '', $owner_field_name)] = $owner_field_value;
                            }
                        }

                        if (apply_filters('tc_use_only_digit_order_number', false) == true) {
                            $metas['ticket_code'] = apply_filters('tc_ticket_code', $order_id . '' . $owner_record_num, $owner_record['ticket_type_id_post_meta']);
                        } else {
                            $metas['ticket_code'] = apply_filters('tc_ticket_code', $order_id . '-' . $owner_record_num, $owner_record['ticket_type_id_post_meta']);
                        }

                        do_action('tc_after_owner_post_field_type_check');

                        $user_id = get_current_user_id();

                        $arg = array(
                            'post_author' => isset($user_id) ? $user_id : '',
                            'post_parent' => (int) $order_id,
                            'post_excerpt' => (isset($excerpt) ? $excerpt : ''),
                            'post_content' => (isset($content) ? $content : ''),
                            'post_status' => 'publish',
                            'post_title' => (isset($title) ? $title : ''),
                            'post_type' => 'tc_tickets_instances',
                        );

                        $owner_record_id = @wp_insert_post($arg, true);

                        foreach ($metas as $meta_name => $mata_value) {
                            update_post_meta($owner_record_id, sanitize_text_field($meta_name), sanitize_text_field($mata_value));
                        }

                        $ticket_type_id = get_post_meta($owner_record_id, 'ticket_type_id', true);
                        $ticket_type = new TC_Ticket($ticket_type_id);
                        $event_id = $ticket_type->get_ticket_event(apply_filters('tc_ticket_type_id', (int) $ticket_type_id));

                        update_post_meta($owner_record_id, 'event_id', $event_id);

                        $owner_record_num++;
                    }

//Save tc_cart_contents meta for the order (used by Tickera)
                    $cart_contents = array();
                    $items = WC()->cart->get_cart();

                    foreach ($items as $item => $values) {
                        $is_ticket_meta = get_post_meta(TC_WooCommerce_Bridge::maybe_get_original_wpml_product_id((int) $values['product_id']), '_tc_is_ticket', true);
                        $is_ticket = $is_ticket_meta == 'yes' ? true : false;

                        if ($is_ticket) {
                            $cart_contents[(int) $values['product_id']] = (int) $values['quantity'];
                        }
                    }

                    if (!empty($cart_contents)) {
                        update_post_meta((int) $order_id, 'tc_cart_contents', $cart_contents);
                    }

                    $wc_order = new WC_Order($order_id);

                    if ($wc_order->get_status() == 'processing' || $wc_order->get_status() == 'completed') {
                        if ($this->allowed_tickets_download($wc_order)) {
                            do_action('tc_wb_allowed_tickets_access', $wc_order);
                        }
                    }
                }
                do_action('tc_woo_bridge_after_order_completed', $wc_order);

            }
        }

        /**
         * Save Tickera custom fields located on the Woo product page
         * 
         * @param int $post_id
         */
        function tc_custom_settings_fields_save($post_id) {

            $post_id = (int) $post_id;
// Check if product is a ticket
            $_tc_is_ticket = isset($_POST['_tc_is_ticket']) ? 'yes' : 'no';

            if ($_tc_is_ticket == 'yes') {

//Save is ticket value
                update_post_meta($post_id, '_tc_is_ticket', $_tc_is_ticket);

// Save related event
                $_event_name = empty($_POST['_event_name']) ? '' : (int) $_POST['_event_name'];
                update_post_meta($post_id, '_event_name', (int) $_event_name);

// Save choosen ticket template
                $_ticket_template = empty($_POST['_ticket_template']) ? '' : (int) $_POST['_ticket_template'];
                update_post_meta($post_id, '_ticket_template', $_ticket_template);

// Save available check-ins
                $_available_checkins_per_ticket = empty($_POST['_available_checkins_per_ticket']) ? '' : (int) $_POST['_available_checkins_per_ticket'];
                update_post_meta($post_id, '_available_checkins_per_ticket', $_available_checkins_per_ticket);

                $_available_checkins_per_ticket = empty($_POST['_available_checkins_per_ticket']) ? '' : (int) $_POST['_available_checkins_per_ticket'];
                update_post_meta($post_id, '_available_checkins_per_ticket', $_available_checkins_per_ticket);

//Save availability dates check-ins
                $_ticket_checkin_availability = empty($_POST['_ticket_checkin_availability']) ? 'open_ended' : $_POST['_ticket_checkin_availability'];
                update_post_meta($post_id, '_ticket_checkin_availability', $_ticket_checkin_availability);


                if ($_ticket_checkin_availability == 'range') {
                    update_post_meta($post_id, '_ticket_checkin_availability_from_date', $_POST['_ticket_checkin_availability_from_date']);
                    update_post_meta($post_id, '_ticket_checkin_availability_to_date', $_POST['_ticket_checkin_availability_to_date']);
                } else {
                    delete_post_meta($post_id, '_ticket_checkin_availability_from_date');
                    delete_post_meta($post_id, '_ticket_checkin_availability_to_date');
                }

                if ($_ticket_checkin_availability == 'time_after_order') {
                    update_post_meta($post_id, '_time_after_order_days', $_POST['_time_after_order_days']);
                    update_post_meta($post_id, '_time_after_order_hours', $_POST['_time_after_order_hours']);
                    update_post_meta($post_id, '_time_after_order_minutes', $_POST['_time_after_order_minutes']);
                } else {
                    delete_post_meta($post_id, '_time_after_order_days');
                    delete_post_meta($post_id, '_time_after_order_hours');
                    delete_post_meta($post_id, '_time_after_order_minutes');
                }

                //save selling availability dates
                $_ticket_selling_availability = empty($_POST['_ticket_availability']) ? 'open_ended' : $_POST['_ticket_availability'];
                update_post_meta($post_id, '_ticket_availability', $_ticket_selling_availability);


                if ($_ticket_selling_availability == 'range') {
                    update_post_meta($post_id, '_ticket_availability_from_date', $_POST['_ticket_availability_from_date']);
                    update_post_meta($post_id, '_ticket_availability_to_date', $_POST['_ticket_availability_to_date']);
                } else {
                    delete_post_meta($post_id, '_ticket_availability_from_date');
                    delete_post_meta($post_id, '_ticket_availability_to_date');
                }

                if (function_exists('tc_custom_form_fields_owner_form_templates_array')) {
                    $_owner_form_template = empty($_POST['_owner_form_template']) ? '' : (int) $_POST['_owner_form_template'];
                    update_post_meta($post_id, '_owner_form_template', $_owner_form_template);
                }
            } else {
                delete_post_meta($post_id, '_tc_is_ticket');
                delete_post_meta($post_id, '_event_name');
                delete_post_meta($post_id, '_available_checkins_per_ticket');
                delete_post_meta($post_id, '_ticket_template');
                delete_post_meta($post_id, '_owner_form_template');

                delete_post_meta($post_id, '_ticket_checkin_availability');
                delete_post_meta($post_id, '_ticket_checkin_availability_from_date');
                delete_post_meta($post_id, '_ticket_checkin_availability_to_date');

                delete_post_meta($post_id, '_ticket_availability');
                delete_post_meta($post_id, '_ticket_availability_from_date');
                delete_post_meta($post_id, '_ticket_availability_to_date');

                delete_post_meta($post_id, '_time_after_order_days');
                delete_post_meta($post_id, '_time_after_order_hours');
                delete_post_meta($post_id, '_time_after_order_minutes');
            }
        }

        function tc_custom_additional_meta_save_variations($variation_id, $i) {
            $_ticket_type_variation_override_checkins = isset($_POST['_ticket_type_variation_override_checkins']) ? $_POST['_ticket_type_variation_override_checkins'] : array();
            update_post_meta($variation_id, '_ticket_type_variation_override_checkins', $_ticket_type_variation_override_checkins[$i]);
        }

        /**
         * Add custom Tickera fields to the admin product screen
         * 
         * @global mixed $woocommerce
         * @global object $post
         */
        function tc_add_custom_settings() {
            global $woocommerce, $post;
            echo '<div class = "options_group show_if_simple show_if_variable hide_if_downloadable">';

            $_ticket_checkin_availability = get_post_meta($post->ID, '_ticket_checkin_availability', true);
            $_ticket_checkin_availability = !isset($_ticket_checkin_availability) || empty($_ticket_checkin_availability) ? 'open_ended' : $_ticket_checkin_availability;

            $_ticket_availability = get_post_meta($post->ID, '_ticket_availability', true);
            $_ticket_availability = !isset($_ticket_availability) || empty($_ticket_availability) ? 'open_ended' : $_ticket_availability;

            woocommerce_wp_checkbox(
                    array(
                        'id' => '_tc_is_ticket',
                        'label' => __('Product is a Ticket', 'woocommerce-tickera-bridge'),
                        'desc_tip' => 'true',
                        'description' => __('Select if this product is an event ticket.', 'woocommerce-tickera-bridge'),
            ));
            echo '</div>';

            echo '<div class="options_group show_if_tc_ticket">';

            do_action('tc_woo_show_if_tc_ticket_before');

            woocommerce_wp_select(
                    array(
                        'id' => '_event_name',
                        'label' => __('Event', 'woocommerce-tickera-bridge'),
                        'desc_tip' => 'true',
                        'description' => __('Select an Event created in Tickera', 'woocommerce-tickera-bridge'),
                        'options' => tc_get_events_array()
                    )
            );

            woocommerce_wp_text_input(
                    array(
                        'id' => '_available_checkins_per_ticket',
                        'label' => __('Check-ins per ticket', 'woocommerce-tickera-bridge'),
                        'placeholder' => __('Unlimited', 'woocommerce-tickera-bridge'),
                        'desc_tip' => 'true',
                        'description' => __('It is useful if the event last more than one day. For instance, if duration of your event is 5 day, you should choose 5 or more for Available Check-ins', 'woocommerce-tickera-bridge'),
                        'type' => 'number'
            ));

            woocommerce_wp_select(
                    array(
                        'id' => '_ticket_template',
                        'label' => __('Ticket Template', 'woocommerce-tickera-bridge'),
                        'options' => tc_get_ticket_templates_array(),
                        'desc_tip' => 'true',
                        'description' => __('Select how ticket will look.', 'woocommerce-tickera-bridge'),
                    )
            );

//Make sure that Tickera's Custom Forms add-on is active
            if (function_exists('tc_custom_form_fields_owner_form_templates_array')) {
                woocommerce_wp_select(
                        array(
                            'id' => '_owner_form_template',
                            'label' => __('Attendee Form', 'woocommerce-tickera-bridge'),
                            'options' => tc_custom_form_fields_owner_form_templates_array(),
                            'desc_tip' => 'true',
                            'description' => __('Attendee form will be shown for each ticket in the cart for selected ticket types / ticket products. You can created multiple different attendee forms.', 'woocommerce-tickera-bridge'),
                        )
                );
            }

            if (method_exists('TC_Ticket', 'is_sales_available')) {

                woocommerce_wp_radio(
                        array(
                            'id' => '_ticket_availability',
                            'label' => __('Available dates for tickets selling', 'woocommerce-tickera-bridge'),
                            'desc_tip' => 'true',
                            'description' => __('Choose if you want to limit ticket sales availability for certain date range or leave it as open-ended.', 'woocommerce-tickera-bridge'),
                            'options' => array(
                                'open_ended' => 'Open-ended',
                                'range' => 'During selected date range',
                            ),
                            'value' => $_ticket_availability
                ));

                echo '<div class="options_group" id="_ticket_availability_dates">';

                woocommerce_wp_text_input(
                        array(
                            'id' => '_ticket_availability_from_date',
                            'class' => 'tc_date_field',
                            'label' => __('Selling allowed From', 'woocommerce-tickera-bridge'),
                ));

                woocommerce_wp_text_input(
                        array(
                            'id' => '_ticket_availability_to_date',
                            'class' => 'tc_date_field',
                            'label' => __('Selling allowed until', 'woocommerce-tickera-bridge'),
                ));

                echo '</div>';

                woocommerce_wp_radio(
                        array(
                            'id' => '_ticket_checkin_availability',
                            'label' => __('Available dates / times for check-in', 'woocommerce-tickera-bridge'),
                            'desc_tip' => 'true',
                            'description' => __('Choose if you want to limit check-ins for certain date range or leave it as open-ended.', 'woocommerce-tickera-bridge'),
                            'options' => array(
                                'open_ended' => 'Open-ended',
                                'range' => 'During selected date range',
                                'time_after_order' => 'Within the following time after order is placed',
                            ),
                            'value' => $_ticket_checkin_availability
                ));

                echo '<div class="options_group" id="_ticket_checkin_availability_dates">';

                woocommerce_wp_text_input(
                        array(
                            'id' => '_ticket_checkin_availability_from_date',
                            'class' => 'tc_date_field',
                            'label' => __('Check-in allowed from', 'woocommerce-tickera-bridge'),
                ));

                woocommerce_wp_text_input(
                        array(
                            'id' => '_ticket_checkin_availability_to_date',
                            'class' => 'tc_date_field',
                            'label' => __('Check-in allowed until', 'woocommerce-tickera-bridge'),
                ));

                echo '</div>';

                echo '<div class="options_group" id="_ticket_checkin_availability_after_order_time">';

                $days_options = array();
                for ($day = apply_filters('tc_ticket_checkin_availability_time_after_order_day_min', 0); $day <= apply_filters('tc_ticket_checkin_availability_time_after_order_day_max', 365); $day++) {
                    $days_options[$day] = $day;
                }
                woocommerce_wp_select(
                        array(
                            'id' => '_time_after_order_days',
                            'label' => __('Days', 'woocommerce-tickera-bridge'),
                            'options' => $days_options,
                            'desc_tip' => 'true',
                            'description' => __('Days after order is placed', 'woocommerce-tickera-bridge'),
                        )
                );

                $hours_options = array();
                for ($hour = apply_filters('tc_ticket_checkin_availability_time_after_order_hour_min', 0); $hour <= apply_filters('tc_ticket_checkin_availability_time_after_order_hour_max', 24); $hour++) {
                    $hours_options[$hour] = $hour;
                }
                woocommerce_wp_select(
                        array(
                            'id' => '_time_after_order_hours',
                            'label' => __('Hours', 'woocommerce-tickera-bridge'),
                            'options' => $hours_options,
                            'desc_tip' => 'true',
                            'description' => __('Hours after order is placed', 'woocommerce-tickera-bridge'),
                        )
                );

                $minutes_options = array();
                for ($minute = apply_filters('tc_ticket_checkin_availability_time_after_order_minute_min', 0); $minute <= apply_filters('tc_ticket_checkin_availability_time_after_order_minute_max', 60); $minute++) {
                    $minutes_options[$minute] = $minute;
                }
                woocommerce_wp_select(
                        array(
                            'id' => '_time_after_order_minutes',
                            'label' => __('Minutes', 'woocommerce-tickera-bridge'),
                            'options' => $minutes_options,
                            'desc_tip' => 'true',
                            'description' => __('Minutes after order is placed', 'woocommerce-tickera-bridge'),
                        )
                );

                echo '</div>';
            }

            do_action('tc_woo_show_if_tc_ticket_after');

            echo '</div>';
        }

        /**
         * Get WooCommerce items from cart and show ticket fields
         * 
         * @global mixed $woocommerce
         * @param array $cart_contents
         * @return array
         */
        function tc_modify_cart_contents($cart_contents) {
            global $woocommerce;
            $cart_contents = array();
            $items = WC()->cart->get_cart();

            foreach ($items as $item => $values) {

                $is_ticket_meta = get_post_meta(TC_WooCommerce_Bridge::maybe_get_original_wpml_product_id((int) $values['product_id']), '_tc_is_ticket', true);
                $is_ticket = $is_ticket_meta == 'yes' ? true : false;

                if ($is_ticket) {

                    $product_id = isset($values['variation_id']) && is_int($values['variation_id']) && $values['variation_id'] > 0 ? (int) $values['variation_id'] : (int) $values['product_id'];

                    if (array_key_exists($product_id, $cart_contents)) {
                        $cart_contents[$product_id] = (int) $cart_contents[$product_id] + (int) $values['quantity'];
                    } else {
                        $cart_contents[$product_id] = (int) $values['quantity'];
                    }
                }
            }

            return $cart_contents;
        }

        /**
         * Add tickera attendee fields to the Woo front-end
         * 
         * @global mixed $woocommerce
         */
        function add_standard_tc_fields_to_checkout() {
            global $woocommerce;

            $cart_contents = array();
            $items = WC()->cart->get_cart();
            $cart_has_tickets = false;

            foreach ($items as $item => $values) {
                $is_ticket_meta = get_post_meta(TC_WooCommerce_Bridge::maybe_get_original_wpml_product_id((int) $values['product_id']), '_tc_is_ticket', true);
                $is_ticket = $is_ticket_meta == 'yes' ? true : false;
                if ($is_ticket) {
                    $cart_has_tickets = true;
                }
            }

            if ($cart_has_tickets) {
                $tc_general_settings = get_option('tc_general_setting', false);

                if (!isset($tc_general_settings['show_owner_fields']) || (isset($tc_general_settings['show_owner_fields']) && $tc_general_settings['show_owner_fields'] == 'yes')) {
                    $show_owner_fields = apply_filters('tc_get_owner_info_fields_front_show', true);
                } else {
                    $show_owner_fields = apply_filters('tc_get_owner_info_fields_front_show', false);
                }
                if ($show_owner_fields) {
//
                }
                echo do_shortcode('[tc_additional_fields]');
            } else {
                echo '<div class="tickera_additional_info"></div>'; //we need this placehoder for one-page checkout
            }
        }

        /**
         * Remove Tickera Payment Gateways TAB
         * 
         * @param array $tabs
         * @return array
         */
        function tc_settings_new_menus($tabs) {
            unset($tabs['gateways']);
            return $tabs;
        }

        /**
         * Initialize plugin variables
         */
        function init_vars() {
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
                wp_die(sprintf(__('There was an issue determining where %s is installed. Please reinstall it.', 'woocommerce-tickera-bridge'), $this->title));
            }
        }

        function load_ticket_template_elements() {

            $dir = $this->plugin_dir . 'ticket-elements/';

            $ticket_template_elements = array();

            if (!is_dir($dir)) {
                return;
            }

            if (!$dh = opendir($dir)) {
                return;
            }

            while (( $plugin = readdir($dh) ) !== false) {
                if (substr($plugin, -4) == '.php') {
                    $ticket_template_elements[] = $dir . '/' . $plugin;
                }
            }

            closedir($dh);

            sort($ticket_template_elements);

            foreach ($ticket_template_elements as $file) {
                include( $file );
            }
        }

        /**
         * Add billing info to CSV Export
         */
        function tc_csv_admin_fields_woo_billing($fields) {
            $fields['col_woo_company_name'] = __('Company Name', 'tc');
            $fields['col_woo_address_1'] = __('Address Line 1', 'tc');
            $fields['col_woo_address_2'] = __('Address Line 2', 'tc');
            $fields['col_woo_city'] = __('City', 'tc');
            $fields['col_woo_state'] = __('State', 'tc');
            $fields['col_woo_postcode'] = __('Postcode', 'tc');
            $fields['col_woo_country'] = __('Country', 'tc');
            $fields['col_woo_phone'] = __('Phone', 'tc');

            return $fields;
        }

        function tc_csv_array_woo_billing($export_array, $order, $instance, $post) {
            global $tc;
            if (isset($post['col_woo_company_name'])) {
                $billing_company = get_post_meta($order->details->ID, '_billing_company', true);
                $new_export_array = array(__('Company Name', 'tc') => $billing_company);
                $export_array = array_merge($export_array, $new_export_array);
            }

            if (isset($post['col_woo_address_1'])) {
                $billing_address_line_1 = get_post_meta($order->details->ID, '_billing_address_1', true);
                $new_export_array = array(__('Address Line 1', 'tc') => $billing_address_line_1);
                $export_array = array_merge($export_array, $new_export_array);
            }

            if (isset($post['col_woo_address_2'])) {
                $billing_address_line_2 = get_post_meta($order->details->ID, '_billing_address_2', true);
                $new_export_array = array(__('Address Line 2', 'tc') => $billing_address_line_2);
                $export_array = array_merge($export_array, $new_export_array);
            }

            if (isset($post['col_woo_city'])) {
                $billing_city = get_post_meta($order->details->ID, '_billing_city', true);
                $new_export_array = array(__('City', 'tc') => $billing_city);
                $export_array = array_merge($export_array, $new_export_array);
            }

            if (isset($post['col_woo_state'])) {
                $billing_state = get_post_meta($order->details->ID, '_billing_state', true);
                $new_export_array = array(__('State', 'tc') => $billing_state);
                $export_array = array_merge($export_array, $new_export_array);
            }

            if (isset($post['col_woo_postcode'])) {
                $billing_postcode = get_post_meta($order->details->ID, '_billing_postcode', true);
                $new_export_array = array(__('Postcode', 'tc') => $billing_postcode);
                $export_array = array_merge($export_array, $new_export_array);
            }

            if (isset($post['col_woo_country'])) {
                $billing_country = get_post_meta($order->details->ID, '_billing_country', true);
                $new_export_array = array(__('Country', 'tc') => $billing_country);
                $export_array = array_merge($export_array, $new_export_array);
            }

            if (isset($post['col_woo_phone'])) {
                $billing_phone = get_post_meta($order->details->ID, '_billing_phone', true);
                $new_export_array = array(__('Phone', 'tc') => $billing_phone);
                $export_array = array_merge($export_array, $new_export_array);
            }

            if (isset($post['col_woo_order_total'])) {
                $wc_order = new WC_Order($order->details->ID);
                $new_export_array = array(__('Order Total', 'tc') => $wc_order->get_total());
                $export_array = array_merge($export_array, $new_export_array);
            }

            return $export_array;
        }

    }

    $tc_woocommerce_bridge = new TC_WooCommerce_Bridge();
}


add_filter('tc_ticket_type_post_type_args', 'tc_woobridge_modify_ticket_type_post_type_args', 21, 1);

/**
 * Overwrite WooCommerce function for "Order Again" - we don't show the button if there is a seat in the order
 */
if (!function_exists('woocommerce_order_again_button')) {

    function woocommerce_order_again_button($order) {

        if (is_int($order)) {
            $order_id = $order;
        } else {
            $order_id = method_exists($order, 'get_id') ? $order->get_id() : $order->id;
        }

        $wc_order = new WC_Order((int) $order_id);

        $args = array(
            'post_type' => 'tc_tickets_instances',
            'post_status' => 'any',
            'post_parent' => $order_id,
            'posts_per_page' => -1,
        );

        $ticket_instances = get_posts($args);

        foreach ($ticket_instances as $ticket_instance) {
            $tc_check_seat = get_post_meta($ticket_instance->ID, 'seat_id', true);
            if (!empty($tc_check_seat)) {
                return;
            }
        }

        wc_get_template('order/order-again.php', array(
            'order' => $order,
        ));
    }

}

/**
 * Remove ticket types from menu when WooBridge is activated
 * @param array $args
 * @return boolean
 */
function tc_woobridge_modify_ticket_type_post_type_args($args) {
    $args['show_ui'] = false;
    return $args; //$args;
}

//Remove dashboard sale stats widget
define('TC_HIDE_STATS_WIDGET', true);

if (function_exists('tc_plugin_updater')) {
    tc_plugin_updater('bridge-for-woocommerce', __FILE__);
}