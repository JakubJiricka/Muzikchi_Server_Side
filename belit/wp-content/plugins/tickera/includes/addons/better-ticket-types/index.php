<?php
/*
  Plugin Name: Better Ticket Types
  Plugin URI: http://tickera.com/
  Description: Better ticket types presentaton for Tickera
  Author: Tickera.com
  Author URI: http://tickera.com/
  Version: 1.0
  Copyright 2015 Tickera (http://tickera.com/)
 */

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly


if (!class_exists('TC_Better_Ticket_Types')) {

    class TC_Better_Ticket_Types {

        var $version = '1.0';
        var $title = 'Better Ticket Types';
        var $name = 'better-ticket-types';

        function __construct() {
            global $post;

            if (!isset($post)) {
                $post = isset($_GET['post']) ? $_GET['post'] : '';
            }

            $post_type = get_post_type($post);

            if (empty($post_type)) {
                $post_type = isset($_GET['post_type']) ? $_GET['post_type'] : '';
            }

            add_filter('tc_ticket_type_post_type_args', array(&$this, 'tc_ticket_type_post_type_args'));

            add_filter('manage_tc_tickets_posts_columns', array(&$this, 'manage_tc_tickets_columns'));
            add_action('manage_tc_tickets_posts_custom_column', array(&$this, 'manage_tc_tickets_posts_custom_column'));
            add_filter("manage_edit-tc_tickets_sortable_columns", array(&$this, 'manage_edit_tc_tickets_sortable_columns'));
            add_action('add_meta_boxes', array(&$this, 'add_ticket_types_metaboxes'));
            add_action('admin_enqueue_scripts', array(&$this, 'admin_enqueue_scripts_and_styles'));

            if ($post_type == 'tc_tickets') {
                add_action('post_submitbox_misc_actions', array(&$this, 'post_submitbox_misc_actions'));
                add_filter('page_row_actions', array($this, 'post_row_actions'), 10, 2);
                add_filter('wp_editor_settings', array($this, 'wp_editor_settings'), 10, 2);
                add_action('edit_form_after_editor', array($this, 'edit_form_after_editor'), 10, 1);
                add_filter('enter_title_here', array($this, 'enter_title_here'), 10, 2);
            }

            add_filter('post_updated_messages', array($this, 'post_updated_messages'));
            add_action('save_post', array($this, 'save_metabox_values'));

            if (apply_filters('tc_is_woo', false) == false) {//make sure to duplicate ticket types for standaline version only
                add_action('tc_after_event_duplication', array($this, 'duplicate_event_ticket_types'), 10, 5);
            }
        }

        function duplicate_event_ticket_types($new_event_id, $old_event_id, $caller = 'standard', $caller_id, $old_caller_id) {
            global $wpdb;

            $new_post_author = wp_get_current_user();
            $new_post_date = current_time('mysql');
            $new_post_date_gmt = get_gmt_from_date($new_post_date);

            $old_event = new TC_Event($old_event_id);

            $old_ticket_types = $old_event->get_event_ticket_types(array('publish', 'draft', 'pending', 'private'));

            $old_and_new_ticket_types = array();
            
            foreach ($old_ticket_types as $old_ticket_type_id) {

                $post_id = $old_ticket_type_id;
                $post = get_post($post_id);

                $post_title = $post->post_title;
                $post_status = $post->post_status;
                /*
                 * new post data array
                 */
                $args = apply_filters('tc_duplicate_event_ticket_types_args', array(
                    'post_author' => $new_post_author->ID,
                    'post_date' => $new_post_date,
                    'post_date_gmt' => $new_post_date_gmt,
                    'comment_status' => $post->comment_status,
                    'ping_status' => $post->ping_status,
                    'pinged' => $post->pinged,
                    'to_ping' => $post->to_ping,
                    'post_content' => $post->post_content,
                    'post_content_filtered' => $post->post_content_filtered,
                    'post_excerpt' => $post->post_excerpt,
                    'post_name' => $post->post_name,
                    'post_parent' => $post->post_parent,
                    'post_password' => $post->post_password,
                    'post_status' => $post->post_status,
                    'post_title' => $post->post_title,
                    'post_type' => $post->post_type,
                    'post_modified' => $new_post_date,
                    'post_modified_gmt' => $new_post_date_gmt,
                    'menu_order' => $post->menu_order,
                    'post_mime_type' => $post->post_mime_type,
                        ), $post_id);

                /*
                 * insert the post by wp_insert_post() function
                 */
                $new_post_id = wp_insert_post($args);

                $old_and_new_ticket_types[] = array($old_ticket_type_id, $new_post_id);
                
                $wpdb->update(
                        $wpdb->posts, array(
                    'post_name' => wp_unique_post_slug(sanitize_title($post_title, $new_post_id), $new_post_id, $post_status, $post->post_type, 0),
                    'guid' => get_permalink($new_post_id),
                        ), array(
                    'ID' => $new_post_id
                        )
                );

                /*
                 * get all current post terms ad set them to the new post draft
                 */
                $taxonomies = get_object_taxonomies($post->post_type); // returns array of taxonomy names for post type, ex array("category", "post_tag");

                foreach ($taxonomies as $taxonomy) {
                    $post_terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs'));
                    wp_set_object_terms($new_post_id, $post_terms, $taxonomy, false);
                }

                /*
                 * duplicate all post meta just in two SQL queries
                 */
               $this->duplicate_post_meta($post_id, $new_post_id);

                /*
                 * Replace event ids
                 */

                update_post_meta($new_post_id, apply_filters('tc_event_name_field_name', 'event_name'), $new_event_id);
            }
            
            do_action('tc_after_ticket_type_duplication', $new_event_id, $old_event_id, $caller, $caller_id, $old_caller_id, $old_and_new_ticket_types);
        }
        
        function duplicate_post_meta($id, $new_id) {
            global $wpdb;

            $sql = $wpdb->prepare("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id = %d", absint($id));
            //$exclude = array_map('esc_sql', array('_edit_lock', '_edit_last'));

            /*if (sizeof($exclude)) {
                $sql .= " AND meta_key NOT IN ( '" . implode("','", $exclude) . "' )";
            }*/

            $post_meta = $wpdb->get_results($sql);

            if (sizeof($post_meta)) {
                $sql_query_sel = array();
                $sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";

                foreach ($post_meta as $post_meta_row) {
                    $sql_query_sel[] = $wpdb->prepare("SELECT %d, %s, %s", $new_id, $post_meta_row->meta_key, $post_meta_row->meta_value);
                }

                $sql_query .= implode(" UNION ALL ", $sql_query_sel);
                $wpdb->query($sql_query);
            }
        }

        function post_updated_messages($messages) {

            $post = get_post();
            $post_type = get_post_type($post);
            $post_type_object = get_post_type_object($post_type);

            $messages['tc_tickets'] = array(
                0 => '', // Unused. Messages start at index 1.
                1 => __('Ticket Type updated.'),
                2 => __('Custom field updated.'),
                3 => __('Custom field deleted.'),
                4 => __('Ticket Type updated.'),
                /* translators: %s: date and time of the revision */
                5 => isset($_GET['revision']) ? sprintf(__('Ticket Type restored to revision from %s'), wp_post_revision_title((int) $_GET['revision'], false)) : false,
                6 => __('Ticket Type published.'),
                7 => __('Ticket Type saved.'),
                8 => __('Ticket Type submitted.'),
                9 => sprintf(
                        __('Ticket Type scheduled for: <strong>%1$s</strong>.'), date_i18n(__('M j, Y @ G:i'), strtotime($post->post_date))
                ),
                10 => __('Ticket Type draft updated.')
            );
            return $messages;
        }

        function edit_form_after_editor($post) {
            echo '<span class="description">' . __('Short description for the ticket type which could be shown on the ticket. Feel free to leave it empty if you won\'t use it in the selected <a href="' . admin_url('edit.php?post_type=tc_events&page=tc_ticket_templates') . '" target="_blank">ticket template</a>.', 'tc') . '</span>';
        }

        function wp_editor_settings($settings, $editor_id) {
            $settings['editor_height'] = 20;
            $settings['textarea_rows'] = 5;
            return $settings;
        }

        function enter_title_here($enter_title_here, $post) {
            if (get_post_type($post) == 'tc_tickets') {
                $enter_title_here = __('Enter ticket type title here. VIP, Standard, Early Bird etc', 'tc');
            }
            return $enter_title_here;
        }

        function post_row_actions($actions, $post) {
            unset($actions['view']);
            unset($actions['inline hide-if-no-js']);
            return $actions;
        }

        /*
         * Save post meta values
         */

        function save_metabox_values($post_id) {

            if (get_post_type($post_id) == 'tc_tickets') {

                $metas = array();

                foreach ($_POST as $field_name => $field_value) {
                    if (preg_match('/_post_meta/', $field_name)) {
                        $metas[sanitize_key(str_replace('_post_meta', '', $field_name))] = sanitize_text_field($field_value);
                    }

                    $metas = apply_filters('tc_ticket_type_metas', $metas);

                    if (isset($metas)) {
                        foreach ($metas as $key => $value) {
                            update_post_meta($post_id, $key, $value);
                        }
                    }
                }
            }
        }

        /*
         * Enqueue scripts and styles
         */

        function admin_enqueue_scripts_and_styles() {
            global $post, $post_type;
            if ($post_type == 'tc_tickets') {
                wp_enqueue_style('tc-better-ticket-types', plugins_url('css/admin.css', __FILE__));
            }
        }

        /*
         * Change Events post type arguments
         */

        function tc_ticket_type_post_type_args($args) {
            $args['show_in_menu'] = 'edit.php?post_type=tc_events';
            $args['show_ui'] = true;
            $args['has_archive'] = false;
            $args['public'] = false;

            $args['supports'] = array(
                'title',
                'editor',
            );

            return apply_filters('tc_ticket_type_post_type_args_val', $args);
        }

        /*
         * Add table column titles
         */

        function manage_tc_tickets_columns($columns) {
            $ticket_types_columns = TC_Tickets::get_ticket_fields();
            foreach ($ticket_types_columns as $ticket_types_column) {
                if (isset($ticket_types_column['table_visibility']) && $ticket_types_column['table_visibility'] == true && $ticket_types_column['field_name'] !== 'post_title') {
                    $columns[$ticket_types_column['field_name']] = $ticket_types_column['field_title'];
                }
            }
            unset($columns['date']);
            return $columns;
        }

        /*
         * Add table column values
         */

        function manage_tc_tickets_posts_custom_column($name) {
            global $post, $tc;
            $ticket_types_columns = TC_Tickets::get_ticket_fields();

            foreach ($ticket_types_columns as $ticket_types_column) {
                if (isset($ticket_types_column['table_visibility']) && $ticket_types_column['table_visibility'] == true && $ticket_types_column['field_name'] !== 'post_title') {

                    if ($ticket_types_column['field_name'] == $name) {

                        if (isset($ticket_types_column['post_field_type']) && $ticket_types_column['post_field_type'] == 'post_meta') {
                            $value = get_post_meta($post->ID, $ticket_types_column['field_name'], true);
                            $value = !empty($value) ? $value : '-';

                            if ($ticket_types_column['field_name'] == 'price_per_ticket') {
                                $value = $tc->get_cart_currency_and_format($value);
                            }

                            if ($ticket_types_column['field_name'] == 'event_name') {
                                $event = new TC_Event($value);
                                $value = $event->details->post_title;
                            }

                            if ($ticket_types_column['field_name'] == 'quantity_available') {
                                if (empty($value) || $value == '-') {
                                    $value = __('Unlimited', 'tc');
                                }
                            }

                            if ($ticket_types_column['field_name'] == 'quantity_sold') {
                                global $wpdb;

                                $sold_count = tc_get_tickets_count_sold($post->ID);

                                if ($sold_count > 0) {
                                    $value = $sold_count;
                                } else {
                                    $value = '-';
                                }
                            }

                            if ($ticket_types_column['field_name'] == 'available_checkins_per_ticket') {
                                if (empty($value) || $value == '-') {
                                    $value = __('Unlimited', 'tc');
                                }
                            }

                            if ($ticket_types_column['field_name'] == 'ticket_fee') {
                                $ticket_fee_type = get_post_meta($post->ID, 'ticket_fee_type', true);
                                if (!empty($value) && $value !== '0' && $value !== 0 && $value !== '-') {
                                    if ($ticket_fee_type == 'fixed') {
                                        $value = $tc->get_cart_currency_and_format($value);
                                    } else {
                                        $value = $value . '%';
                                    }
                                } else {
                                    $value = '-';
                                }
                            }


                            /* if ( $ticket_types_column[ 'field_name' ] == 'ticket_fee_type' ) {
                              $value = ucfirst( $value );
                              } */


                            echo $value;
                        } else if ($ticket_types_column['field_name'] == 'ticket_active') {
                            $ticket_type_status = get_post_status($post->ID);
                            $on = $ticket_type_status == 'publish' ? 'tc-on' : '';
                            echo '<div class="tc-control ' . $on . '" ticket_id="' . esc_attr($post->ID) . '"><div class="tc-toggle"></div></div>';
                        } elseif ($ticket_types_column['field_name'] == 'ticket_shortcode') {
                            echo '[tc_ticket id="' . $post->ID . '"]';
                        } else {
//unknown column
                        }
                    }
                }
            }
        }

        function manage_edit_tc_tickets_sortable_columns($columns) {
            $custom = array(
                    /* 'quantity_available' => 'quantity_available',
                      'quantity_sold'		 => 'quantity_sold', */
            );
            return wp_parse_args($custom, $columns);
        }

        /*
         * Add control for setting an event as active or inactive
         */

        function post_submitbox_misc_actions() {
            global $post, $post_type;

            $ticket_type_columns = TC_Tickets::get_ticket_fields();

            foreach ($ticket_type_columns as $ticket_type_column) {
                if (isset($ticket_type_column['show_in_post_type']) && $ticket_type_column['show_in_post_type'] == true && isset($ticket_type_column['post_type_position']) && $ticket_type_column['post_type_position'] == 'publish_box') {
                    ?>
                    <div class="misc-pub-section <?php echo esc_attr($ticket_type_column['field_name']); ?>">
                        <?php
                        TC_Fields::render_post_type_field('TC_Ticket', $ticket_type_column, $post->ID, false);
                        ?>
                    </div>
                    <?php
                }
            }

            $ticket_type_status = get_post_status($post->ID);
            $on = $ticket_type_status == 'publish' ? 'tc-on' : '';
            ?>
            <div class="misc-pub-section misc-pub-visibility-activity" id="visibility">
                <?php
                if (current_user_can(apply_filters('tc_ticket_type_activation_capability', 'edit_others_ticket_types')) || current_user_can('manage_options')) {
                    ?>
                    <span id="post-visibility-display"><?php echo '<div class="tc-control ' . $on . '" ticket_id="' . esc_attr($post->ID) . '"><div class="tc-toggle"></div></div>'; ?></span>
                    <?php
                }
                if (isset($_GET['post'])) {
                    $ticket = new TC_Ticket((int) $_GET['post']);
                    $template_id = $ticket->details->ticket_template;
                    ?>
                    <a class="ticket_preview_link" target="_blank" href="<?php echo esc_attr(apply_filters('tc_ticket_preview_link', admin_url('edit.php?post_type=tc_events&page=tc_ticket_templates&action=preview&ticket_type_id=' . (int) $_GET['post']) . '&template_id=' . $template_id)); ?>"><?php _e('Preview', 'tc'); ?></a>
                <?php } ?>
            </div>
            <?php
        }

        function non_visible_fields() {
            $fields = array(
                'ID',
                'ticket_type_name',
                'quantity_sold',
                'ticket_active',
                'ticket_shortcode'
            );
            return $fields;
        }

        function add_ticket_types_metaboxes() {
            global $pagenow, $typenow, $post;

            if (('edit.php' == $pagenow) || ($post->post_type !== 'tc_tickets')) {
                return;
            }

            $post_id = isset($_GET['post']) ? (int) $_GET['post'] : 0;

            $ticket_types_columns = TC_Tickets::get_ticket_fields();

            foreach ($ticket_types_columns as $ticket_types_column) {
                if (!in_array($ticket_types_column['field_name'], $this->non_visible_fields())) {
                    eval("if(!function_exists('" . $ticket_types_column['field_name'] . "_metabox')){function " . $ticket_types_column['field_name'] . "_metabox() {
						tc_render_ticket_type_metabox(" . $post_id . ", '" . $ticket_types_column['field_name'] . "');
						}}");
                    add_meta_box($ticket_types_column['field_name'] . '-tc-metabox-wrapper', $ticket_types_column['field_title'] . (isset($ticket_types_column['tooltip']) ? tc_tooltip($ticket_types_column['tooltip'], false) : ''), $ticket_types_column['field_name'] . '_metabox', 'tc_tickets', isset($ticket_types_column['metabox_context']) ? $ticket_types_column['metabox_context'] : 'normal' );
                }
            }
        }

    }

    global $better_ticket_types;
    $better_ticket_types = new TC_Better_Ticket_Types();
}

function tc_render_ticket_type_metabox($post_id, $field_name) {
    $ticket_types_columns = TC_Tickets::get_ticket_fields();

    foreach ($ticket_types_columns as $ticket_types_column) {
        if ($ticket_types_column['field_name'] == $field_name) {
            TC_Fields::render_post_type_field('TC_Ticket', $ticket_types_column, $post_id, false);
        }
    }
}
?>
