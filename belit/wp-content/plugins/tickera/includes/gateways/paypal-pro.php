<?php
/*
  PayPal PRO - Payment Gateway
 */

class TC_Gateway_PayPal_Pro extends TC_Gateway_API {

    var $plugin_name = 'paypal_pro';
    var $admin_name = '';
    var $public_name = '';
    var $method_img_url = '';
    var $admin_img_url = '';
    var $force_ssl;
    var $ipn_url;
    var $currency;
    var $currencies = array();
    var $api_version = '85.0';
    var $api_endpoint = '';
    var $sandbox = false;
    var $api_username = '';
    var $api_password = '';
    var $api_signature = '';
    var $automatically_activated = false;
    var $skip_payment_screen = false;

    //Support for older payment gateway API
    function on_creation() {
        $this->init();
    }

    function init() {
        global $tc;

        $this->admin_name = __('PayPal PRO', 'tc');
        $this->public_name = __('Credit Card', 'tc');

        $this->method_img_url = apply_filters('tc_gateway_method_img_url', $tc->plugin_url . 'images/gateways/paypal-pro.png', $this->plugin_name);
        $this->admin_img_url = apply_filters('tc_gateway_admin_img_url', $tc->plugin_url . 'images/gateways/small-paypal-pro.png', $this->plugin_name);

        $this->sandbox = $this->get_option('is_ssl', '0');
        $this->api_endpoint = $this->sandbox == '0' ? 'https://api-3t.sandbox.paypal.com/nvp' : 'https://api-3t.paypal.com/nvp';
        $this->api_username = $this->get_option('api_username');
        $this->api_password = $this->get_option('api_password');
        $this->api_signature = $this->get_option('api_signature');

        $this->force_ssl = $this->get_option('is_ssl', '0') == '1' ? true : false;
        $this->currency = $this->get_option('currency', 'USD');

        $currencies = array(
            "AUD" => __('AUD - Australian Dollar', 'tc'),
            "CAD" => __('CAD - Canadian Dollar', 'tc'),
            "CZK" => __('CZK - Czech Koruna', 'tc'),
            "DKK" => __('DKK - Danish Krone', 'tc'),
            "EUR" => __('EUR - Euro', 'tc'),
            "HKD" => __('HKD - Hong Kong Dollar', 'tc'),
            "HUF" => __('HUF - Hungarian Forint', 'tc'),
            "JPY" => __('JPY - Japanese Yen', 'tc'),
            "NOK" => __('NOK - Norwegian Krone', 'tc'),
            "NZD" => __('NZD - New Zealand Dollar', 'tc'),
            "PLN" => __('PLN - Polish Zloty', 'tc'),
            "GBP" => __('GBP - British Pound', 'tc'),
            "SGD" => __('SGD - Singapore Dollar', 'tc'),
            "SEK" => __('SEK - Swedish Krona', 'tc'),
            "CHF" => __('CHF - Swiss Franc', 'tc'),
            "USD" => __('USD - U.S. Dollar', 'tc'),
        );

        $this->currencies = $currencies;

        add_action('wp_enqueue_scripts', array(&$this, 'enqueue_scripts'));
    }

    function enqueue_scripts() {
        global $tc, $wp;

        if ($this->is_active() && $this->is_payment_page()) {
            wp_enqueue_script('tc-paypal-pro', $tc->plugin_url . '/includes/gateways/paypal-pro/paypal-pro.js', array('jquery'));
        }
    }

