<?php
/*
  iPay88 - Payment Gateway
 */

class TC_Gateway_iPay88 extends TC_Gateway_API {

	var $plugin_name				 = 'ipay';
	var $admin_name				 = '';
	var $public_name				 = '';
	var $method_img_url			 = '';
	var $admin_img_url			 = '';
	var $force_ssl				 = false;
	var $ipn_url;
	var $currencies				 = array();
	var $live_url;
	var $merchant_id				 = '';
	var $notify_url				 = '';
	var $success_url				 = '';
	var $fail_url				 = '';
	var $automatically_activated	 = false;
	var $currency				 = '';
	var $skip_payment_screen		 = false;

	//Support for older payment gateway API
	function on_creation() {
		$this->init();
	}

	function init() {
		global $tc;

		$this->admin_name	 = __( 'iPay88', 'tc' );
		$this->public_name	 = __( 'iPay88', 'tc' );
		$this->live_url		 = 'https://www.mobile88.com/epayment/entry.asp';
		$this->notify_url	 = $this->ipn_url;
		$this->success_url	 = '';

		$this->method_img_url	 = apply_filters( 'tc_gateway_method_img_url', $tc->plugin_url . 'images/gateways/ipay88.png', $this->plugin_name );
		$this->admin_img_url	 = apply_filters( 'tc_gateway_admin_img_url', $tc->plugin_url . 'images/gateways/small-ipay88.png', $this->plugin_name );

		$this->currency = $this->get_option( 'currency', 'MYR' );

		$this->MerchantCode	 = $this->get_option( 'MerchantCode' );
		$this->MerchantKey	 = $this->get_option( 'MerchantKey' );

		$this->hash_amount		 = 0;
		$this->formatted_amount	 = 0;

		$currencies = array(
			"MYR"	 => __( 'MYR - Malaysian Ringgit', 'tc' ),
			"THB"	 => __( 'THB - Thailand Baht', 'tc' ),
			"SGD"	 => __( 'SGD - Singapore Dollar', 'tc' ),
			"CNY"	 => __( 'CNY - Chinese Yuan Renminbi', 'tc' ),
			"AUD"	 => __( 'AUD - Australian Dollar', 'tc' ),
			"GBP"	 => __( 'GBP - British Pound', 'tc' ),
			"CAD"	 => __( 'CAD - Canadian Dollar', 'tc' ),
			"EUR"	 => __( 'EUR - Euro', 'tc' ),
			"USD"	 => __( 'USD - U.S. Dollar', 'tc' ),
		);

		$this->paymenttype_options = array(
			'2'		 => __( 'Credit Card', 'tc' ),
			'6'		 => __( 'Maybank2U', 'tc' ),
			'8'		 => __( 'Alliance Online', 'tc' ),
			'10'	 => __( 'AmBank', 'tc' ),
			'14'	 => __( 'RHB', 'tc' ),
			'15'	 => __( 'Hong Leong Online', 'tc' ),
			'16'	 => __( 'FPX', 'tc' ),
			'17'	 => __( 'Mobile Money', 'tc' ),
			'20'	 => __( 'CIMB Click', 'tc' ),
			'22'	 => __( 'Web Cash', 'tc' ),
			'23'	 => __( 'MEPS Cash', 'tc' ),
			'33'	 => __( 'PayPal', 'tc' ),
			'103'	 => __( 'AffinBank', 'tc' ),
		);

		$this->currencies = $currencies;
	}

	function payment_form( $cart ) {
		global $tc;

		$settings = get_option( 'tc_settings' );

		$saved_payment_option_values = $this->get_option( 'payment_types', array() );

		$content = '
			<table class="tc_cart_billing">
<thead>
<tr>
<th colspan="2">' . __( 'Choose payment method:', 'tc' ) . '</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2">';
		$first	 = true;
		foreach ( $this->paymenttype_options as $payment_option => $payment_title ) {
			if ( in_array( $payment_option, $saved_payment_option_values ) ) {
				$content .= '<input type="radio" name="ipay_payment_method" value="' . esc_attr( $payment_option ) . '" ' . ($first ? 'checked' : '') . ' /> ' . $payment_title . '<br />';
				$first = false;
			}
		}
		$content .= '</td>
</tr>
</tbody></table>';


		return $content;
	}

