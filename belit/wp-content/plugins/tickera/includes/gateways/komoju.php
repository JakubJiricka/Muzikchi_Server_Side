<?php
/*
  Komoju - Payment Gateway
 */

class TC_Gateway_Komoju extends TC_Gateway_API {

    var $plugin_name = 'komoju';
    var $admin_name = '';
    var $public_name = '';
    var $method_img_url = '';
    var $admin_img_url = '';
    var $force_ssl = false;
    var $ipn_url;
    var $currencies = array();
    var $live_url;
    var $merchant_id = '';
    var $notify_url = '';
    var $success_url = '';
    var $fail_url = '';
    var $automatically_activated = false;
    var $currency = '';
    var $skip_payment_screen = false;

    //Support for older payment gateway API
    function on_creation() {
        $this->init();
    }

    function init() {
        global $tc;

        $this->admin_name = __('Komoju', 'tc');
        $this->public_name = __('Komoju', 'tc');

        $this->sandbox_url = 'https://sandbox.komoju.com';
        $this->live_url = 'https://komoju.com';

        $this->notify_url = $this->ipn_url;
        $this->success_url = '';

        $this->method_img_url = apply_filters('tc_gateway_method_img_url', $tc->plugin_url . 'images/gateways/komoju.png', $this->plugin_name);
        $this->admin_img_url = apply_filters('tc_gateway_admin_img_url', $tc->plugin_url . 'images/gateways/small-komoju.png', $this->plugin_name);

        $this->locale = $this->get_option('locale', 'ja');
        $this->currency = $this->get_option('currency', 'JPY');

        $this->mode = $this->get_option('mode', 'sandbox');

        $this->account_id = $this->get_option('account_id');
        $this->secret_key = $this->get_option('secret_key');

        $currencies = array(
            "JPY" => __('JPY - Japanese Yen', 'tc'),
        );

        $this->locales = array(
            "ja" => __('Japanese', 'tc'),
            "en" => __('English', 'tc'),
        );

        $this->payment_methods = array(
            'bank_transfer' => __('銀行振込 Bank transfer', 'tc'),
            'credit_card' => __('コンビニ Credit Card (Visa, MasterCard, JCB, AMEX)', 'tc'),
            'konbini' => __('クレジットカード Convenience Store Payment (Konbini)', 'tc'),
            'pay_easy' => __('ペイジー PayEasy', 'tc'),
            'web_money' => __('WebMoney', 'tc'),
            'bit_cash' => __('BitCash', 'tc')
        );

        $this->currencies = $currencies;
    }

    function payment_form($cart) {
        global $tc;
        $saved_payment_option_values = $this->get_option('payment_methods', array());

        $content = '
			<table class="tc_cart_billing">
<thead>
<tr>
<th colspan="2">' . __('Choose payment method:', 'tc') . '</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2">';
        $first = true;
        foreach ($this->payment_methods as $payment_method => $payment_title) {
            if (in_array($payment_method, $saved_payment_option_values)) {
                $content .= '<input type="radio" name="komoju_payment_method" value="' . esc_attr($payment_method) . '" ' . ($first ? 'checked' : '') . ' /> ' . $payment_title . '<br />';
                $first = false;
            }
        }
        $content .= '</td>
</tr>
</tbody></table>';


        return $content;
    }

