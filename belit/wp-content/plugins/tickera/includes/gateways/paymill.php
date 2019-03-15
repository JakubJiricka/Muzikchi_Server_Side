<?php
/*
  Paymill - Payment Gateway
 */

class TC_Gateway_Paymill extends TC_Gateway_API {

    var $plugin_name = 'paymill';
    var $admin_name = '';
    var $public_name = '';
    var $method_img_url = '';
    var $admin_img_url = '';
    var $force_ssl = true;
    var $ipn_url;
    var $publishable_key, $private_key, $currency;
    var $currencies = array();
    var $automatically_activated = false;
    var $skip_payment_screen = false;

    //Support for older payment gateway API
    function on_creation() {
        $this->init();
    }

    function init() {
        global $tc;

        $this->admin_name = __('Paymill', 'tc');
        $this->public_name = __('Credit Card', 'tc');

        $this->method_img_url = apply_filters('tc_gateway_method_img_url', $tc->plugin_url . 'images/gateways/paymill.png', $this->plugin_name);
        $this->admin_img_url = apply_filters('tc_gateway_admin_img_url', $tc->plugin_url . 'images/gateways/small-paymill.png', $this->plugin_name);

        $this->public_key = $this->get_option('public_key');
        $this->private_key = $this->get_option('private_key');

        $this->force_ssl = $this->get_option('is_ssl', '0') == '1' ? true : false;
        $this->currency = $this->get_option('currency', 'EUR');

        $currencies = array(
            "EUR" => __('EUR - Euro', 'tc'),
            "CZK" => __('CZK - Czech Koruna', 'tc'),
            "DKK" => __('DKK - Danish Krone', 'tc'),
            "HUF" => __('HUF - Hungarian Forint', 'tc'),
            "ISK" => __('ISK - Iceland Krona', 'tc'),
            "ILS" => __('ILS - Israeli Shekel', 'tc'),
            "LVL" => __('LVL - Latvian Lat', 'tc'),
            "CHF" => __('CHF - Swiss Franc', 'tc'),
            "LTL" => __('LTL - Lithuanian Litas', 'tc'),
            "NOK" => __('NOK - Norwegian Krone', 'tc'),
            "PLN" => __('PLN - Polish Zloty', 'tc'),
            "SEK" => __('SEK - Swedish Krona', 'tc'),
            "TRY" => __('TRY - Turkish Lira', 'tc'),
            "GBP" => __('GBP - British Pound', 'tc')
        );

        $this->currencies = $currencies;

        add_action('wp_enqueue_scripts', array(&$this, 'enqueue_scripts'));
    }

    function enqueue_scripts() {
        global $tc, $wp;


        if ($this->is_active() && $this->is_payment_page()) {

            wp_enqueue_script('js-paymill', 'https://bridge.paymill.com/', array('jquery'));
            wp_enqueue_script('paymill-token', $tc->plugin_url . '/includes/gateways/paymill/paymill_token.js', array('js-paymill', 'jquery'));
            wp_localize_script('paymill-token', 'paymill_token', array(
                'public_key' => $this->public_key,
                'invalid_cc_number' => __('Please enter a valid Credit Card Number.', 'tc'),
                'invalid_expiration' => __('Please choose a valid Expiration Date.', 'tc'),
                'invalid_cvc' => __('Please enter a valid Card CVC', 'tc'),
                'expired_card' => __('Card is no longer valid or has expired', 'tc'),
                'invalid_cardholder' => __('Invalid cardholder', 'tc'),
                    )
            );
        }
    }

    function payment_form($cart) {
        global $tc;
        $this->maybe_start_session();

        $content = '';

        $content .= '<div id="paymill_checkout_errors"></div>';

        $content .= '<table class="tc_cart_billing">
        <thead><tr>
          <th colspan="2">' . __('Enter Your Credit Card Information:', 'tc') . '</th>
        </tr></thead>
        <tbody>
          <tr>
          <td>' . __('Cardholder Name:', 'tc') . '</td>
          <td><input class="card-holdername tickera-input-field" type="text" value="' . esc_attr($this->buyer_info('full_name')) . '" /> </td>
          </tr>';

        $content .= '<tr>';
        $content .= '<td>';
        $content .= __('Card Number', 'tc');
        $content .= '</td>';
        $content .= '<td>';
        $content .= '<input type="text" autocomplete="off" class="card-number"/>';
        $content .= '</td>';
        $content .= '</tr>';
        $content .= '<tr>';
        $content .= '<td>';
        $content .= __('Expiration:', 'tc');
        $content .= '</td>';
        $content .= '<td>';
        $content .= '<select class="card-expiry-month">';
        $content .= tc_months_dropdown();
        $content .= '</select>';
        $content .= '<span> / </span>';
        $content .= '<select class="card-expiry-year">';
        $content .= tc_years_dropdown('', true);
        $content .= '</select>';
        $content .= '</td>';
        $content .= '</tr>';
        $content .= '<tr>';
        $content .= '<td>';
        $content .= __('CVC:', 'tc');
        $content .= '</td>';
        $content .= '<td>';
        $content .= '<input type="text" size="4" autocomplete="off" class="card-cvc" />';
        $content .= '<input type="hidden" class="currency" value="' . $this->currency . '" />';
        $content .= '<input type="hidden" class="amount" value="' . $this->total() * 100 . '" />';
        $content .= '</td>';
        $content .= '</tr>';
        $content .= '</table>';
        $content .= '<span id="paymill_processing" style="display: none;float: right;"><img src="' . $tc->plugin_url . 'images/loading.gif" /> ' . __('Processing...', 'tc') . '</span>';
        return $content;
    }

