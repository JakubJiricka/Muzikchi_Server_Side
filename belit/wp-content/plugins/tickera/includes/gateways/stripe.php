<?php
/*
  Stripe - Payment Gateway
 */
global $tc;

class TC_Gateway_Stripe extends TC_Gateway_API {

    var $plugin_name = 'stripe';
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

    function init_stripe() {
        global $tc;
        if (!class_exists('Stripe\Stripe') && !class_exists('Stripe') && !class_exists('Stripe\\Stripe')) {//Load Stripe classes only if it doesn't exist already
            require_once($tc->plugin_dir . "/includes/gateways/stripe/init.php");
        }
    }

    function init() {
        global $tc;

        $settings = get_option('tc_settings');

        $this->admin_name = __('Stripe', 'tc');
        $this->public_name = __('Stripe', 'tc');

        $this->method_img_url = apply_filters('tc_gateway_method_img_url', $tc->plugin_url . 'images/gateways/stripe.png', $this->plugin_name);
        $this->admin_img_url = apply_filters('tc_gateway_admin_img_url', $tc->plugin_url . 'images/gateways/small-stripe.png', $this->plugin_name);

        $this->publishable_key = $this->get_option('publishable_key');
        $this->private_key = $this->get_option('private_key');
        $this->force_ssl = $this->get_option('is_ssl', '0') == '1' ? true : false;
        $this->currency = $this->get_option('currency', 'USD');

        $this->send_receipt = $this->get_option('send_receipt', '0');

        add_action('wp_enqueue_scripts', array(&$this, 'enqueue_scripts'));

        $currencies = array(
            "AED" => __('AED - United Arab Emirates Dirham', 'tc'),
            "AFN" => __('AFN - Afghan Afghani', 'tc'),
            "ALL" => __('ALL - Albanian Lek', 'tc'),
            "AMD" => __('AMD - Armenian Dram', 'tc'),
            "ANG" => __('ANG - Netherlands Antillean Gulden', 'tc'),
            "AOA" => __('AOA - Angolan Kwanza', 'tc'),
            "ARS" => __('ARS - Argentine Peso', 'tc'),
            "AUD" => __('AUD - Australian Dollar', 'tc'),
            "AWG" => __('AWG - Aruban Florin', 'tc'),
            "AZN" => __('AZN - Azerbaijani Manat', 'tc'),
            "BAM" => __('BAM - Bosnia & Herzegovina Convertible Mark', 'tc'),
            "BBD" => __('BBD - Barbadian Dollar', 'tc'),
            "BDT" => __('BDT - Bangladeshi Taka', 'tc'),
            "BGN" => __('BGN - Bulgarian Lev', 'tc'),
            "BIF" => __('BIF - Burundian Franc', 'tc'),
            "BMD" => __('BMD - Bermudian Dollar', 'tc'),
            "BND" => __('BND - Brunei Dollar', 'tc'),
            "BOB" => __('BOB - Bolivian Boliviano', 'tc'),
            "BRL" => __('BRL - Brazilian Real', 'tc'),
            "BSD" => __('BSD - Bahamian Dollar', 'tc'),
            "BWP" => __('BWP - Botswana Pula', 'tc'),
            "BZD" => __('BZD - Belize Dollar', 'tc'),
            "CAD" => __('CAD - Canadian Dollar', 'tc'),
            "CDF" => __('CDF - Congolese Franc', 'tc'),
            "CHF" => __('CHF - Swiss Franc', 'tc'),
            "CLP" => __('CLP - Chilean Peso', 'tc'),
            "CNY" => __('CNY - Chinese Renminbi Yuan', 'tc'),
            "COP" => __('COP - Colombian Peso', 'tc'),
            "CRC" => __('CRC - Costa Rican Colon', 'tc'),
            "CVE" => __('CVE - Cape Verdean Escudo', 'tc'),
            "CZK" => __('CZK - Czech Koruna', 'tc'),
            "DJF" => __('DJF - Djiboutian Franc', 'tc'),
            "DKK" => __('DKK - Danish Krone', 'tc'),
            "DOP" => __('DOP - Dominican Peso', 'tc'),
            "DZD" => __('DZD - Algerian Dinar', 'tc'),
            "EEK" => __('EEK - Estonian Kroon', 'tc'),
            "EGP" => __('EGP - Egyptian Pound', 'tc'),
            "ETB" => __('ETB - Ethiopian Birr', 'tc'),
            "EUR" => __('EUR - Euro', 'tc'),
            "FJD" => __('FJD - Fijian Dollar', 'tc'),
            "FKP" => __('FKP - Falkland Islands Pound', 'tc'),
            "GBP" => __('GBP - British Pound', 'tc'),
            "GEL" => __('GEL - Georgian Lari', 'tc'),
            "GIP" => __('GIP - Gibraltar Pound', 'tc'),
            "GMD" => __('GMD - Gambian Dalasi', 'tc'),
            "GNF" => __('GNF - Guinean Franc', 'tc'),
            "GTQ" => __('GTQ - Guatemalan Quetzal', 'tc'),
            "GYD" => __('GYD - Guyanese Dollar', 'tc'),
            "HKD" => __('HKD - Hong Kong Dollar', 'tc'),
            "HNL" => __('HNL - Honduran Lempira', 'tc'),
            "HRK" => __('HRK - Croatian Kuna', 'tc'),
            "HTG" => __('HTG - Haitian Gourde', 'tc'),
            "HUF" => __('HUF - Hungarian Forint', 'tc'),
            "IDR" => __('IDR - Indonesian Rupiah', 'tc'),
            "ILS" => __('ILS - Israeli New Sheqel', 'tc'),
            "INR" => __('INR - Indian Rupee', 'tc'),
            "ISK" => __('ISK - Icelandic Krona', 'tc'),
            "JMD" => __('JMD - Jamaican Dollar', 'tc'),
            "JPY" => __('JPY - Japanese Yen', 'tc'),
            "KES" => __('KES - Kenyan Shilling', 'tc'),
            "KGS" => __('KGS - Kyrgyzstani Som', 'tc'),
            "KHR" => __('KHR - Cambodian Riel', 'tc'),
            "KMF" => __('KMF - Comorian Franc', 'tc'),
            "KRW" => __('KRW - South Korean Won', 'tc'),
            "KYD" => __('KYD - Cayman Islands Dollar', 'tc'),
            "KZT" => __('KZT - Kazakhstani Tenge', 'tc'),
            "LAK" => __('LAK - Lao Kip', 'tc'),
            "LBP" => __('LBP - Lebanese Pound', 'tc'),
            "LKR" => __('LKR - Sri Lankan Rupee', 'tc'),
            "LRD" => __('LRD - Liberian Dollar', 'tc'),
            "LSL" => __('LSL - Lesotho Loti', 'tc'),
            "LTL" => __('LTL - Lithuanian Litas', 'tc'),
            "LVL" => __('LVL - Latvian Lats', 'tc'),
            "MAD" => __('MAD - Moroccan Dirham', 'tc'),
            "MDL" => __('MDL - Moldovan Leu', 'tc'),
            "MGA" => __('MGA - Malagasy Ariary', 'tc'),
            "MKD" => __('MKD - Macedonian Denar', 'tc'),
            "MNT" => __('MNT - Mongolian Tögrög', 'tc'),
            "MOP" => __('MOP - Macanese Pataca', 'tc'),
            "MRO" => __('MRO - Mauritanian Ouguiya', 'tc'),
            "MUR" => __('MUR - Mauritian Rupee', 'tc'),
            "MVR" => __('MVR - Maldivian Rufiyaa', 'tc'),
            "MWK" => __('MWK - Malawian Kwacha', 'tc'),
            "MXN" => __('MXN - Mexican Peso', 'tc'),
            "MYR" => __('MYR - Malaysian Ringgit', 'tc'),
            "MZN" => __('MZN - Mozambican Metical', 'tc'),
            "NAD" => __('NAD - Namibian Dollar', 'tc'),
            "NGN" => __('NGN - Nigerian Naira', 'tc'),
            "NIO" => __('NIO - Nicaraguan Cordoba', 'tc'),
            "NOK" => __('NOK - Norwegian Krone', 'tc'),
            "NPR" => __('NPR - Nepalese Rupee', 'tc'),
            "NZD" => __('NZD - New Zealand Dollar', 'tc'),
            "PAB" => __('PAB - Panamanian Balboa', 'tc'),
            "PEN" => __('PEN - Peruvian Nuevo Sol', 'tc'),
            "PGK" => __('PGK - Papua New Guinean Kina', 'tc'),
            "PHP" => __('PHP - Philippine Peso', 'tc'),
            "PKR" => __('PKR - Pakistani Rupee', 'tc'),
            "PLN" => __('PLN - Polish Zloty', 'tc'),
            "PYG" => __('PYG - Paraguayan Guaraní', 'tc'),
            "QAR" => __('QAR - Qatari Riyal', 'tc'),
            "RON" => __('RON - Romanian Leu', 'tc'),
            "RSD" => __('RSD - Serbian Dinar', 'tc'),
            "RUB" => __('RUB - Russian Ruble', 'tc'),
            "RWF" => __('RWF - Rwandan Franc', 'tc'),
            "SAR" => __('SAR - Saudi Riyal', 'tc'),
            "SBD" => __('SBD - Solomon Islands Dollar', 'tc'),
            "SCR" => __('SCR - Seychellois Rupee', 'tc'),
            "SEK" => __('SEK - Swedish Krona', 'tc'),
            "SGD" => __('SGD - Singapore Dollar', 'tc'),
            "SHP" => __('SHP - Saint Helenian Pound', 'tc'),
            "SLL" => __('SLL - Sierra Leonean Leone', 'tc'),
            "SOS" => __('SOS - Somali Shilling', 'tc'),
            "SRD" => __('SRD - Surinamese Dollar', 'tc'),
            "STD" => __('STD - São Tomé and Príncipe Dobra', 'tc'),
            "SVC" => __('SVC - Salvadoran Colon', 'tc'),
            "SZL" => __('SZL - Swazi Lilangeni', 'tc'),
            "THB" => __('THB - Thai Baht', 'tc'),
            "TJS" => __('TJS - Tajikistani Somoni', 'tc'),
            "TOP" => __('TOP - Tonga Pa\'anga', 'tc'),
            "TRY" => __('TRY - Turkish Lira', 'tc'),
            "TTD" => __('TTD - Trinidad and Tobago Dollar', 'tc'),
            "TWD" => __('TWD - New Taiwan Dollar', 'tc'),
            "TZS" => __('TZS - Tanzanian Shilling', 'tc'),
            "UAH" => __('UAH - Ukrainian Hryvnia', 'tc'),
            "UGX" => __('UGX - Ugandan Shilling', 'tc'),
            "USD" => __('USD - United States Dollar', 'tc'),
            "UYI" => __('UYI - Uruguayan Peso', 'tc'),
            "UZS" => __('UZS - Uzbekistani Som', 'tc'),
            "VEF" => __('VEF - Venezuelan Bolivar', 'tc'),
            "VND" => __('VND - Vietnamese Dong ', 'tc'),
            "VUV" => __('VUV - Vanuatu Vatu', 'tc'),
            "WST" => __('WST - Samoan Tala', 'tc'),
            "XAF" => __('XAF - Central African Cfa Franc', 'tc'),
            "XCD" => __('XCD - East Caribbean Dollar', 'tc'),
            "XOF" => __('XOF - West African Cfa Franc', 'tc'),
            "XPF" => __('XPF - Cfp Franc', 'tc'),
            "YER" => __('YER - Yemeni Rial', 'tc'),
            "ZAR" => __('ZAR - South African Rand', 'tc'),
            "ZMW" => __('ZMW - Zambian Kwacha', 'tc'),
        );

        $this->currencies = $currencies;
    }

