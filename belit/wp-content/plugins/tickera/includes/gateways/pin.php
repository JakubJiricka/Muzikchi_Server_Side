<?php
/*
  PIN Payment Gateway (www.pin.net.au)
 */

class TC_Gateway_PIN extends TC_Gateway_API {

    var $plugin_name = 'pin';
    var $admin_name = '';
    var $public_name = '';
    var $method_img_url = '';
    var $admin_img_url = '';
    var $force_ssl;
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

        $settings = get_option('tc_settings');

        $this->admin_name = __('PIN', 'tc');
        $this->public_name = __('PIN', 'tc');
        $this->public_key = $this->get_option('public_key');
        $this->private_key = $this->get_option('private_key');
        $this->force_ssl = $this->get_option('is_ssl', '0') == '1' ? true : false;
        $this->currency = $this->get_option('currency', 'AUD');

        $this->method_img_url = apply_filters('tc_gateway_method_img_url', $tc->plugin_url . 'images/gateways/pin.png', $this->plugin_name);
        $this->admin_img_url = apply_filters('tc_gateway_admin_img_url', $tc->plugin_url . 'images/gateways/small-pin.png', $this->plugin_name);

        add_action('wp_enqueue_scripts', array(&$this, 'enqueue_scripts'));

        $currencies = array(
            "AUD" => __('AUD - Australian Dollar', 'tc'),
            "USD" => __('USD - United States Dollar', 'tc'),
            "NZD" => __('NZD - New Zealand Dollar', 'tc'),
            "SGD" => __('SGD - Singaporean Dollar', 'tc'),
            "EUR" => __('EUR - Euro', 'tc'),
            "GBP" => __('GBP - British Pound', 'tc'),
            "HKD" => __('HKD - Hong Kong Dollar', 'tc'),
            "JPY" => __('JPY - Japanese Yen', 'tc'),
        );

