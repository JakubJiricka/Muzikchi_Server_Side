<?php

class TC_Gateway_Beanstream extends TC_Gateway_API {

	var $plugin_name				 = 'beanstream';
	var $admin_name				 = '';
	var $public_name				 = '';
	var $force_ssl;
	var $ipn_url;
	var $skip_form				 = false;
	var $currency;
	var $skip_payment_screen		 = false;
	var $automatically_activated	 = false;

	//Support for older payment gateway API
	function on_creation() {
		$this->init();
	}

	function init() {
		global $tc;

		$this->method_img_url	 = apply_filters( 'tc_gateway_method_img_url', $tc->plugin_url . 'images/gateways/beanstream.png', $this->plugin_name );
		$this->admin_img_url	 = apply_filters( 'tc_gateway_admin_img_url', $tc->plugin_url . 'images/gateways/small-beanstream.png ', $this->plugin_name );

		$this->admin_name	 = __( 'Beanstream ', 'tc' );
		$this->public_name	 = __( 'Beanstream ', 'tc' );

		$this->mode			 = $this->get_option( 'mode ', 'sandbox' );
		$this->merchant_id	 = $this->get_option( 'merchant_id' );
		$this->api_access	 = $this->get_option( 'api_access' );
		$this->force_ssl	 = $this->mode == 'sandbox' ? false : true;

		$this->currency = $this->get_option( 'currency', 'USD' );

		$this->currencies = array(
			"CAD"	 => __( 'CAD - Canadian Dollar', 'tc' ),
			"EUR"	 => __( 'EUR - Euro', 'tc' ),
			"GBP"	 => __( 'GBP - British Pound', 'tc' ),
			"USD"	 => __( 'USD - United States Dollar', 'tc' ),
		);
	}