    function enqueue_scripts() {
        global $tc;

        if ($this->is_active() && $this->is_payment_page()) {
            $this->init_stripe();
            wp_enqueue_script('js-stripe', 'https://js.stripe.com/v1/', array('jquery'));
            wp_enqueue_script('stripe-token', $tc->plugin_url . '/includes/gateways/stripe/stripe_token.js', array('js-stripe', 'jquery'));
            wp_localize_script('stripe-token', 'stripe', array('publisher_key' => $this->publishable_key,
                'name' => __('Please enter the full Cardholder Name.', 'tc'),
                'number' => __('Please enter a valid Credit Card Number.', 'tc'),
                'expiration' => __('Please choose a valid expiration date.', 'tc'),
                'cvv2' => __('Please enter a valid card security code. This is the 3 digits on the signature panel, or 4 digits on the front of Amex cards.', 'tc')
            ));
        }
    }

    function payment_form($cart) {
        global $tc;

        $this->maybe_start_session();

        $content = '';

        $content .= '<div id="stripe_checkout_errors"></div>';

        $content .= '<table class="tc_cart_billing">
        <thead><tr>
          <th colspan="2">' . __('Enter Your Credit Card Information:', 'tc') . '</th>
        </tr></thead>
        <tbody>
          <tr>
          <td>' . __('Cardholder Name:', 'tc') . '</td><td>
					<input id="cc_name" type="text" value="' . apply_filters('tc_stripe_full_name', esc_attr($this->buyer_info('full_name'))) . '" /> </td>
          </tr>';
        $content .= '<tr>';
        $content .= '<td>';
        $content .= __('Card Number', 'tc');
        $content .= '</td>';
        $content .= '<td>';
        $content .= '<input type="text" autocomplete="off" id="cc_number"/>';
        $content .= '</td>';
        $content .= '</tr>';
        $content .= '<tr>';
        $content .= '<td>';
        $content .= __('Expiration:', 'tc');
        $content .= '</td>';
        $content .= '<td>';
        $content .= '<select id="cc_month">';
        $content .= tc_months_dropdown();
        $content .= '</select>';
        $content .= '<span> / </span>';
        $content .= '<select id="cc_year">';
        $content .= tc_years_dropdown('', true);
        $content .= '</select>';
        $content .= '</td>';
        $content .= '</tr>';
        $content .= '<tr>';
        $content .= '<td>';
        $content .= __('CVC:', 'tc');
        $content .= '</td>';
        $content .= '<td>';
        $content .= '<input type="text" size="4" autocomplete="off" id="cc_cvv2" />';
        $content .= '</td>';
        $content .= '</tr>';
        $content .= '<tr>';
        $content .= '<td>';
        $content .= __('Country:', 'tc');
        $content .= '</td>';   
        $content .= '<td>';
        $content .=   tc_countries('field_country', '');                                                    
        $content .= '</td>';
        $content .= '</tr>';
        $content .= '<tr>';
        $content .= '<td>';
        $content .= __('Region / State:', 'tc');
        $content .= '</td>';   
        $content .= '<td>';
        $content .= '<input tabindex="6" id="tc_region"  type="text" autocomplete="off"/>';
        $content .= '</td>';
        $content .= '</tr>';
        $content .= '<tr>';
        $content .= '<td>';
        $content .= __('City:', 'tc');
        $content .= '</td>';   
        $content .= '<td>';
        $content .= '<input tabindex="7" id="tc_city" type="text" autocomplete="off" />';
        $content .= '</td>';
        $content .= '</tr>';
        $content .= '<tr>';
        $content .= '<td>';
        $content .= __('Address:', 'tc');
        $content .= '</td>';   
        $content .= '<td>';
        $content .= '<input tabindex="8" type="text" id="tc_address" autocomplete="off" />';
        $content .= '</td>';
        $content .= '</tr>';
        $content .= '<tr>';
        $content .= '<td>';
        $content .= __('Billing Zip / Postal Code:', 'tc');
        $content .= '</td>';   
        $content .= '<td>';
        $content .= '<input type="text" tabindex="9" size="4" autocomplete="off" id="cc_zip_code" />';
        $content .= '</td>';
        $content .= '</tr>';

        $content .= '</table>';
        $content .= '<span id="stripe_processing" style="display:none; float:right;"><img src="' . $tc->plugin_url . 'images/loading.gif" /> ' . __('Processing...', 'tc') . '</span>';
        return $content;
    }

