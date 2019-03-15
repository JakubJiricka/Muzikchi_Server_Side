<?php

if (class_exists('TC_Ticket_Template_Elements')) {

    class tc_woo_shipping_info_element extends TC_Ticket_Template_Elements {

        var $element_name = 'tc_woo_shipping_info_element';
        var $element_title = 'WooCommerce Shipping Info';
        var $font_awesome_icon = '<i class="fa fa-paper-plane"></i>';

        function on_creation() {
            $this->element_title = apply_filters('tc_woo_shipping_info_element', __('WooCommerce Shipping Info', 'tc'));
        }

        function ticket_content($ticket_instance_id = false, $ticket_type_id = false) {

            $ticket_instance = new TC_Ticket_Instance((int) $ticket_instance_id);
            $order_id = $ticket_instance->details->post_parent; //$order->details->ID;

            $shipping_first_name = get_post_meta($order_id, '_shipping_first_name', true);
            $shipping_last_name = get_post_meta($order_id, '_shipping_last_name', true);
            $shipping_company = get_post_meta($order_id, '_shipping_company', true);
            $shipping_address_1 = get_post_meta($order_id, '_shipping_address_1', true);
            $shipping_address_2 = get_post_meta($order_id, '_shipping_address_2', true);
            $shipping_city = get_post_meta($order_id, '_shipping_city', true);
            $shipping_state = get_post_meta($order_id, '_shipping_state', true);
            $shipping_postcode = get_post_meta($order_id, '_shipping_postcode', true);
            $shipping_country = get_post_meta($order_id, '_shipping_country', true);

            $shipping_info = '';
            $shipping_info .= isset($shipping_first_name) ? $shipping_first_name . '<br />' : '';
            $shipping_info .= isset($shipping_last_name) ? $shipping_last_name . '<br />' : '';
            $shipping_info .= isset($shipping_company) ? $company . '<br />' : '';
            $shipping_info .= isset($shipping_address_1) ? $shipping_address_1 . '<br />' : '';
            $shipping_info .= isset($shipping_address_2) ? $shipping_address_2 . '<br />' : '';
            $shipping_info .= isset($shipping_city) ? $shipping_city . '<br />' : '';
            $shipping_info .= isset($shipping_state) ? $shipping_state . '<br />' : '';
            $shipping_info .= isset($shipping_postcode) ? $shipping_postcode . '<br />' : '';
            $shipping_info .= isset($shipping_country) ? $shipping_country . '<br />' : '';

            return $shipping_info;
        }

    }

    tc_register_template_element('tc_woo_shipping_info_element', __('WooCommerce Shipping Info', 'tc'));
}

