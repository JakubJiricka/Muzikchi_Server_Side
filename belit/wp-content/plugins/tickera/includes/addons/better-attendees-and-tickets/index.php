<?php
/*
  Plugin Name: Better Attendees and Tickets
  Plugin URI: http://tickera.com/
  Description: Better attendees and tickets presentaton for Tickera
  Author: Tickera.com
  Author URI: http://tickera.com/
  Version: 1.0
  Copyright 2016 Tickera (http://tickera.com/)
 */

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly


if (!class_exists('TC_Better_Attendees_and_Tickets')) {

    class TC_Better_Attendees_and_Tickets {

        var $version = '1.0';
        var $title = 'Better Attendees and Tickets';
        var $name = 'better-attendees-and-tickets';

        function __construct() {
            global $post;

            if (!isset($post)) {
                $post = isset($_GET['post']) ? $_GET['post'] : '';
            }

            $post_type = get_post_type($post);

            if (empty($post_type)) {
                $post_type = isset($_GET['post_type']) ? $_GET['post_type'] : '';
            }

            add_filter('tc_tickets_instances_post_type_args', array(&$this, 'tc_tickets_instances_post_type_args'));
            add_filter('manage_tc_tickets_instances_posts_columns', array(&$this, 'manage_tc_tickets_instances_columns'));
            add_action('manage_tc_tickets_instances_posts_custom_column', array(&$this, 'manage_tc_tickets_instances_posts_custom_column'), 10, 2);
            add_action('add_meta_boxes', array(&$this, 'add_tickets_instances_metaboxes'));
            add_action('admin_enqueue_scripts', array(&$this, 'admin_enqueue_scripts_and_styles'));

            if ($post_type == 'tc_tickets_instances') {
                add_filter('page_row_actions', array($this, 'post_row_actions'), 10, 2);
            }

            add_action('save_post', array($this, 'save_tickets_instances_meta'), 10, 3);

            add_filter('post_updated_messages', array($this, 'post_updated_messages'));

            add_filter('posts_join', array($this, 'extended_search_join'));
            add_filter('posts_where', array($this, 'extended_search_where'));

            add_action('restrict_manage_posts', array($this, 'add_events_filter'));
            add_action('restrict_manage_posts', array($this, 'add_order_status_filter'));

            add_action('pre_get_posts', array($this, 'pre_get_posts_reorder'));
            add_action('pre_get_posts', array($this, 'pre_get_posts_events_filter'));
            add_action('pre_get_posts', array($this, 'pre_get_posts_order_status_filter'));

            add_filter('bulk_actions-edit-tc_tickets_instances', array($this, 'remove_edit_bulk_action'));
        }

        function remove_edit_bulk_action($actions) {
            unset($actions['edit']);
            return $actions;
        }

        function pre_get_posts_reorder($query) {
            global $post_type, $pagenow;
            if ($pagenow == 'edit.php' && $post_type == 'tc_tickets_instances') {
                $query->set('orderby', 'date');
                $query->set('order', 'DESC');
            }
            return $query;
        }

        function pre_get_posts_events_filter($query) {
            global $post_type, $pagenow;
            if ($pagenow == 'edit.php' && $post_type == 'tc_tickets_instances') {

                if (isset($_REQUEST['tc_event_filter']) && $query->query['post_type'] == 'tc_tickets_instances') {

                    if ((int) $_REQUEST['tc_event_filter'] !== 0) {
                        $query->set('meta_key', 'event_id');
                        $query->set('meta_value', (int) $_REQUEST['tc_event_filter']);
                    }
                }
            }
            return $query;
        }

        function pre_get_posts_order_status_filter($query) {
            global $post_type, $pagenow;
            if ($pagenow == 'edit.php' && $post_type == 'tc_tickets_instances') {

                if (isset($_REQUEST['tc_order_status_filter']) && $query->query['post_type'] == 'tc_tickets_instances') {

                    $tc_order_status_filter = sanitize_text_field($_REQUEST['tc_order_status_filter']);

                    if ($_REQUEST['tc_order_status_filter'] !== '0') {
                        add_filter('posts_where', array($this, 'pre_get_posts_order_status_filter_where'));
                    }
                }
            }
            return $query;
        }

        function pre_get_posts_order_status_filter_where($where) {
            global $wpdb;
            $where .= " AND " . $wpdb->posts . ".post_parent IN (SELECT " . $wpdb->posts . ".ID FROM " . $wpdb->posts . " WHERE " . $wpdb->posts . ".post_status = '" . sanitize_text_field($_REQUEST['tc_order_status_filter']) . "')";
            return $where;
        }

        function add_events_filter() {
            global $post_type;
            if ($post_type == 'tc_tickets_instances') {

                $wp_events_search = new TC_Events_Search('', '', '-1');

                $currently_selected = isset($_REQUEST['tc_event_filter']) ? (int) $_REQUEST['tc_event_filter'] : '';
                ?>
                <select name="tc_event_filter">
                    <option value="0"><?php _e('All Events', 'tc'); ?></option>
                    <?php
                    foreach ($wp_events_search->get_results() as $event) {
                        $event_obj = new TC_Event($event->ID);
                        $event_object = $event_obj->details;
                        ?>
                        <option value="<?php echo esc_attr($event_object->ID); ?>" <?php selected($currently_selected, $event_object->ID, true); ?>><?php echo apply_filters('tc_event_select_name', $event_object->post_title, $event_object->ID); ?></option>
                        <?php
                    }
                    ?>
                </select>
                <?php
            }
        }

        function add_order_status_filter() {
            global $post_type;
            if ($post_type == 'tc_tickets_instances') {
                $currently_selected = isset($_REQUEST['tc_order_status_filter']) ? $_REQUEST['tc_order_status_filter'] : '';
                ?>
                <select name="tc_order_status_filter">
                    <option value="0"><?php _e('All Order Statuses', 'tc'); ?></option>
                    <?php
                    $payment_statuses = apply_filters('tc_csv_payment_statuses', array(
                        'any' => __('Any', 'tc'),
                        'order_paid' => __('Paid', 'tc'),
                        'order_received' => __('Pending / Received', 'tc'),
                        'order_cancelled' => __('Cancelled', 'tc'),
                    ));

                    unset($payment_statuses['any']); // we need "All Order Statuses" which has a value of 0 so we'll unset this one (and from the Bridge for WooCommerce too)

                    foreach ($payment_statuses as $payment_status_key => $payment_status_value) {
                        ?>
                        <option value="<?php echo esc_attr($payment_status_key); ?>" <?php selected($currently_selected, $payment_status_key, true); ?>><?php echo esc_attr($payment_status_value); ?></option>
                        <?php
                    }
                    ?>
                </select>
                <?php
            }
        }

        function save_tickets_instances_meta($post_id, $post, $update) {
            $ticket_instance = new TC_Ticket_Instance((int) $post_id);
            $ticket_type = new TC_Ticket($ticket_instance->details->ticket_type_id);
            $ticket_event_id = $ticket_type->get_ticket_event($ticket_instance->details->ticket_type_id);

            if (isset($_POST['api_key'])) {
                $api_key = new TC_API_Key(sanitize_text_field($_POST['api_key']));
                $checkin = new TC_Checkin_API($api_key->details->api_key, apply_filters('tc_checkin_request_name', 'tickera_scan'), 'return', $ticket_instance->details->ticket_code, false);
                $checkin_result = $checkin->ticket_checkin(false);
                if (isset($checkin_result['status']) && $checkin_result['status'] == 1) {
                    $message_type = 'updated';
                    $message = __('Ticket checked in successfully.', 'tc');
                } else {
                    if ($checkin_result == 11) {
                        wp_redirect('post.php?post=' . $post_id . '&action=edit&message=11');
                        exit;
                    } else if ($checkin_result == 403) {
                        wp_redirect('post.php?post=' . $post_id . '&action=edit&message=403');
                        exit;
                    } else {
                        wp_redirect('post.php?post=' . $post_id . '&action=edit&message=11');
                        exit;
                    }
                }
            }
        }

        function extended_search_join($join) {
            global $pagenow, $wpdb;
            if (is_admin() && $pagenow == 'edit.php' && isset($_GET['post_type']) && $_GET['post_type'] == 'tc_tickets_instances' && isset($_REQUEST['s']) && $_REQUEST['s'] != '') {
                $join .='LEFT JOIN ' . $wpdb->postmeta . ' ON ' . $wpdb->posts . '.ID = ' . $wpdb->postmeta . '.post_id ';
            }
            return $join;
        }

        function extended_search_where($where) {
            global $pagenow, $wpdb;
            if (is_admin() && $pagenow == 'edit.php' && isset($_GET['post_type']) && $_GET['post_type'] == 'tc_tickets_instances' && isset($_REQUEST['s']) && $_REQUEST['s'] != '') {
                $where = preg_replace(
                        "/\(\s*" . $wpdb->posts . ".post_title\s+LIKE\s*(\'[^\']+\')\s*\)/", "(" . $wpdb->posts . ".post_title LIKE $1) OR (" . $wpdb->postmeta . ".meta_value LIKE $1)", $where);
            }
            return $where;
        }

        function post_updated_messages($messages) {

            $post = get_post();
            $post_type = get_post_type($post);
            $post_type_object = get_post_type_object($post_type);

            $messages['tc_tickets_instances'] = array(
                0 => '', // Unused. Messages start at index 1.
                1 => __('Attendee updated.'),
                2 => __('Custom field updated.'),
                3 => __('Custom field deleted.'),
                4 => __('Check-in records updated.'),
                /* translators: %s: date and time of the revision */
                5 => isset($_GET['revision']) ? sprintf(__('Attendee data restored to revision from %s'), wp_post_revision_title((int) $_GET['revision'], false)) : false,
                6 => __('Attendee data published.'),
                7 => __('Attendee data saved.'),
                8 => __('Attendee data submitted.'),
                9 => sprintf(
                        __('Attendee data scheduled for: <strong>%1$s</strong>.'), date_i18n(__('M j, Y @ G:i'), strtotime($post->post_date))
                ),
                10 => __('Attendee data draft updated.'),
                11 => __('Ticket is invalid or expired', 'tc'),
                403 => __('Insufficient permissions. This API key cannot check in this ticket.', 'tc')
            );
            return $messages;
        }

        function post_row_actions($actions, $post) {
            unset($actions['view']);
            unset($actions['edit']);
            unset($actions['inline hide-if-no-js']);
            return $actions;
        }

        /*
         * Enqueue scripts and styles
         */

        function admin_enqueue_scripts_and_styles() {
            global $post, $post_type;
            if ($post_type == 'tc_tickets_instances') {
                wp_enqueue_style('tc-better-attendees-and-tickets', plugins_url('css/admin.css', __FILE__));
            }
        }

        /*
         * Change tickets intances post type arguments
         */

        function tc_tickets_instances_post_type_args($args) {
            $args['show_in_menu'] = 'edit.php?post_type=tc_events';
            $args['show_ui'] = true;
            $args['has_archive'] = false;
            $args['public'] = false;

            $args['supports'] = array(
                'title',
                    //'editor',
            );

            return apply_filters('tc_tickets_instances_post_type_args_val', $args);
        }

        /*
         * Add table column titles
         */

        function manage_tc_tickets_instances_columns($columns) {

            $tickets_instances_columns = TC_Tickets_Instances::get_tickets_instances_fields();
            foreach ($tickets_instances_columns as $tickets_instances_column) {
                if (isset($tickets_instances_column['table_visibility']) && $tickets_instances_column['table_visibility'] == true && $tickets_instances_column['field_name'] !== 'post_title') {
                    $columns[$tickets_instances_column['field_name']] = $tickets_instances_column['field_title'];
                }
            }
            unset($columns['date']);
            unset($columns['title']);
            return $columns;
        }

        /*
         * Add table column values
         */

        function manage_tc_tickets_instances_posts_custom_column($name, $post_id) {
            global $post, $tc;
            $tickets_instances_columns = TC_Tickets_Instances::get_tickets_instances_fields();

            foreach ($tickets_instances_columns as $tickets_instances_column) {
                if (isset($tickets_instances_column['table_visibility']) && $tickets_instances_column['table_visibility'] == true && $tickets_instances_column['field_name'] !== 'post_title') {

                    if ($tickets_instances_column['field_name'] == $name) {

                        $post_field_type = TC_Tickets_Instances::check_field_property($tickets_instances_column['field_name'], 'post_field_type');
                        $field_id = $tickets_instances_column['id']; //$tickets_instances->get_field_id($col['field_name'], 'post_field_type');
                        $field_name = $tickets_instances_column['field_name'];

                        $ticket_instance_obj = new TC_Ticket_Instance($post_id);
                        $ticket_instance_object = apply_filters('tc_ticket_instance_object_details', $ticket_instance_obj->details);

                        if (isset($post_field_type) && $post_field_type == 'post_meta') {

                            if (isset($field_id)) {
                                echo apply_filters('tc_ticket_instance_field_value', $ticket_instance_object->ID, $ticket_instance_object->{$field_name}, $post_field_type, (isset($tickets_instances_column['field_id']) ? $tickets_instances_column['field_id'] : ''), $field_id);
                            } else {
                                echo apply_filters('tc_ticket_instance_field_value', $ticket_instance_object->ID, $ticket_instance_object->{$field_name}, $post_field_type, (isset($tickets_instances_column['field_id']) ? $tickets_instances_column['field_id'] : ''));
                            }
                        } else {
                            if (isset($field_id)) {
                                echo apply_filters('tc_ticket_instance_field_value', $ticket_instance_object->ID, (isset($ticket_instance_object->{$post_field_type}) ? $ticket_instance_object->{$post_field_type} : $ticket_instance_object->{$field_name}), $post_field_type, $tickets_instances_column['field_name'], $field_id);
                            } else {
                                echo apply_filters('tc_ticket_instance_field_value', $ticket_instance_object->ID, (isset($ticket_instance_object->{$post_field_type}) ? $ticket_instance_object->{$post_field_type} : $ticket_instance_object->{$field_name}), $post_field_type, $tickets_instances_column['field_name']);
                            }
                        }
                    }
                }
            }
        }

        function add_tickets_instances_metaboxes() {
            global $pagenow, $typenow, $post;
            add_meta_box('attendees-checkin-details-tc-metabox-wrapper', __('Check-in List', 'tc'), 'tc_attendees_check_in_details_metabox', 'tc_tickets_instances', 'normal');
        }

    }

    global $TC_Better_Attendees_and_Tickets;
    $TC_Better_Attendees_and_Tickets = new TC_Better_Attendees_and_Tickets();
}