    function payment_form($cart) {
        global $tc;
        $this->maybe_start_session();

        $content = '';

        $content .= '<div id="paypal_checkout_errors"></div>';

        $content .= '<table class="tc_cart_billing" cellpadding="10">
        <thead><tr>
          <th colspan="2">' . __('Enter Your Credit Card Information:', 'tc') . '</th>
        </tr></thead>
        <tbody>
          <tr>
          <td>' . __('Cardholder First Name:', 'tc') . '</td>
          <td><input class="card-holdername tickera-input-field" name="FIRSTNAME" type="text" value="' . esc_attr($this->buyer_info('first_name')) . '" /> </td>
          </tr>';

        $content .= '<tr>
          <td>' . __('Cardholder Last Name:', 'tc') . '</td>
          <td><input class="card-holdername tickera-input-field" name="LASTNAME" type="text" value="' . esc_attr($this->buyer_info('last_name')) . '" /> </td>
          </tr>';

        $content .= '<tr>
          <td>' . __('Street:', 'tc') . '</td>
          <td><input class="card-street tickera-input-field" name="STREET" type="text" value="" /> </td>
          </tr>';

        $content .= '<tr>
          <td>' . __('City:', 'tc') . '</td>
          <td><input class="card-city tickera-input-field" name="CITY" type="text" value="' . esc_attr(tc_get_client_city()) . '" /> </td>
          </tr>';

        $content .= '<tr>
          <td>' . __('State or province:', 'tc') . '</td>
          <td><input class="card-state tickera-input-field" name="STATE" type="text" value="" /> </td>
          </tr>';

        $content .= '<tr>
          <td>' . __('Country:', 'tc') . '</td>
          <td>' . tc_countries('', 'COUNTRYCODE') . '</td>
          </tr>';

        $content .= '<tr>
          <td>' . __('ZIP Code:', 'tc') . '</td>
          <td><input class="card-state tickera-input-field" name="ZIP" type="text" value="' . esc_attr(tc_get_client_zip()) . '" /> </td>
          </tr>';

        $content .= '<tr>';
        $content .= '<td>';
        $content .= __('Card Number', 'tc');
        $content .= '</td>';
        $content .= '<td>';
        $content .= '<input type="text" name="ACCT" autocomplete="off" class="card-number tickera-input-field"/>';
        $content .= '</td>';
        $content .= '</tr>';
        $content .= '<tr>';
        $content .= '<td>';
        $content .= __('Expiration:', 'tc');
        $content .= '</td>';
        $content .= '<td>';
        $content .= '<select class="card-expiry-month" name="CARD_MONTH">';
        $content .= tc_months_dropdown();
        $content .= '</select>';
        $content .= '<span> / </span>';
        $content .= '<select class="card-expiry-year" name="CARD_YEAR">';
        $content .= tc_years_dropdown('', true);
        $content .= '</select>';
        $content .= '</td>';
        $content .= '</tr>';
        $content .= '<tr>';
        $content .= '<td>';
        $content .= __('CVC:', 'tc');
        $content .= '</td>';
        $content .= '<td>';
        $content .= '<input type="text" size="4" autocomplete="off" name="CCV2" class="card-cvc tickera-input-field" />';
        $content .= '<input type="hidden" name="CURRENCYCODE" value="' . $this->currency . '" />';
        $content .= '<input type="hidden" class="AMT" value="' . $this->total() . '" />';
        $content .= '</td>';
        $content .= '</tr>';
        $content .= '</table>';
        $content .= '<span id="paypal_processing" style="display: none;float: right;"><img src="' . $tc->plugin_url . 'images/loading.gif" /> ' . __('Processing...', 'tc') . '</span>';
        return $content;
    }

