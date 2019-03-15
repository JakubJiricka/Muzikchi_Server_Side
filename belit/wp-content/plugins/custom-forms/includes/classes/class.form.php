<?php

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

if (!class_exists('TC_Form')) {

    class TC_Form {

        var $id = '';
        var $output = 'OBJECT';
        var $template = array();
        var $details;

        function __construct($id = '', $output = 'OBJECT') {
            $this->id = $id;
            $this->output = $output;
            $this->details = get_post($this->id, $this->output);

            $forms = new TC_Forms();
            $fields = $forms->get_form_col_fields();

            foreach ($fields as $field) {

                if (!isset($this->details->{$field['field_name']})) {
                    $this->details->{$field['field_name']} = get_post_meta($this->id, $field['field_name'], true);
                }
            }
        }

        function TC_Form($id = '', $output = 'OBJECT') {
            $this->__construct($id, $output);
        }

        /* function get_ticket() {
          $ticket = get_post_custom( $this->id, $this->output );
          return $ticket;
          } */

        function delete_form($force_delete = false) {
            if ($force_delete) {
                wp_delete_post($this->id);
            } else {
                wp_trash_post($this->id);
            }
        }

        function get_form_id_by_name($slug) {

            $args = array(
                'name' => $slug,
                'post_type' => 'tc_forms',
                'post_status' => 'any',
                'posts_per_page' => 1,
                'no_found_rows' => true,
                'update_post_term_cache' => false,
                'update_post_meta_cache' => false,
                'cache_results' => false
            );

            $post = get_posts($args);

            if ($post) {
                return $post[0]->ID;
            } else {
                return false;
            }
        }

    }

}
?>