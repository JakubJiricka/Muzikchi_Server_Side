<?php

class TC_Gateway_Netbanx extends TC_Gateway_API {

	var $plugin_name			 = 'netbanx';
	var $admin_name			 = '';
	var $public_name			 = '';
	var $force_ssl;
	var $ipn_url;
	var $skip_form			 = false;
	var $publishable_key, $secret_key, $currency;
	var $skip_payment_screen	 = false;

	function on_creation() {
		$this->init();
	}

	function init() {
		global $tc;

		$this->admin_name	 = __( 'Netbanx', 'tc' );
		$this->public_name	 = __( 'Netbanx', 'tc' );

		$this->method_img_url	 = apply_filters( 'tc_gateway_method_img_url', $tc->plugin_url . 'images/gateways/netbanx.jpg', $this->plugin_name );
		$this->admin_img_url	 = apply_filters( 'tc_gateway_admin_img_url', $tc->plugin_url . 'images/gateways/small-netbanx.png', $this->plugin_name );

		$this->mode				 = $this->get_option( 'mode', 'sandbox' );
		$this->api_key_id		 = $this->get_option( 'api_key_id' );
		$this->api_key_secret	 = $this->get_option( 'api_key_secret' );
		$this->account_number	 = $this->get_option( 'account_number' );

		$this->force_ssl = $this->mode == 'live' ? true : false;
	}

	function payment_form( $cart ) {

		$name = $this->buyer_info( 'full_name' );

		$content = '';

		$content .= '<table class="tc_cart_billing"><thead><tr><th colspan="2">' . __( 'Enter Your Credit Card Information:', 'tc' ) . '</th></tr></thead><tbody><tr><td align="right">' . __( 'Cardholder Name:', 'tc' ) . '</td><td><input name= "tc_nb_name" id="tc_nb_name" type="text" value="' . esc_attr( $name ) . '" /> </td></tr>';
		$content .= '<tr>';
		$content .= '<td align="right">';
		$content .= __( 'Card Number', 'tc' );
		$content .= '</td>';
		$content .= '<td>';
		$content .= '<input type="text" autocomplete="off" id="tc_nb_number" name="tc_nb_number"/>';
		$content .= '</td>';
		$content .= '</tr>';
		$content .= '<tr>';
		$content .= '<td align="right">';
		$content .= __( 'Expiration:', 'tc' );
		$content .= '</td>';
		$content .= '<td>';
		$content .= '<select id="tc_nb_month" name="tc_nb_month">';
		$content .= tc_months_dropdown();
		$content .= '</select>';
		$content .= '<span> / </span>';
		$content .= '<select id="tc_nb_year" name="tc_nb_year">';
		$content .= tc_years_dropdown( '', false );
		$content .= '</select>';
		$content .= '</td>';
		$content .= '</tr>';
		$content .= '<tr>';
		$content .= '<td align="right">';
		$content .= __( 'CVC:', 'tc' );
		$content .= '</td>';
		$content .= '<td>';
		$content .= '<input id="tc_nb_cvc" name="tc_nb_cvc" type="text" maxlength="4" autocomplete="off" value=""/>';
		$content .= '</td>';
		$content .= '</tr>';
		$content .= '<tr>';
		$content .= '<td align="right">';
		$content .= __( 'ZIP:', 'tc-netbanx' );
		$content .= '</td>';
		$content .= '<td>';
		$content .= '<input id="tc_nb_zip" name="tc_nb_zip" type="text" maxlength="6" minlength="1">';
		$content .= '</td>';
		$content .= '</tr>';
		$content .= '</table>';
		return $content;
	}

