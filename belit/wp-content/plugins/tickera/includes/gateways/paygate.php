<?php
/*
  Paygate - Payment Gateway
 */

class TC_Gateway_Paygate extends TC_Gateway_API {

	var $plugin_name				 = 'paygate';
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
	var $currency				 = 'ZAR';
	var $skip_payment_screen		 = true;
	var $encryption_key			 = 'secret';

	//Support for older payment gateway API
	function on_creation() {
		$this->init();
	}

	function init() {
		global $tc;
		$this->admin_name	 = __( 'Paygate', 'tc' );
		$this->public_name	 = __( 'Paygate', 'tc' );
		$this->live_url		 = 'https://www.paygate.co.za/paywebv2/process.trans';
		$this->notify_url	 = $this->ipn_url;
		$this->success_url	 = '';

		$this->method_img_url	 = apply_filters( 'tc_gateway_method_img_url', $tc->plugin_url . 'images/gateways/paygate.png', $this->plugin_name );
		$this->admin_img_url	 = apply_filters( 'tc_gateway_admin_img_url', $tc->plugin_url . 'images/gateways/small-paygate.png', $this->plugin_name );

		$this->currency			 = $this->get_option( 'currency', 'ZAR' );
		$this->merchant_id		 = $this->get_option( 'merchant_id', '10011072130' );
		$this->encryption_key	 = $this->get_option( 'encryption_key', 'secret' );

		$currencies = array(
			"GBP"	 => __( 'GBP - British Pound', 'tc' ),
			"USD"	 => __( 'USD - U.S. Dollar', 'tc' ),
			"ZAR"	 => __( 'ZAR - South Africa', 'tc' ),
		);

		$this->currencies = $currencies;
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

		$fields = array(
			'PAYGATE_ID'		 => $this->merchant_id,
			'REFERENCE'			 => $order_id,
			'AMOUNT'			 => $this->total() * 100,
			'CURRENCY'			 => $this->currency,
			'RETURN_URL'		 => esc_url( $tc->get_confirmation_slug( true, $order_id ) ),
			'TRANSACTION_DATE'	 => date( 'Y-m-d H:m:s' ),
			'EMAIL'				 => $this->buyer_info( 'email' ),
		);

		$checksum_source = $fields[ 'PAYGATE_ID' ] . "|" . $fields[ 'REFERENCE' ] . "|" . $fields[ 'AMOUNT' ] . "|" . $fields[ 'CURRENCY' ] . "|" . $fields[ 'RETURN_URL' ] . "|" . $fields[ 'TRANSACTION_DATE' ] . "|" . $fields[ 'EMAIL' ] . "|" . $this->encryption_key;

		$CHECKSUM = md5( $checksum_source );

		$fields[ 'CHECKSUM' ] = $CHECKSUM;

		$tc->create_order( $order_id, $this->cart_contents(), $this->cart_info(), $payment_info, $paid );
		header( 'Content-Type: text/html' );
		?>
		<form action="<?php echo esc_attr( $this->live_url ); ?>" method="post" name="paygate">
			<?php foreach ( $fields as $field_key => $field_val ) {
				?>
				<input name="<?php echo esc_attr( $field_key ); ?>" type="hidden" value="<?php echo esc_attr( $field_val ); ?>" />
				<?php
			}
			?>
		</form>
		<script>document.forms['paygate'].submit();</script>
		<?php
		die;
	}

	function get_status() {
		global $tc;

		if ( isset( $_POST[ 'REFERENCE' ] ) ) {
			$key = $_POST[ 'REFERENCE' ]; //order id

			$order = tc_get_order_id_by_name( $key );

			if ( $_POST[ 'TRANSACTION_STATUS' ] == '1' ) {
				$tc->update_order_payment_status( $order->ID, true );
			} else {
				//Payment failed
			}
		}
	}

	function order_confirmation( $order, $payment_info = '', $cart_info = '' ) {
		$this->ipn();
	}

	function gateway_admin_settings( $settings, $visible ) {
		global $tc;
		?>
		<div id="<?php echo $this->plugin_name; ?>" class="postbox" <?php echo (!$visible ? 'style="display:none;"' : ''); ?>>
			<h3 class='hndle'><span><?php printf( __( '%s Settings', 'tc' ), $this->admin_name ); ?></span></h3>
			<div class="inside">
				<span class="description">
					<?php echo sprintf( __( 'Sell your tickets via <a target="_blank" href="%s">PayGate</a>', 'tc' ), "https://www.paygate.co.za/" ); ?>
				</span>

				<?php
				$fields	 = array(
					'merchant_id'	 => array(
						'title'		 => __( 'Merchant ID', 'tc' ),
						'type'		 => 'text',
						'default'	 => '10011013800'
					),
					'encryption_key' => array(
						'title'		 => __( 'Encryption Key', 'tc' ),
						'type'		 => 'text',
						'default'	 => 'secret'
					),
					'currency'		 => array(
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

	function ipn() {
		$this->get_status();
	}

}

tc_register_gateway_plugin( 'TC_Gateway_Paygate', 'paygate', __( 'Paygate', 'tc' ) );
?>