<?php

class tc_textarea_field_form_element extends TC_Form_Elements {

    var $element_name = 'tc_textarea_field_form_element';
    var $element_title = 'Textarea';
    var $element_type = 'textarea';

    function on_creation() {
        $this->element_title = apply_filters('tc_input_field_form_element_title', __('Textarea', 'tc'));
    }

    function save($form_id) {
        $element_data = $_POST[$this->element_name];
        $elements_count = (count($element_data['field_name']) - 1);

        for ($i = 0; $i <= $elements_count; $i++) {
            if ((isset($element_data['field_label'][$i]) && !empty($element_data['field_label'][$i])) || (isset($element_data['field_name'][$i]) && !empty($element_data['field_name'][$i]))) {
                $post_data = array(
                    'post_status' => 'publish',
                    'post_parent' => $form_id,
                    'post_title' => isset($element_data['field_label'][$i]) ? $element_data['field_label'][$i] : '',
                    'post_name' => isset($element_data['field_name'][$i]) ? $element_data['field_name'][$i] : sanitize_key($element_data['field_label'][$i]),
                    'post_content' => isset($element_data['field_values'][$i]) ? $element_data['field_values'][$i] : '',
                    'post_type' => $this->post_type
                );

                if (isset($element_data['field_post_id'][$i]) && is_numeric($element_data['field_post_id'][$i])) {
                    $post_data['ID'] = $element_data['field_post_id'][$i];
                }

                $post_id = wp_insert_post($post_data);

                $post_metas = array(
                    'placeholder' => isset($element_data['field_placeholder'][$i]) ? $element_data['field_placeholder'][$i] : '',
                    'description' => isset($element_data['field_description'][$i]) ? $element_data['field_description'][$i] : '',
                    'default_value' => isset($element_data['field_default_values'][$i]) ? $element_data['field_default_values'][$i] : '',
                    'required' => isset($element_data['field_required'][$i]) ? $element_data['field_required'][$i] : '',
                    'export' => isset($element_data['field_export'][$i]) ? $element_data['field_export'][$i] : '',
                    'show_in_checkin_app' => isset($element_data['field_show_in_checkin_app'][$i]) ? $element_data['field_show_in_checkin_app'][$i] : '',
                    'as_ticket_template' => isset($element_data['field_as_ticket_template'][$i]) ? $element_data['field_as_ticket_template'][$i] : '',
                    'order_details' => isset($element_data['field_order_details'][$i]) ? $element_data['field_order_details'][$i] : '',
                    'order_column' => isset($element_data['field_order_column'][$i]) ? $element_data['field_order_column'][$i] : '',
                    'order' => isset($element_data['field_order'][$i]) ? $element_data['field_order'][$i] : '',
                    'row' => isset($element_data['field_row'][$i]) ? $element_data['field_row'][$i] : '',
                    'field_type' => $this->element_name,
                );

                $post_metas = apply_filters('tc_custom_forms_custom_field_metas', $post_metas, $element_data, $i, $form_id, $this, $this->element_name);

                //Make sure that we set field name only once so we don't loose data upon changing field label
                $name = get_post_meta($post_id, 'name', true);
                if (!isset($name) || (isset($name) && empty($name))) {
                    $post_metas['name'] = sanitize_key(isset($element_data['field_label'][$i]) ? str_replace('-', '_', $element_data['field_label'][$i]) . '_tcfn_' . rand(1, 9999) : 'tc_field_name_' . rand(1, 9999));
                    $post_metas['name'] = $this->maybe_fix_form_element_name($post_metas['name']);
                }

                foreach ($post_metas as $meta_key => $meta_value) {
                    update_post_meta($post_id, $meta_key, $meta_value);
                }
            }
        }
    }

    function admin_order_details_page_value() {
        $value = array(
            'id' => $this->standard_field_name($this->element_name, true),
            'field_name' => 'tc_cart_info',
            'field_title' => $this->standard_field_label($this->element_name, true),
            'field_type' => 'function',
            'function' => 'tc_get_textarea_admin_order_details_page_value',
            'field_description' => '',
            'table_visibility' => false,
            'post_field_type' => 'post_meta'
        );

        return isset($value) && !empty($value) ? $value : '-';
    }

    function admin_content() {
        $this->standard_fields($this->element_name);
        $this->common_fields($this->element_name);
        do_action('tc_custom_forms_admin_content', $this->element_name, $this);
    }

}

tc_register_form_element('tc_textarea_field_form_element', __('Textarea', 'tc'));
