<?php
/*
  PayPal Standard - Payment Gateway
 * ENABLE AUTO-RETURN https://www.paypal.com/rs/cgi-bin/webscr?cmd=p/mer/express_return_summary-outside
 */

class TC_Gateway_PayPal_Standard extends TC_Gateway_API {

    var $plugin_name = 'paypal_standard';
    var $admin_name = '';
    var $public_name = '';
    var $method_img_url = '';
    var $admin_img_url = '';
    var $force_ssl = false;
    var $ipn_url;
    var $business, $SandboxFlag, $returnURL, $cancelURL, $API_Endpoint, $version, $currency, $locale;
    var $currencies = array();
    var $automatically_activated = false;
    var $skip_payment_screen = true;

    //Support for older payment gateway API
    function on_creation() {
        $this->init();
    }

    function init() {
        global $tc;

        $this->admin_name = __('PayPal Standard', 'tc');
        $this->public_name = __('PayPal', 'tc');

        $this->method_img_url = apply_filters('tc_gateway_method_img_url', $tc->plugin_url . 'images/gateways/paypal-standard.png', $this->plugin_name);
        $this->admin_img_url = apply_filters('tc_gateway_admin_img_url', $tc->plugin_url . 'images/gateways/small-paypal-standard.png', $this->plugin_name);

        $this->currency = $this->get_option('currency', 'USD');
        $this->SandboxFlag = $this->get_option('mode', 'sandbox');
        $this->business = $this->get_option('email');
        $this->locale = $this->get_option('locale', 'US');
        $this->ignore_ipn_errors = $this->get_option('ignore_ipn_errors', 'no');

        $currencies = array(
            "AUD" => __('AUD - Australian Dollar', 'tc'),
            "BRL" => __('BRL - Brazilian Real', 'tc'),
            "CAD" => __('CAD - Canadian Dollar', 'tc'),
            "CZK" => __('CZK - Czech Koruna', 'tc'),
            "DKK" => __('DKK - Danish Krone', 'tc'),
            "EUR" => __('EUR - Euro', 'tc'),
            "HKD" => __('HKD - Hong Kong Dollar', 'tc'),
            "HUF" => __('HUF - Hungarian Forint', 'tc'),
            "ILS" => __('ILS - Israeli New Shekel', 'tc'),
            "JPY" => __('JPY - Japanese Yen', 'tc'),
            "MYR" => __('MYR - Malaysian Ringgit', 'tc'),
            "MXN" => __('MXN - Mexican Peso', 'tc'),
            "NOK" => __('NOK - Norwegian Krone', 'tc'),
            "NZD" => __('NZD - New Zealand Dollar', 'tc'),
            "PHP" => __('PHP - Philippine Peso', 'tc'),
            "PLN" => __('PLN - Polish Zloty', 'tc'),
            "GBP" => __('GBP - Pound Sterling', 'tc'),
            "RUB" => __('RUB - Russian Ruble', 'tc'),
            "SGD" => __('SGD - Singapore Dollar', 'tc'),
            "SEK" => __('SEK - Swedish Krona', 'tc'),
            "CHF" => __('CHF - Swiss Franc', 'tc'),
            "TWD" => __('TWD - Taiwan New Dollar', 'tc'),
            "TRY" => __('TRY - Turkish Lira', 'tc'),
            "USD" => __('USD - U.S. Dollar', 'tc'),
            "THB" => __('THB - Thai Baht', 'tc'),
        );

        $this->currencies = apply_filters('tc_paypal_standard_currencies', $currencies);

        $locales = array(
            'AU' => __('Australia', 'tc'),
            'AT' => __('Austria', 'tc'),
            'BE' => __('Belgium', 'tc'),
            'BR' => __('Brazil', 'tc'),
            'CA' => __('Canada', 'tc'),
            'CH' => __('Switzerland', 'tc'),
            'CN' => __('China', 'tc'),
            'DE' => __('Germany', 'tc'),
            'ES' => __('Spain', 'tc'),
            'SG' => __('Singapore', 'tc'),
            'GB' => __('United Kingdom', 'tc'),
            'FR' => __('France', 'tc'),
            'IT' => __('Italy', 'tc'),
            'MX' => __('Mexico', 'tc'),
            'NL' => __('Netherlands', 'tc'),
            'NZ' => __('New Zealand', 'tc'),
            'PL' => __('Poland', 'tc'),
            'PT' => __('Portugal', 'tc'),
            'RU' => __('Russia', 'tc'),
            'US' => __('United States', 'tc'),
            'MY' => __('Malaysia', 'tc'),
            'da_DK' => __('Danish (for Denmark only)', 'tc'),
            'he_IL' => __('Hebrew (all)', 'tc'),
            'id_ID' => __('Indonesian (for Indonesia only)', 'tc'),
            'ja_JP' => __('Japanese (for Japan only)', 'tc'),
            'no_NO' => __('Norwegian (for Norway only)', 'tc'),
            'pt_BR' => __('Brazilian Portuguese (for Portugal and Brazil only)', 'tc'),
            'ru_RU' => __('Russian (for Lithuania, Latvia, and Ukraine only)', 'tc'),
            'sv_SE' => __('Swedish (for Sweden only)', 'tc'),
            'th_TH' => __('Thai (for Thailand only)', 'tc'),
            'tr_TR' => __('Turkish (for Turkey only)', 'tc'),
            'zh_CN' => __('Simplified Chinese (for China only)', 'tc'),
            'zh_HK' => __('Traditional Chinese (for Hong Kong only)', 'tc'),
            'zh_TW' => __('Traditional Chinese (for Taiwan only)', 'tc'),
        );

        $this->locales = apply_filters('tc_paypal_standard_locales', $locales);
    }

