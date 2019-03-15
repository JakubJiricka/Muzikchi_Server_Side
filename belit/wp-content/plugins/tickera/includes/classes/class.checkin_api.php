<?php

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

if (!class_exists('TC_Checkin_API')) {

    class TC_Checkin_API {

        var $api_key = '';
        var $ticket_code = '';
        var $page_number = 1;
        var $results_per_page = 10;
        var $keyword = '';

        function __construct($api_key, $request, $return_method = 'echo', $ticket_code = '', $execute_request = true) {
            global $wp;

            if (defined('TC_DEBUG') && isset($_GET['tc_debug'])) {
                error_reporting(E_ALL);
                @ini_set('display_errors', 'On');
            } else {
                error_reporting(0);
            }

            $this->api_key = $api_key;

            $checksum = isset($wp->query_vars['checksum']) ? $wp->query_vars['checksum'] : (isset($_REQUEST['checksum']) ? sanitize_key($_REQUEST['checksum']) : '');
            $page_number = isset($wp->query_vars['page_number']) ? $wp->query_vars['page_number'] : (isset($_REQUEST['page_number']) ? (int) $_REQUEST['page_number'] : apply_filters('tc_ticket_info_default_page_number', 1));
            $results_per_page = isset($wp->query_vars['results_per_page']) ? $wp->query_vars['results_per_page'] : (isset($_REQUEST['results_per_page']) ? (int) $_REQUEST['results_per_page'] : apply_filters('tc_ticket_info_default_results_per_page', 50));
            $keyword = isset($wp->query_vars['keyword']) ? $wp->query_vars['keyword'] : (isset($_REQUEST['keyword']) ? sanitize_text_field($_REQUEST['keyword']) : '');

            if ($checksum !== '') {
                $findme = 'checksum'; //old QR code character
                $pos = strpos($checksum, $findme);
                if ($pos === false) {//new code
                    //$checksum
                } else {//old code
                    $ticket_strings_array = explode('%7C', $checksum); //%7C = |
                    $checksum = end($ticket_strings_array);
                }
            }

            $this->ticket_code = apply_filters('tc_ticket_code_var_name', isset($ticket_code) && $ticket_code != '' ? $this->extract_checksum_from_code($ticket_code) : $this->extract_checksum_from_code($checksum));
            $this->page_number = apply_filters('tc_tickets_info_page_number_var_name', $page_number);
            $this->results_per_page = apply_filters('tc_tickets_info_results_per_page_var_name', $results_per_page);
            $this->keyword = apply_filters('tc_tickets_info_keyword_var_name', $keyword);

            if ($execute_request) {
                header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
                header("Pragma: no-cache"); // HTTP 1.0.
                header("Expires: 0"); // Proxies.
                header('Content-Type: application/json');

                try {
                    if ((int) @ini_get('output_buffering') === 1 || strtolower(@ini_get('output_buffering')) === 'on') {
                        ob_flush();
                        @ob_start('ob_gzhandler');
                    }
                } catch (Exception $e) {
                    //do not compress
                }

                if ($request == apply_filters('tc_translation_request_name', 'tickera_translation')) {
                    $this->translation();
                }

                if ($request == apply_filters('tc_check_credentials_request_name', 'tickera_check_credentials')) {
                    $this->check_credentials();
                }

                if ($request == apply_filters('tc_event_essentials_request_name', 'tickera_event_essentials')) {
                    if (apply_filters('tc_checkin_api_use_new_version', true) == true) {
                        $this->get_event_essentials();
                    } else {
                        $this->get_event_essentials_old();
                    }
                }

                if ($request == apply_filters('tc_checkins_request_name', 'tickera_checkins')) {
                    $this->ticket_checkins();
                }

                if ($request == apply_filters('tc_scan_request_name', 'tickera_scan')) {
                    $this->ticket_checkin($return_method);
                }

                if ($request == apply_filters('tc_tickets_info_request_name', 'tickera_tickets_info')) {
                    if (apply_filters('tc_checkin_api_use_new_version', true) == true) {
                        $this->tickets_info();
                    } else {
                        $this->tickets_info_old();
                    }
                }
            }
        }

        function right_timestamp($timestamp) {
            return intval($timestamp);
        }

        function get_license_key() {
            $tc_general_settings = get_option('tc_general_setting', false);
            $license_key = (defined('TC_LCK') && TC_LCK !== '') ? TC_LCK : (isset($tc_general_settings['license_key']) && $tc_general_settings['license_key'] !== '' ? $tc_general_settings['license_key'] : '');
            return $license_key;
        }

        function extract_checksum_from_code($code) {

            if ($code !== '') {
                $findme = 'checksum'; //old or QR code characters
                $pos = strpos($code, $findme);

                if ($pos === false) {//new code
                    //$checksum
                } else {//old code
                    if (strpos($code, '|')) {
                        $ticket_strings_array = explode('|', $code); //received from barcode reader addon
                        $code = end($ticket_strings_array);
                    }

                    if (strpos($code, '%7C')) {
                        $ticket_strings_array = explode('%7C', $code); //received from mobile app when reading a QR code or from 2D barcode reader
                        $code = end($ticket_strings_array);
                    }

                    if (strpos($code, '~')) {
                        $ticket_strings_array = explode('~', $code); //received from 2D barcode reader like this one QR Barcode Scanner Eyoyo EY-001
                        $code = end($ticket_strings_array);
                    }
                }
            }

            return $code;
        }

        function get_api_event() {
            return get_post_meta($this->get_api_key_id(), 'event_name', true);
        }

        function get_api_key_id() {
            $args = array(
                'post_type' => 'tc_api_keys',
                'post_status' => 'publish',
                'posts_per_page' => 1,
                'meta_key' => 'api_key',
                'meta_value' => $this->api_key,
                'fields' => 'ids'
            );

            $post = get_posts($args);

            if (isset($post[0])) {
                return $post[0];
            } else {
                return false;
            }
        }

        function translation($echo = true) {

            if ($this->get_api_key_id()) {
                $data = array(
                    'WORDPRESS_INSTALLATION_URL' => 'WORDPRESS INSTALLATION URL',
                    'API_KEY' => 'API KEY',
                    'AUTO_LOGIN' => 'AUTO LOGIN',
                    'SIGN_IN' => 'SIGN IN',
                    'SOLD_TICKETS' => 'TICKETS SOLD',
                    'CHECKED_IN_TICKETS' => 'CHECKED-IN TICKETS',
                    'HOME_STATS' => 'Home - Stats',
                    'LIST' => 'LIST',
                    'SIGN_OUT' => 'SIGN OUT',
                    'CHECK_IN' => 'CHECK IN',
                    'CANCEL' => 'CANCEL',
                    'SEARCH' => 'Search',
                    'ID' => 'ID',
                    'PURCHASED' => 'PURCHASED',
                    'CHECKINS' => 'CHECK-INS',
                    'CHECK_IN' => 'CHECK IN',
                    'SUCCESS' => 'SUCCESS',
                    'SUCCESS_MESSAGE' => 'Ticket has been checked in',
                    'OK' => 'OK',
                    'ERROR' => 'ERROR',
                    'ERROR_MESSAGE' => 'Wrong ticket code',
                    'PASS' => 'Pass',
                    'FAIL' => 'Fail',
                    'ERROR_LOADING_DATA' => 'Error loading data. Please check the URL and API KEY provided',
                    'API_KEY_LOGIN_ERROR' => 'Error. Please check the URL and API KEY provided',
                    'APP_TITLE' => 'Ticket Check-in',
                    'PLEASE_WAIT' => 'Please wait...',
                    'EMPTY_LIST' => 'The list is empty',
                    'ERROR_LICENSE_KEY' => 'License key is not valid. Please contact your administrator.'
                );
            } else {
                $data = array(
                    'pass' => false //api key is NOT valid
                );
            }

            $json = json_encode(apply_filters('tc_translation_data_output', $data));

            if ($echo) {
                echo $json;
                exit;
            } else {
                return $json;
            }
        }

        function check_credentials($echo = true) {

            if ($this->get_api_key_id()) {
                $data = array(
                    'pass' => true, //api key is valid
                    'license_key' => $this->get_license_key(),
                    'admin_email' => get_option('admin_email'),
                    'tc_iw_is_pr' => tc_iw_is_pr()
                );
            } else {
                $data = array(
                    'pass' => false //api key is NOT valid
                );
            }

            $json = json_encode(apply_filters('tc_check_credentials_data_output', $data));

            if ($echo) {
                echo $json;
                exit;
            } else {
                return $json;
            }
        }

        function get_event_essentials($echo = true) {
            $start = microtime(true);
            if ($this->get_api_key_id()) {

                global $wpdb;

                $event_id = $this->get_api_event();

                if ($event_id == 'all') {
                    //show all events
                    $event_id_query = '';
                } else {
                    $event_id_query = 'AND wp_pm.meta_value = ' . (int) $event_id;
                }

                $order_post_statuses = apply_filters('tc_paid_post_statuses', array('order_paid'));

                $having_statuses = "(";
                foreach ($order_post_statuses as $order_post_statuses_key => $order_post_statuses_val) {
                    $order_post_statuses[$order_post_statuses_key] = $order_post_statuses_val;
                    $having_statuses .= "parent_status = '" . $order_post_statuses_val . "' OR ";
                }

                $having_statuses = rtrim($having_statuses, 'OR ');

                $having_statuses .= ")";

                global $wpdb;

                $results = $wpdb->get_results("
    SELECT 
    ID,
    (SELECT post_status FROM {$wpdb->prefix}posts wp2 WHERE wp2.ID = wp.post_parent) as parent_status
    FROM {$wpdb->prefix}posts wp, {$wpdb->prefix}postmeta wp_pm 
    WHERE post_type = 'tc_tickets_instances'
    AND wp.ID = wp_pm.post_id
    AND wp_pm.meta_key = 'event_id'
    {$event_id_query}
    AND post_status = 'publish'
    GROUP BY wp.ID
    HAVING {$having_statuses}", ARRAY_A);

                $event_tickets_total = 0;
                $event_checkedin_tickets = 0;

                foreach ($results as $result_id) {
                    $event_tickets_total++;
                    $result_id = $result_id['ID'];

                    $checkins = get_post_meta($result_id, 'tc_checkins', true);

                    if (isset($checkins) && is_array($checkins) && count($checkins) > 0) {
                        $event_checkedin_tickets++;
                    }
                }

                $data = array(
                    'event_name' => $event_id == 'all' ? __('Multiple Events', 'tc') : stripslashes(get_the_title($event_id)),
                    'event_date_time' => $event_id == 'all' ? __('N/A', 'tc') : date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime(get_post_meta($event_id, 'event_date_time', true)), false),
                    'event_location' => $event_id == 'all' ? __('N/A', 'tc') : stripslashes(get_post_meta($event_id, 'event_location', true)),
                    'sold_tickets' => $event_tickets_total,
                    'checked_tickets' => $event_checkedin_tickets,
                    'execution_time' => microtime(true) - $start,
                    'pass' => true
                );

                $json = json_encode(apply_filters('tc_get_event_essentials_data_output', $data, $event_id, $this->get_api_key_id()));

                if ($echo) {
                    echo $json;
                    exit;
                } else {
                    return $json;
                }
            }
        }

        function get_event_essentials_old($echo = true) {

            if ($this->get_api_key_id()) {

                $event_id = $this->get_api_event();

                $event_tickets_total = 0;
                $event_checkedin_tickets = 0;

                $args = array(
                    'post_type' => 'tc_tickets_instances',
                    'post_status' => 'publish',
                    'posts_per_page' => -1,
                    'fields' => 'id=>parent'
                );

                if ($event_id == 'all') {
                    //do not filter it by an event
                } else {
                    $event = new TC_Event($event_id);
                    $args['meta_key'] = 'event_id';
                    $args['meta_value'] = $event_id;
                }

                $ticket_instances = get_posts($args);

                $tickets_sold = 0;

                foreach ($ticket_instances as $ticket_instance_id => $order_id) {

                    $order_status = get_post_status($order_id);

                    if ($order_status == 'order_paid') {
                        $order_is_paid = true;
                    } else {
                        $order_is_paid = false;
                    }

                    //if ($order_is_paid == false) {
                    $order_is_paid = apply_filters('tc_order_is_paid', $order_is_paid, $order_id);
                    //}

                    if ($order_is_paid) {
                        $tickets_sold++;
                    }

                    $checkins = get_post_meta($ticket_instance_id, 'tc_checkins', true);

                    if (isset($checkins) && is_array($checkins) && count($checkins) > 0) {
                        $event_checkedin_tickets++;
                    }
                }

                $event_tickets_total = $tickets_sold;

                $data = array(
                    'event_name' => $event_id == 'all' ? __('Multiple Events', 'tc') : stripslashes(get_the_title($event_id)),
                    'event_date_time' => $event_id == 'all' ? __('N/A', 'tc') : date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime(get_post_meta($event_id, 'event_date_time', true)), false),
                    'event_location' => $event_id == 'all' ? __('N/A', 'tc') : stripslashes(get_post_meta($event_id, 'event_location', true)),
                    //'event_logo' => $event_id == 'all' ? __('N/A', 'tc') : stripslashes(get_post_meta($event_id, 'event_logo_file_url', true)),
                    //'event_sponsors_logos' => $event_id == 'all' ? __('N/A', 'tc') : stripslashes(get_post_meta($event_id, 'sponsors_logo_file_url', true)),
                    'sold_tickets' => $event_tickets_total,
                    'checked_tickets' => $event_checkedin_tickets,
                    'pass' => true
                );

                $json = json_encode(apply_filters('tc_get_event_essentials_data_output', $data, $event_id, $this->get_api_key_id()));

                if ($echo) {
                    echo $json;
                    exit;
                } else {
                    return $json;
                }
            }
        }

        function get_event_essentials_oldest($echo = true) {

            if ($this->get_api_key_id()) {

                $event_id = $this->get_api_event();

                $event_ticket_types = array();

                if ($event_id == 'all') {
                    $wp_events_search = new TC_Events_Search('', '', -1, 'publish');
                    foreach ($wp_events_search->get_results() as $event) {
                        $event_obj = new TC_Event($event->ID);
                        $event_ticket_types = array_merge($event_ticket_types, $event_obj->get_event_ticket_types());
                    }
                } else {
                    $event = new TC_Event($event_id);
                    $event_ticket_types = $event->get_event_ticket_types();
                }

                $event_tickets_total = 0;
                $event_checkedin_tickets = 0;

                $meta_query = array('relation' => 'OR');

                foreach ($event_ticket_types as $event_ticket_type) {
                    $meta_query[] = array(
                        'key' => 'ticket_type_id',
                        'value' => (string) $event_ticket_type,
                    );
                }

                $args = array(
                    'post_type' => 'tc_tickets_instances',
                    'post_status' => 'publish',
                    'posts_per_page' => -1,
                    'meta_query' => $meta_query,
                    'fields' => 'id=>parent'//array('ID', 'post_parent')
                );

                $ticket_instances = get_posts($args);


                $tickets_sold = 0;

                foreach ($ticket_instances as $ticket_instance_id => $order_id) {
                    $order_status = get_post_status($order_id);

                    if ($order_status == 'order_paid') {
                        $order_is_paid = true;
                    } else {
                        $order_is_paid = false;
                    }

                    $order_is_paid = apply_filters('tc_order_is_paid', $order_is_paid, $order_id);

                    if ($order_is_paid) {
                        $tickets_sold++;
                    }

                    $checkins = get_post_meta($ticket_instance_id, 'tc_checkins', true);

                    if (isset($checkins) && is_array($checkins) && count($checkins) > 0) {
                        $event_checkedin_tickets++;
                    }
                }

                $event_tickets_total = $tickets_sold;

                $data = array(
                    'event_name' => $event_id == 'all' ? __('Multiple Events', 'tc') : stripslashes($event->details->post_title),
                    'event_date_time' => $event_id == 'all' ? __('N/A', 'tc') : date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($event->details->event_date_time), false),
                    'event_location' => $event_id == 'all' ? __('N/A', 'tc') : stripslashes($event->details->event_location),
                    //'event_logo' => $event_id == 'all' ? __('N/A', 'tc') : stripslashes($event->details->event_logo_file_url),
                    //'event_sponsors_logos' => $event_id == 'all' ? __('N/A', 'tc') : stripslashes($event->details->sponsors_logo_file_url),
                    'sold_tickets' => $event_tickets_total,
                    'checked_tickets' => $event_checkedin_tickets,
                    'pass' => true
                );

                $json = json_encode(apply_filters('tc_get_event_essentials_data_output', $data, $event_id));

                if ($echo) {
                    echo $json;
                    exit;
                } else {
                    return $json;
                }
            }
        }

        function get_number_of_allowed_checkins_for_ticket_instance($ticket_id = false, $ticket_type = false) {

            if (!$ticket_id || !$ticket_type) {//ticket instance id and ticket type object are required so we cannot proceed without them
                return 0;
            }

            $ticket_type_id = $ticket_type_id = apply_filters('tc_ticket_type_id', $ticket_type->details->ID);

            $checkins_data = get_post_meta($ticket_id, 'tc_checkins', true);

            $pass_checkin_status = apply_filters('tc_checkin_status_title_get_number_of_allowed_checkins_for_ticket_instance', 'Pass');
            $checkins = 0;

            if (isset($checkins_data) && count($checkins_data) > 0 && is_array($checkins_data)) {
                foreach ($checkins_data as $check_in) {
                    if ($check_in['status'] == $pass_checkin_status) {
                        $checkins++;
                    }
                }
            }

            $available_checkins = get_post_meta($ticket_type_id, 'available_checkins_per_ticket', true);
            $alternate_available_checkins = get_post_meta($ticket_type_id, '_available_checkins_per_ticket', true);

            $available_checkins = !empty($available_checkins) ? $available_checkins : $alternate_available_checkins;
            $available_checkins = (is_numeric($available_checkins) ? $available_checkins : 9999); //9999 means unlimited check-ins but it's set for easier comparation

            if ($available_checkins > 0) {
                $allowed_checkins = $available_checkins - $checkins;
            } else {
                $allowed_checkins = 0;
            }

            return $allowed_checkins;
        }

        function ticket_checkins($echo = true) {
            if ($this->get_api_key_id()) {

                $ticket_id = ticket_code_to_id($this->ticket_code);

                $check_ins = get_post_meta($ticket_id, 'tc_checkins', true);

                $rows = array();

                $check_ins = apply_filters('tc_ticket_checkins_array', $check_ins);

                if (isset($check_ins) && count($check_ins) > 0 && is_array($check_ins)) {
                    foreach ($check_ins as $check_in) {
                        $r['date_checked'] = tc_format_date($check_in['date_checked']);
                        $r['status'] = apply_filters('tc_check_in_status_title', $check_in['status']);
                        $rows[] = array('data' => $r);
                    }
                }

                echo json_encode($rows);
                exit;
            }
        }

        /**
         * @param type $digits
         * @return boolean         
         * 
         * check if the number is from ean13 barcode
        */
        function tc_ean13_check_digit($digits){
            
            $digits_new = $this->tc_ean13_convert($digits);
            
            //first change digits to a string so that we can access individual numbers
            $digits_new =(string)$digits_new;
            
            // 1. Add the values of the digits in the even-numbered positions: 2, 4, 6, etc.
            $even_sum = $digits_new{1} + $digits_new{3} + $digits_new{5} + $digits_new{7} + $digits_new{9} + $digits_new{11};
            // 2. Multiply this result by 3.
            $even_sum_three = $even_sum * 3;
            // 3. Add the values of the digits in the odd-numbered positions: 1, 3, 5, etc.
            $odd_sum = $digits_new{0} + $digits_new{2} + $digits_new{4} + $digits_new{6} + $digits_new{8} + $digits_new{10};
            // 4. Sum the results of steps 2 and 3.
            $total_sum = $even_sum_three + $odd_sum;
            // 5. The check character is the smallest number which, when added to the result in step 4,  produces a multiple of 10.
            $next_ten = (ceil($total_sum/10))*10;
            $check_digit = $next_ten - $total_sum;
            $tc_new_digit = $digits_new . $check_digit;
            
            if($tc_new_digit == $digits) {
                return true;
            } else {
                return false;
            }
        }
        
        
        /*
         *convert ean13 to normal barcode 
        */
        function tc_ean13_convert($digits){
            $digits_count = strlen($digits);
            $check_first = substr($digits, 0, 1);
            if($check_first !== 0 && $digits_count == 13) {
                $check_first = '';
            } 
            $digits_new = substr($digits, 0, -1);
            $digits_new = $check_first.$digits_new;
            return $digits_new;
        }
        
        function ticket_checkin($echo = true) {

            if ($this->get_api_key_id()) {

                $api_key_id = $this->get_api_key_id();
                $tc_code_lenght = strlen($this->ticket_code);
                $ticket_code = $this->ticket_code;
                
                //ean 13 contains 12 numberic characters
                if($tc_code_lenght == 12 || $tc_code_lenght == 13 && is_numeric($this->ticket_code)){                                    
                    $tc_check_ean13 = $this->tc_ean13_check_digit($this->ticket_code);
                    if($tc_check_ean13 == true) {
                        $ticket_code = $this->tc_ean13_convert($this->ticket_code);
                    }                    
                }
                
                $ticket_id = ticket_code_to_id($ticket_code);

                if ($ticket_id) {

                    $ticket_instance = new TC_Ticket_Instance($ticket_id);
                    $ticket_type_id = apply_filters('tc_ticket_type_id', $ticket_instance->details->ticket_type_id);

                    $ticket_type = new TC_Ticket($ticket_type_id);
                    $order = new TC_Order($ticket_instance->details->post_parent);

                    if ($order->details->post_status == 'order_paid') {
                        $order_is_paid = true;
                    } else {
                        $order_is_paid = false;
                    }

                    $order_is_paid = apply_filters('tc_order_is_paid', $order_is_paid, $order->details->ID);

                    if ($order_is_paid) {
                        //all good, continue with check-in process
                    } else {
                        if ($echo) {
                            _e('Ticket does not exist', 'tc');
                            exit;
                        } else {
                            return 11;
                        }
                    }

                    $ticket_event_id = $ticket_type->get_ticket_event($ticket_type_id);
                } else {
                    if ($echo) {
                        _e('Ticket does not exist', 'tc');
                        exit;
                    } else {
                        return 11;
                    }
                }

                if ($this->get_api_event() != $ticket_event_id) {//Only API key for the parent event can check-in this ticket
                    if ($this->get_api_event() !== 'all') {
                        if ($echo) {
                            _e('Insufficient permissions. This API key cannot check in this ticket.', 'tc');
                        } else {
                            return 403; //error code for incufficient persmissions
                        }
                        exit;
                    }
                }

                $check_ins = $ticket_instance->get_ticket_checkins();

                /* $num_of_check_ins = apply_filters('tc_num_of_checkins', (is_array($check_ins) ? count($check_ins) : 0));

                  $available_checkins = get_post_meta($ticket_type_id, 'available_checkins_per_ticket', true);
                  $alternate_available_checkins = get_post_meta($ticket_type_id, '_available_checkins_per_ticket', true);

                  $available_checkins = !empty($available_checkins) ? $available_checkins : $alternate_available_checkins;
                  $available_checkins = (is_numeric($available_checkins) ? $available_checkins : 9999); //9999 means unlimited check-ins but it's set for easier comparation
                 */
                $allowed_checkins = $this->get_number_of_allowed_checkins_for_ticket_instance($ticket_id, $ticket_type);

                if ($allowed_checkins > 0) {
                    $check_in_status = apply_filters('tc_checkin_status_name', true);
                    $check_in_status_bool = true;
                    do_action('tc_check_in_notification', $ticket_id);
                } else {
                    $check_in_status = apply_filters('tc_checkin_status_name', false);
                    $check_in_status_bool = false;
                }
                /* if ($available_checkins > $num_of_check_ins) {
                  $check_in_status = apply_filters('tc_checkin_status_name', true);
                  $check_in_status_bool = true;
                  do_action('tc_check_in_notification', $ticket_id);
                  } else {
                  $check_in_status = apply_filters('tc_checkin_status_name', false);
                  $check_in_status_bool = false;
                  } */

                if (!TC_Ticket::is_checkin_available($ticket_type_id, $order)) {
                    $check_in_status = apply_filters('tc_checkin_status_name', false);
                    $check_in_status_bool = false;
                }


                $new_checkins = array();

                if (is_array($check_ins)) {
                    foreach ($check_ins as $check_in) {
                        $new_checkins[] = $check_in;
                    }
                }

                $new_checkin = array(
                    "date_checked" => isset($_GET['timestamp']) ? $this->right_timestamp($_GET['timestamp']) : time(),
                    "status" => $check_in_status ? apply_filters('tc_checkin_status_name', 'Pass') : apply_filters('tc_checkin_status_name', 'Fail'),
                    "api_key_id" => $api_key_id
                );

                $new_checkins[] = apply_filters('tc_new_checkin_array', $new_checkin);

                do_action('tc_before_checkin_array_update', $new_checkins);

                $new_checkins = apply_filters('tc_all_attendee_checkin_records', $new_checkins);
                
                update_post_meta($ticket_id, "tc_checkins", $new_checkins);

                do_action('tc_after_checkin_array_update');

                $payment_date = apply_filters('tc_checkin_payment_date', tc_format_date(apply_filters('tc_ticket_checkin_order_date', $order->details->tc_order_date, $order->details->ID))); //date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $order->details->tc_order_date, false )

                if ($payment_date == '') {
                    $payment_date = 'N/A';
                }

                $name = apply_filters('tc_checkin_owner_name', $ticket_instance->details->first_name . ' ' . $ticket_instance->details->last_name);

                if (trim($name) == '') {
                    $name = 'N/A';
                }

                $address = apply_filters('tc_checkin_owner_address', $ticket_instance->details->address);

                if ($address == '') {
                    $address = 'N/A';
                }

                $city = apply_filters('tc_checkin_owner_city', $ticket_instance->details->city);

                if ($city == '') {
                    $city = 'N/A';
                }

                $state = apply_filters('tc_checkin_owner_state', $ticket_instance->details->state);

                if ($state == '') {
                    $state = 'N/A';
                }

                $country = apply_filters('tc_checkin_owner_country', $ticket_instance->details->country);

                if ($country == '') {
                    $country = 'N/A';
                }

                $data = array(
                    'status' => $check_in_status_bool, //false
                    'previous_status' => '',
                    'pass' => true, //api is valid
                    'name' => $name,
                    'payment_date' => $payment_date,
                    'address' => $address,
                    'city' => $city,
                    'state' => $state,
                    'country' => $country,
                    'checksum' => $this->ticket_code
                );

                if (isset($_GET['timestamp'])) {
                    $data['timestamp'] = $this->right_timestamp($_GET['timestamp']);
                }

                $buyer_full_name = isset($order->details->tc_cart_info['buyer_data']['first_name_post_meta']) ? ($order->details->tc_cart_info['buyer_data']['first_name_post_meta'] . ' ' . $order->details->tc_cart_info['buyer_data']['last_name_post_meta']) : '';
                $buyer_email = isset($order->details->tc_cart_info['buyer_data']['email_post_meta']) ? $order->details->tc_cart_info['buyer_data']['email_post_meta'] : '';

                $data['custom_fields'] = array(
                    array(apply_filters('tc_ticket_checkin_custom_field_title', 'Ticket Type'), apply_filters('tc_checkout_owner_info_ticket_title', $ticket_type->details->post_title, $ticket_type->details->ID, array(), $ticket_instance->details->ID)),
                    array(apply_filters('tc_ticket_checkin_custom_field_title', 'Buyer Name'), apply_filters('tc_ticket_checkin_buyer_full_name', $buyer_full_name, $order->details->ID)),
                    array(apply_filters('tc_ticket_checkin_custom_field_title', 'Buyer E-mail'), apply_filters('tc_ticket_checkin_buyer_email', $buyer_email, $order->details->ID)),
                );

                $data['custom_fields'] = apply_filters('tc_checkin_custom_fields', $data['custom_fields'], $ticket_instance->details->ID, $ticket_event_id, $order, $ticket_type);

                $data = apply_filters('tc_checkin_output_data', $data, $api_key_id);

                if ($echo === true || $echo == 'echo') {
                    echo json_encode($data);
                    exit;
                } else {
                    return $data;
                }
            }
        }

        function tickets_info($echo = true) {
            $start = microtime(true);
            if ($this->get_api_key_id()) {

                global $wpdb;

                $event_id = $this->get_api_event();

                if ($event_id == 'all') {
                    //show all events
                    $event_id_query = '';
                } else {
                    $event_id_query = 'AND wp_pm.meta_value = ' . (int) $event_id;
                }

                $order_post_statuses = apply_filters('tc_paid_post_statuses', array('order_paid'));

                $having_statuses = "(";
                foreach ($order_post_statuses as $order_post_statuses_key => $order_post_statuses_val) {
                    $order_post_statuses[$order_post_statuses_key] = $order_post_statuses_val;
                    $having_statuses .= "parent_status = '" . $order_post_statuses_val . "' OR ";
                }

                $having_statuses = rtrim($having_statuses, 'OR ');

                $having_statuses .= ")";

                $offset = (( $this->page_number - 1 ) * $this->results_per_page );

                $results = $wpdb->get_results("
    SELECT 
    ID, 
    post_parent as parent,
    post_status,
    (SELECT post_status FROM {$wpdb->prefix}posts wp2 WHERE wp2.ID = wp.post_parent) as parent_status
    FROM {$wpdb->prefix}posts wp, {$wpdb->prefix}postmeta wp_pm 
    WHERE post_type = 'tc_tickets_instances'
    AND wp.ID = wp_pm.post_id
    AND wp_pm.meta_key = 'event_id'
    {$event_id_query}
    AND post_status = 'publish'
    GROUP BY wp.ID
    HAVING {$having_statuses}
    ORDER BY post_date DESC
    LIMIT {$this->results_per_page} OFFSET {$offset};", ARRAY_A);

                $results_count = 0;

                foreach ($results as $result_id) {
                    $result_id = $result_id['ID'];
                    $ticket_code = get_post_meta($result_id, 'ticket_code', true);
                    $ticket_first_name = get_post_meta($result_id, 'first_name', true);
                    $ticket_last_name = get_post_meta($result_id, 'last_name', true);
                    $attendee_email = get_post_meta($result_id, 'owner_email', true);
                    $ticket_type_id = get_post_meta($result_id, 'ticket_type_id', true);
                    $ticket_post_parent = wp_get_post_parent_id($result_id);

                    $ticket_type = new TC_Ticket($ticket_type_id);

                    $order = new TC_Order($ticket_post_parent);
                    
                    
                    /* OLD */
                    $check_ins = get_post_meta($result_id, 'tc_checkins', true);
                    $checkin_date = '';

                    if (!empty($check_ins)) {
                        foreach ($check_ins as $check_in) {
                            $checkin_date = tc_format_date($check_in['date_checked']);
                        }
                    }

                    $r['date_checked'] = $checkin_date;
                    
                    if (apply_filters('tc_bridge_for_woocommerce_is_active', false) == true && is_plugin_active('woocommerce/woocommerce.php') && get_post_type( $order->details->ID ) == 'shop_order' ) {
                        $wc_post = get_post( $result_id, 'OBJECT' );                        
                        $format = get_option('date_format') . ' - ' . get_option('time_format');;
                        $r['payment_date'] = date($format, strtotime( $wc_post->post_date ));
                    } else {
                        $r['payment_date'] = tc_format_date($order->details->tc_order_date); //date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $order->details->post_modified ), false )                        
                    }
                    
                    
                    $r['transaction_id'] = $ticket_code;
                    $r['checksum'] = $ticket_code;

                    $buyer_full_name = isset($order->details->tc_cart_info['buyer_data']['first_name_post_meta']) ? ($order->details->tc_cart_info['buyer_data']['first_name_post_meta'] . ' ' . $order->details->tc_cart_info['buyer_data']['last_name_post_meta']) : '';
                    $buyer_email = isset($order->details->tc_cart_info['buyer_data']['email_post_meta']) ? $order->details->tc_cart_info['buyer_data']['email_post_meta'] : '';

                    if (!empty($ticket_first_name) && !empty($ticket_last_name)) {
                        $r['buyer_first'] = $ticket_first_name;
                        $r['buyer_last'] = $ticket_last_name;
                    } else {
                        $r['buyer_first'] = apply_filters('tc_ticket_checkin_buyer_first_name', $order->details->tc_cart_info['buyer_data']['first_name_post_meta'], $order->details->ID);
                        $r['buyer_last'] = apply_filters('tc_ticket_checkin_buyer_last_name', $order->details->tc_cart_info['buyer_data']['last_name_post_meta'], $order->details->ID);
                    }

                    $r['custom_fields'] = array(
                        array(apply_filters('tc_ticket_checkin_custom_field_title', 'Ticket Type'), apply_filters('tc_checkout_owner_info_ticket_title', $ticket_type->details->post_title, $ticket_type->details->ID, array(), $result_id)),
                        array(apply_filters('tc_ticket_checkin_custom_field_title', 'Buyer Name'), apply_filters('tc_ticket_checkin_buyer_full_name', $buyer_full_name, $order->details->ID)),
                        array(apply_filters('tc_ticket_checkin_custom_field_title', 'Buyer E-mail'), apply_filters('tc_ticket_checkin_buyer_email', $buyer_email, $order->details->ID)),
                    );

                    if (isset($attendee_email) && !empty($attendee_email)) {
                        $r['custom_fields'][] = array(apply_filters('tc_ticket_checkin_custom_field_title', 'Attendee E-mail'), apply_filters('tc_ticket_checkin_attendee_email', $attendee_email, $result_id));
                    }

                    $r = apply_filters('tc_checkins_row', $r, $result_id, $event_id, $order, $ticket_type);

                    $r['custom_fields'] = apply_filters('tc_checkin_custom_fields', $r['custom_fields'], $result_id, $event_id, $order, $ticket_type);
                    $r['custom_field_count'] = count($r['custom_fields']);
                    $r['allowed_checkins'] = $this->get_number_of_allowed_checkins_for_ticket_instance($result_id, $ticket_type);

                    $rows[] = array('data' => $r);

                    $results_count++;
                }

                $additional['results_count'] = $results_count;
                $additional['execution_time'] = microtime(true) - $start;
                $rows[] = array('additional' => $additional);
                echo json_encode($rows);
                exit;
            }
        }

        function tickets_info_old($echo = true) {
            $start = microtime(true);
            if ($this->get_api_key_id()) {

                global $wpdb;

                $event_id = $this->get_api_event();

                $event_ticket_types = array();

                if ($event_id == 'all') {
                    $wp_events_search = new TC_Events_Search('', '', -1, 'publish');
                    foreach ($wp_events_search->get_results() as $event) {
                        $event_obj = new TC_Event($event->ID);
                        $event_ticket_types = array_merge($event_ticket_types, $event_obj->get_event_ticket_types());
                    }
                } else {
                    $event = new TC_Event($event_id);
                    $event_ticket_types = $event->get_event_ticket_types();
                }

                $meta_query = array('relation' => 'OR');

                foreach ($event_ticket_types as $event_ticket_type) {
                    if ($this->keyword == '' || empty($this->keyword)) {
                        $meta_query[] = array(
                            'key' => 'ticket_type_id',
                            'value' => (string) $event_ticket_type,
                        );
                    } else {
                        $meta_query[] = array(
                            'key' => 'first_name',
                            'value' => $this->keyword,
                            'compare' => 'LIKE'
                        );
                        $meta_query[] = array(
                            'key' => 'last_name',
                            'value' => $this->keyword,
                            'compare' => 'LIKE'
                        );
                        $meta_query[] = array(
                            'key' => 'ticket_code',
                            'value' => $this->keyword,
                            'compare' => 'LIKE'
                        );
                    }
                }

                $order_post_types = apply_filters('tc_order_post_type_name', array('tc_orders'));

                foreach ($order_post_types as $order_post_type_key => $order_post_type_val) {
                    $order_post_types[$order_post_type_key] = $order_post_type_val;
                }

                $order_post_statuses = apply_filters('tc_paid_post_statuses', array('order_paid'));

                foreach ($order_post_statuses as $order_post_statuses_key => $order_post_statuses_val) {
                    $order_post_statuses[$order_post_statuses_key] = $order_post_statuses_val;
                }

                $results_args = array(
                    'post_type' => $order_post_types,
                    'post_status' => $order_post_statuses,
                    'posts_per_page' => -1,
                    'fields' => 'ids'
                );

                $results = get_posts($results_args);

                $paid_orders_ids = array();

                foreach ($results as $result_id) {
                    $paid_orders_ids[] = $result_id;
                }

                if (!empty($paid_orders_ids)) {
                    $args = array(
                        'post_type' => 'tc_tickets_instances',
                        'post_status' => 'publish',
                        'meta_query' => $meta_query,
                        'posts_per_page' => $this->results_per_page,
                        'offset' => (( $this->page_number - 1 ) * $this->results_per_page ),
                        'post_parent__in' => $paid_orders_ids,
                        'fields' => 'ids'
                    );

                    $results = get_posts($args);
                } else {
                    $results = array();
                }

                $results_count = 0;

                foreach ($results as $result_id) {
                    $ticket_code = get_post_meta($result_id, 'ticket_code', true);
                    $ticket_first_name = get_post_meta($result_id, 'first_name', true);
                    $ticket_last_name = get_post_meta($result_id, 'last_name', true);
                    $ticket_type_id = get_post_meta($result_id, 'ticket_type_id', true);
                    $ticket_post_parent = wp_get_post_parent_id($result_id);

                    $ticket_type = new TC_Ticket($ticket_type_id);

                    $order = new TC_Order($ticket_post_parent);

                    /* OLD */
                    $check_ins = get_post_meta($result_id, 'tc_checkins', true);
                    $checkin_date = '';

                    if (!empty($check_ins)) {
                        foreach ($check_ins as $check_in) {
                            $checkin_date = tc_format_date($check_in['date_checked']);
                        }
                    }

                    $r['date_checked'] = $checkin_date;

                    $r['payment_date'] = tc_format_date(strtotime($order->details->post_modified)); //date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $order->details->post_modified ), false )
                    $r['transaction_id'] = $ticket_code;
                    $r['checksum'] = $ticket_code;

                    $buyer_full_name = isset($order->details->tc_cart_info['buyer_data']['first_name_post_meta']) ? ($order->details->tc_cart_info['buyer_data']['first_name_post_meta'] . ' ' . $order->details->tc_cart_info['buyer_data']['last_name_post_meta']) : '';
                    $buyer_email = isset($order->details->tc_cart_info['buyer_data']['email_post_meta']) ? $order->details->tc_cart_info['buyer_data']['email_post_meta'] : '';

                    if (!empty($ticket_first_name) && !empty($ticket_last_name)) {
                        $r['buyer_first'] = $ticket_first_name;
                        $r['buyer_last'] = $ticket_last_name;
                    } else {
                        $r['buyer_first'] = apply_filters('tc_ticket_checkin_buyer_first_name', $order->details->tc_cart_info['buyer_data']['first_name_post_meta'], $order->details->ID);
                        $r['buyer_last'] = apply_filters('tc_ticket_checkin_buyer_last_name', $order->details->tc_cart_info['buyer_data']['last_name_post_meta'], $order->details->ID);
                    }

                    $r['custom_fields'] = array(
                        array(apply_filters('tc_ticket_checkin_custom_field_title', 'Ticket Type'), apply_filters('tc_checkout_owner_info_ticket_title', $ticket_type->details->post_title, $ticket_type->details->ID, array(), $result_id)),
                        array(apply_filters('tc_ticket_checkin_custom_field_title', 'Buyer Name'), apply_filters('tc_ticket_checkin_buyer_full_name', $buyer_full_name, $order->details->ID)),
                        array(apply_filters('tc_ticket_checkin_custom_field_title', 'Buyer E-mail'), apply_filters('tc_ticket_checkin_buyer_email', $buyer_email, $order->details->ID)),
                    );

                    $r['custom_fields'] = apply_filters('tc_checkin_custom_fields', $r['custom_fields'], $result_id, $event_id, $order, $ticket_type);
                    $r['custom_field_count'] = count($r['custom_fields']);
                    $r['allowed_checkins'] = $this->get_number_of_allowed_checkins_for_ticket_instance($result_id, $ticket_type);

                    $rows[] = array('data' => $r);

                    $results_count++;
                }

                $additional['results_count'] = $results_count;
                $additional['execution_time'] = microtime(true) - $start;
                $rows[] = array('additional' => $additional);
                echo json_encode($rows);

                exit;
            }
        }

    }

}
?>