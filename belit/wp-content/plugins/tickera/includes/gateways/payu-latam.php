<?php
/*
  PayU - Payment Gateway
 */

class TC_Gateway_PayU_Latam extends TC_Gateway_API {

	var $plugin_name				 = 'payu_latam';
	var $admin_name				 = '';
	var $public_name				 = '';
	var $method_img_url			 = '';
	var $admin_img_url			 = '';
	var $force_ssl				 = false;
	var $ipn_url;
	var $API_Username, $API_Password, $mode, $returnURL, $API_Endpoint, $version, $locale;
	var $currencies				 = array();
	var $automatically_activated	 = false;
	var $skip_payment_screen		 = true;

	//Support for older payment gateway API
	function on_creation() {
		$this->init();
	}

	function init() {
		global $tc;

		$this->admin_name	 = __( 'PayU Latam', 'tc' );
		$this->public_name	 = __( 'PayU Latam', 'tc' );

		$this->method_img_url	 = apply_filters( 'tc_gateway_method_img_url', $tc->plugin_url . 'images/gateways/payulatam.png', $this->plugin_name );
		$this->admin_img_url	 = apply_filters( 'tc_gateway_admin_img_url', $tc->plugin_url . 'images/gateways/small-payulatam.png', $this->plugin_name );

		$this->currency		 = $this->get_option( 'currency', 'ARS' );
		$this->mode			 = $this->get_option( 'mode', 'sandbox' );
		$this->merchant_id	 = $this->get_option( 'merchant_id' );
		$this->account_id	 = $this->get_option( 'account_id' );
		$this->api_key		 = $this->get_option( 'api_key' );
		$this->language		 = $this->get_option( 'language', 'EN' );

		$this->live_url	 = 'https://gateway.payulatam.com/ppp-web-gateway/';
		$this->test_url	 = 'https://stg.gateway.payulatam.com/ppp-web-gateway';

		$currencies = array(
			"ARS"	 => __( 'ARS - Argentine Peso', 'tc' ),
			"BRL"	 => __( 'BRL - Brazilian Real', 'tc' ),
			"CLP"	 => __( 'CLP - Chilean Peso', 'tc' ),
			"COP"	 => __( 'COP - Colombian Peso', 'tc' ),
			"MXN"	 => __( 'MXN - Mexican Peso', 'tc' ),
			"PEN"	 => __( 'PEN - Peruvian Nuevo Sol', 'tc' ),
			"USD"	 => __( 'USD - US Dollar', 'tc' ),
		);

		$this->currencies = $currencies;

		$this->languages = array(
			'ES' => 'Spanish',
			'EN' => 'English',
			'PT' => 'Portuguese'
		);
	}

	function payment_form( $cart ) {
		global $tc;
	}

	function process_payment( $cart ) {
		global $tc;

                tc_final_cart_check($cart);
                
		if ( $this->mode == 'sandbox' ) {
			$url = $this->test_url;
		} else {
			$url = $this->live_url;
		}

		$this->maybe_start_session();
		$this->save_cart_info();

		$order_id = $tc->generate_order_id();


		$paid = false;

		$payment_info = $this->save_payment_info();

		$tc->create_order( $order_id, $this->cart_contents(), $this->cart_info(), $payment_info, $paid );

		$str	 = $this->api_key . "~" . $this->merchant_id . "~" . $order_id . "~" . $this->total() . "~" . $this->currency;
		$hash	 = strtolower( md5( $str ) );

		$payulatam_args = array(
			'merchantId'		 => $this->merchant_id,
			'accountId'			 => $this->account_id,
			'signature'			 => $hash,
			'referenceCode'		 => $order_id,
			'amount'			 => $this->total(),
			'currency'			 => $this->currency,
			'payerFullName'		 => $this->buyer_info( 'full_name' ),
			'buyerEmail'		 => $this->buyer_info( 'email' ),
			'lng'				 => $this->language,
			'description'		 => $this->cart_items(),
			'responseUrl'		 => $tc->get_confirmation_slug( true, $order_id ),
			'confirmationUrl'	 => $tc->get_confirmation_slug( true, $order_id ),
			'tax'				 => 0,
			'taxReturnBase'		 => 0,
			'extra1'			 => $order_id,
			'discount'			 => '0',
		);

		if ( $this->mode == 'sandbox' ) {
			$payulatam_args[ 'test' ] = '1';
		}

		$payulatam_args_array = array();

		foreach ( $payulatam_args as $key => $value ) {
			$payulatam_args_array[] = '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" />';
		}

		header( 'Content-Type: text/html' );

		echo '<form action="' . esc_url( $url ) . '" method="POST" id="payulatam_payment_form" target="_top">
					' . implode( '', $payulatam_args_array ) . '
					<script type="text/javascript">
					document.getElementById("payulatam_payment_form").submit();
				</script>
				</form>';
	}

	function order_confirmation( $order, $payment_info = '', $cart_info = '' ) {
		global $tc;
		$this->request( $_REQUEST );
	}

	function gateway_admin_settings( $settings, $visible ) {
		global $tc;
		?>
		<div id="<?php echo $this->plugin_name; ?>" class="postbox" <?php echo (!$visible ? 'style="display:none;"' : ''); ?>>
			<h3 class='hndle'><span><?php printf( __( '%s Settings', 'tc' ), $this->admin_name ); ?></span></h3>
			<div class="inside">
				<span class="description">
					<?php _e( 'PayU Latam works by sending the user to <a href="https://www.payu.com/">PayU</a> to enter their payment information.', 'tc' ); ?>
				</span>

				<?php
				$fields	 = array(
					'mode'			 => array(
						'title'		 => __( 'Mode', 'tc' ),
						'type'		 => 'select',
						'options'	 => array(
							'sandbox'	 => __( 'Sandbox / Test', 'tc' ),
							'live'		 => __( 'Live', 'tc' )
						),
						'default'	 => 'sandbox',
					),
					'merchant_id'	 => array(
						'title'	 => __( 'Merchant ID', 'tc' ),
						'type'	 => 'text',
					),
					'account_id'	 => array(
						'title'	 => __( 'Account ID', 'tc' ),
						'type'	 => 'text',
					),
					'api_key'		 => array(
						'title'	 => __( 'Api Key', 'tc' ),
						'type'	 => 'text',
					),
					'language'		 => array(
						'title'		 => __( 'Gateway Language', 'tc' ),
						'type'		 => 'select',
						'options'	 => $this->languages,
					),
					'currency'		 => array(
						'title'		 => __( 'Currency', 'tc' ),
						'type'		 => 'select',
						'options'	 => $this->currencies,
					),
				);
				$form	 = new TC_Form_Fields_API( $fields, 'tc', 'gateways', $this->plugin_name );
				?>
				<table class="form-table">
					<?php $form->admin_options(); ?>
				</table>
			</div>
		</div>

		<?php
	}

	function request( $posted ) {
		global $tc;

		if ( !empty( $posted[ 'reference_sale' ] ) ) {

			$order = tc_get_order_id_by_name( $posted[ 'reference_sale' ] );

			if ( !empty( $posted[ 'response_message_pol' ] ) ) {
				// We are here so lets check status and do actions
				if ( isset( $posted[ 'response_message_pol' ] ) && $posted[ 'response_message_pol' ] == 'APPROVED' ) {
					$tc->update_order_payment_status( $order->ID, true );
				}
			}
		}
	}

}

tc_register_gateway_plugin( 'TC_Gateway_PayU_Latam', 'payu_latam', __( 'PayU Latam', 'tc' ) );
?>