    function payment_form($cart) {
        global $tc;
    }

    function process_payment($cart) {
        global $tc;

        tc_final_cart_check($cart);
        
        $this->maybe_start_session();
        $this->save_cart_info();

        $order_id = $tc->generate_order_id();

        $params = array();
        $params['no_shipping'] = '1'; //do not prompt for an address
        $params['cmd'] = '_xclick';
        $params['business'] = $this->business;
        $params['currency_code'] = $this->currency;
        $params['item_name'] = $this->cart_items();
        $params['amount'] = $this->total();
        $params['custom'] = $order_id;
        $params['return'] = $tc->get_confirmation_slug(true, $order_id);
        $params['cancel_return'] = apply_filters('tc_paypal_standard_cancel_url', $tc->get_cancel_url($order_id));
        $params['notify_url'] = $this->ipn_url;
        $params['charset'] = apply_filters('tc_paypal_standard_charset', 'UTF-8');
        $params['rm'] = '2'; //the buyer's browser is redirected to the return URL by using the POST method, and all payment variables are included
        $params['lc'] = $this->locale;
        $params['email'] = $this->buyer_info('email');
        $params['first_name'] = $this->buyer_info('first_name');
        $params['last_name'] = $this->buyer_info('last_name');
        $params['bn'] = 'Tickera_SP';

        if ($this->SandboxFlag == 'live') {
            $url = 'https://www.paypal.com/cgi-bin/webscr';
        } else {
            $params['demo'] = 'Y';
            $url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
        }

        $param_list = array();

        foreach ($params as $k => $v) {
            $param_list[] = "{$k}=" . rawurlencode($v);
        }

        $param_str = implode('&', $param_list);

        $paid = false;

        $payment_info = $this->save_payment_info();

        $tc->create_order($order_id, $this->cart_contents(), $this->cart_info(), $payment_info, $paid);

        ob_start();
        @wp_redirect("{$url}?{$param_str}");
        tc_js_redirect("{$url}?{$param_str}");

        exit(0);
    }