	function process_payment( $cart ) {
		global $tc;

                tc_final_cart_check($cart);
                
		$this->maybe_start_session();
		$this->save_cart_info();

		require_once($tc->plugin_dir . "/includes/gateways/netbanx/optimalpayments.php");

		$order_id = $tc->generate_order_id();

		$total = $this->total();

		$nb_api_key		 = $this->api_key_id;
		$nb_api_secret	 = $this->api_key_secret;
		$nb_acc_number	 = $this->account_number;

		if ( 'live' == $this->mode ) {
			$client = new OptimalPayments\OptimalApiClient( $nb_api_key, $nb_api_secret, OptimalPayments\Environment::LIVE, $nb_acc_number );
		} else {
			$client = new OptimalPayments\OptimalApiClient( $nb_api_key, $nb_api_secret, OptimalPayments\Environment::TEST, $nb_acc_number );
		}

		$netbanx_params = array(
			'merchantRefNum' => $order_id,
			'amount'		 => $total * 100,
			'settleWithAuth' => true,
			'card'			 => array(
				'cardNum'	 => $_POST[ 'tc_nb_number' ],
				'cvv'		 => $_POST[ 'tc_nb_cvc' ],
				'cardExpiry' => array(
					'month'	 => (int) $_POST[ 'tc_nb_month' ],
					'year'	 => 2000 + (int) $_POST[ 'tc_nb_year' ]
				)
			),
			'billingDetails' => array(
				'zip' => $_POST[ 'tc_nb_zip' ]
			)
		);

		try {
			$auth = $client->cardPaymentService()->authorize( new OptimalPayments\CardPayments\Authorization( $netbanx_params ) );

			$payment_info = array(
				'total'			 => $total,
				'currency'		 => $tc->get_cart_currency(),
				'method'		 => __( 'Credit Card', 'tc' ),
				'transaction_id' => $auth->id
			);

			$payment_info = $this->save_payment_info();

			$order = $tc->create_order( $order_id, $this->cart_contents(), $this->cart_info(), $payment_info, true );

			ob_start();
			@wp_redirect( $tc->get_confirmation_slug( true, $order_id ) );
			tc_js_redirect( $tc->get_confirmation_slug( true, $order_id ) );
			exit;
		} catch ( OptimalPayments\NetbanxException $e ) {

			$this->add_error( sprintf( __( 'There was an error processing your card - "%s".', 'tc' ), $e->getMessage() ) ); //'payment'
			return false;

			exit;
		}
	}

	function gateway_admin_settings( $settings, $visible ) {
		global $tc;
		?>
		<div id="<?php echo $this->plugin_name; ?>" class="postbox" <?php echo (!$visible ? 'style="display:none;"' : ''); ?>>
			<h3 class='hndle'><span><?php printf( __( '%s Settings', 'tc' ), $this->admin_name ); ?></span></h3>
			<div class="inside">
				<span class="description">
					<?php _e( 'Accepts credit card payments with Optimal Payments Netbanx in your store', 'tc' ); ?>
				</span>

				<?php
				$fields = array(
					'mode'			 => array(
						'title'			 => __( 'Mode', 'tc' ),
						'type'			 => 'select',
						'description'	 => '',
						'default'		 => 'sandbox',
						'options'		 => array(
							'sandbox'	 => __( 'Sandbox / Test' ),
							'live'		 => __( 'Live', 'tc' )
						),
					),
					'account_number' => array(
						'title'			 => __( 'Account Number', 'tc' ),
						'type'			 => 'text',
						'description'	 => '',
						'default'		 => '',
					),
					'api_key_id'	 => array(
						'title'			 => __( 'Api Key ID', 'tc' ),
						'type'			 => 'text',
						'description'	 => '',
						'default'		 => '',
					),
					'api_key_secret' => array(
						'title'			 => __( 'Api Key Secret', 'tc' ),
						'type'			 => 'text',
						'description'	 => '',
						'default'		 => '',
					), );

				$form = new TC_Form_Fields_API( $fields, 'tc', 'gateways', $this->plugin_name );
				?>
				<table class="form-table">
					<?php $form->admin_options(); ?>
				</table>
			</div>
		</div>  
		<?php
	}

}

tc_register_gateway_plugin( 'TC_Gateway_Netbanx', 'netbanx', __( 'Netbanx', 'tc' ) );
?>