    function process_payment($cart) {
        global $tc;

        tc_final_cart_check($cart);
        
        $this->maybe_start_session();
        $this->save_cart_info();

        $payment_method = $_POST['komoju_payment_method'];

        $order_id = $tc->generate_order_id();

        $param_list = array();

        $paid = false;

        $total = number_format($this->total(), 2, '.', '');

        $start_point = $this->mode == 'sandbox' ? $this->sandbox_url : $this->live_url;

        $secret_key = $this->secret_key;

        $endpoint = "/" . $this->locale . "/api/" . $this->account_id . "/transactions/" . $payment_method . "/new";
        $params = array(
            "transaction[amount]" => $this->total(),
            "transaction[currency]" => $this->currency,
            "transaction[customer][given_name]" => $this->buyer_info('first_name'),
            "transaction[customer][family_name]" => $this->buyer_info('last_name'),
            "transaction[customer][given_name_kana]" => $this->buyer_info('first_name'),
            "transaction[customer][family_name_kana]" => $this->buyer_info('last_name'),
            "transaction[external_order_num]" => $order_id,
            "transaction[return_url]" => $tc->get_confirmation_slug(true, $order_id),
            "transaction[cancel_url]" => $tc->get_cancel_url($order_id),
            "transaction[callback_url]" => $this->ipn_url,
            "transaction[tax]" => "0",
            "timestamp" => time(),
        );

        $qs_params = array();

        foreach ($params as $key => $val) {
            $qs_params[] = urlencode($key) . '=' . urlencode($val);
        }

        sort($qs_params);

        $query_string = implode('&', $qs_params);
        $url = $endpoint . '?' . $query_string;
        $hmac = hash_hmac('sha256', $url, $secret_key);

        $payment_info = $this->save_payment_info();

        $tc->create_order($order_id, $this->cart_contents(), $this->cart_info(), $payment_info, $paid);

        ob_start();
        @wp_redirect($start_point . $url . '&hmac=' . $hmac);
        tc_js_redirect($start_point . $url . '&hmac=' . $hmac);
        exit;
    }

    function order_confirmation($order, $payment_info = '', $cart_info = '') {
        global $tc;

        if (isset($_GET['hmac'])) {
            //$amount			 = $_GET[ 'amount' ];
            //$payment_method	 = $_GET[ 'payment_method' ];
            $order_num = $_GET['transaction']['external_order_num'];
            $status = $_GET['transaction']['status'];

            $order = tc_get_order_id_by_name($order_num);

            if ($status == 'captured') {
                $tc->update_order_payment_status($order->ID, true);
            }
            $tc->remove_order_session_data();
        }
    }

    function gateway_admin_settings($settings, $visible) {
        global $tc;
        ?>
        <div id="<?php echo $this->plugin_name;
        ?>" class="postbox" <?php echo (!$visible ? 'style="display:none;"' : ''); ?>>
            <h3 class='hndle'><span><?php printf(__('%s Settings', 'tc'), $this->admin_name); ?></span></h3>
            <div class="inside">
                <span class="description"><?php _e('Accept payments in Japan with Komoju.', 'tc') ?></span>
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
                    'account_id' => array(
                        'title' => __('Account ID', 'tc'),
                        'type' => 'text',
                    ),
                    /* 'public_key'		 => array(
                      'title'	 => __( 'Publishable Key', 'tc' ),
                      'type'	 => 'text',
                      ), */
                    'secret_key' => array(
                        'title' => __('Secret Key', 'tc'),
                        'type' => 'text',
                    ),
                    'payment_methods' => array(
                        'title' => __('Payment Methods', 'tc'),
                        'type' => 'checkboxes',
                        'options' => $this->payment_methods,
                    ),
                    'locale' => array(
                        'title' => __('Locale', 'tc'),
                        'type' => 'select',
                        'options' => $this->locales,
                        'default' => 'ja',
                    ),
                    'currency' => array(
                        'title' => __('Currency', 'tc'),
                        'type' => 'select',
                        'options' => $this->currencies,
                        'default' => 'JPY',
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

    function ipn() {
        global $tc;

        if (isset($_GET['hmac'])) {

            $order_num = $_GET['transaction']['external_order_num'];
            $status = $_GET['transaction']['status'];

            $order = tc_get_order_id_by_name($order_num);

            if ($status == 'captured') {
                $tc->update_order_payment_status($order->ID, true);
            }
        }
    }

}

tc_register_gateway_plugin('TC_Gateway_Komoju', 'komoju', __('Komoju', 'tc'));
?>