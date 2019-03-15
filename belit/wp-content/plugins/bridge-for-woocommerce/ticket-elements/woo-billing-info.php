<?php

if (class_exists('TC_Ticket_Template_Elements')) {

    class tc_woo_billing_info_element extends TC_Ticket_Template_Elements {

        var $element_name = 'tc_woo_billing_info_element';
        var $element_title = 'WooCommerce Billing Info';
        var $font_awesome_icon = '<i class="fa fa-credit-card"></i>';

        function on_creation() {
            $this->element_title = apply_filters('tc_woo_billing_info_element', __('WooCommerce Billing Info', 'tc'));
        }

        function ticket_content($ticket_instance_id = false, $ticket_type_id = false) {

            $ticket_instance = new TC_Ticket_Instance((int) $ticket_instance_id);
            $order_id = (int) $ticket_instance->details->post_parent;

            $billing_first_name = get_post_meta($order_id, '_billing_first_name', true);
            $billing_last_name = get_post_meta($order_id, '_billing_last_name', true);
            $billing_company = get_post_meta($order_id, '_billing_company', true);
            $billing_address_1 = get_post_meta($order_id, '_billing_address_1', true);
            $billing_address_2 = get_post_meta($order_id, '_billing_address_2', true);
            $billing_city = get_post_meta($order_id, '_billing_city', true);
            $billing_state = get_post_meta($order_id, '_billing_state', true);
            $billing_postcode = get_post_meta($order_id, '_billing_postcode', true);
            $billing_country = get_post_meta($order_id, '_billing_country', true);
            $billing_email = get_post_meta($order_id, '_billing_email', true);
            $billing_phone = get_post_meta($order_id, '_billing_phone', true);

            $billing_info = '';
            $billing_info .= isset($billing_first_name) ? $billing_first_name . '<br />' : '';
            $billing_info .= isset($billing_last_name) ? $billing_last_name . '<br />' : '';
            $billing_info .= isset($billing_company) ? $billing_company . '<br />' : '';
            $billing_info .= isset($billing_address_1) ? $billing_address_1 . '<br />' : '';
            $billing_info .= isset($billing_address_2) ? $billing_address_2 . '<br />' : '';
            $billing_info .= isset($billing_city) ? $billing_city . '<br />' : '';
            $billing_info .= isset($billing_state) ? $billing_state . '<br />' : '';
            $billing_info .= isset($billing_postcode) ? $billing_postcode . '<br />' : '';
            $billing_info .= isset($billing_country) ? $billing_country . '<br />' : '';
            $billing_info .= isset($billing_email) ? $billing_email . '<br />' : '';
            $billing_info .= isset($billing_phone) ? $billing_phone . '<br />' : '';

            return $billing_info;
        }

    }

    tc_register_template_element('tc_woo_billing_info_element', __('WooCommerce Billing Info', 'tc'));
}

