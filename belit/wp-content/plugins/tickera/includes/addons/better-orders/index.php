<?php
/*
  Plugin Name: Better Orders
  Plugin URI: http://tickera.com/
  Description: Better orders presentaton for Tickera
  Author: Tickera.com
  Author URI: http://tickera.com/
  Version: 1.0
  Copyright 2016 Tickera (http://tickera.com/)
 */

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly


if (!class_exists('TC_Better_Orders')) {

    class TC_Better_Orders {

        var $version = '1.0';
        var $title = 'Better Orders';
        var $name = 'better-orders';

        function __construct() {
            global $post;

            if (!isset($post)) {
                $post = isset($_GET['post']) ? $_GET['post'] : '';
            }

            $post_type = get_post_type($post);

            if (empty($post_type)) {
                $post_type = isset($_GET['post_type']) ? $_GET['post_type'] : '';
            }

            add_action('wp_trash_post', array($this, 'tc_trash_post'));
            add_action('delete_post', array($this, 'tc_delete_post'));
            add_action('untrash_post', array($this, 'tc_untrash_post'));

            add_filter('tc_orders_post_type_args', array($this, 'tc_orders_post_type_args'), 99, 1);
            add_filter('manage_tc_orders_posts_columns', array(&$this, 'manage_tc_orders_columns'));
            add_action('manage_tc_orders_posts_custom_column', array(&$this, 'manage_tc_orders_posts_custom_column'), 10, 2);
            add_action('add_meta_boxes', array(&$this, 'add_orders_metaboxes'));
            add_action('admin_enqueue_scripts', array(&$this, 'admin_enqueue_scripts_and_styles'));

            if ($post_type == 'tc_orders') {
                add_filter('post_row_actions', array($this, 'post_row_actions'), 10, 2);
            }

            add_action('save_post', array($this, 'save_orders_meta'), 10, 3);

            add_filter('post_updated_messages', array($this, 'post_updated_messages'));

            add_action('pre_get_posts', array($this, 'pre_get_posts_reorder'));

            add_filter('posts_join', array($this, 'extended_search_join'));
            add_filter('posts_where', array($this, 'extended_search_where'));
            add_filter('posts_groupby', array($this, 'extended_groupby'));
            add_action('restrict_manage_posts', array($this, 'add_events_filter'));
            //add_action( 'restrict_manage_posts', array( $this, 'add_order_status_filter' ) );
            add_action('pre_get_posts', array($this, 'pre_get_posts_events_filter'));
            //add_action( 'pre_get_posts', array( $this, 'pre_get_posts_order_status_filter' ) );
            add_filter('bulk_actions-edit-tc_orders', array($this, 'remove_edit_bulk_action'));
        }

        function tc_trash_post($post_id) {
            if (get_post_type($post_id) == 'tc_orders') {
                //Delete associated ticket instances
                $args = array(
                    'posts_per_page' => -1,
                    'post_type' => 'tc_tickets_instances',
                    'post_status' => array('publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit'),
                    'post_parent' => $post_id
                );

                $ticket_instances = get_posts($args);

                foreach ($ticket_instances as $ticket_instance) {
                    $ticket_instance_instance = new TC_Ticket_Instance($ticket_instance->ID);
                    $ticket_instance_instance->delete_ticket_instance(false);
                }
            }
        }

        function tc_delete_post($post_id) {
            if (get_post_type($post_id) == 'tc_orders') {
                //Delete associated ticket instances
                $args = array(
                    'posts_per_page' => -1,
                    'post_type' => 'tc_tickets_instances',
                    'post_status' => array('publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash'),
                    'post_parent' => $post_id
                );

                $ticket_instances = get_posts($args);

                foreach ($ticket_instances as $ticket_instance) {
                    $ticket_instance_instance = new TC_Ticket_Instance($ticket_instance->ID);
                    $ticket_instance_instance->delete_ticket_instance(true);
                }
            }
        }

        function tc_untrash_post($post_id) {
            if (get_post_type($post_id) == 'tc_orders') {
                $args = array(
                    'posts_per_page' => -1,
                    'post_type' => 'tc_tickets_instances',
                    'post_status' => 'trash',
                    'post_parent' => $post_id
                );

                $ticket_instances = get_posts($args);

                foreach ($ticket_instances as $ticket_instance) {
                    wp_untrash_post($ticket_instance->ID);
                }
            }
        }

        function remove_edit_bulk_action($actions) {
            unset($actions['edit']);
            return $actions;
        }

        function pre_get_posts_reorder($query) {
            global $post_type, $pagenow;
            if ($pagenow == 'edit.php' && $post_type == 'tc_orders') {
                $query->set('orderby', isset($_REQUEST['orderby']) ? sanitize_key($_REQUEST['orderby']) : 'date' );
                $query->set('order', isset($_REQUEST['order']) ? sanitize_key($_REQUEST['order']) : 'DESC' );
            }
            return $query;
        }

        function pre_get_posts_events_filter($query) {
            global $post_type, $pagenow;
            if ($pagenow == 'edit.php' && $post_type == 'tc_orders') {

                if (isset($_REQUEST['tc_event_filter']) && $query->query['post_type'] == 'tc_orders') {

                    $tc_tc_event_filter = sanitize_text_field($_REQUEST['tc_event_filter']);

                    if ($tc_tc_event_filter !== '0') {
                        add_filter('posts_where', array($this, 'pre_get_posts_event_filter_where'));
                    }
                }
            }
            return $query;
        }

        function pre_get_posts_event_filter_where($where) {
            global $wpdb, $post_type, $pagenow;
            if ($pagenow == 'edit.php' && $post_type == 'tc_orders') {
                if (isset($_REQUEST['tc_event_filter']) && $_REQUEST['tc_event_filter'] != 0) {
                    $where .= " AND (" . $wpdb->postmeta . ".meta_key='tc_parent_event' AND " . $wpdb->postmeta . ".meta_value LIKE '%\"" . (int) $_REQUEST['tc_event_filter'] . "\"%')";
                }
            }
            return $where;
        }

        function add_events_filter() {
            global $post_type;
            if ($post_type == 'tc_orders') {

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
            if ($post_type == 'tc_orders') {
                $currently_selected = isset($_REQUEST['tc_order_status_filter']) ? sanitize_key($_REQUEST['tc_order_status_filter']) : '';
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

        function save_orders_meta($post_id, $post, $update) {
            global $wpdb;
            $order_id = $post_id;

            if (!isset($_POST['order_status_change'])) {//Make sure the edit comes from the order details page
                return;
            }

            $post_status = sanitize_key($_POST['order_status_change']);
            $order = new TC_Order($order_id);

            $old_post_status = $order->details->post_status;

            if ($post_status == 'trash') {
                $order->delete_order(false);
            } else {
                if ($old_post_status == 'trash') {//untrash attendees & tickets only in case that order was in the trash
                    $order->untrash_order();
                }
                $wpdb->update(
                        $wpdb->posts, array(
                    'post_status' => $post_status
                        ), array(
                    'ID' => $order_id
                        ), array('%s'), array('%d')
                );
            }

            if ($post_status == 'order_paid') {

                if ($post_status !== $old_post_status) {//make sure that order status wasn't order_paid after the update (so we don't send out duplicate confirmation e-mails)
                    tc_order_created_email($order->details->post_name, $post_status, false, false, false, false);
                    $payment_info = get_post_meta($order_id, 'tc_payment_info', true);
                    do_action('tc_order_paid_change', $order_id, $post_status, '', '', $payment_info);
                } else {
                    //echo 'already was paid!';
                    exit;
                }
            } else {
                //echo 'post status is not order_paid';
            }

            //update buyer e-mail
            $cart_info = get_post_meta($post_id, 'tc_cart_info', true);
            $cart_info['buyer_data']['email_post_meta'] = sanitize_text_field($_POST['customer_email']);
            update_post_meta($post_id, 'tc_cart_info', $cart_info);

            //update buyer name
            $cart_info = get_post_meta($post_id, 'tc_cart_info', true);
            $cart_info['buyer_data']['first_name_post_meta'] = sanitize_text_field($_POST['customer_first_name']);
            $cart_info['buyer_data']['last_name_post_meta'] = sanitize_text_field($_POST['customer_last_name']);
            update_post_meta($post_id, 'tc_cart_info', $cart_info);
        }

        function extended_search_join($join) {
            global $pagenow, $wpdb;
            $joined = false;
            if (is_admin() && $pagenow == 'edit.php' && isset($_GET['post_type']) && $_GET['post_type'] == 'tc_orders') {
                if (((isset($_REQUEST['tc_event_filter']) && $_REQUEST['tc_event_filter'] != 0))) {
                    $joined = true;
                    $join .=' LEFT JOIN ' . $wpdb->postmeta . ' ON ' . $wpdb->posts . '.ID = ' . $wpdb->postmeta . '.post_id ';
                }
                if ((isset($_REQUEST['s']) && $_REQUEST['s'] != '')) {
                    if (!$joined) {
                        $join .=' LEFT JOIN ' . $wpdb->postmeta . ' ON ' . $wpdb->posts . '.ID = ' . $wpdb->postmeta . '.post_id';
                    }
                }
            }

            return $join;
        }

        function extended_search_where($where) {
            global $pagenow, $wpdb;
            if (is_admin() && $pagenow == 'edit.php' && isset($_GET['post_type']) && $_GET['post_type'] == 'tc_orders' && isset($_REQUEST['s']) && $_REQUEST['s'] != '') {
                $where = preg_replace(
                        "/\(\s*" . $wpdb->posts . ".post_title\s+LIKE\s*(\'[^\']+\')\s*\)/", "(" . $wpdb->posts . ".post_title LIKE $1) OR (" . $wpdb->postmeta . ".meta_value LIKE $1)", $where);
            }
            return $where;
        }

        function extended_groupby($groupby) {
            global $pagenow, $wpdb;
            if (is_admin() && $pagenow == 'edit.php' && isset($_GET['post_type']) && $_GET['post_type'] == 'tc_orders' && isset($_REQUEST['s']) && $_REQUEST['s'] != '') {
                global $wpdb;
                $groupby = "{$wpdb->posts}.ID";
            }
            return $groupby;
        }

        function post_updated_messages($messages) {

            $post = get_post();
            $post_type = get_post_type($post);
            $post_type_object = get_post_type_object($post_type);

            $messages['tc_orders'] = array(
                0 => '', // Unused. Messages start at index 1.
                1 => __('Order updated.'),
                2 => __('Custom field updated.'),
                3 => __('Custom field deleted.'),
                4 => __('Order updated.'),
                /* translators: %s: date and time of the revision */
                5 => isset($_GET['revision']) ? sprintf(__('Order data restored to revision from %s'), wp_post_revision_title((int) $_GET['revision'], false)) : false,
                6 => __('Order data published.'),
                7 => __('Order data saved.'),
                8 => __('Order data submitted.'),
                9 => sprintf(
                        __('Order scheduled for: <strong>%1$s</strong>.'), date_i18n(__('M j, Y @ G:i'), strtotime($post->post_date))
                ),
                10 => __('Order draft updated.'),
            );
            return $messages;
        }

        function post_row_actions($actions, $post) {
            //unset( $actions[ 'view' ] );
            //unset( $actions[ 'edit' ] );
            unset($actions['inline hide-if-no-js']);
            return $actions;
        }

        /*
         * Enqueue scripts and styles
         */

        function admin_enqueue_scripts_and_styles() {
            global $post, $post_type;
            if ($post_type == 'tc_orders') {
                wp_enqueue_style('tc-orders', plugins_url('css/admin.css', __FILE__));
            }
        }

        /*
         * Change tickets intances post type arguments
         */

        function tc_orders_post_type_args($args) {
            $args['show_in_menu'] = 'edit.php?post_type=tc_events';
            $args['show_ui'] = true;
            $args['has_archive'] = false;
            $args['public'] = false;

            $args['supports'] = array(
                'title',
                    //'editor',
            );

            return apply_filters('tc_orders_post_type_args_val', $args);
        }

        /*
         * Add table column titles
         */

        function manage_tc_orders_columns($columns) {

            $tickets_orders_columns = TC_Orders::get_order_fields();
            foreach ($tickets_orders_columns as $tickets_orders_column) {
                if (isset($tickets_orders_column['table_visibility']) && $tickets_orders_column['table_visibility'] == true && $tickets_orders_column['field_name'] !== 'post_title') {
                    $columns[isset($tickets_orders_column['id']) ? $tickets_orders_column['id'] : $tickets_orders_column['field_name']] = $tickets_orders_column['field_title'];
                }
            }
            unset($columns['date']);
            unset($columns['title']);
            return $columns;
        }

        /*
         * Add table column values
         */

        function manage_tc_orders_posts_custom_column($name, $post_id) {
            global $post, $tc;
            $tickets_orders_columns = TC_Orders::get_order_fields();

            foreach ($tickets_orders_columns as $tickets_orders_column) {
                if (isset($tickets_orders_column['table_visibility']) && $tickets_orders_column['table_visibility'] == true && $tickets_orders_column['field_name'] !== 'post_title') {

                    $id = isset($tickets_orders_column['id']) ? $tickets_orders_column['id'] : '';

                    if ($tickets_orders_column['field_name'] == $name || ($id == $name)) {

                        $post_field_type = TC_Orders::check_field_property($tickets_orders_column['field_name'], 'post_field_type');
                        $field_id = isset($tickets_orders_column['id']) ? $tickets_orders_column['id'] : $tickets_orders_column['field_name']; //$tickets_instances->get_field_id($col['field_name'], 'post_field_type');
                        $field_name = $tickets_orders_column['field_name'];


                        $order_obj = new TC_Order($post_id);
                        $order_object = apply_filters('tc_order_object_details', $order_obj->details);

                        if (isset($post_field_type) && $post_field_type == 'post_meta') {
                            if (isset($field_id)) {
                                echo apply_filters('tc_order_field_value', $order_object->ID, $order_object->{$field_name}, $post_field_type, isset($tickets_orders_column['field_id']) ? $tickets_orders_column['field_id'] : '', $field_id);
                            } else {
                                echo apply_filters('tc_order_field_value', $order_object->ID, $order_object->{$field_name}, $post_field_type, $tickets_orders_column['field_id']);
                            }
                        } else {
                            if (isset($field_id)) {
                                echo apply_filters('tc_order_field_value', $order_object->ID, (isset($order_object->{$post_field_type}) ? $order_object->{$post_field_type} : $order_object->{$field_name}), $post_field_type, $tickets_orders_column['field_name'], $field_id);
                            } else {
                                echo apply_filters('tc_order_field_value', $order_object->ID, (isset($order_object->{$post_field_type}) ? $order_object->{$post_field_type} : $order_object->{$field_name}), $post_field_type, $tickets_orders_column['field_name']);
                            }
                        }
                    }
                }
            }
        }

        function add_orders_metaboxes() {
            global $pagenow, $typenow, $post;
            add_meta_box('order-details-tc-metabox-wrapper', __('Order Details', 'tc'), 'tc_order_details_metabox', 'tc_orders', 'normal');
        }

    }

    if (apply_filters('tc_bridge_for_woocommerce_is_active', false) == true) {
        $woo_bridge_is_active = true;
    } else {
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        if (is_plugin_active('bridge-for-woocommerce/bridge-for-woocommerce.php')) {
            $woo_bridge_is_active = true;
        } else {
            $woo_bridge_is_active = false;
        }
    }
    //Make sure not to load the add-on if Bridge for WooCommerce is active

    if (!$woo_bridge_is_active) {
        global $TC_Better_Orders;
        $TC_Better_Orders = new TC_Better_Orders();
    }
}

function tc_order_details_metabox() {
    //do_action( 'tc_order_details_page_start' );
    $orders = new TC_Orders();
    $fields = TC_Orders::get_order_fields();

    $columns = $orders->get_columns();
    $order = new TC_Order(isset($_REQUEST['post']) ? (int) $_REQUEST['post'] : 0 );
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            var replaceWith = $('<input name="temp" class="tc_temp_value" type="text" />'),
                    connectWith = $('input[name="hiddenField"]');

            $('td.first_name, td.last_name, td.owner_email').inlineEdit(replaceWith, connectWith);
        });
    </script>
    <form name="tc_order_details" method="post" >
        <input type='hidden' id='order_id' value='<?php echo esc_attr($order->details->ID); ?>' />
        <input type="hidden" name="hiddenField" />
        <?php do_action('tc_order_details_before_table'); ?>
        <table class="order-table">
            <tbody>
                <?php foreach ($fields as $field) { ?>
                    <?php if ($orders->is_valid_order_field_type($field['field_type'])) { ?>    
                        <tr valign="top">

                            <?php if ($field['field_type'] !== 'separator') { ?>
                                <th scope="row"><label for="<?php echo esc_attr($field['field_name']); ?>"><?php echo $field['field_title']; ?></label></th>
                            <?php } ?>
                            <td <?php echo ($field['field_type'] == 'separator') ? 'colspan = "2"' : ''; ?>>
                                <?php do_action('tc_before_orders_field_type_check'); ?>
                                <?php
                                if ($field['field_type'] == 'ID') {
                                    echo $order->details->{$field['post_field_type'] };
                                }
                                ?>
                                <?php
                                if ($field['field_type'] == 'function') {
                                    eval($field['function'] . '("' . $field['field_name'] . '"' . (isset($order->details->ID) ? ', ' . $order->details->ID : '') . (isset($field['id']) ? ', "' . $field['id'] . '"' : '') . ');
			');
                                    ?>
                                <?php } ?>
                                <?php if ($field['field_type'] == 'text') { ?>
                                    <input type="text" class="regular-<?php echo esc_attr($field['field_type']); ?>" value="<?php
                                           if (isset($order)) {
                                               if ($field['post_field_type'] == 'post_meta') {
                                                   echo esc_attr(isset($order->details->{$field['field_name']}) ? $order->details->{$field['field_name']} : '' );
                                               } else {
                                                   echo esc_attr($order->details->{$field['post_field_type']});
                                               }
                                               ?>" id="<?php
                                               echo esc_attr($field['field_name']);
                                           }
                                           ?>" name="<?php echo esc_attr($field['field_name'] . '_' . $field['post_field_type']); ?>">

                                <?php } ?>
                                <?php if ($field['field_type'] == 'separator') { ?>
                                    <hr />
                                <?php } ?>

                                <?php do_action('tc_after_orders_field_type_check'); ?>
                            </td>
                        </tr>
                        <?php
                    }
                }
                do_action('tc_after_order_details_fields');
                ?>
            </tbody>
        </table>
        <?php submit_button(__('Save Changes', 'tc'), 'primary', 'tc_order_save_changes', false); ?>
        <?php do_action('tc_order_details_after_table'); ?>
    </form>
    <?php
}
?>