	function process_payment( $cart ) {
		global $tc;

                tc_final_cart_check($cart);
                
		$this->maybe_start_session();
		$this->save_cart_info();

		$order_id			 = $tc->generate_order_id();
		$this->success_url	 = $tc->get_confirmation_slug( true, $order_id );

		$param_list = array();

		$paid = false;

		$payment_info = $this->save_payment_info();

		$total = number_format( $this->total(), 2, '.', '' );

		$ipay88_args = array(
			'MerchantCode'	 => trim( $this->MerchantCode ),
			'PaymentId'		 => $_POST[ 'ipay_payment_method' ],
			'RefNo'			 => trim( $order_id ),
			'Amount'		 => $total,
			'Currency'		 => $this->currency,
			'ProdDesc'		 => $this->cart_items(),
			'UserName'		 => trim( $this->buyer_info( 'full_name' ) ),
			'UserEmail'		 => trim( $this->buyer_info( 'email' ) ),
			'UserContact'	 => trim( $this->buyer_info( 'email' ) ),
			'Remark'		 => '',
			'Lang'			 => 'UTF-8',
			'signature'		 => $this->iPay88_signature( $this->MerchantKey . $this->MerchantCode . $order_id . number_format( $this->total(), 2, '', '' ) . $this->currency ),
			'ResponseURL'	 => esc_url( $tc->get_confirmation_slug( true, $order_id ) ),
			'BackendURL'	 => esc_url( $tc->get_confirmation_slug( true, $order_id ) ),
		);

		$ipay88_form_array = array();

		foreach ( $ipay88_args as $key => $value ) {
			$ipay88_form_array[] = '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" />';
		}

		$tc->create_order( $order_id, $this->cart_contents(), $this->cart_info(), $payment_info, $paid );
		?>
		<form action="<?php echo esc_attr( $this->live_url ); ?>" method="post" name="ipay88_payment_form">
			<?php echo implode( '', $ipay88_form_array ); ?>
		</form>
		<script>document.forms['ipay88_payment_form'].submit();</script>
		<?php
		die;
	}

	function iPay88_signature( $source ) {
		return base64_encode( $this->hex2bin( sha1( $source ) ) );
	}

	function hex2bin( $hexSource ) {
		$bin = '';
		for ( $i = 0; $i < strlen( $hexSource ); $i = $i + 2 ) {
			$bin .= chr( hexdec( substr( $hexSource, $i, 2 ) ) );
		}
		return $bin;
	}

	function order_confirmation( $order, $payment_info = '', $cart_info = '' ) {
		global $tc;

		if ( isset( $_POST[ 'ErrDesc' ] ) && !empty( $_POST[ 'ErrDesc' ] ) ) {
			$_SESSION[ 'tc_gateway_error' ] = $_POST[ 'ErrDesc' ];
			ob_start();
			@wp_redirect( $tc->get_payment_slug( true ) );
			tc_js_redirect( $tc->get_payment_slug( true ) );
			exit;
		} else {
			$this->ipn();
		}
	}

	function gateway_admin_settings( $settings, $visible ) {
		global $tc;
		?>
		<div id="<?php echo $this->plugin_name; ?>" class="postbox" <?php echo (!$visible ? 'style="display:none;"' : ''); ?>>
			<h3 class='hndle'><span><?php printf( __( '%s Settings', 'tc' ), $this->admin_name ); ?></span></h3>
			<div class="inside">
				<span class="description"><?php _e( 'iPay88 is a payment gateway for Malaysia. It works by redirecting the customer to iPay88 server to make a payment and then returns the customer back to your confirmation page.', 'tc' ) ?></span>
				<?php
				$fields = array(
					'MerchantCode'	 => array(
						'title'	 => __( 'Merchant Code', 'tc' ),
						'type'	 => 'text',
					),
					'MerchantKey'	 => array(
						'title'	 => __( 'Merchant Key', 'tc' ),
						'type'	 => 'text',
					),
					'payment_types'	 => array(
						'title'		 => __( 'Payment Types', 'tc' ),
						'type'		 => 'checkboxes',
						'options'	 => $this->paymenttype_options,
					),
					'currency'		 => array(
						'title'		 => __( 'Currency', 'tc' ),
						'type'		 => 'select',
						'options'	 => $this->currencies,
						'default'	 => 'MYR',
					),
				);

				$form = new TC_Form_Fields_API( $fields, 'tc', 'gateways', $this->plugin_name );
				?>
				<table class="form-table">
					<?php $form->admin_options(); ?>
				</table>

			</div>
		</div>

		<?php
	}

	function ipn() {
		$this->check_status_response_ipay88();
	}

	function check_status_response_ipay88() {
		global $tc;

		$posted = stripslashes_deep( $_POST );

		if ( $this->validate_response() ) {

			$refno	 = $_POST[ 'RefNo' ];
			$transid = $_POST[ 'TransId' ];
			$estatus = $_POST[ 'Status' ];
			$errdesc = $_POST[ 'ErrDesc' ];

			$order = tc_get_order_id_by_name( $refno );

			if ( $estatus == 1 ) {
				$tc->update_order_payment_status( $order->ID, true );
			} else {
				//not paid
			}
		} else {
			//echo 'INVALID RESPONSE';
		}
	}

	function validate_response() {

		$signature = $this->iPay88_signature( $_POST );

		if ( $_POST[ 'Signature' ] == $signature ) {
			return true;
		} else {
			return true; //should be false here
		}
	}

}

tc_register_gateway_plugin( 'TC_Gateway_iPay88', 'ipay', __( 'iPay88', 'tc' ) );
?>