<?php
/*
  PayUMoney - Payment Gateway
 */

class TC_Gateway_PayUMoney extends TC_Gateway_API {

    var $plugin_name = 'payumoney';
    var $admin_name = '';
    var $public_name = '';
    var $method_img_url = '';
    var $admin_img_url = '';
    var $force_ssl = false;
    var $ipn_url;
    var $API_Username, $API_Password, $mode, $returnURL, $API_Endpoint, $version, $locale;
    var $currencies = array();
    var $automatically_activated = false;
    var $skip_payment_screen = true;
    var $currency = 'INR';

    //Support for older payment gateway API
    function on_creation() {
        $this->init();
    }

    function init() {
        global $tc;

        $this->admin_name = __('PayUMoney', 'tc');
        $this->public_name = __('PayUMoney', 'tc');

        $this->method_img_url = apply_filters('tc_gateway_method_img_url', $tc->plugin_url . 'images/gateways/payumoney.png', $this->plugin_name);
        $this->admin_img_url = apply_filters('tc_gateway_admin_img_url', $tc->plugin_url . 'images/gateways/small-payumoney.png', $this->plugin_name);

        $this->currency = $this->get_option('currency', 'INR');
        $this->merchantid = $this->get_option('merchantid');
        $this->salt = $this->get_option('salt');
        $this->mode = $this->get_option('mode', 'sandbox');

        $this->currencies = array(
            "INR" => __('INR - Indian Rupee', 'tc'),
        );
    }

    function payment_form($cart) {
        global $tc;
        if (isset($_GET[$this->failed_slug])) {
            $_SESSION['tc_gateway_error'] = __('Payment Failed.', 'tc');
            ob_start();
            @wp_redirect($tc->get_payment_slug(true));
            tc_js_redirect($tc->get_payment_slug(true));
            exit;
        }
    }

    function process_payment($cart) {
        global $tc;

        tc_final_cart_check($cart);
        
        $payu_in_args = array();

        $this->maybe_start_session();
        $this->save_cart_info();

        $order_id = $tc->generate_order_id();

        $productinfo = sprintf(__('Order %s', 'tc'), $order_id);

        //Hash data
        $hash_data['key'] = $this->merchantid;
        $hash_data['txnid'] = substr(hash('sha256', mt_rand() . microtime()), 0, 20); // Unique alphanumeric Transaction ID
        $hash_data['amount'] = $this->total();
        $hash_data['productinfo'] = $productinfo;
        $hash_data['firstname'] = $this->buyer_info('first_name');
        $hash_data['email'] = $this->buyer_info('email');
        $hash_data['hash'] = $this->calculate_hash_before_transaction($hash_data);

        $counter = 0;

        $paid = false;

        $payment_info = $this->save_payment_info();

        $tc->create_order($order_id, $this->cart_contents(), $this->cart_info(), $payment_info, $paid);

        // PayU Args
        $payu_in_args = array(
            // Merchant details
            'key' => $this->merchantid,
            'surl' => $tc->get_confirmation_slug(true, $order_id),
            'furl' => $this->failed_url,
            'curl' => $tc->get_cancel_url($order_id),
            // Customer details
            'firstname' => $this->buyer_info('first_name'),
            'lastname' => $this->buyer_info('last_name'),
            'email' => $this->buyer_info('email'),
            // Item details
            'productinfo' => $productinfo,
            'amount' => $this->total(),
            // Pre-selection of the payment method tab
            'pg' => 'CC'
        );
        
        if ($this->mode == 'sandbox') {
            $url = 'https://test.payu.in/_payment';
        } else {
            $payu_in_args['service_provider'] = 'payu_paisa';
            $url = 'https://secure.payu.in/_payment';
        }

        $payuform = '';

        foreach ($payu_in_args as $key => $value) {
            if ($value) {
                $payuform .= '<input type="hidden" name="' . $key . '" value="' . $value . '" />' . "\n";
            }
        }

        $payuform .= '<input type="hidden" name="txnid" value="' . $hash_data['txnid'] . '" />' . "\n";
        $payuform .= '<input type="hidden" name="hash" value="' . $hash_data['hash'] . '" />' . "\n";

        // The form
        echo '<form action="' . $url . '" method="POST" name="payform" id="payform" style="display: none;">
				' . $payuform . '
				<input type="submit" value="' . __('Pay via PayUMoney', 'tc') . '" />
				
			</form>
		<script>
			document.getElementById( "payform" ).submit();
		</script>			
';
    }

    /**
     * @since 1.0.0
     */
    function calculate_hash_before_transaction($hash_data) {

        $hash_sequence = "key|txnid|amount|productinfo|firstname|email|udf1|udf2|udf3|udf4|udf5|udf6|udf7|udf8|udf9|udf10";
        $hash_vars_seq = explode('|', $hash_sequence);
        $hash_string = '';

        foreach ($hash_vars_seq as $hash_var) {
            $hash_string .= isset($hash_data[$hash_var]) ? $hash_data[$hash_var] : '';
            $hash_string .= '|';
        }

        $hash_string .= $this->salt;
        $hash_data['hash'] = strtolower(hash('sha512', $hash_string));

        return $hash_data['hash'];
    }

// End calculate_hash_before_transaction()

