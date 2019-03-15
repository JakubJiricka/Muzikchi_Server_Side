<?php
/*
  PayTabs - Payment Gateway
 */

class TC_Gateway_PayTabs extends TC_Gateway_API {

	var $plugin_name				 = 'paytabs';
	var $admin_name				 = '';
	var $public_name				 = '';
	var $method_img_url			 = '';
	var $admin_img_url			 = '';
	var $force_ssl				 = false;
	var $ipn_url;
	var $currencies				 = array();
	var $automatically_activated	 = false;
	var $skip_payment_screen		 = true;
	var $currency				 = 'USD';
	var $liveurl					 = 'https://www.paytabs.com/';
	var $language				 = 'English';

	//Support for older payment gateway API
	function on_creation() {
		$this->init();
	}

	function init() {
		global $tc;

		$this->admin_name	 = __( 'PayTabs', 'tc' );
		$this->public_name	 = __( 'PayTabs', 'tc' );

		$this->method_img_url	 = apply_filters( 'tc_gateway_method_img_url', $tc->plugin_url . 'images/gateways/paytabs.png', $this->plugin_name );
		$this->admin_img_url	 = apply_filters( 'tc_gateway_admin_img_url', $tc->plugin_url . 'images/gateways/small-paytabs.png', $this->plugin_name );

		$this->currency		 = $tc->get_cart_currency();
		$this->merchantid	 = $this->get_option( 'merchantid' );
		$this->password		 = $this->get_option( 'password' );
		$this->language		 = $this->get_option( 'language', 'English' );

		$paytabs_languages = array(
			'English'	 => 'English',
			'Arabic'	 => 'Arabic'
		);

		$this->paytabs_languages = $paytabs_languages;
	}

	function payment_form( $cart ) {
		global $tc;

		if ( isset( $_GET[ 'payumoney_failed' ] ) ) {
			$_SESSION[ 'tc_gateway_error' ] = __( 'Payment Failed.', 'tc' );
			ob_start();
			@wp_redirect( $tc->get_payment_slug( true ) );
			tc_js_redirect( $tc->get_payment_slug( true ) );
			exit;
		}

		if ( isset( $_GET[ 'payumoney_cancelled' ] ) ) {
			$_SESSION[ 'tc_gateway_error' ] = __( 'Your transaction has been canceled.', 'tc' );
			ob_start();
			@wp_redirect( $tc->get_payment_slug( true ) );
			tc_js_redirect( $tc->get_payment_slug( true ) );
			exit;
		}
	}

	function get_paytabs_args( $order_id, $buyer_first_name, $buyer_last_name, $buyer_email, $total ) {
		global $tc;

		$txnid = $order_id;

		$redirect = $tc->get_confirmation_slug( true, $order_id );

		//array values for authentication
		$loginarray = array(
			'merchant_id'		 => $this->merchantid,
			'merchant_password'	 => $this->password
		);

		//authentication process begine
		$request_login		 = http_build_query( $loginarray );
		$response_data_login = $this->sendRequest( $this->liveurl . 'api/authentication', $request_login );

		//get response data from authentication (api_key)
		$object_login = json_decode( $response_data_login );

		if ( $object_login->access == 'denied' ) {
			$_SESSION[ 'tc_gateway_error' ] = __( 'Merchant ID and password does not match', 'tc' );
			ob_start();
			@wp_redirect( $tc->get_payment_slug( true ) );
			tc_js_redirect( $tc->get_payment_slug( true ) );
			exit;
		}

		//store api into session variable
		$_SESSION[ 'api_key' ] = $object_login->api_key;

		// PayTabs Args
		$paytabs_args = array(
			'key'				 => $this->merchantid,
			'txnid'				 => $txnid,
			'productinfo'		 => $this->cart_items(),
			'firstname'			 => $this->buyer_info( 'first_name' ),
			'lastname'			 => $this->buyer_info( 'last_name' ),
			'address1'			 => '',
			'address2'			 => '',
			'zipcode'			 => '',
			'phone'				 => '',
			'api_key'			 => $_SESSION[ 'api_key' ],
			"cc_first_name"		 => $this->buyer_info( 'first_name' ),
			"cc_last_name"		 => $this->buyer_info( 'last_name' ),
			"phone_number"		 => '',
			"billing_address"	 => '',
			'state'				 => '',
			'city'				 => '',
			"postal_code"		 => '',
			'country'			 => '',
			'email'				 => $this->buyer_info( 'email' ),
			'amount'			 => $this->total(),
			'reference_no'		 => $txnid,
			"currency"			 => strtoupper( $this->currency ),
			"title"				 => __( 'Order #', 'tc' ) . $txnid,
			'ip_customer'		 => $_SERVER[ 'REMOTE_ADDR' ],
			'ip_merchant'		 => $_SERVER[ 'SERVER_ADDR' ],
			"return_url"		 => $redirect,
			'msg_lang'			 => $this->language
		);

		$paytabs_args[ 'products_per_title' ]	 = __( 'Order #', 'tc' ) . $txnid;
		$paytabs_args[ 'ProductName' ]			 = __( 'Order #', 'tc' ) . $txnid;
		$paytabs_args[ 'quantity' ]				 = 1;
		$paytabs_args[ 'unit_price' ]			 = $this->total();

		$paytabs_args[ "CustomerID" ]			 = get_current_user_id();
		$paytabs_args[ "channelOfOperations" ]	 = "channelOfOperations";

		$paytabs_args	 = apply_filters( 'tc_paytabs_args', $paytabs_args );
		$pay_url		 = $this->before_process( $paytabs_args );
		return $pay_url;
	}

