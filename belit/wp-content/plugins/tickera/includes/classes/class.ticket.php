<?php

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

if (!class_exists('TC_Ticket')) {

    class TC_Ticket {

        var $id = '';
        var $output = 'OBJECT';
        var $ticket = array();
        var $details;

        function __construct($id = '', $status = 'any', $output = 'OBJECT') {
            $continue = true;

            if ($status !== 'any') {
                if (get_post_status($id) == $status) {
                    $continue = true;
                } else {
                    $continue = false;
                }
            }

            if ($continue) {
                $this->id = $id;
                $this->output = $output;
                $this->details = get_post($this->id, $this->output);

                $tickets = new TC_Tickets();
                $fields = $tickets->get_ticket_fields();

                if (isset($this->details)) {
                    if (!empty($fields)) {
                        foreach ($fields as $field) {
                            if (!isset($this->details->{$field['field_name']})) {
                                $this->details->{$field['field_name']} = get_post_meta($this->id, $field['field_name'], true);
                            }
                        }
                    }
                }
            } else {
                $this->id = null;
            }
        }

        function TC_Ticket($id = '', $output = 'OBJECT') {
            $this->__construct($id, $output);
        }

        public static function is_sales_available($ticket_type_id = false) {
            if (!$ticket_type_id) {
                return false;
            } else {

                $ticket_availability = get_post_meta($ticket_type_id, '_ticket_availability', true);
                if (empty($ticket_availability)) {
                    $ticket_availability = 'open_ended';
                }

                if ($ticket_availability == 'range') {
                    $from_date = get_post_meta($ticket_type_id, '_ticket_availability_from_date', true);
                    $to_date = get_post_meta($ticket_type_id, '_ticket_availability_to_date', true);

                    if ((date('U', current_time('timestamp', false)) >= date('U', strtotime($from_date))) && (date('U', current_time('timestamp', false)) <= date('U', strtotime($to_date)))) {
                        return true;
                    } else {
                        return false;
                    }
                } else {//open-ended
                    return true;
                }
            }
        }

        public static function is_checkin_available($ticket_type_id = false, $order = false) {
     
            if (!$ticket_type_id) {
                return false;
            } else {
      
                $ticket_checkin_availability = get_post_meta($ticket_type_id, '_ticket_checkin_availability', true);
                if (empty($ticket_checkin_availability)) {
                    $ticket_checkin_availability = 'open_ended';
                }

                if ($ticket_checkin_availability == 'range') {
                    $from_date = get_post_meta($ticket_type_id, '_ticket_checkin_availability_from_date', true);
                    $to_date = get_post_meta($ticket_type_id, '_ticket_checkin_availability_to_date', true);

                    if ((date('U', current_time('timestamp', false)) >= date('U', strtotime($from_date))) && (date('U', current_time('timestamp', false)) <= date('U', strtotime($to_date)))) {
                        return true;
                    } else {
                        return false;
                    }
                } else if ($ticket_checkin_availability == 'time_after_order') {
           
                    $days_selected = get_post_meta($ticket_type_id, '_time_after_order_days', true);
                    $hours_selected = get_post_meta($ticket_type_id, '_time_after_order_hours', true);
                    $minutes_selected = get_post_meta($ticket_type_id, '_time_after_order_minutes', true);
                    
                    $total_seconds = (int)($days_selected * 60 * 60 * 60) + ($hours_selected * 60 * 60) + ($minutes_selected * 60);
                    
                    $order_date = $order->details->post_date;//date
                    
                    $order_limit_timestamp = strtotime($order_date) + $total_seconds;
                    $current_site_timestamp = current_time('timestamp', false);
                    
                    if($order_limit_timestamp > $current_site_timestamp){
                        return true;
                    }else{
                        return false;
                    }
                 
                } else {//open-ended
                    return true;
                }
            }
        }

        function get_ticket() {
            $ticket = get_post_custom($this->id, $this->output);
            return $ticket;
        }

        function get_number_of_sold_tickets() {
            $ticket_search = new TC_Tickets_Instances_Search('', '', -1, false, false, 'ticket_type_id', $this->id);
            if (is_array($ticket_search->get_results())) {
                return count($ticket_search->get_results());
            } else {
                return 0;
            }
        }

        function get_tickets_quantity_left() {
            $max_quantity = $this->details->quantity_available;
            $sold_quantity = tc_get_tickets_count_sold($this->id); //$this->get_number_of_sold_tickets();

            if ($max_quantity == 0 || $max_quantity == '') {
                return 9999; //means no limit
            } else {
                return ($max_quantity - $sold_quantity);
            }
        }

        function is_ticket_exceeded_quantity_limit() {
            $max_quantity = $this->details->quantity_available;
            if ($max_quantity == 0 || $max_quantity == '') {
                return false;
            } else {
                $sold_quantity = tc_get_tickets_count_sold($this->id); //$this->get_number_of_sold_tickets();

                if ($sold_quantity < $max_quantity) {
                    return false;
                } else {
                    return true;
                }
            }
        }

        function delete_ticket($force_delete = false) {
            if ($force_delete) {
                wp_delete_post($this->id);
            } else {
                wp_trash_post($this->id);
            }
        }

        function get_ticket_event($ticket_type_id = false) {
            if ($ticket_type_id == false) {
                $ticket_type_id = $this->id;
            }

            $event_id = get_post_meta($ticket_type_id, 'event_name', true);
            $alternate_event_id = get_post_meta($ticket_type_id, apply_filters('tc_event_name_field_name', '_event_name'), true);

            $event_id = !empty($event_id) ? $event_id : $alternate_event_id;

            return $event_id;
        }

        function get_ticket_id_by_name($slug) {

            $args = array(
                'name' => $slug,
                'post_type' => 'tc_tickets',
                'post_status' => 'any',
                'posts_per_page' => 1
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