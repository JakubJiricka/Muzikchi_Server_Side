<?php
/*
  Shortcodes
 */

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

if (class_exists('TC')) {

    class TC_Seat_Shortcodes extends TC {

        function __construct() {
            add_shortcode('tc_seat_chart', array(&$this, 'tc_seat_chart'));
            add_action('wp_ajax_tc_load_seating_map', array(&$this, 'load_seating_map'));
            add_action('wp_ajax_nopriv_tc_load_seating_map', array(&$this, 'load_seating_map'));
        }

        /**
         * Enqueues required scripts and styles for shortcode
         * @global type $TC_Seat_Chart
         */
        function enqueues($post_id = false) {
            global $TC_Seat_Chart, $post;

            if (!$post_id) {
                $post_id = $post->ID;
            }

            $tc_seat_charts_settings = TC_Seat_Chart::get_settings();

            $use_firebase_integration = isset($tc_seat_charts_settings['user_firebase_integration']) ? $tc_seat_charts_settings['user_firebase_integration'] : '0';
            
            if ($use_firebase_integration == '1') {
                wp_enqueue_script('tc-server-date', plugins_url('../js/ServerDate.js', __FILE__));
                wp_enqueue_script('tc-firebase', 'https://www.gstatic.com/firebasejs/3.2.1/firebase.js');
                wp_enqueue_script('tc-seat-charts-firebase', plugins_url('../js/tc-firebase.js', __FILE__), array('jquery', 'tc-firebase'), $TC_Seat_Chart->version, false);

                wp_localize_script('tc-seat-charts-firebase', 'tc_firebase_vars', array(
                    'apiKey' => isset($tc_seat_charts_settings['apiKey']) ? $tc_seat_charts_settings['apiKey'] : '',
                    'authDomain' => isset($tc_seat_charts_settings['authDomain']) ? $tc_seat_charts_settings['authDomain'] : '',
                    'databaseURL' => isset($tc_seat_charts_settings['databaseURL']) ? $tc_seat_charts_settings['databaseURL'] : '',
                    'session_id' => session_id(),
                    'tc_reserved_seat_color' => isset($tc_seat_charts_settings['reserved_seat_color']) ? $tc_seat_charts_settings['reserved_seat_color'] : '#DCCBCB',
                    'tc_in_cart_seat_color' => isset($tc_seat_charts_settings['in_cart_seat_color']) ? $tc_seat_charts_settings['in_cart_seat_color'] : '#4187C9',
                    'tc_in_others_cart_seat_color' => isset($tc_seat_charts_settings['in_others_cart_seat_color']) ? $tc_seat_charts_settings['in_others_cart_seat_color'] : '#ec1244',
                ));
            }

            if (apply_filters('tc_is_woo', false) == true) {
                wp_enqueue_script('tc-seats-front', plugins_url('../assets/js/front/front-woo.js', __FILE__), array('jquery', 'jquery-ui-selectable'), $this->version, true);
            } else {
                wp_enqueue_script('tc-seats-front', plugins_url('../assets/js/front/front.js', __FILE__), array('jquery', 'jquery-ui-selectable'), $this->version, true);
            }

            wp_localize_script('tc-seats-front', 'tc_seat_chart_ajax', array(
                'ajaxUrl' => admin_url('admin-ajax.php', (is_ssl() ? 'https' : 'http')),
                'tc_reserved_seat_color' => isset($tc_seat_charts_settings['reserved_seat_color']) ? $tc_seat_charts_settings['reserved_seat_color'] : '#DCCBCB',
                'tc_in_cart_seat_color' => isset($tc_seat_charts_settings['in_cart_seat_color']) ? $tc_seat_charts_settings['in_cart_seat_color'] : '#4187C9',
                'tc_in_others_cart_seat_color' => isset($tc_seat_charts_settings['in_others_cart_seat_color']) ? $tc_seat_charts_settings['in_others_cart_seat_color'] : '#ec1244',
                'tc_unavailable_seat_color' => isset($tc_seat_charts_settings['unavailable_seat_color']) ? $tc_seat_charts_settings['unavailable_seat_color'] : '#aaaaaa',
                'tc_add_to_cart_button_title' => __('Add to Cart', 'tcsc'),
                'tc_remove_from_cart_button_title' => __('Remove from Cart', 'tcsc'),
                'tc_cancel_button_title' => __('Cancel', 'tcsc'),
                'tc_adding_to_cart_title' => __('Adding to cart, please wait...', 'tcsc'),
                'tc_removing_from_cart_title' => __('Removing from cart, please wait...', 'tcsc'),
                'tc_loading_options_message' => __('Loading options, please wait...', 'tcsc'),
                'tc_minimum_tickets_message' => __('Minimum required number of tickets for  ', 'tcsc'),
                'tc_maximum_tickets_message' => __('Maximum number of tickets for  ', 'tcsc'),
                'tc_minimum_tickets_message_is' => __(' is ', 'tcsc'),
                'tc_minimum_tickets_title' => __('Minimum Tickets', 'tcsc'),
                'tc_check_firebase' => isset($use_firebase_integration) ? $use_firebase_integration : '',
            ));

            wp_enqueue_style('tc-seat-charts-jquery-ui', plugins_url('../assets/js/jquery-ui/jquery-ui.css', __FILE__));
            wp_enqueue_style('tc-seat-charts-front', plugins_url('../assets/style-front.css', __FILE__));
            wp_enqueue_style('tc-seat-font-awesome', plugins_url('../assets/font-awesome/css/font-awesome.min.css', __FILE__));

            wp_enqueue_script('jquery-pan', plugins_url('../js/jquery.pan.js', __FILE__), array('jquery'), $this->version, true);

            wp_enqueue_script('tc-unslider', plugins_url('../assets/js/unslider/src/js/unslider.js', __FILE__), false, $this->version, true);

            wp_enqueue_script('tc-seats-common-front', plugins_url('../assets/js/front/common.js', __FILE__), array('jquery', 'jquery-ui-selectable', 'jquery-ui-draggable', 'jquery-pan', 'jquery-ui-dialog', 'tc-seats-front'), $this->version, true);
            wp_localize_script('tc-seats-common-front', 'tc_common_vars', array(
                'ajaxUrl' => admin_url('admin-ajax.php', (is_ssl() ? 'https' : 'http')),
                'front_zoom_level' => get_post_meta($post_id, 'tc_admin_zoom_level', true),
                'seat_translation' => __('Seat', 'tcsc')
            ));

            wp_enqueue_style('tc-seat-charts-jquery-ui', plugins_url('../assets/js/jquery-ui/jquery-ui.css', __FILE__));

            wp_enqueue_style('jquery-ui-rotatable', plugins_url('../assets/jquery.ui.rotatable.css', __FILE__));

            wp_enqueue_script('tc-seats-controls-front', plugins_url('../assets/js/front/controls.js', __FILE__), array('jquery', 'jquery-ui-slider'), $this->version, true);
            wp_localize_script('tc-seats-controls-front', 'tc_controls_vars', array(
                'ajaxUrl' => admin_url('admin-ajax.php', (is_ssl() ? 'https' : 'http')),
                'front_zoom_level' => get_post_meta($post_id, 'tc_admin_zoom_level', true)
            ));
            

            wp_enqueue_script('jquery-ui-selectable');

            wp_enqueue_script('tc-touch-punch', plugins_url('../assets/js/front/jquery.ui.touch-punch.min.js', __FILE__), '', $this->version, true);
            
            wp_enqueue_script('tc-seats-tooltips-admin', plugins_url('../assets/js/front/tooltips.js', __FILE__), array('tc-seats-common-front'), $this->version, true);
            wp_localize_script('tc-seats-tooltips-admin', 'tc_seatings_tooltips', array(
                'pan_wrapper' => __('Left click and drag to pan (or use arrow keys). Use mouse wheel to zoom', 'tcsc'),
                'selectable' => __('Click to remove or add it to the cart.', 'tcsc'),
            ));

            wp_enqueue_script('wc-add-to-cart-variation');
        }

        /**
         * Shortcode which shows a seat map
         * @global type $TC_Seat_Chart
         * @global type $tc
         * @global type $post
         * @param type $atts
         */
        function tc_seat_chart($atts) {
            global $TC_Seat_Chart, $tc, $post;

            extract(shortcode_atts(array(
                'id' => false,
                'show_legend' => 'true',
                'button_title' => __('Pick your seat(s)'),
                'subtotal_title' => __('Subtotal', 'tcsc'),
                'cart_title' => __('Go to Cart', 'tcsc')
                            ), $atts));

            if ($id == false) {
                if (get_post_type($post->ID) == 'tc_seat_charts') {
                    $id = $post->ID;
                }
            }

            ob_start();

            if ($id == false) {
                echo __('Seating chart is not selected.', 'tcsc');
            } else {
                $tc_seat_charts_settings = TC_Seat_Chart::get_settings();

                $reserved_seat_color = isset($tc_seat_charts_settings['reserved_seat_color']) ? $tc_seat_charts_settings['reserved_seat_color'] : '#DCCBCB';
                $in_cart_seat_color = isset($tc_seat_charts_settings['in_cart_seat_color']) ? $tc_seat_charts_settings['in_cart_seat_color'] : '#4187C9';
                $in_others_cart_seat_color = isset($tc_seat_charts_settings['in_others_cart_seat_color']) ? $tc_seat_charts_settings['in_others_cart_seat_color'] : '#ec1244';
                ?>
                <style>.tc_seating_map{display: none;}</style>

                <button class="tc_seating_map_button" data-seating-map-id="<?php echo esc_attr($id); ?>" data-show_legend="<?php echo esc_attr($show_legend); ?>" data-button_title="<?php echo esc_attr($button_title); ?>" data-subtotal_title="<?php echo esc_attr($subtotal_title); ?>" data-cart_title="<?php echo esc_attr($cart_title); ?>"><?php echo $button_title; ?></button>

                <?php
                $this->footer_content($id);
            }
            $this->enqueues($id);
            $content = ob_get_clean();
            return $content;
        }

        function footer_content($id) {
            if (!function_exists('tc_chart_footer_' . $id)) {
                eval("function tc_chart_footer_$id(\$id) {echo '<div class=\"tc_seating_map tc_seating_map_" . $id . "\" data-seating-chart-id=\"" . $id . "\"></div>';}");
            }
            add_action('wp_footer', 'tc_chart_footer_' . $id, 99);
        }

        function load_seating_map() {
            global $TC_Seat_Chart, $tc, $post;

            $show_legend = $_POST['show_legend'];
            $button_title = $_POST['button_title'];
            $subtotal_title = $_POST['subtotal_title'];
            $cart_title = $_POST['cart_title'];
            $id = (int) $_POST['chart_id'];

            $tc_seat_charts_settings = TC_Seat_Chart::get_settings();

            $reserved_seat_color = isset($tc_seat_charts_settings['reserved_seat_color']) ? $tc_seat_charts_settings['reserved_seat_color'] : '#DCCBCB';
            $in_cart_seat_color = isset($tc_seat_charts_settings['in_cart_seat_color']) ? $tc_seat_charts_settings['in_cart_seat_color'] : '#4187C9';
            $unavailable_seat_color = isset($tc_seat_charts_settings['unavailable_seat_color']) ? $tc_seat_charts_settings['unavailable_seat_color'] : '#aaaaaa';
            $in_others_cart_seat_color = isset($tc_seat_charts_settings['in_others_cart_seat_color']) ? $tc_seat_charts_settings['in_others_cart_seat_color'] : '#ec1244';
            ?>
            <div class="tc-full-screen"><i class="fa fa-times"></i></div>

            <?php
            TC_Seat_Chart::get_reserved_seats($id);
            TC_Seat_Chart::get_in_cart_seats($id);

            if ($show_legend == 'false') {
                $show_legend_visibility = ' style="display:none;"';
            } else {
                $show_legend_visibility = '';
            }

            $chart_ticket_types = TC_Seat_Chart::get_unique_ticket_types($id);
            ?>
            <div class="tc-seating-legend-wrap" <?php echo $show_legend_visibility; ?>>

                <span class="tc-legend-arrow tc-legend-close">
                    <i class="fa fa-caret-left" aria-hidden="true"></i>
                </span>

                <div class="tc-seating-legend">
                    <ul>
                        <?php
                        $global_is_sales_available = true;
                        foreach ($chart_ticket_types as $ticket_type_id) {
                            $ticket_type = new TC_Ticket($ticket_type_id);
                            $is_sales_available = true;

                            if (is_plugin_active('bridge-for-woocommerce/bridge-for-woocommerce.php') && is_plugin_active('woocommerce/woocommerce.php') && is_plugin_active('min-max-quantities-for-woocommerce-master/index.php')) {
                                $tc_minimum_tickets_per_order = get_post_meta($ticket_type_id, 'minimum_allowed_quantity', true);
                                $tc_maximum_tickets_per_order = get_post_meta($ticket_type_id, 'maximum_allowed_quantity', true);
                            } else {
                                $tc_minimum_tickets_per_order = get_post_meta($ticket_type_id, 'min_tickets_per_order', true);
                                $tc_maximum_tickets_per_order = get_post_meta($ticket_type_id, 'max_tickets_per_order', true);
                            }

                            $is_sales_available = TC_Ticket::is_sales_available($ticket_type_id);
                            if ($is_sales_available == false) {
                                $global_is_sales_available = false;
                            }

                            if (empty($tc_minimum_tickets_per_order)) {
                                $tc_minimum_tickets_per_order = 0;
                            }
                            $seat_color_default = apply_filters('tc_seat_color_default', '#6b5f89');
                            $seat_color = $ticket_type->details->_seat_color;

                            if (empty($seat_color)) {
                                $seat_color = $seat_color_default;
                            }

                            $is_variable = TC_Seat_Chart::is_woo_variable_product($ticket_type_id) ? 1 : 0;
                            $qty_left = TC_Seat_Chart::qty_left($ticket_type_id);

                            $price = apply_filters('tc_seat_chart_shortcode_price', apply_filters('tc_cart_currency_and_format', tc_get_ticket_price($ticket_type_id)), $ticket_type_id, false, true);
                            $title = apply_filters('tc_checkout_owner_info_ticket_title', $ticket_type->details->post_title, $ticket_type->details->ID);
                            ?>
                            <li class="tt_<?php echo esc_attr($ticket_type_id); ?> tc-ticket-listing" data-is-sales-available="<?php echo esc_attr($is_sales_available); ?>" data-ticket-type-id="<?php echo esc_attr($ticket_type_id); ?>" data-min-tickets-per-order="<?php echo $tc_minimum_tickets_per_order; ?>" data-max-tickets-per-order="<?php echo $tc_maximum_tickets_per_order; ?>" data-qty-left="<?php echo esc_attr($qty_left); ?>" data-is-variable="<?php echo esc_attr($is_variable); ?>" data-tt-price="<?php echo esc_attr($price); ?>" data-tt-title="<?php echo esc_attr($title); ?>" style="color:<?php echo esc_attr($seat_color); ?>"><span style="background-color:<?php echo esc_attr($seat_color); ?>"></span><?php echo $price; ?> - <?php echo $title; ?></li>
                        <?php } ?>
                        <li class="tc_reserved_seat_color_status" style="color:<?php echo esc_attr($reserved_seat_color); ?>"><span style="background-color:<?php echo esc_attr($reserved_seat_color); ?>"></span><?php _e('Reserved', 'tcsc'); ?></li>
                        <li class="tc_in_cart_seat_color_status" style="color:<?php echo esc_attr($in_cart_seat_color); ?>"><span style="background-color:<?php echo esc_attr($in_cart_seat_color); ?>"></span><?php _e('In Cart', 'tcsc'); ?></li>
                        <?php
                        if ($global_is_sales_available == false) {
                            ?>
                            <li class = "tc_unavailable_seat_color_status" style = "color:<?php echo esc_attr($unavailable_seat_color); ?>"><span style = "background-color:<?php echo esc_attr($unavailable_seat_color); ?>"></span><?php _e('Unavailable', 'tcsc');
                            ?></li>
                            <?php
                        }
                        $use_firebase_integration = isset($tc_seat_charts_settings['user_firebase_integration']) ? $tc_seat_charts_settings['user_firebase_integration'] : '0';

                        if ($use_firebase_integration == '1') {
                            ?>
                            <li class="tc_in_others_cart_seat_color_status" style="color:<?php echo esc_attr($in_others_cart_seat_color); ?>"><span style="background-color:<?php echo esc_attr($in_others_cart_seat_color); ?>"></span><?php _e('In Other\'s Cart', 'tcsc'); ?></li>                
                        <?php } ?>
                    </ul>

                </div><!-- .tc-seating-legend -->

            </div><!-- .tc-seating-legend-wrap -->

            <?php
            $is_woo_active = is_plugin_active('woocommerce/woocommerce.php');
            $is_bridge_active = is_plugin_active('bridge-for-woocommerce/bridge-for-woocommerce.php');
            $tc_current_screen_width = get_post_meta($id, 'tc_current_screen_width', true);
            ?>
            <div class="tc-wrapper <?php
            if (($is_woo_active == true) && ($is_bridge_active == true)) {
                echo 'tc-woo-active';
            }
            ?>" data-csw="<?php echo esc_attr($tc_current_screen_width); ?>">
                     <?php
                     $tc_chart_content = TC_Seat_Chart::get_seating_chart_html($id, true);
                     echo $tc_chart_content;
                     ?>

                <div class="tc-bottom-controls">

                    <div class="tc-bottom-controls-inside">

                        <div class="tc-zoom-wrap" style="left: 175px;">

                            <div class="tc-minus-wrap">
                                <div class="tc-minus"></div>
                            </div>

                            <div class="tc-zoom-slider ui-slider ui-slider-horizontal ui-widget ui-widget-content ui-corner-all">
                                <span class="ui-slider-handle ui-state-default ui-corner-all" tabindex="0" style="left: 16.6667%;"></span>
                            </div>

                            <div class="tc-plus-wrap">
                                <div class="tc-plus-vertical"></div>
                                <div class="tc-plus-horizontal"></div>
                            </div>

                        </div><!-- .tc-zoom-wrap -->


                        <div class="tc-seating-tooltips">
                            <p><?php _e('Left click and drag to pan. Click on available seats for booking.', 'tcsc'); ?></p>
                        </div>


                        <div class="tc-seatchart-cart-info">
                            <?php
                            TC_Seat_Chart::show_seat_chart_front($id);

                            $cart_contents = $tc->get_cart_cookie();
                            $cart_subtotal = 0;
                            $in_cart_count = 0;

                            foreach ($cart_contents as $ticket_type => $ordered_count) {
                                $ticket = new TC_Ticket($ticket_type);
                                $cart_subtotal = $cart_subtotal + (tc_get_ticket_price($ticket->details->ID) * $ordered_count);
                                if ($ordered_count > 0) {
                                    $in_cart_count++;
                                }
                            }

                            $in_cart_count = apply_filters('tc_seat_chart_in_cart_count', $in_cart_count);
                            ?>
                            <input type="hidden" class="tc-seatchart-in-cart-count" value="<?php echo esc_attr($in_cart_count); ?>" />
                            <a class="tc-checkout-button" href="<?php echo apply_filters('tc_seat_chart_add_to_cart_url', esc_attr($tc->get_cart_page(true))); ?>"><?php echo $cart_title; ?></a>

                            <div class="tc-checkout-bar">
                                <p class="tc-seatchart-subtotal"><?php echo $subtotal_title . ':'; ?> <strong><?php echo apply_filters('tc_seat_chart_cart_subtotal', apply_filters('tc_cart_currency_and_format', isset($cart_subtotal) ? $cart_subtotal : 0 )); ?></strong></p>
                            </div><!-- .tc-checkout -->
                        </div>

                    </div><!-- .tc-bottom-controls-inside -->

                    <div id="tc-regular-modal" class="tc-modal-wrap" style="display: none;">

                        <div class="tc-modal">
                            <input type="hidden" class="tc_regular_modal_seating_chart_id" value="">
                            <input type="hidden" class="tc_regular_modal_ticket_type_id" value="">
                            <input type="hidden" class="tc_regular_modal_seat_id" value="">

                            <button type="button" class="tc_modal_close_dialog"><i class="fa fa-times"></i></button>

                            <h5><span class="tc_regular_modal_ticket_type"></span> - <span class="tc_regular_modal_seat_label"></span></h5>
                            <h5 class="tc_regular_price_modal_holder"><?php _e('Price:', 'tcsc'); ?> <span class="tc_regular_modal_price">$0</span></h5>

                            <button type="button" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only tc_cart_button" role="button"><?php _e('Add to Cart', 'tcsc'); ?></button>
                        </div><!-- .tc-modal -->

                    </div>


                    <div id="tc-modal-added-to-cart" class="tc-modal-wrap" style="display: none;">

                        <div class="tc-modal tc-added-to-cart" >

                            <input type="hidden" class="tc_regular_modal_seating_chart_id" value="">
                            <input type="hidden" class="tc_regular_modal_ticket_type_id" value="">
                            <input type="hidden" class="tc_regular_modal_seat_id" value="">

                            <button type="button" class="tc_modal_close_dialog"><i class="fa fa-times"></i></button>

                            <h5><span class="tc_regular_modal_ticket_type"></span> - <span class="tc_regular_modal_seat_label"></span></h5>
                            <h5><?php _e('Price:', 'tcsc'); ?> <span class="tc_regular_modal_price">$0</span></h5>

                            <button type="button" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only tc_remove_from_cart_button" role="button"><?php _e('Remove from Cart', 'tcsc'); ?></button>
                        </div><!-- .tc-modal -->

                    </div><!-- #tc-modal-added-to-cart -->


                    <div id="tc-modal-woobridge" class="tc-modal-wrap" style="display: none;">

                        <div class="tc-modal-woobridge">
                            <button type="button" class="tc_modal_close_dialog"><i class="fa fa-times"></i></button>
                            <input type="hidden" class="tc_regular_modal_seating_chart_id" value="">
                            <input type="hidden" class="tc_regular_modal_ticket_type_id" value="">
                            <input type="hidden" class="tc_regular_modal_seat_id" value="">

                            <h5><span class="tc_regular_modal_ticket_type"></span> - <span class="tc_regular_modal_seat_label"></span></h5>
                            <h5 class="tc_regular_price_modal_holder"><?php _e('Price:', 'tcsc'); ?> <span class="tc_regular_modal_price">$0</span></h5>

                            <div class="tc-modal-woobridge-inner">

                            </div>
                            <!--<button type="button" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only tc_cart_button single_add_to_cart_button disabled wc-variation-selection-needed" role="button"><?php _e('Add to Cart', 'tcsc'); ?></button>-->


                        </div><!-- .tc-modal -->

                    </div><!-- #tc-modal-added-to-cart -->

                </div><!-- .tc-bottom-controls -->
            </div><!-- .tc-wrapper -->

            <div id="tc-ticket-requirements"></div>

            <?php
            TC_Seat_Chart::set_event_ticket_types_colors($id, true);
             
            if ($use_firebase_integration == '1' ) {
                ?>
                <script type="text/javascript">
                    tc_firebase.init(<?php echo (int) $id; ?>);
                </script>
                <?php
            }
            exit;
        }

    }

    $tc_seat_shortcodes = new TC_Seat_Shortcodes();
}
?>