    function order_confirmation_message($order, $cart_info = '') {
        global $tc;

        $order = tc_get_order_id_by_name($order);
        $order = new TC_Order($order->ID);

        $content = '';

        if ($order->details->post_status == 'order_received') {
            $content .= '<p>' . sprintf(__('Your payment via Stripe for this order totaling <strong>%s</strong> is not yet complete.', 'tc'), apply_filters('tc_cart_currency_and_format', $order->details->tc_payment_info['total'])) . '</p>';
            $content .= '<p>' . __('Current order status:', 'tc') . ' <strong>' . __('Pending Payment') . '</strong></p>';
        } else if ($order->details->post_status == 'order_fraud') {
            $content .= '<p>' . __('Your payment is under review. We will back to you soon.', 'tc') . '</p>';
        } else if ($order->details->post_status == 'order_paid') {
            $content .= '<p>' . sprintf(__('Your payment via Stripe for this order totaling <strong>%s</strong> is complete.', 'tc'), apply_filters('tc_cart_currency_and_format', $order->details->tc_payment_info['total'])) . '</p>';
        } else if ($order->details->post_status == 'order_cancelled') {
            $content .= '<p>' . sprintf(__('Your payment via Stripe for this order totaling <strong>%s</strong> is cancelled.', 'tc'), $this->public_name, apply_filters('tc_cart_currency_and_format', $order->details->tc_payment_info['total'])) . '</p>';
        }

        $content = apply_filters('tc_order_confirmation_message_content_' . $this->plugin_name, $content);

        $content = apply_filters('tc_order_confirmation_message_content', $content, $order);

        $tc->remove_order_session_data();
        unset($_SESSION['stripeToken']);
        $tc->maybe_skip_confirmation_screen($this, $order);
        return $content;
    }

