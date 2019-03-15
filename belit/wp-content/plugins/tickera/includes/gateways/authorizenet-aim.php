<?php
/*
  Authorize.net (AIM) - Payment Gateway
 */

class TC_Gateway_AuthorizeNet_AIM extends TC_Gateway_API {

	var $plugin_name				 = 'authorizenet-aim';
	var $admin_name				 = '';
	var $public_name				 = '';
	var $method_img_url			 = '';
	var $admin_img_url			 = '';
	var $force_ssl				 = true;
	var $ipn_url;
	var $API_Username, $API_Password, $API_Signature, $SandboxFlag, $returnURL, $API_Endpoint, $currency;
	var $currencies				 = array();
	var $automatically_activated	 = false;
	var $skip_payment_screen		 = false;

	//Support for older payment gateway API
	function on_creation() {
		$this->init();
	}

	function init() {
		global $tc;

		$this->admin_name	 = __( 'Authorize.net (AIM)', 'tc' );
		$this->public_name	 = __( 'Authorize.net', 'tc' );

		$this->method_img_url	 = apply_filters( 'tc_gateway_method_img_url', $tc->plugin_url . 'images/gateways/authorize.png', $this->plugin_name );
		$this->admin_img_url	 = apply_filters( 'tc_gateway_admin_img_url', $tc->plugin_url . 'images/gateways/small-authorize.png', $this->plugin_name );


		$this->API_Username		 = $this->get_option( 'api_user' );
		$this->API_Password		 = $this->get_option( 'api_pass' );
		$this->API_Signature	 = $this->get_option( 'api_sig' );
		$this->currency			 = $this->get_option( 'currency', 'USD' );
		$this->additional_fields = $this->get_option( 'additional_fields', 'no' );

		if ( $this->get_option( 'mode', 'sandbox' ) == 'sandbox' ) {
			$this->API_Endpoint	 = "https://test.authorize.net/gateway/transact.dll";
			$this->force_ssl	 = false;
		} else {
			$this->API_Endpoint = "https://secure.authorize.net/gateway/transact.dll";
		}


		$currencies = array(
			'CAD'	 => __( 'CAD - Canadian Dollar', 'tc' ),
			'EUR'	 => __( 'EUR - Euro', 'tc' ),
			'GBP'	 => __( 'GBP - Pound Sterling', 'tc' ),
			'USD'	 => __( 'USD - U.S. Dollar', 'tc' )
		);

		$this->currencies = $currencies;
	}

	function payment_form( $cart ) {
		global $tc;
		$content = '';

		$content .= '<table class="tc_cart_billing">
        <thead><tr>
          <th colspan="2">' . __( 'Billing Information:', 'tc' ) . '</th>
        </tr></thead>
        <tbody>
          <tr>
            <td>' . __( 'Credit Card Number:', 'tc' ) . '*</td>
            <td>
              <input name="card_num"  id="card_num" class="credit_card_number input_field noautocomplete" type="text" size="22" maxlength="22" /><div class="hide_after_success nocard cardimage"  id="cardimage" style="background: url(' . $tc->plugin_url . 'images/card_array.png) no-repeat;"></div></td>
          </tr>
          
          <tr>
            <td>' . __( 'Expiration Date:', 'tc' ) . '*</td>
            <td>
            <label class="inputLabel" for="exp_month">' . __( 'Month', 'tc' ) . '</label>
		        <select name="exp_month" id="exp_month">
		          ' . tc_months_dropdown() . '
		        </select>
		        <label class="inputLabel" for="exp_year">' . __( 'Year', 'tc' ) . '</label>
		        <select name="exp_year" id="exp_year">
		          ' . tc_years_dropdown( '', true ) . '
		        </select>
		        </td>
          </tr>
          
          <tr>
            <td>' . __( 'CCV:', 'tc' ) . '</td>
            <td>
            <input id="card_code" name="card_code" class="input_field noautocomplete" type="text" size="4" maxlength="4" /></td>
          </tr>';

		//check to see if it's required to have these fields
		if ( $this->additional_fields == 'yes' ) {
			$content .= '<tr>
              <td>' . __( 'Address', 'tc' ) . '</td>
              <td>
              <input id="billing_address" name="billing_address" class="input_field noautocomplete" type="text" /></td>
            </tr>

            <tr>
              <td>' . __( 'City', 'tc' ) . '</td>
              <td>
              <input id="city" name="city" class="input_field noautocomplete" type="text" /></td>
            </tr>

            <tr>
              <td>' . __( 'State', 'tc' ) . '</td>
              <td>
              <input id="state" name="state" class="input_field noautocomplete" type="text" /></td>
            </tr>

            <tr>
              <td>' . __( 'Zip Code', 'tc' ) . '</td>
              <td>
              <input id="zip" name="zip" class="input_field noautocomplete" type="text" /></td>
            </tr>

            <tr>
              <td>' . __( 'Country', 'tc' ) . '</td>
              <td>
              <input id="country" name="country" class="input_field noautocomplete" type="text" /></td>
            </tr>
            
            <tr>
              <td>' . __( 'Phone Number', 'tc' ) . '</td>
              <td>
              <input id="phone_number" name="phone_number" class="input_field noautocomplete" type="text" /></td>
            </tr>   

            ';
		} //if($this->additional_fields == 'yes')
		$content .= '
        </tbody>
      </table>';

		return $content;
	}