    function process_payment($cart) {
        global $tc;

        tc_final_cart_check($cart);
        
        $this->maybe_start_session();
        $this->save_cart_info();

        $order_id = $tc->generate_order_id();

        $request_params = array
            (
            'METHOD' => 'DoDirectPayment',
            'USER' => $this->api_username,
            'PWD' => $this->api_password,
            'SIGNATURE' => $this->api_signature,
            'VERSION' => $this->api_version,
            'PAYMENTACTION' => 'Sale',
            'IPADDRESS' => $_SERVER['REMOTE_ADDR'],
            'ACCT' => $_POST['ACCT'],
            'EXPDATE' => $_POST['CARD_MONTH'] . $_POST['CARD_YEAR'],
            'CVV2' => $_POST['CCV2'],
            'FIRSTNAME' => $_POST['FIRSTNAME'],
            'LASTNAME' => $_POST['LASTNAME'],
            'STREET' => $_POST['STREET'],
            'CITY' => $_POST['CITY'],
            'STATE' => $_POST['STATE'],
            'COUNTRYCODE' => $_POST['COUNTRYCODE'],
            'ZIP' => $_POST['ZIP'],
            'AMT' => $this->total(),
            'CURRENCYCODE' => $_POST['CURRENCYCODE'],
            'DESC' => $this->cart_items(),
            'BUTTONSOURCE' => 'Tickera_SP'
        );

        $nvp_string = '';

        foreach ($request_params as $var => $val) {
            $nvp_string .= '&' . $var . '=' . urlencode($val);
        }

        $response = wp_remote_post($this->api_endpoint, array(
            'timeout' => 120,
            'httpversion' => '1.1',
            'body' => $request_params,
            'user-agent' => $tc->title,
            'sslverify' => false,
                ));

        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            $_SESSION['tc_gateway_error'] = __("Something went wrong:", 'tc') . $error_message;
            ob_start();
            @wp_redirect($tc->get_payment_slug(true));
            tc_js_redirect($tc->get_payment_slug(true));
            exit;
        } else {
            $nvp_response = $this->NVPToArray($response['body']);

            if ($nvp_response['ACK'] == 'Success' || $nvp_response['ACK'] == 'SuccessWithWarning') {
                //setup our payment details

                $payment_info = array();
                $payment_info['method'] = __('Credit Card', 'tc');
                $payment_info['transaction_id'] = $nvp_response['TRANSACTIONID'];
                $payment_info = $this->save_payment_info();

                $paid = true;
                $order = $tc->create_order($order_id, $this->cart_contents(), $this->cart_info(), $payment_info, $paid);

                ob_start();
                @wp_redirect($tc->get_confirmation_slug(true, $order_id));
                tc_js_redirect($tc->get_confirmation_slug(true, $order_id));
                exit;
            } else {
                $_SESSION['tc_gateway_error'] = $nvp_response['L_LONGMESSAGE0'];
                ob_start();
                @wp_redirect($tc->get_payment_slug(true));
                tc_js_redirect($tc->get_payment_slug(true));
                exit;
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
                    <?php _e("PayPal Payments Pro is an affordable website payment processing solution for businesses with more than 100+ orders/month. Our integration with PayPal PRO will appear seamlessly to your customers.", 'tc'); ?> <a href="https://www.paymill.com/en-gb/support-3/worth-knowing/pci-security/" target="_blank"><?php _e('Read More &raquo;', 'tc') ?></a>
                </span>

                <?php
                $fields = array(
                    'is_ssl' => array(
                        'title' => __('Mode', 'tc'),
                        'type' => 'select',
                        'options' => array(
                            '0' => __('Sandbox / Test', 'tc'),
                            '1' => __('Live', 'tc')
                        ),
                        'default' => '0',
                    ),
                    'api_username' => array(
                        'title' => __('API Username', 'tc'),
                        'type' => 'text',
                    ),
                    'api_password' => array(
                        'title' => __('API Password', 'tc'),
                        'type' => 'text',
                    ),
                    'api_signature' => array(
                        'title' => __('API Signature', 'tc'),
                        'type' => 'text',
                        'description' => '',
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

    function NVPToArray($NVPString) {
        $proArray = array();
        while (strlen($NVPString)) {
            // name
            $keypos = strpos($NVPString, '=');
            $keyval = substr($NVPString, 0, $keypos);
            // value
            $valuepos = strpos($NVPString, '&') ? strpos($NVPString, '&') : strlen($NVPString);
            $valval = substr($NVPString, $keypos + 1, $valuepos - $keypos - 1);
            // decoding the respose
            $proArray[$keyval] = urldecode($valval);
            $NVPString = substr($NVPString, $valuepos + 1, strlen($NVPString));
        }
        return $proArray;
    }

}

tc_register_gateway_plugin('TC_Gateway_PayPal_Pro', 'paypal_pro', __('PayPal PRO', 'tc'));
?>