    function process_payment($cart) {
        global $tc;

        tc_final_cart_check($cart);
        
        $this->init_stripe();

        $this->maybe_start_session();
        $this->save_cart_info();

        $_SESSION['stripeToken'] = $_POST['stripeToken'];

        if (!isset($_SESSION['stripeToken'])) {
            $tc->cart_checkout_error(__('The Stripe Token was not generated correctly. Please go back and try again.', 'tc'));
            return false;
        }

        \Stripe\Stripe::setApiKey($this->private_key);

        $order_id = $tc->generate_order_id();

        try {
            $stripe_params = array(
                "amount" => $this->total() * 100, //cents
                "currency" => strtolower($this->currency),
                "card" => $_SESSION['stripeToken'],
                "description" => $this->cart_items(),
                //"zip-code" =>
            );

            if ($this->send_receipt == '1') {
                $stripe_params["receipt_email"] = $this->buyer_info('email');
            }

            $charge = \Stripe\Charge::create($stripe_params);

            if ($charge->paid == 'true') {
                $payment_info = array();
                $payment_info['method'] = __('Credit Card', 'tc');
                $payment_info['transaction_id'] = $charge->id;
                $payment_info = $this->save_payment_info();

                $paid = true;
                $order = $tc->create_order($order_id, $this->cart_contents(), $this->cart_info(), $payment_info, $paid);

                ob_start();
                @wp_redirect($tc->get_confirmation_slug(true, $order_id));
                tc_js_redirect($tc->get_confirmation_slug(true, $order_id));
                exit;
            }
        } catch (\Stripe\Error\Card $e) {
            unset($_SESSION['stripeToken']);
            $_SESSION['tc_gateway_error'] = sprintf(__('There was an error processing your card - "%s".', 'tc'), $e->getMessage());
            ob_start();
            @wp_redirect($tc->get_payment_slug(true));
            tc_js_redirect($tc->get_payment_slug(true));
            exit;
        }
        return false;
    }