	function process_payment( $cart ) {
		global $tc;

                tc_final_cart_check($cart);
                
		$this->maybe_start_session();
		$this->save_cart_info();

		$payment = new TC_Gateway_Worker_AuthorizeNet_AIM( $this->API_Endpoint, 'yes', ',', '', $this->get_option( 'api_user' ), $this->get_option( 'api_key' ), $this->get_option( 'mode', 'sandbox' ) );

		$payment->transaction( $_POST[ 'card_num' ] );

		$order_id = $tc->generate_order_id();

		if ( $this->additional_fields == 'yes' ) {
			$payment->setParameter( "x_address", $_POST[ 'billing_address' ] );
			$payment->setParameter( "x_state", $_POST[ 'state' ] );
			$payment->setParameter( "x_city", $_POST[ 'city' ] );
			$payment->setParameter( "x_zip", $_POST[ 'zip' ] );
			$payment->setParameter( "x_country", $_POST[ 'country' ] );
			$payment->setParameter( "x_phone", $_POST[ 'phone_number' ] );
		}

		$payment->setParameter( "x_card_code", $_POST[ 'card_code' ] );
		$payment->setParameter( "x_exp_date ", $_POST[ 'exp_month' ] . $_POST[ 'exp_year' ] );
		$payment->setParameter( "x_amount", $this->total() );
		$payment->setParameter( "x_currency_code", $this->currency );

		$payment->setParameter( "x_description", $this->cart_items() );
		$payment->setParameter( "x_invoice_num", $order_id );

		if ( $this->get_option( 'mode', 'sandbox' ) == 'sandbox' ) {
			$payment->setParameter( "x_test_request", true );
		} else {
			$payment->setParameter( "x_test_request", false );
		}

		$payment->setParameter( "x_duplicate_window", 30 );

		$address = $_POST[ 'address1' ];

		$payment->setParameter( "x_first_name", $this->buyer_info( 'first_name' ) );
		$payment->setParameter( "x_last_name", $this->buyer_info( 'last_name' ) );
		$payment->setParameter( "x_email", $this->buyer_info( 'email' ) );
		$payment->setParameter( "x_customer_ip", $_SERVER[ 'REMOTE_ADDR' ] );

		$payment->process();

		if ( $payment->isApproved() ) {
			$payment_info						 = array();
			$payment_info[ 'method' ]			 = $payment->getMethod();
			$payment_info[ 'transaction_id' ]	 = $payment->getTransactionID();

			$payment_info = $this->save_payment_info( $payment_info );

			$paid	 = true;
			$order	 = $tc->create_order( $order_id, $this->cart_contents(), $this->cart_info(), $payment_info, $paid );

			ob_start();
			@wp_redirect( $tc->get_confirmation_slug( true, $order_id ) );
			tc_js_redirect( $tc->get_confirmation_slug( true, $order_id ) );
			exit;
		} else {
			$_SESSION[ 'tc_gateway_error' ] = $payment->getResponseText();
			ob_start();
			@wp_redirect( $tc->get_payment_slug( true ) );
			tc_js_redirect( $tc->get_payment_slug( true ) );
			exit;
		}
	}