	/**
	 * Check process for form submittion
	 * */
	function before_process( $array ) {
		$gateway_url	 = $this->liveurl;
		$request_string	 = http_build_query( $array );
		$response_data	 = $this->sendRequest( $gateway_url . 'api/create_pay_page', $request_string );
		return $object			 = json_decode( $response_data );
	}

	function sendRequest( $gateway_url, $request_string ) {

		$ch		 = @curl_init();
		@curl_setopt( $ch, CURLOPT_URL, $gateway_url );
		@curl_setopt( $ch, CURLOPT_POST, true );
		@curl_setopt( $ch, CURLOPT_POSTFIELDS, $request_string );
		@curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		@curl_setopt( $ch, CURLOPT_HEADER, false );
		@curl_setopt( $ch, CURLOPT_TIMEOUT, 30 );
		@curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
		@curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
		@curl_setopt( $ch, CURLOPT_VERBOSE, true );
		$result	 = @curl_exec( $ch );
		if ( !$result )
			die( curl_error( $ch ) );

		@curl_close( $ch );

		return $result;
	}

	function process_payment( $cart ) {
		global $tc;

                tc_final_cart_check($cart);
                
		$this->maybe_start_session();
		$this->save_cart_info();

		$order_id = $tc->generate_order_id();

		$counter	 = 0;
		$cart_total	 = 0;

		$paid = false;

		$payment_info = $this->save_payment_info();

		/* PAY TABS SPECIFIC */
		$paytabs_payment_url = $this->get_paytabs_args( $order_id, $this->buyer_info( 'first_name' ), $this->buyer_info( 'last_name' ), $this->buyer_info( 'email' ), $this->total() );
		$paytabs_adr		 = $paytabs_payment_url->payment_url;

		//check if api is wrong or dont get payment url
		if ( $paytabs_adr == '' || $paytabs_payment_url->error_code == '0002' ) {
			$_SESSION[ 'tc_gateway_error' ] = sprintf( __( 'Transaction declined, Merchant information is wrong', 'tc' ), $e->getMessage() );
			ob_start();
			@wp_redirect( $tc->get_payment_slug( true ) );
			tc_js_redirect( $tc->get_payment_slug( true ) );
			exit;
		} else {
			$tc->create_order( $order_id, $this->cart_contents(), $this->cart_info(), $payment_info, $paid );
			ob_start();
			@wp_redirect( $paytabs_adr );
			tc_js_redirect( $paytabs_adr );
			exit;
		}
		/* PAY TABS SPECIFIC */
	}

	function order_confirmation( $order, $payment_info = '', $cart_info = '' ) {
		global $tc;
		$this->maybe_start_session();
		$order = tc_get_order_id_by_name( $order );

		if ( isset( $_REQUEST[ 'payment_reference' ] ) ) {
			$request_string = array(
				'api_key'			 => $_SESSION[ 'api_key' ],
				'payment_reference'	 => $_REQUEST[ 'payment_reference' ]
			);

			$gateway_url	 = $this->liveurl . 'api/verify_payment';
			$getdataresponse = $this->sendRequest( $gateway_url, $request_string );
			$object			 = json_decode( $getdataresponse );

			if ( $object->response == '3' || $object->response == '6' ) {
				$tc->update_order_payment_status( $order->ID, true );
			} else {
				//do nothing, transaction still pending
			}
		} else {
			//do nothing
		}
	}

	function gateway_admin_settings( $settings, $visible ) {
		global $tc;
		?>
		<div id="<?php echo $this->plugin_name; ?>" class="postbox" <?php echo (!$visible ? 'style="display:none;"' : ''); ?>>
			<h3 class='hndle'><span><?php printf( __( '%s Settings', 'tc' ), $this->admin_name ); ?></span></h3>
			<div class="inside">
				<span class="description">
					<?php echo __( 'PayTabs works by sending the user to PayTabs to enter their payment information.', 'tc' ); ?>
				</span>

				<?php
				$fields	 = array(
					'merchantid' => array(
						'title'	 => __( 'Merchant ID / Username', 'tc' ),
						'type'	 => 'text',
					),
					'password'	 => array(
						'title'	 => __( 'Password', 'tc' ),
						'type'	 => 'text',
					),
					'language'	 => array(
						'title'		 => __( 'Language', 'tc' ),
						'type'		 => 'select',
						'options'	 => $this->paytabs_languages,
						'default'	 => 'English',
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

}

tc_register_gateway_plugin( 'TC_Gateway_PayTabs', 'paytabs', __( 'PayTabs', 'tc' ) );
?>