    function gateway_admin_settings($settings, $visible) {
        global $tc;
        ?>
        <div id="<?php echo $this->plugin_name; ?>" class="postbox" <?php echo (!$visible ? 'style="display:none;"' : ''); ?>>
            <h3 class='hndle'><span><?php printf(__('%s Settings', 'tc'), $this->admin_name); ?></span></h3>
            <div class="inside">
                <span class="description">
                    <?php _e("Accept Visa, MasterCard, American Express, Discover, JCB, and Diners Club cards directly on your site. Credit cards go directly to Stripe's secure environment, and never hit your servers so you can avoid most PCI requirements.", 'tc') ?>
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
                    'send_receipt' => array(
                        'title' => __('Send Receipt', 'tc'),
                        'type' => 'select',
                        'options' => array(
                            '1' => __('Yes', 'tc'),
                            '0' => __('No', 'tc')
                        ),
                        'default' => '0',
                        'description' => __('Send Stripe Receipt to a customer automatically upon completed purchase.', 'tc')
                    ),
                    'private_key' => array(
                        'title' => __('Secret API Key', 'tc'),
                        'type' => 'text',
                        'description' => __('You must login to Stripe to <a target="_blank" href="https://manage.stripe.com/#account/apikeys">get your API credentials</a>. You can enter your test credentials, then live ones when ready.', 'tc'),
                    ),
                    'publishable_key' => array(
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

tc_register_gateway_plugin('TC_Gateway_Stripe', 'stripe', __('Stripe', 'tc'));
?>