    function order_confirmation($order, $payment_info = '', $cart_info = '') {
        global $tc;

        if (isset($_POST['payment_status']) || isset($_POST['txn_type'])) {
            echo '';

            $total = $_REQUEST['mc_gross'];
            $order_var = $_REQUEST['custom'];
            $order = tc_get_order_id_by_name($order_var);

            $raw_post_data = file_get_contents('php://input');

            $raw_post_array = explode('&', $raw_post_data);
            $myPost = array();

            foreach ($raw_post_array as $keyval) {
                $keyval = explode('=', $keyval);
                if (count($keyval) == 2)
                    $myPost[$keyval[0]] = urldecode($keyval[1]);
            }

            $req = 'cmd=_notify-validate';

            if (function_exists('get_magic_quotes_gpc')) {
                $get_magic_quotes_exists = true;
            }

            foreach ($myPost as $key => $value) {
                if ($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) {
                    $value = urlencode(stripslashes($value));
                } else {
                    $value = urlencode($value);
                }
                $req .= "&$key=$value";
            }

            if ($this->get_option('mode', 'sandbox') == 'sandbox') {
                $url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
            } else {
                $url = 'https://www.paypal.com/cgi-bin/webscr';
            }

            $args['user-agent'] = $tc->title;
            $args['body'] = $req;
            $args['sslverify'] = false;
            $args['timeout'] = 120;

            $response = wp_remote_post($url, $args);

            if (is_wp_error($response) || wp_remote_retrieve_response_code($response) != 200 || $response['body'] != 'VERIFIED') {
                if ($this->ignore_ipn_errors == 'no') {
                    if (is_wp_error($response)) {
                        TC_Order::add_order_note($order->ID, 'PayPal error: ' . $response->get_error_message() . '. That means that your website cannot communicate with the PayPal IPN server for some reason. You can turn off IPN check errors by setting option "Ignore IPN errors" to yes or you can contact support.');
                    }
                    if (wp_remote_retrieve_response_code($response) != 200) {
                        TC_Order::add_order_note($order->ID, 'PayPal IPN server responded with a code: ' . wp_remote_retrieve_response_code($response) . '. That means that your website cannot communicate with the PayPal IPN server for some reason. You can turn off IPN check errors by setting option "Ignore IPN errors" to yes or you can contact support.');
                    }
                } else {//ignore_ipn_errors is set to yes
                    //Ignore errors and execute this part of the code anyway
                    switch ($_POST['payment_status']) {
                        case 'Completed':
                            $tc->update_order_payment_status($order->ID, true);
                            break;

                        case 'Pending':
                            if (isset($_POST['pending_reason']) && $_POST['pending_reason'] == 'multi_currency') {
                                TC_Order::add_order_note($order->ID, sprintf(__('You do not have a balance in the currency sent, and you do not have your profiles\'s Payment Receiving Preferences option set to automatically convert and accept this payment. As a result, you must manually accept or deny this payment. Read more %shere%s.', 'tc'), '<a href="https://tickera.com/tickera-documentation/settings/payment-gateways/paypal-standard/">', '</a>'));
                            }
                            if (isset($_POST['pending_reason']) && $_POST['pending_reason'] == 'address') {
                                TC_Order::add_order_note($order->ID, __('The payment is pending because your customer did not include a confirmed shipping address and your Payment Receiving Preferences is set yo allow you to manually accept or deny each of these payments. To change your preference, go to the Preferences section of your Profile.', 'tc'));
                            }
                            if (isset($_POST['pending_reason']) && $_POST['pending_reason'] == 'authorization') {
                                TC_Order::add_order_note($order->ID, __('PayPal Order Pending reason: You set the payment action to Authorization and have not yet captured funds.', 'tc'));
                            }
                            if (isset($_POST['pending_reason']) && $_POST['pending_reason'] == 'echeck') {
                                TC_Order::add_order_note($order->ID, __('The payment is pending because it was made by an eCheck that has not yet cleared.', 'tc'));
                            }
                            if (isset($_POST['pending_reason']) && $_POST['pending_reason'] == 'intl') {
                                TC_Order::add_order_note($order->ID, __('The payment is pending because you hold a non-U.S. account and do not have a withdrawal mechanism. You must manually accept or deny this payment from your Account Overview.', 'tc'));
                            }
                            if (isset($_POST['pending_reason']) && $_POST['pending_reason'] == 'order') {
                                TC_Order::add_order_note($order->ID, __('PayPal Order Pending reason: You set the payment action to Order and have not yet captured funds.', 'tc'));
                            }
                            if (isset($_POST['pending_reason']) && $_POST['pending_reason'] == 'paymentreview') {
                                TC_Order::add_order_note($order->ID, __('The payment is pending while it is reviewed by PayPal for risk.', 'tc'));
                            }
                            if (isset($_POST['pending_reason']) && $_POST['pending_reason'] == 'regulatory_review') {
                                TC_Order::add_order_note($order->ID, __('The payment is pending because PayPal is reviewing it for compliance with government regulations. PayPal will complete this review within 72 hours.', 'tc'));
                            }
                            if (isset($_POST['pending_reason']) && $_POST['pending_reason'] == 'unilateral') {
                                TC_Order::add_order_note($order->ID, __('The payment is pending because it was made to an email address that is not yet registered or confirmed.', 'tc'));
                            }
                            if (isset($_POST['pending_reason']) && $_POST['pending_reason'] == 'upgrade') {
                                TC_Order::add_order_note($order->ID, __('The payment is pending because it was made via credit card and you must upgrade your account to Business or Premier status before you can receive the funds.', 'tc'));
                            }
                            if (isset($_POST['pending_reason']) && $_POST['pending_reason'] == 'verify') {
                                TC_Order::add_order_note($order->ID, __('The payment is pending because you are not yet verified. You must verify your account before you can accept this payment.', 'tc'));
                            }
                            if (isset($_POST['pending_reason']) && $_POST['pending_reason'] == 'other') {
                                TC_Order::add_order_note($order->ID, __('The payment is pending for an unknown reason, please contact PayPal Customer Service.', 'tc'));
                            }
                            break;

                        case 'Processed':
                            break;

                        case 'Canceled-Reversal':
                            break;

                        case 'Canceled':
                            $tc->update_order_status($order->ID, 'order_cancelled');
                            break;

                        default:
                        //do nothing, wait for IPN message
                    }
                }
                //do nothing, wait for IPN message
            } else {//request is verified
                switch ($_POST['payment_status']) {
                    case 'Completed':
                        $tc->update_order_payment_status($order->ID, true);
                        break;

                    case 'Pending':
                        if (isset($_POST['pending_reason']) && $_POST['pending_reason'] == 'multi_currency') {
                            TC_Order::add_order_note($order->ID, sprintf(__('You do not have a balance in the currency sent, and you do not have your profiles\'s Payment Receiving Preferences option set to automatically convert and accept this payment. As a result, you must manually accept or deny this payment. Read more %shere%s.', 'tc'), '<a href="https://tickera.com/tickera-documentation/settings/payment-gateways/paypal-standard/">', '</a>'));
                        }
                        if (isset($_POST['pending_reason']) && $_POST['pending_reason'] == 'address') {
                            TC_Order::add_order_note($order->ID, __('The payment is pending because your customer did not include a confirmed shipping address and your Payment Receiving Preferences is set yo allow you to manually accept or deny each of these payments. To change your preference, go to the Preferences section of your Profile.', 'tc'));
                        }
                        if (isset($_POST['pending_reason']) && $_POST['pending_reason'] == 'authorization') {
                            TC_Order::add_order_note($order->ID, __('PayPal Order Pending reason: You set the payment action to Authorization and have not yet captured funds.', 'tc'));
                        }
                        if (isset($_POST['pending_reason']) && $_POST['pending_reason'] == 'echeck') {
                            TC_Order::add_order_note($order->ID, __('The payment is pending because it was made by an eCheck that has not yet cleared.', 'tc'));
                        }
                        if (isset($_POST['pending_reason']) && $_POST['pending_reason'] == 'intl') {
                            TC_Order::add_order_note($order->ID, __('The payment is pending because you hold a non-U.S. account and do not have a withdrawal mechanism. You must manually accept or deny this payment from your Account Overview.', 'tc'));
                        }
                        if (isset($_POST['pending_reason']) && $_POST['pending_reason'] == 'order') {
                            TC_Order::add_order_note($order->ID, __('PayPal Order Pending reason: You set the payment action to Order and have not yet captured funds.', 'tc'));
                        }
                        if (isset($_POST['pending_reason']) && $_POST['pending_reason'] == 'paymentreview') {
                            TC_Order::add_order_note($order->ID, __('The payment is pending while it is reviewed by PayPal for risk.', 'tc'));
                        }
                        if (isset($_POST['pending_reason']) && $_POST['pending_reason'] == 'regulatory_review') {
                            TC_Order::add_order_note($order->ID, __('The payment is pending because PayPal is reviewing it for compliance with government regulations. PayPal will complete this review within 72 hours.', 'tc'));
                        }
                        if (isset($_POST['pending_reason']) && $_POST['pending_reason'] == 'unilateral') {
                            TC_Order::add_order_note($order->ID, __('The payment is pending because it was made to an email address that is not yet registered or confirmed.', 'tc'));
                        }
                        if (isset($_POST['pending_reason']) && $_POST['pending_reason'] == 'upgrade') {
                            TC_Order::add_order_note($order->ID, __('The payment is pending because it was made via credit card and you must upgrade your account to Business or Premier status before you can receive the funds.', 'tc'));
                        }
                        if (isset($_POST['pending_reason']) && $_POST['pending_reason'] == 'verify') {
                            TC_Order::add_order_note($order->ID, __('The payment is pending because you are not yet verified. You must verify your account before you can accept this payment.', 'tc'));
                        }
                        if (isset($_POST['pending_reason']) && $_POST['pending_reason'] == 'other') {
                            TC_Order::add_order_note($order->ID, __('The payment is pending for an unknown reason, please contact PayPal Customer Service.', 'tc'));
                        }
                        break;

                    case 'Processed':
                        break;

                    case 'Canceled-Reversal':
                        break;

                    case 'Canceled':
                        $tc->update_order_status($order->ID, 'order_cancelled');
                        break;
                    default:
                    //do nothing, wait for IPN message
                }

                $tc->remove_order_session_data();
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
                    <?php _e("Sell tickets via PayPal standard payment gateway", 'tc'); ?>
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
                        'default' => '0',
                    ),
                    'email' => array(
                        'title' => __('PayPal E-Mail', 'tc'),
                        'type' => 'text',
                    ),
                    'locale' => array(
                        'title' => __('Locale', 'tc'),
                        'type' => 'select',
                        'options' => $this->locales,
                        'default' => 'US',
                    ),
                    'currency' => array(
                        'title' => __('Currency', 'tc'),
                        'type' => 'select',
                        'options' => $this->currencies,
                        'default' => 'USD',
                    ),
                    'ignore_ipn_errors' => array(
                        'title' => __('Ignore IPN errors', 'tc'),
                        'type' => 'select',
                        'options' => array(
                            'yes' => __('Yes', 'tc'),
                            'no' => __('No', 'tc')
                        ),
                        'default' => 'no',
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

        if (isset($_REQUEST['custom'])) {
            do_action('tc_custom_paypal_ipn_action');
            $this->order_confirmation($_REQUEST['custom']);
        }
    }

}

tc_register_gateway_plugin('TC_Gateway_PayPal_Standard', 'paypal_standard', __('PayPal Standard', 'tc'));
?>