    function order_confirmation_message($order, $cart_info = '') {
        global $tc;

        $order = tc_get_order_id_by_name($order);

        $order = new TC_Order($order->ID);

        $content = '';

        if ($order->details->post_status == 'order_received') {
            $content .= '<p>' . sprintf(__('Your payment via Paymill for this order totaling <strong>%s</strong> is not yet complete.', 'tc'), apply_filters('tc_cart_currency_and_format', $order->details->tc_payment_info['total'])) . '</p>';
            $content .= '<p>' . __('Current order status:', 'tc') . ' <strong>' . __('Pending Payment', 'tc') . '</strong></p>';
        } else if ($order->details->post_status == 'order_fraud') {
            $content .= '<p>' . __('Your payment is under review. We will back to you soon.', 'tc') . '</p>';
        } else if ($order->details->post_status == 'order_paid') {
            $content .= '<p>' . sprintf(__('Your payment via Paymill for this order totaling <strong>%s</strong> is complete.', 'tc'), apply_filters('tc_cart_currency_and_format', $order->details->tc_payment_info['total'])) . '</p>';
        } else if ($order->details->post_status == 'order_cancelled') {
            $content .= '<p>' . sprintf(__('Your payment via Paymill for this order totaling <strong>%s</strong> is cancelled.', 'tc'), $this->public_name, apply_filters('tc_cart_currency_and_format', $order->details->tc_payment_info['total'])) . '</p>';
        }

        $content = apply_filters('tc_order_confirmation_message_content_' . $this->plugin_name, $content);

        $content = apply_filters('tc_order_confirmation_message_content', $content, $order);

        $tc->remove_order_session_data();

        unset($_SESSION['paymillToken']);
        $tc->maybe_skip_confirmation_screen($this, $order);
        return $content;
    }

    function process_payment($cart) {
        global $tc;

        tc_final_cart_check($cart);
        
        $this->maybe_start_session();
        $this->save_cart_info();

        if (isset($_POST['paymillToken'])) {
            $_SESSION['paymillToken'] = $_POST['paymillToken'];
        }

        if (!isset($_SESSION['paymillToken'])) {
            $_SESSION['tc_gateway_error'] = __('The Paymill Token was not generated correctly.', 'tc');
            ob_start();
            @wp_redirect($tc->get_payment_slug(true));
            tc_js_redirect($tc->get_payment_slug(true));
            exit;
            return false;
        }

        define('PAYMILL_API_HOST', 'https://api.paymill.com/v2/');
        define('PAYMILL_API_KEY', $this->get_option('private_key'));

        $token = $_SESSION['paymillToken'];

        if ($token) {
            require "paymill/lib/Services/Paymill/Transactions.php";
            $transactionsObject = new Services_Paymill_Transactions(PAYMILL_API_KEY, PAYMILL_API_HOST);

            $order_id = $tc->generate_order_id();

            try {
                $params = array(
                    'amount' => $this->total() * 100, //// I.e. 49 * 100 = 4900 Cents = 49 EUR
                    'currency' => strtolower($this->currency), // ISO 4217
                    'token' => $token,
                    'description' => $this->cart_items()
                );

                $charge = $transactionsObject->create($params);

                if ($charge['status'] == 'closed') {
                    //setup our payment details
                    $payment_info = array();
                    $payment_info['method'] = sprintf(__('%1$s Card ending in %2$s - Expires %3$s', 'tc'), ucfirst($charge['payment']['card_type']), $charge['payment']['last4'], $charge['payment']['expire_month'] . '/' . $charge['payment']['expire_year']);
                    $payment_info['transaction_id'] = $charge['id'];
                    $payment_info = $this->save_payment_info();

                    $paid = true;
                    $order = $tc->create_order($order_id, $this->cart_contents(), $this->cart_info(), $payment_info, $paid);

                    ob_start();
                    @wp_redirect($tc->get_confirmation_slug(true, $order_id));
                    tc_js_redirect($tc->get_confirmation_slug(true, $order_id));
                    exit;
                }
            } catch (Exception $e) {
                unset($_SESSION['paymillToken']);
                $_SESSION['tc_gateway_error'] = sprintf(__('There was an error processing your card: "%s".', 'tc'), $e->getMessage());
                ob_start();
                @wp_redirect($tc->get_payment_slug(true));
                tc_js_redirect($tc->get_payment_slug(true));
                exit;
                return false;
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
                    <?php _e('Accept all major credit and debit cards directly on your site. Credit cards go directly to Paymill\'s secure environment, and never hit your servers so you can avoid most PCI requirements.', 'tc'); ?>
                </span>

                <?php
                $fields = array(
                    'is_ssl' => array(
                        'title' => __('Force SSL', 'tc'),
                        'type' => 'select',
                        'options' => array(
                            '0' => __('No', 'tc'),
                            '1' => __('Yes', 'tc')
                        ),
                        'default' => '0',
                    ),
                    'private_key' => array(
                        'title' => __('Private Key', 'tc'),
                        'type' => 'text',
                        'description' => __('You must login to Paymill to <a target="_blank" href="https://app.paymill.com/en-gb/auth/login">get your API credentials</a>. You can enter your test keys, then live ones when ready.', 'tc'),
                    ),
                    'public_key' => array(
                        'title' => __('Public Key', 'tc'),
                        'type' => 'text',
                        'description' => '',
                        'default' => ''
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

tc_register_gateway_plugin('TC_Gateway_Paymill', 'paymill', __('Paymill', 'tc'));
?>