    /**
     * @since 1.0.0
     */
    function check_hash_after_transaction($salt, $txnRs) {

        $hash_sequence = "key|txnid|amount|productinfo|firstname|email|udf1|udf2|udf3|udf4|udf5|udf6|udf7|udf8|udf9|udf10";
        $hash_vars_seq = explode('|', $hash_sequence);
        //generation of hash after transaction is = salt + status + reverse order of variables
        $hash_vars_seq = array_reverse($hash_vars_seq);

        $merc_hash_string = $salt . '|' . $txnRs['status'];

        foreach ($hash_vars_seq as $merc_hash_var) {
            $merc_hash_string .= '|';
            $merc_hash_string .= isset($txnRs[$merc_hash_var]) ? $txnRs[$merc_hash_var] : '';
        }

        $merc_hash = strtolower(hash('sha512', $merc_hash_string));

        /* The hash is valid */
        if ($merc_hash == $txnRs['hash']) {
            return true;
        } else {
            return false;
        }
    }

// End check_hash_after_transaction()

    /**
     * @since 1.0.0
     */
    function calculate_hash_before_verification($hash_data) {

        $hash_sequence = "key|command|var1";
        $hash_vars_seq = explode('|', $hash_sequence);
        $hash_string = '';

        foreach ($hash_vars_seq as $hash_var) {
            $hash_string .= isset($hash_data[$hash_var]) ? $hash_data[$hash_var] : '';
            $hash_string .= '|';
        }

        $hash_string .= $this->salt;
        $hash_data['hash'] = strtolower(hash('sha512', $hash_string));

        return $hash_data['hash'];
    }

// End calculate_hash_before_verification()

    /**
     *  Get post data if set
     *
     * @since 1.4
     */
    function get_post_var($name) {
        if (isset($_POST[$name])) {
            return $_POST[$name];
        }
        return NULL;
    }

    /**
     *  Get get data if set
     *
     * @since 1.4
     */
    function get_get_var($name) {
        if (isset($_GET[$name])) {
            return $_GET[$name];
        }
        return NULL;
    }

    function payu_in_transaction_verification($txnid) {

        $this->verification_liveurl = 'https://info.payu.in/merchant/postservice';
        $this->verification_testurl = 'https://test.payu.in/merchant/postservice';

        $host = $this->verification_liveurl;

        if ($this->mode == 'sandbox') {
            $host = $this->verification_testurl;
        }

        $hash_data['key'] = $this->merchantid;
        $hash_data['command'] = 'verify_payment';
        $hash_data['var1'] = $txnid;
        $hash_data['hash'] = $this->calculate_hash_before_verification($hash_data);

        // Call the PayU, and verify the status
        $response = $this->send_request($host, $hash_data);

        $response = unserialize($response);

        return $response['transaction_details'][$txnid]['status'];
    }

    function send_request($host, $data) {

        $response = wp_remote_post($host, array(
            'method' => 'POST',
            'body' => $data,
            'timeout' => 70,
            'sslverify' => false
                ));

        if (is_wp_error($response)) {
            $_SESSION['tc_gateway_error'] = __('There was a problem connecting to the payment gateway.', 'tc');
            ob_start();
            @wp_redirect($tc->get_payment_slug(true));
            tc_js_redirect($tc->get_payment_slug(true));
            exit;
        }

        if (empty($response['body'])) {
            $_SESSION['tc_gateway_error'] = __('Empty PayUMoney response.', 'tc');
            ob_start();
            @wp_redirect($tc->get_payment_slug(true));
            tc_js_redirect($tc->get_payment_slug(true));
            exit;
        }

        $parsed_response = $response['body'];

        return $parsed_response;
    }

    function order_confirmation($order, $payment_info = '', $cart_info = '') {
        global $tc;

        $order = tc_get_order_id_by_name($order);
        // IPN
        if (isset($_POST['mihpayid'])) {
            if (isset($_POST['status'])) {
                if ($_POST['status'] == 'success') {
                    $paid = true;
                    $tc->update_order_payment_status($order->ID, true);
                }
            }

            $order = new TC_Order($order->ID);

            if (round($_POST['amount'], 2) >= round($order->details->tc_payment_info['total'], 2)) {
                //Amount is OK
            } else {
                $tc->update_order_status($order->details->ID, 'order_fraud');
            }
        }
    }

    function gateway_admin_settings($settings, $visible) {
        global $tc;
        ?>
        <div id="<?php echo $this->plugin_name; ?>" class="postbox" <?php echo (!$visible ? 'style="display:none;"' : ''); ?>>
            <h3 class='hndle'><span><?php printf(__('%s Settings', 'tc'), $this->admin_name); ?></span></h3>
            <div class="inside">
                <span class="description">
                    <?php _e('PayUMoney works by sending the user to <a href="https://www.payumoney.com/">PayUMoney</a> to enter their payment information. Note that PayUMoney will only take payments in Indian Rupee.', 'tc'); ?>
                </span>

                <?php
                $fields = array(
                    'mode' => array(
                        'title' => __('Mode', 'tc'),
                        'type' => 'select',
                        'options' => array(
                            'sandbox' => __('Sandbox / Test', 'tc'),
                            'live' => __('Live', 'tc')
                        ),
                        'default' => 'sandbox',
                    ),
                    'merchantid' => array(
                        'title' => __('Merchant Key', 'tc'),
                        'type' => 'text',
                    ),
                    'salt' => array(
                        'title' => __('Merchant Salt', 'tc'),
                        'type' => 'text',
                    ),
                    'currency' => array(
                        'title' => __('Currency', 'tc'),
                        'type' => 'select',
                        'options' => $this->currencies,
                        'default' => 'USD',
                    ),
                );
                $form = new TC_Form_Fields_API($fields, 'tc', 'gateways', $this->plugin_name);
                ?>
                <table class="form-table">
                    <?php $form->admin_options(); ?>
                </table>
            </div>
        </div>

        <?php
    }

}

tc_register_gateway_plugin('TC_Gateway_PayUMoney', 'payumoney', __('PayUMoney', 'tc'));
?>