        $this->currencies = $currencies;
    }

    function enqueue_scripts() {
        global $tc, $wp;

        if ($this->is_active() && $this->is_payment_page()) {
            if ($this->force_ssl) {
                wp_enqueue_script('js-pin', 'https://api.pin.net.au/pin.js', array('jquery'));
            } else {
                wp_enqueue_script('js-pin', 'https://test-api.pin.net.au/pin.js', array('jquery'));
            }

            wp_enqueue_script('pin-handler', $tc->plugin_url . '/includes/gateways/pin/pin-handler.js', array('js-pin', 'jquery'));
            wp_localize_script('pin-handler', 'pin_vars', array(
                'publishable_api_key' => $this->public_key,
                    )
            );
        }
    }

    function payment_form($cart) {
        global $tc;

        $this->maybe_start_session();

        $content = '';

        $content .= '<div id="pin_checkout_errors"><ul></ul></div>';

        $content .= '<table class="tc_cart_billing">
        <thead><tr>
          <th colspan="2">' . __('Enter Your Credit Card Information:', 'tc') . '</th>
        </tr></thead>
        <tbody>
          <tr>
          <td>' . __('Cardholder Name:', 'tc') . '</td>
          <td><input id="cc-name" type="text" value="' . esc_attr($this->buyer_info('full_name')) . '" /> </td>
          </tr>';

        $content .= '<tr>';
        $content .= '<td>';
        $content .= __('Card Number', 'tc');
        $content .= '</td>';
        $content .= '<td>';
        $content .= '<input type="text" autocomplete="off" id="cc-number"/>';
        $content .= '</td>';
        $content .= '</tr>';
        $content .= '<tr>';
        $content .= '<td>';
        $content .= __('Expiration:', 'tc');
        $content .= '</td>';
        $content .= '<td>';
        $content .= '<select id="cc-expiry-month">';
        $content .= tc_months_dropdown();
        $content .= '</select>';
        $content .= '<span> / </span>';
        $content .= '<select id="cc-expiry-year">';
        $content .= tc_years_dropdown('', true);
        $content .= '</select>';
        $content .= '</td>';
        $content .= '</tr>';
        $content .= '<tr>';
        $content .= '<td>';
        $content .= __('CVC:', 'tc');
        $content .= '</td>';
        $content .= '<td>';
        $content .= '<input type="text" size="4" autocomplete="off" id="cc-cvc" />';
        $content .= '</td>';
        $content .= '</tr>';
        $content .= '</table>';
        $content .= '<span id="pin_processing" style="display: none;float: right;"><img src="' . $tc->plugin_url . 'images/loading.gif" /> ' . __('Processing...', 'tc') . '</span>';

        return $content;
    }

    function order_confirmation_message($order, $cart_info = '') {
        global $tc;

        $cart_info = isset($_SESSION['cart_info']) ? $_SESSION['cart_info'] : $cart_info;

        $order = tc_get_order_id_by_name($order);

        $order = new TC_Order($order->ID);

        $content = '';

        if ($order->details->post_status == 'order_received') {
            $content .= '<p>' . sprintf(__('Your payment via PIN for this order totaling <strong>%s</strong> is not yet complete.', 'tc'), apply_filters('tc_cart_currency_and_format', $order->details->tc_payment_info['total'])) . '</p>';
            $content .= '<p>' . __('Current order status:', 'tc') . ' <strong>' . __('Pending Payment', 'tc') . '</strong></p>';
        } else if ($order->details->post_status == 'order_fraud') {
            $content .= '<p>' . __('Your payment is under review. We will back to you soon.', 'tc') . '</p>';
        } else if ($order->details->post_status == 'order_paid') {
            $content .= '<p>' . sprintf(__('Your payment via PIN for this order totaling <strong>%s</strong> is complete.', 'tc'), apply_filters('tc_cart_currency_and_format', $order->details->tc_payment_info['total'])) . '</p>';
        } else if ($order->details->post_status == 'order_cancelled') {
            $content .= '<p>' . sprintf(__('Your payment via PIN for this order totaling <strong>%s</strong> is cancelled.', 'tc'), $this->public_name, apply_filters('tc_cart_currency_and_format', $order->details->tc_payment_info['total'])) . '</p>';
        }

        $content = apply_filters('tc_order_confirmation_message_content_' . $this->plugin_name, $content);

        $content = apply_filters('tc_order_confirmation_message_content', $content, $order);

        $tc->remove_order_session_data();
        unset($_SESSION['card_token']);
        $tc->maybe_skip_confirmation_screen($this, $order);
        return $content;
    }

    function process_payment($cart) {
        global $tc;

        tc_final_cart_check($cart);
        
        $this->maybe_start_session();
        $this->save_cart_info();

        if (isset($_POST['card_token'])) {
            $_SESSION['card_token'] = $_POST['card_token'];
        }

        if (!isset($_SESSION['card_token'])) {
            $_SESSION['tc_gateway_error'] = __('The PIN Token was not generated correctly.', 'tc');
            ob_start();
            @wp_redirect($tc->get_payment_slug(true));
            tc_js_redirect($tc->get_payment_slug(true));
            exit;
            return false;
        }

        if ($this->force_ssl) {
            define('PIN_API_CHARGE_URL', 'https://api.pin.net.au/1/charges');
        } else {
            define('PIN_API_CHARGE_URL', 'https://test-api.pin.net.au/1/charges');
        }

        define('PIN_API_KEY', $this->private_key);

        $token = $_SESSION['card_token'];

        if ($token) {

            $order_id = $tc->generate_order_id();

            try {

                $args = array(
                    'method' => 'POST',
                    'httpversion' => '1.1',
                    'timeout' => apply_filters('tc_http_request_timeout', 30),
                    'blocking' => true,
                    'compress' => true,
                    'headers' => array('Authorization' => 'Basic ' . base64_encode(PIN_API_KEY . ':' . '')),
                    'body' => array(
                        'amount' => (int) ($this->total() * 100),
                        'currency' => strtolower($this->currency),
                        'description' => $this->cart_items(),
                        'email' => $this->buyer_info('email'),
                        'ip_address' => $_SESSION['ip_address'],
                        'card_token' => $_SESSION['card_token']
                    ),
                    'cookies' => array()
                );

                $charge = wp_remote_post(PIN_API_CHARGE_URL, $args);
                $charge = json_decode($charge['body'], true);
                $charge = $charge['response'];

                if ($charge['success'] == true) {
                    $payment_info = array();
                    $payment_info['method'] = sprintf(__('%1$s Card %2$s', 'tc'), ucfirst($charge['card']['scheme']), $charge['card']['display_number']);
                    $payment_info['transaction_id'] = $charge['token'];
                    $payment_info = $this->save_payment_info();

                    $paid = true;
                    $order = $tc->create_order($order_id, $this->cart_contents(), $this->cart_info(), $payment_info, $paid);

                    ob_start();
                    @wp_redirect($tc->get_confirmation_slug(true, $order_id));
                    tc_js_redirect($tc->get_confirmation_slug(true, $order_id));
                    exit;
                } else {
                    unset($_SESSION['card_token']);
                    $_SESSION['tc_gateway_error'] = sprintf(__('There was an error processing your card.', 'tc'));
                    ob_start();
                    @wp_redirect($tc->get_payment_slug(true));
                    tc_js_redirect($tc->get_payment_slug(true));
                    exit;

                    return false;
                }
            } catch (Exception $e) {
                unset($_SESSION['card_token']);
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
                    <?php _e("Accept all major credit cards directly on your site. Your sales proceeds are deposited to any Australian bank account, no merchant account required.", 'tc') ?>
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
                    'private_key' => array(
                        'title' => __('Secret API Key', 'tc'),
                        'type' => 'text',
                        'description' => __('You must login to PIN to <a target="_blank" href="https://dashboard.pin.net.au/account">get your API credentials</a>. You can enter your test keys, then live ones when ready.', 'tc'),
                    ),
                    'public_key' => array(
                        'title' => __('Publishable API Key', 'tc'),
                        'type' => 'text',
                    ),
                    'currency' => array(
                        'title' => __('Currency', 'tc'),
                        'type' => 'select',
                        'options' => $this->currencies,
                        'default' => 'AUD',
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

tc_register_gateway_plugin('TC_Gateway_PIN', 'pin', __('PIN', 'tc'));
?>