	function payment_form( $cart ) {

		$cc_number		 = isset( $_POST[ 'tcbs_cc_number' ] ) ? $_POST[ 'tcbs_cc_number' ] : '';
		$cc_exp_month	 = isset( $_POST[ 'tcbs_cc_month' ] ) ? $_POST[ 'tcbs_cc_month' ] : '';
		$cc_exp_year	 = isset( $_POST[ 'tcbs_cc_year' ] ) ? $_POST[ 'tcbs_cc_year' ] : '';
		$cc_exp_cvc		 = isset( $_POST[ 'tcbs_cc_cvc' ] ) ? $_POST[ 'tcbs_cc_cvc' ] : '';

		$name = $this->buyer_info( 'full_name' );

		$content = '';

		$content .= '<table class = "cart_billing">
	<thead><tr>
	<th colspan = "2">' . __( 'Enter Your Credit Card Information: ', 'tc' ) . '</th>
	</tr></thead>
	<tbody>
	<tr>
	<td align = "right">' . __( 'Cardholder Name: ', 'tc' ) . '</td><td>
	<input id = "tcbs_cc_name" name = "' . $this->plugin_name . '_cc_name" type = "text" value = "' . esc_attr( $name ) . '" /> </td>
	</tr>';
		$content .= '<tr>';
		$content .= '<td align = "right">';
		$content .= __( 'Card Number', 'tc' );
		$content .= '</td>';
		$content .= '<td>';
		$content .= '<input type = "text" autocomplete = "off" name = "' . $this->plugin_name . '_cc_number" id = "' . $this->plugin_name . '_cc_number"/>';
		$content .= '</td>';
		$content .= '</tr>';
		$content .= '<tr>';
		$content .= '<td align = "right">';
		$content .= __( 'Expiration: ', 'tc' );
		$content .= '</td>';
		$content .= '<td>';
		$content .= '<select id = "' . $this->plugin_name . '_cc_month" name = "' . $this->plugin_name . '_cc_month">';
		$content .= tc_months_dropdown();
		$content .= '</select>';
		$content .= '<span> / </span>';
		$content .= '<select id = "' . $this->plugin_name . '_cc_year" name = "' . $this->plugin_name . '_cc_year">';
		$content .= tc_years_dropdown( '', false );
		$content .= '</select>';
		$content .= '</td>';
		$content .= '</tr>';
		$content .= '<tr>';
		$content .= '<td align = "right">';
		$content .= __( 'CVC: ', 'tc' );
		$content .= '</td>';
		$content .= '<td>';
		$content .= '<input id = "' . $this->plugin_name . '_cc_cvc" name = "' . $this->plugin_name . '_cc_cvc" type = "text" maxlength = "4" autocomplete = "off" value = ""/>';
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

		include_once ('beanstream/Gateway.php');
		include_once ('beanstream/Exception.php');

		$order_id	 = $tc->generate_order_id();
		$total		 = $this->total();

		$beanstream = new \Beanstream\Gateway( $this->merchant_id, $this->api_access, 'www', 'v1' );

		$payment_data = array(
			'order_number'	 => $order_id,
			'amount'		 => $total,
			'payment_method' => 'card',
			'card'			 => array(
				'name'			 => $this->buyer_info( 'full_name' ),
				'number'		 => $_POST[ $this->plugin_name . '_cc_number' ],
				'expiry_month'	 => $_POST[ $this->plugin_name . '_cc_month' ],
				'expiry_year'	 => $_POST[ $this->plugin_name . '_cc_year' ],
				'cvd'			 => $_POST[ $this->plugin_name . '_cc_cvc' ]
			)
		);

		try {

			$result = $beanstream->payments()->makeCardPayment( $payment_data, TRUE ); //set to FALSE for Pre-Auth

			if ( $result[ 'approved' ] ) {

				$payment_info = array(
					'gateway_public_name'	 => $this->public_name,
					'gateway_private_name'	 => $this->admin_name,
					'total'					 => $total,
					'currency'				 => $this->currency,
					'method'				 => __( 'Credit Card', 'tc' ),
					'transaction_id'		 => $payment->id
				);

				$payment_info = $this->save_payment_info();

				$paid	 = true;
				$order	 = $tc->create_order( $order_id, $this->cart_contents(), $this->cart_info(), $payment_info, $paid );

				ob_start();
				@wp_redirect( $tc->get_confirmation_slug( true, $order_id ) );
				tc_js_redirect( $tc->get_confirmation_slug( true, $order_id ) );
				exit;
			} else {
				$this->add_error( 'We\'re very sorry but the card you entered was not approved.' );
				return false;
			}
		} catch ( \Beanstream\Exception $e ) {
			$this->add_error( sprintf( __( '<li>There was an error processing your card: "%s".</li>', 'tc' ), $e->getMessage() ) );
			return false;
		}
	}

	function gateway_admin_settings( $settings, $visible ) {
		global $tc;
		?>
		<div id="<?php echo $this->plugin_name; ?>" class="postbox" <?php echo (!$visible ? 'style="display:none;"' : ''); ?>>
			<h3 class='hndle'><span><?php printf( __( '%s Settings', 'tc' ), $this->admin_name ); ?></span></h3>
			<div class="inside">
				<span class="description">
					<?php _e( 'Accept credit card payments', 'tc' ); ?>
				</span>

				<?php
				$fields = array
					(
					'mode'			 => array(
						'title'			 => __( 'Mode', 'tc' ),
						'type'			 => 'select',
						'description'	 => '',
						'default'		 => 'sandbox',
						'options'		 => array(
							'sandbox'	 => __( 'Sandbox / Test' ),
							'live'		 => __( 'Live' )
						),
					),
					'merchant_id'	 => array(
						'title'			 => __( 'Merchant ID', 'tc' ),
						'type'			 => 'text',
						'description'	 => '',
						'default'		 => '',
					),
					'api_access'	 => array(
						'title'			 => __( 'API access passcode', 'tc' ),
						'type'			 => 'text',
						'description'	 => '',
						'default'		 => '',
					),
					'currency'		 => array(
						'title'			 => __( 'Currency', 'tc' ),
						'type'			 => 'select',
						'description'	 => __( 'Selecting a currency other than that used for your store may cause problems at checkout.', 'tc' ),
						'default'		 => 'USD',
						'options'		 => $this->currencies
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

tc_register_gateway_plugin( 'TC_Gateway_Beanstream', 'beanstream', __( 'Beanstream', 'tc' ) );
?>