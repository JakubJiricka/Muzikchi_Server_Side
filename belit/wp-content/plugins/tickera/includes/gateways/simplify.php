<?php
/*
  Simplify Commerce - Payment Gateway
 */

class TC_Gateway_Simplify extends TC_Gateway_API {

    var $plugin_name = 'simplify_commerce';
    var $admin_name = '';
    var $public_name = '';
    var $method_img_url = '';
    var $admin_img_url = '';
    var $force_ssl;
    var $ipn_url;
    var $currency;
    var $currencies = array();
    var $automatically_activated = false;
    var $skip_payment_screen = false;
    var $sandbox_public_key;
    var $sandbox_private_key;
    var $live_public_key;
    var $live_private_key;

    //Support for older payment gateway API
    function on_creation() {
        $this->init();
    }

    function init() {
        global $tc;
        $settings = get_option('tc_settings');

        $this->admin_name = __('Simplify Commerce', 'tc');
        $this->public_name = __('Simplify Commerce', 'tc');

        $this->method_img_url = apply_filters('tc_gateway_method_img_url', $tc->plugin_url . 'images/gateways/simplify.png', $this->plugin_name);
        $this->admin_img_url = apply_filters('tc_gateway_admin_img_url', $tc->plugin_url . 'images/gateways/small-simplify.png', $this->plugin_name);

        $this->public_key = $this->get_option('public_key');
        $this->private_key = $this->get_option('private_key');

        $this->currency = $this->get_option('currency', 'USD');

        add_action('wp_enqueue_scripts', array(&$this, 'enqueue_scripts'));

        $this->currencies = array(
            "USD" => __('USD - United States Dollar', 'tc'),
        );
    }

    function enqueue_scripts() {
        global $tc;

        if ($this->is_active() && $this->is_payment_page()) {

            wp_enqueue_script(
                    'js-simplify-commerce', 'https://www.simplify.com/commerce/v1/simplify.js', array('jquery')
            );

            wp_enqueue_script(
                    'simplify-commerce-token', $tc->plugin_url . '/includes/gateways/simplify/simplify_commerce_token.js', array('js-simplify-commerce', 'jquery')
            );

            wp_localize_script(
                    'simplify-commerce-token', 'SimplifyGateway', array(
                'public_key' => $this->get_option('public_key'),
                    )
            );
        }
    }

    function payment_form($cart) {
        global $tc;

        $content = '';

        $content .= '<div id="simplify_commerce_checkout_errors"></div>';

        $content .= '<table class="tc_cart_billing">
	        <thead><tr>
	          <th colspan="2">' . __('Enter Your Credit Card Information:', 'tc') . '</th>
	        </tr></thead>
	        <tbody>
	          <tr>
	          <td align="right">' . __('Cardholder Name:', 'tc') . '</td><td>
						<input id="sc_cc_name" type="text" value="' . esc_attr($this->buyer_info('full_name')) . '" /> </td>
	          </tr>';
        $content .= '<tr>';
        $content .= '<td align="right">';
        $content .= __('Card Number', 'tc');
        $content .= '</td>';
        $content .= '<td>';
        $content .= '<input type="text" autocomplete="off" id="sc_cc_number"/>';
        $content .= '</td>';
        $content .= '</tr>';
        $content .= '<tr>';
        $content .= '<td align="right">';
        $content .= __('Expiration:', 'tc');
        $content .= '</td>';
        $content .= '<td>';
        $content .= '<select id="sc_cc_month">';
        $content .= tc_months_dropdown();
        $content .= '</select>';
        $content .= '<span> / </span>';
        $content .= '<select id="sc_cc_year">';
        $content .= tc_years_dropdown('', false);
        $content .= '</select>';
        $content .= '</td>';
        $content .= '</tr>';
        $content .= '<tr>';
        $content .= '<td align="right">';
        $content .= __('CVC:', 'tc');
        $content .= '</td>';
        $content .= '<td>';
        $content .= '<input id="sc_cc_cvc" type="text" maxlength="4" autocomplete="off" value=""/>';
        $content .= '</td>';
        $content .= '</tr>';
        $content .= '</table>';
        $content .= '<span id="simplify_commerce_processing" style="display:none; float:right;"><img src="' . $tc->plugin_url . 'images/loading.gif" /> ' . __('Processing...', 'tc') . '</span>';
        return $content;
    }