function tc_attendees_check_in_details_metabox() {

    $ticket_instance = new TC_Ticket_Instance((int) $_GET['post']);
    $ticket_type = new TC_Ticket($ticket_instance->details->ticket_type_id);
    $ticket_event_id = $ticket_type->get_ticket_event($ticket_instance->details->ticket_type_id);

    $ticket_checkins = $ticket_instance->get_ticket_checkins();

    if (isset($_GET['checkin_action']) && $_GET['checkin_action'] == 'delete_checkin' && check_admin_referer('delete_checkin') && !isset($_POST['api_key'])) {
        $entry_to_delete = $_GET['checkin_entry'];

        $checkin_row = 0;

        if ($ticket_checkins) {
            foreach ($ticket_checkins as $ticket_key => $ticket_checkin) {
                if ($ticket_checkin['date_checked'] == $entry_to_delete) {
                    unset($ticket_checkins[$ticket_key]);
                }
                $checkin_row++;
            }
            update_post_meta($ticket_instance->details->ID, 'tc_checkins', $ticket_checkins);
            do_action('tc_check_in_deleted', $ticket_instance->details->ID, $ticket_checkins);
            $message_type = 'updated';
            $message = __('Check-in record deleted successfully.', 'tc');
        }
    }

    $ticket_checkins = $ticket_instance->get_ticket_checkins();
    ?>

    <?php
    if (isset($message)) {
        ?>
        <div id="message" class="<?php echo $message_type; ?> fade"><p><?php echo $message; ?></p></div>
        <?php
    }
    ?>

    <table class="checkins-table widefat shadow-table">
        <thead>
            <tr valign="top">
                <th><?php _e('Date & Time', 'tc'); ?></th>
                <th><?php _e('Status', 'tc'); ?></th>
                <th><?php _e('API Key', 'tc'); ?></th>
                <?php if (current_user_can('manage_options') || current_user_can('delete_tickets_cap')) { ?>
                    <th><?php _e('Delete', 'tc'); ?></th>
                <?php } ?>
            </tr>
        </thead>
        <tbody>

            <?php
            $style = '';
            if ($ticket_checkins) {
                arsort($ticket_checkins);
                foreach ($ticket_checkins as $ticket_checkin) {
                    $style = ( ' class="alternate"' == $style ) ? '' : ' class="alternate"';
                    ?>  
                    <tr <?php echo $style; ?>>
                        <td><?php echo tc_format_date($ticket_checkin['date_checked']); ?></td>
                        <td><?php echo apply_filters('tc_checkins_status', $ticket_checkin['status']); ?></td>
                        <td><?php echo apply_filters('tc_checkins_api_key_id', $ticket_checkin['api_key_id']); ?></td>
                        <?php if (current_user_can('manage_options') || current_user_can('delete_checkins_cap')) { ?>
                            <td><?php echo '<a class="tc_delete_link" href="' . wp_nonce_url(admin_url('post.php?post=' . (int) $_GET['post'] . '&action=edit&checkin_action=delete_checkin&checkin_entry=' . $ticket_checkin['date_checked']), 'delete_checkin') . '">' . __('Delete', 'tc') . '</a>'; ?></td>
                        <?php } ?>
                    </tr>
                    <?php
                }
            } else {
                ?>
                <tr>
                    <td colspan="4"><?php _e("There are no any check-ins for this ticket yet."); ?></td>
                </tr>
                <?php
            }
            ?>
        </tbody>
    </table>

    <?php
    $current_user = wp_get_current_user();
    $current_user_name = $current_user->user_login;
    $staff_api_keys_num = 0;
    $has_api_records = false;

    if (current_user_can('manage_options')) {
        $wp_api_keys_search_all = new TC_API_Keys_Search('', '', 'all');

        if (count($wp_api_keys_search_all->get_results()) > 0) {
            $has_api_records = true;
        }
    }

    $wp_api_keys_search = new TC_API_Keys_Search('', '', $ticket_event_id, 999);

    if (count($wp_api_keys_search->get_results()) > 0) {
        $has_api_records = true;
    }

    if (!current_user_can('manage_options')) {
        foreach ($wp_api_keys_search->get_results() as $api_key) {
            $api_key_obj = new TC_API_Key($api_key->ID);
            if (($api_key_obj->details->api_username == $current_user_name)) {
                $staff_api_keys_num++;
            }
        }
    }

    if ($has_api_records && (current_user_can('manage_options') || (!current_user_can('manage_options') && $staff_api_keys_num > 0))) {
        ?>
        <form action="" method="post" enctype="multipart/form-data">
            <table class="checkin-table">
                <tbody>
                    <tr valign="top">
                        <th scope="row"><label for="api_key"><?php _e('API Key') ?></label></th>
                        <td>
                            <select name="api_key">
                                <?php
                                foreach ($wp_api_keys_search->get_results() as $api_key) {
                                    $api_key_obj = new TC_API_Key($api_key->ID);
                                    if (current_user_can('manage_options') || ($api_key_obj->details->api_username == $current_user_name)) {
                                        ?>
                                        <option value="<?php echo esc_attr($api_key->ID); ?>"><?php echo $api_key_obj->details->api_key_name; ?></option>
                                        <?php
                                    }
                                }
                                if (current_user_can('manage_options')) {
                                    foreach ($wp_api_keys_search_all->get_results() as $api_key) {
                                        $api_key_obj = new TC_API_Key($api_key->ID);
                                        if (current_user_can('manage_options') || ($api_key_obj->details->api_username == $current_user_name)) {
                                            ?>
                                            <option value="<?php echo esc_attr($api_key->ID); ?>"><?php echo $api_key_obj->details->api_key_name; ?></option>
                                            <?php
                                        }
                                    }
                                }
                                ?>
                            </select>
                            <input type="submit" name="check_in_ticket" id="check_in_ticket" class="button button-primary" value="<?php _e('Check In', 'tc'); ?>">
                        </td>
                    </tr>
                </tbody>
            </table>
        </form>
    <?php } ?>
    <?php
}
?>