	function gateway_admin_settings( $settings, $visible ) {
		global $tc;
		?>
		<div id="<?php echo $this->plugin_name; ?>" class="postbox" <?php echo (!$visible ? 'style="display:none;"' : ''); ?>>
			<h3 class='hndle'><span><?php printf( __( '%s Settings', 'tc' ), $this->admin_name ); ?></span></h3>
			<div class="inside">
				<span class="description"><?php _e( 'A SSL certificate is required for live transactions.', 'tc' ) ?></span>
				<?php
				$fields	 = array(
					'mode'				 => array(
						'title'		 => __( 'Mode', 'tc' ),
						'type'		 => 'select',
						'options'	 => array(
							'sandbox'	 => __( 'Sandbox / Test', 'tc' ),
							'live'		 => __( 'Live', 'tc' )
						),
						'default'	 => 'sandbox',
					),
					'api_user'			 => array(
						'title'	 => __( 'Login ID', 'tc' ),
						'type'	 => 'text',
					),
					'api_key'			 => array(
						'title'			 => __( 'Transaction Key', 'tc' ),
						'type'			 => 'text',
						'description'	 => '',
						'default'		 => ''
					),
					'md5_hash'			 => array(
						'title'			 => __( 'MD5 Hash', 'tc' ),
						'type'			 => 'text',
						'description'	 => '',
						'default'		 => ''
					),
					'additional_fields'	 => array(
						'title'			 => __( 'Show Additional Fields (required by European merchants)', 'tc' ),
						'type'			 => 'select',
						'default'		 => 'no',
						'options'		 => array(
							'yes'	 => __( 'Yes', 'tc' ),
							'no'	 => __( 'No', 'tc' )
						),
						'description'	 => 'Fields added to checkout are billing information: Address, City, State, Zip Code, Country',
						'default'		 => 'no'
					),
					'currency'			 => array(
						'title'		 => __( 'Currency', 'tc' ),
						'type'		 => 'select',
						'options'	 => $this->currencies,
						'default'	 => 'USD',
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

if ( !class_exists( 'TC_Gateway_Worker_AuthorizeNet_AIM' ) ) {

	class TC_Gateway_Worker_AuthorizeNet_AIM {

		var $login;
		var $transkey;
		var $params		 = array();
		var $results		 = array();
		var $line_items	 = array();
		var $approved	 = false;
		var $declined	 = false;
		var $error		 = true;
		var $method		 = "";
		var $fields;
		var $response;
		var $instances	 = 0;

		function __construct( $url, $delim_data, $delim_char, $encap_char, $gw_username, $gw_tran_key, $gw_test_mode ) {
			if ( $this->instances == 0 ) {
				$this->url = $url;

				$this->params[ 'x_delim_data' ]		 = ($delim_data == 'yes') ? 'TRUE' : 'FALSE';
				$this->params[ 'x_delim_char' ]		 = $delim_char;
				$this->params[ 'x_encap_char' ]		 = $encap_char;
				$this->params[ 'x_relay_response' ]	 = "FALSE";
				$this->params[ 'x_url' ]			 = "FALSE";
				$this->params[ 'x_version' ]		 = "3.1";
				$this->params[ 'x_method' ]			 = "CC";
				$this->params[ 'x_type' ]			 = "AUTH_CAPTURE";
				$this->params[ 'x_login' ]			 = $gw_username;
				$this->params[ 'x_tran_key' ]		 = $gw_tran_key;
				$this->params[ 'x_test_request' ]	 = $gw_test_mode;

				$this->instances++;
			} else {
				return false;
			}
		}

		function transaction( $cardnum ) {
			$this->params[ 'x_card_num' ] = trim( $cardnum );
		}

		function addLineItem( $id, $name, $description, $quantity, $price, $taxable = 0 ) {
			$this->line_items[] = "{$id}<|>{$name}<|>{$description}<|>{$quantity}<|>{$price}<|>{$taxable}";
		}

		function process( $retries = 1 ) {
			global $tc;

			$this->_prepareParameters();
			$query_string = rtrim( $this->fields, "&" );

			$count = 0;

			while ( $count < $retries ) {
				$args[ 'user-agent' ]	 = $tc->title;
				$args[ 'body' ]			 = $query_string;
				$args[ 'sslverify' ]	 = false;
				$args[ 'timeout' ]		 = 30;

				$response = wp_remote_post( $this->url, $args );

				if ( is_array( $response ) && isset( $response[ 'body' ] ) ) {
					$this->response = $response[ 'body' ];
				} else {
					$this->response	 = "";
					$this->error	 = true;
					return;
				}

				$this->parseResults();

				if ( $this->getResultResponseFull() == "Approved" ) {
					$this->approved	 = true;
					$this->declined	 = false;
					$this->error	 = false;
					$this->method	 = $this->getMethod();
					break;
				} else if ( $this->getResultResponseFull() == "Declined" ) {
					$this->approved	 = false;
					$this->declined	 = true;
					$this->error	 = false;
					break;
				}
				$count++;
			}
		}

		function parseResults() {
			$this->results = explode( $this->params[ 'x_delim_char' ], $this->response );
		}

		function setParameter( $param, $value ) {
			$param					 = trim( $param );
			$value					 = trim( $value );
			$this->params[ $param ]	 = $value;
		}

		function setTransactionType( $type ) {
			$this->params[ 'x_type' ] = strtoupper( trim( $type ) );
		}

		function _prepareParameters() {
			foreach ( $this->params as $key => $value ) {
				$this->fields .= "$key=" . urlencode( $value ) . "&";
			}
			for ( $i = 0; $i < count( $this->line_items ); $i++ ) {
				$this->fields .= "x_line_item={$this->line_items[ $i ]}&";
			}
		}

		function getMethod() {
			if ( isset( $this->results[ 51 ] ) ) {
				return str_replace( $this->params[ 'x_encap_char' ], '', $this->results[ 51 ] );
			}
			return "";
		}

		function getGatewayResponse() {
			return str_replace( $this->params[ 'x_encap_char' ], '', $this->results[ 0 ] );
		}

		function getResultResponseFull() {
			$response = array( "", "Approved", "Declined", "Error" );
			return $response[ str_replace( $this->params[ 'x_encap_char' ], '', $this->results[ 0 ] ) ];
		}

		function isApproved() {
			return $this->approved;
		}

		function isDeclined() {
			return $this->declined;
		}

		function isError() {
			return $this->error;
		}

		function getResponseText() {
			return $this->results[ 3 ];
			$strip = array( $this->params[ 'x_delim_char' ], $this->params[ 'x_encap_char' ], '|', ',' );
			return str_replace( $strip, '', $this->results[ 3 ] );
		}

		function getAuthCode() {
			return str_replace( $this->params[ 'x_encap_char' ], '', $this->results[ 4 ] );
		}

		function getAVSResponse() {
			return str_replace( $this->params[ 'x_encap_char' ], '', $this->results[ 5 ] );
		}

		function getTransactionID() {
			return str_replace( $this->params[ 'x_encap_char' ], '', $this->results[ 6 ] );
		}

	}

}

//register payment gateway plugin
tc_register_gateway_plugin( 'TC_Gateway_AuthorizeNet_AIM', 'authorizenet-aim', __( 'Authorize.net (AIM)', 'tc' ) );
?>