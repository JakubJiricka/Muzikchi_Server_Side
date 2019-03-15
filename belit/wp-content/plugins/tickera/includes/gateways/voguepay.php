<?php
/*
  VoguePay - Payment Gateway
 */

class TC_Gateway_VoguePay extends TC_Gateway_API {

    var $plugin_name = 'voguepay';
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
    var $currency = 'NGN';
    var $skip_payment_screen = true;

    //Support for older payment gateway API
    function on_creation() {
        $this->init();
    }

    function init() {
        global $tc;

        $this->admin_name = __('VoguePay', 'tc');
        $this->public_name = __('VoguePay', 'tc');
        $this->live_url = 'https://voguepay.com/pay/';
        $this->notify_url = $this->ipn_url;
        $this->success_url = '';

        $this->method_img_url = apply_filters('tc_gateway_method_img_url', $tc->plugin_url . 'images/gateways/voguepay.png', $this->plugin_name);
        $this->admin_img_url = apply_filters('tc_gateway_admin_img_url', $tc->plugin_url . 'images/gateways/small-vogue.png', $this->plugin_name);

        $this->currency = $this->get_option('currency', 'NGN');
        $this->merchant_id = $this->get_option('merchant_id', 'demo');

        $currencies = array(
            "NGN" => __('NGN - Nigerian Naira', 'tc'),
        );

        $this->currencies = $currencies;
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
        $this->success_url = $tc->get_confirmation_slug(true, $order_id);

        $param_list = array();

        $paid = false;

        $payment_info = $this->save_payment_info();

        $tc->create_order($order_id, $this->cart_contents(), $this->cart_info(), $payment_info, $paid);
        header('Content-Type: text/html');
        ?>
        <form method='POST' action='<?php echo $this->live_url; ?>' style="display: none;" id="voguepay_form">
            <input type='hidden' name='v_merchant_id' value='<?php echo $this->merchant_id; ?>' />
            <input type='hidden' name='merchant_ref' value='<?php echo $order_id; ?>' />
            <input type='hidden' name='memo' value='<?php
            echo $this->cart_items();
            ?>' />
            <input type='hidden' name='total' value='<?php echo $this->total(); ?>' />
            <input type='hidden' name='notify_url' value='<?php echo $this->notify_url; ?>' />
            <input type='hidden' name='success_url' value='<?php echo $this->success_url; ?>' />
            <input type='hidden' name='fail_url' value='<?php echo $tc->get_cancel_url($order_id); ?>' />
            <input type='hidden' name='developer_code' value='5479c315e3369' />
            <input type="submit" name="voguepay_submit" />
        </form>
        <script>
            document.getElementById("voguepay_form").submit();
        </script>
        <?php
        exit(0);
    }

    function order_confirmation($order, $payment_info = '', $cart_info = '') {
        $this->ipn();
    }

    function gateway_admin_settings($settings, $visible) {
        global $tc;
        ?>
        <div id="<?php echo $this->plugin_name; ?>" class="postbox" <?php echo (!$visible ? 'style="display:none;"' : ''); ?>>
            <h3 class='hndle'><span><?php printf(__('%s Settings', 'tc'), $this->admin_name); ?></span></h3>
            <div class="inside">
                <span class="description">
                    <?php _e("VoguePay Payment Gateway allows you to sell tickets and receive Mastercard, Verve Card and Visa Card Payments. Please note that gateway suppports only Nigerian Naira (NGN) currency.", 'tc') ?>
                </span>

                <?php
                $fields = array(
                    'merchant_id' => array(
                        'title' => __('Merchant ID', 'tc'),
                        'type' => 'text',
                    ),
                    'currency' => array(
                        'title' => __('Currency', 'tc'),
                        'type' => 'select',
                        'options' => $this->currencies,
                        'default' => 'NGD',
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

        if (isset($_POST['transaction_id'])) {

            $transaction_id = $_POST['transaction_id'];

            if ($this->merchant_id == 'demo') {
                $json = wp_remote_get('https://voguepay.com/?v_transaction_id=' . $transaction_id . '&type=json&demo=true');
            } else {
                $json = wp_remote_get('https://voguepay.com/?v_transaction_id=' . $transaction_id . '&type=json');
            }

            $transaction = json_decode($json['body'], true);
            $transaction_id = $transaction['transaction_id'];
            $merchant_ref = $transaction['merchant_ref'];

            $order_id = tc_get_order_id_by_name($merchant_ref); //get order id from order name
            $order_id = $order_id->ID;

            $order = new TC_Order($order_id);
            $order_total = $order->details->tc_payment_info['total'];
            $amount_paid = $transaction['total'];

            if ($transaction['status'] == 'Approved') {
                if (round($amount_paid, 2) < round($order_total, 2)) {
                    $tc->update_order_status($order->ID, 'order_fraud');
                    //die('Fraud detected. Price paid ' . $amount_paid . ' and original price of ' . $order_total . ' do not match.');
                    $_SESSION['tc_gateway_error'] = sprintf(__('Something went wrong. Price paid %s and original price of %s do not match.', 'tc'), $amount_paid, $order_total);
                    ob_start();
                    @wp_redirect($tc->get_payment_slug(true));
                    tc_js_redirect($tc->get_payment_slug(true));
                    exit;
                }
                $tc->update_order_payment_status($order_id, true);
                //die( 'IPN Processed OK. Payment for order successfull.' );
            } else {
                //die( 'IPN Processed OK. Payment Failed' );
            }
        }
    }

}

tc_register_gateway_plugin('TC_Gateway_VoguePay', 'voguepay', __('VoguePay', 'tc'));
?>