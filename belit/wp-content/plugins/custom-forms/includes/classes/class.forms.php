<?php

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

if (!class_exists('TC_Forms')) {

    class TC_Forms {

        var $form_title = '';
        var $valid_admin_fields_type = array('text', 'textarea', 'checkbox', 'function');

        function __construct() {
            $this->valid_admin_fields_type = apply_filters('tc_valid_admin_fields_type', $this->valid_admin_fields_type);
        }

        function TC_Forms() {
            $this->__construct();
        }

        function add_new_form() {
            global $wpdb, $tc_form_elements;

            if (isset($_POST['form_title'])) {

                if (isset($_POST['fields_to_remove'])) {
                    foreach ($_POST['fields_to_remove'] as $field_to_remove) {
                        wp_delete_post($field_to_remove, true);
                    }
                }

                $post = array(
                    'post_content' => '',
                    'post_status' => 'publish',
                    'post_title' => $_POST['form_title'],
                    'post_type' => 'tc_forms',
                );

                $post = apply_filters('tc_forms_post', $post);

                if (isset($_POST['form_id'])) {
                    $post['ID'] = $_POST['form_id']; //If ID is set, wp_insert_post will do the UPDATE instead of insert
                }

                $post_id = wp_insert_post($post);

                //Update post meta
                if ($post_id != 0) {

                    foreach ($_POST as $key => $value) {
                        if (preg_match("/_post_meta/i", $key)) {//every field name with sufix "_post_meta" will be saved as post meta automatically
                            update_post_meta($post_id, str_replace('_post_meta', '', str_replace('-', '_', $key)), $value);
                            do_action('tc_form_post_metas');
                        }
                    }
                }

                foreach ($tc_form_elements as $form_element) {
                    $class_name = $form_element[0];
                    if (class_exists($class_name)) {
                        $form_element = new $class_name;
                        $form_element->save($post_id);
                    }
                }

                return $post_id;
            }
        }

        function get_forms($type = 'buyer', $limit = -1, $ticket_type_id = '') {

            if ($ticket_type_id !== '') {
                $form_id = get_post_meta(apply_filters('tc_ticket_type_id', $ticket_type_id), apply_filters('tc_custom_forms_owner_form_template_meta', 'owner_form_template'), true);
                $forms[0] = get_post($form_id);
            } else {
                $args = array(
                    'posts_per_page' => $limit,
                    'orderby' => 'post_date',
                    'order' => 'DESC',
                    'post_type' => 'tc_forms',
                    'post_status' => 'publish',
                    'suppress_filters' => true,
                    'no_found_rows' => true,
                    'update_post_term_cache' => false,
                    'update_post_meta_cache' => false,
                    'cache_results' => false
                );

                if ($type == 'buyer' || $type == 'owner') {
                    $args['meta_key'] = 'form_type';
                    $args['meta_value'] = $type;
                }
                if (defined('TEVOLUTION_VERSION')) {//fix for the Tevolution
                    $forms = query_posts($args);
                    wp_reset_query();
                } else {
                    $forms = get_posts($args);
                }
            }

            return $forms;
        }

        function get_form_col_fields() {

            $default_fields = array(
                array(
                    'field_name' => 'post_title',
                    'field_title' => __('Form Title', 'tc'),
                    'field_type' => 'text',
                    'field_description' => '',
                    'post_field_type' => 'post_title',
                    'table_visibility' => true,
                ),
                array(
                    'field_name' => 'post_date',
                    'field_title' => __('Date', 'tc'),
                    'field_type' => 'text',
                    'field_description' => '',
                    'post_field_type' => 'post_date',
                    'table_visibility' => true,
                ),
                array(
                    'field_name' => 'form_type',
                    'field_title' => __('Form Type', 'tc'),
                    'field_type' => 'text',
                    'field_description' => '',
                    'post_field_type' => 'form_type',
                    'table_visibility' => true,
                ),
            );

            return apply_filters('tc_form_col_fields', $default_fields);
        }

        function get_columns() {
            $fields = $this->get_form_col_fields();
            $results = search_array($fields, 'table_visibility', true);

            $columns = array();

            $columns['ID'] = __('ID', 'tc');

            foreach ($results as $result) {
                $columns[$result['field_name']] = $result['field_title'];
            }

            $columns['edit'] = __('Edit', 'tc');
            $columns['delete'] = __('Delete', 'tc');

            return $columns;
        }

        function check_field_property($field_name, $property) {
            $fields = $this->get_form_col_fields();
            $result = search_array($fields, 'field_name', $field_name);
            return $result[0]['post_field_type'];
        }

        function is_valid_form_col_field_type($field_type) {
            if (in_array($field_type, $this->valid_admin_fields_type)) {
                return true;
            } else {
                return false;
            }
        }

    }

}
?>