    function order_confirmation_message($order, $cart_info = '') {

        global $tc;

        $order = tc_get_order_id_by_name($order);
        $order = new TC_Order($order->ID);

        $content = '';

        if ($order->details->post_status == 'order_received') {

            $content .= '<p>' . sprintf(__('Your payment via Simplify Commerce for this order totaling <strong>%s</strong> is not yet complete.', 'tc'), apply_filters('tc_cart_currency_and_format', $order->details->tc_payment_info['total'])) . '</p>';
            $content .= '<p>' . __('Current order status:', 'tc') . ' <strong>' . __('Pending Payment') . '</strong></p>';
        } else if ($order->details->post_status == 'order_fraud') {
            $content .= '<p>' . __('Your payment is under review. We will back to you soon.', 'tc') . '</p>';
        } else if ($order->details->post_status == 'order_paid') {
            $content .= '<p>' . sprintf(__('Your payment via Simplify Commerce for this order totaling <strong>%s</strong> is complete.', 'tc'), apply_filters('tc_cart_currency_and_format', $order->details->tc_payment_info['total'])) . '</p>';
        } else if ($order->details->post_status == 'order_cancelled') {
            $content .= '<p>' . sprintf(__('Your payment via Simplify for this order totaling <strong>%s</strong> is cancelled.', 'tc'), $this->public_name, apply_filters('tc_cart_currency_and_format', $order->details->tc_payment_info['total'])) . '</p>';
        }

        $content = apply_filters('tc_order_confirmation_message_content_' . $this->plugin_name, $content);

        $content = apply_filters('tc_order_confirmation_message_content', $content, $order);

        $tc->remove_order_session_data();

        unset($_SESSION['simplifyToken']);
        $tc->maybe_skip_confirmation_screen($this, $order);
        return $content;
    }

    function process_payment($cart) {

        global $tc;

        tc_final_cart_check($cart);
        
        $this->maybe_start_session();
        $this->save_cart_info();

        if (isset($_POST['simplify_payment_form']) && $_POST['simplify_payment_form'] == 'not_available') {

            $_SESSION['tc_gateway_error'] = __('The Simplify Commerce is not available at the moment. Please try another method or contact the admnistrator', 'tc-sc');
            ob_start();
            @wp_redirect($tc->get_payment_slug(true));
            tc_js_redirect($tc->get_payment_slug(true));
            return false;
        }

        if (!isset($_POST['simplifyToken'])) {
            $_SESSION['tc_gateway_error'] = __('The Simplify Commerce Token was not generated correctly. Please go back and try again.', 'tc');
            ob_start();
            @wp_redirect($tc->get_payment_slug(true));
            tc_js_redirect($tc->get_payment_slug(true));
            return false;
        }


        $_SESSION['simplifyToken'] = $_POST['simplifyToken'];

        require_once($tc->plugin_dir . "/includes/gateways/simplify/Simplify.php");

        //generate a tickera order id
        $order_id = $tc->generate_order_id();

        Simplify::$publicKey = $this->public_key;
        Simplify::$privateKey = $this->private_key;

        try {

            $payment = Simplify_Payment::createPayment(
                            array(
                                'amount' => $this->total() * 100,
                                'token' => $_SESSION['simplifyToken'],
                                'description' => $this->cart_items(),
                                'reference' => $order_id,
                                'currency' => $this->currency
                            )
            );


            if ($payment->paymentStatus == 'APPROVED') {

                $payment_info = array();
                $payment_info['transaction_id'] = $payment->id;
                $payment_info = $this->save_payment_info();

                $tc->create_order($order_id, $this->cart_contents(), $this->cart_info(), $payment_info, true);

                ob_start();
                @wp_redirect($tc->get_confirmation_slug(true, $order_id));
                tc_js_redirect($tc->get_confirmation_slug(true));
                exit;
            } else if ($payment->paymentStatus == 'DECLINED') {
                //run if the card is declined etc.
                //
				$_SESSION['tc_gateway_error'] = apply_filters('tc_simplify_declined_card', __('We\'re very sorry but the card you entered was declined ', 'tc-sc'));
                ob_start();
                @wp_redirect($tc->get_payment_slug(true));
                tc_js_redirect($tc->get_payment_slug(true));
                exit;
            }
        } catch (Simplify_ApiException $e) {
            unset($_SESSION['simplifyToken']);
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
                    <?php _e("Simplify makes it easy to accept payments for U.S citizen online consumer.", 'tc') ?>
                </span>

                <?php
                $fields = array(
                    'public_key' => array(
                        'title' => __('Public API Key', 'tc'),
                        'type' => 'text',
                    ),
                    'private_key' => array(
                        'title' => __('Private API Key', 'tc'),
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

tc_register_gateway_plugin('TC_Gateway_Simplify', 'simplify_commerce', __('Simplify Commerce', 'tc'));
?>