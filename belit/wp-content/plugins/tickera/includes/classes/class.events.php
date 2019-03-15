<?php

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

if (!class_exists('TC_Events')) {

    class TC_Events {

        var $form_title = '';
        var $valid_admin_fields_type = array('text', 'textarea', 'textarea_editor', 'image', 'function');

        function __construct() {
            $this->form_title = __('Events', 'tc');
            $this->valid_admin_fields_type = apply_filters('tc_valid_admin_fields_type', $this->valid_admin_fields_type);
        }

        function TC_Events() {
            $this->__construct();
        }

        public static function get_event_fields() {

            $default_fields = array(
                array(
                    'field_name' => 'post_title',
                    'field_title' => __('Event Name', 'tc'),
                    'field_type' => 'text',
                    'field_description' => '',
                    'table_visibility' => true,
                    'post_field_type' => 'post_title',
                    'show_in_post_type' => false
                ),
                array(
                    'field_name' => 'event_location',
                    'field_title' => __('Event Location', 'tc'),
                    'field_type' => 'text',
                    'tooltip' => sprintf(__('Location of your event. This field could be shown on a %sticket template%s and/or on the event\'s page via shortcode builder (located above the main content editor). Example: Grosvenor Square, Mayfair, London', 'tc'), '<a href="' . admin_url('edit.php?post_type=tc_events&page=tc_ticket_templates') . '" target="_blank">', '</a>'),
                    'table_visibility' => true,
                    'post_field_type' => 'post_meta',
                    'show_in_post_type' => true
                ),
                array(
                    'field_name' => 'event_date_time',
                    'field_title' => __('Start Date & Time', 'tc'),
                    'field_type' => 'text',
                    'tooltip' => __('Start date & time of your event', 'tc'),
                    'table_visibility' => true,
                    'post_field_type' => 'post_meta',
                    'show_in_post_type' => true,
                    'post_type_position' => 'publish_box'
                ),
                array(
                    'field_name' => 'event_end_date_time',
                    'field_title' => __('End Date & Time', 'tc'),
                    'field_type' => 'text',
                    'tooltip' => __('End date of your event.', 'tc'),
                    'table_visibility' => true,
                    'post_field_type' => 'post_meta',
                    'show_in_post_type' => true,
                    'post_type_position' => 'publish_box'
                ),
                array(
                    'field_name' => 'event_terms',
                    'field_title' => __('Terms of Use', 'tc'),
                    'field_type' => 'textarea_editor',
                    'tooltip' => sprintf(__('Terms and Conditions for your event. This field could be shown on a %sticket template%s and/or on the event\'s page via shortcode builder (located above the main content editor). Optional field.', 'tc'), '<a href="' . admin_url('edit.php?post_type=tc_events&page=tc_ticket_templates') . '" target="_blank">', '</a>'),
                    'table_visibility' => false,
                    'post_field_type' => 'post_meta',
                    'show_in_post_type' => true
                ),
                array(
                    'field_name' => 'event_logo',
                    'field_title' => __('Event Logo', 'tc'),
                    'field_type' => 'image',
                    'tooltip' => sprintf(__('Logo of your event. 300 DPI is recommended. This field could be shown on a %sticket template%s and/or on the event\'s page via shortcode builder (located above the main content editor). Optional field.', 'tc'), '<a href="' . admin_url('edit.php?post_type=tc_events&page=tc_ticket_templates') . '" target="_blank">', '</a>'),
                    'table_visibility' => false,
                    'post_field_type' => 'post_meta',
                    'show_in_post_type' => true
                ),
                array(
                    'field_name' => 'sponsors_logo',
                    'field_title' => __('Sponsors Logo', 'tc'),
                    'field_type' => 'image',
                    'tooltip' => sprintf(__('Sponsors logos (one image) which could be shown on the %sticket template%s and/or on the event\'s page via shortcode builder (located above the main content editor). 300 DPI is recommended. Optional field.', 'tc'), '<a href="' . admin_url('edit.php?post_type=tc_events&page=tc_ticket_templates') . '" target="_blank">', '</a>'),
                    'table_visibility' => false,
                    'post_field_type' => 'post_meta',
                    'show_in_post_type' => true
                ),
            );

            return apply_filters('tc_event_fields', $default_fields);
        }

        function get_columns() {
            $fields = $this->get_event_fields();
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
            $fields = $this->get_event_fields();
            $result = search_array($fields, 'field_name', $field_name);
            return $result[0]['post_field_type'];
        }

        function is_valid_event_field_type($field_type) {
            if (in_array($field_type, $this->valid_admin_fields_type)) {
                return true;
            } else {
                return false;
            }
        }

        function add_new_event() {
            global $user_id, $post;

            if (isset($_POST['add_new_event'])) {

                $metas = array();
                $post_field_types = tc_post_fields();

                foreach ($_POST as $field_name => $field_value) {

                    if (preg_match('/_post_title/', $field_name)) {
                        $title = sanitize_text_field($field_value);
                    }

                    if (preg_match('/_post_excerpt/', $field_name)) {
                        $excerpt = sanitize_text_field($field_value);
                    }

                    if (preg_match('/_post_content/', $field_name)) {
                        $content = sanitize_text_field($field_value);
                    }

                    if (preg_match('/_post_meta/', $field_name)) {
                        $metas[sanitize_key(str_replace('_post_meta', '', $field_name))] = sanitize_text_field($field_value);
                    }

                    do_action('tc_after_event_post_field_type_check');
                }

                $metas = apply_filters('events_metas', $metas);

                $arg = array(
                    'post_author' => (int) $user_id,
                    'post_excerpt' => (isset($excerpt) ? $excerpt : ''),
                    'post_content' => (isset($content) ? $content : ''),
                    'post_status' => 'publish',
                    'post_title' => (isset($title) ? $title : ''),
                    'post_type' => 'tc_events',
                );

                if (isset($_POST['post_id'])) {
                    $arg['ID'] = (int) $_POST['post_id']; //for edit 
                }

                $post_id = @wp_insert_post($arg, true);

                //Update post meta
                if ($post_id !== 0) {
                    if (isset($metas)) {
                        foreach ($metas as $key => $value) {
                            update_post_meta($post_id, $key, $value);
                        }
                    }
                }

                return $post_id;
            }
        }

        public static function get_hidden_events_ids() {
            global $wpdb;
            $hidden_events_ids = array();

            $results = $wpdb->get_results(
                    "SELECT $wpdb->posts.ID as ID FROM $wpdb->posts, $wpdb->postmeta "
                    . "WHERE $wpdb->posts.ID = $wpdb->postmeta.post_id "
                    . "AND $wpdb->posts.post_type = 'tc_events' "
                    . "AND ($wpdb->postmeta.meta_key = 'hide_event_after_expiration') "
                    . "AND ($wpdb->postmeta.meta_value = '1') "
                    , ARRAY_A);


            foreach ($results as $maybe_hidden_event_id) {
                $maybe_hidden_event_id = (int)$maybe_hidden_event_id['ID'];
                $event_end_date_time = get_post_meta($maybe_hidden_event_id, 'event_end_date_time', true);
                //var_dump($event_end_date_time);
                if ((date('U', current_time('timestamp', false)) > date('U', strtotime($event_end_date_time)))) {
                    $hidden_events_ids[] = $maybe_hidden_event_id;
                }else{
                    //echo 'CURRENT SERVER: '.(date('U', current_time('timestamp', false))).'<br />';
                    //echo 'EVENT: '.date('U', strtotime($event_end_date_time));
                }
            }
           
            return $hidden_events_ids;
        }

    }

}

//$events = new TC_Events();
?>
