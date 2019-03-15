<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Persian_Woocommerce_Gateways' ) && class_exists( 'WC_Payment_Gateway' ) ) :

	abstract class Persian_Woocommerce_Gateways extends WC_Payment_Gateway {

		protected $order_id = 0;

		private $verification_params;

		const PREFIX = 'Woocommerce_Ir_Gateway_';

		public static function register( $gateway ) {
			add_filter( 'woocommerce_payment_gateways', function ( $methods ) use ( $gateway ) {

				$methods[] = self::PREFIX . $gateway;

				return array_unique( $methods );
			} );
		}

		protected function init( $gateway ) {

			$Gateway_Class = get_class( $gateway );

			if ( stripos( $Gateway_Class, self::PREFIX ) === false ) {
				return false;
			}

			$gateway_class = strtolower( $Gateway_Class );
			$gateway->id   = str_ireplace( self::PREFIX, '', $gateway_class );

			if ( method_exists( $gateway, 'init_form_fields' ) ) {
				$gateway->init_form_fields();
			} else {
				$this->init_form_fields();
			}

			if ( method_exists( $gateway, 'init_settings' ) ) {
				$gateway->init_settings();
			} else {
				$this->init_settings();
			}

			$gateway->has_fields         = false;
			$gateway->title              = $gateway->settings['title'] ?: $gateway->method_title;
			$gateway->description        = $gateway->settings['description'];
			$gateway->method_description = $gateway->method_description ?: sprintf( 'تنظیمات درگاه پرداخت %s برای افزونه فروشگاه ساز ووکامرس', $gateway->method_title );

			if ( empty( $gateway->icon ) && class_exists( 'ReflectionClass' ) ) {
				try {
					$file          = ( new ReflectionClass( $Gateway_Class ) )->getFileName();
					$gateway->icon = trailingslashit( WP_PLUGIN_URL ) . plugin_basename( dirname( $file ) ) . '/assets/images/logo.png';
				} catch ( Exception $e ) {
				}
			}

			add_action( 'woocommerce_update_options_payment_gateways_' . $gateway->id,
				array( $gateway, 'process_admin_options' ) );

			add_filter( 'woocommerce_settings_api_sanitized_fields_' . $gateway->id, array(
				$gateway,
				'unsanitie_fields'
			) );

			add_action( 'woocommerce_receipt_' . $gateway->id,
				array( $gateway, 'process_payment_request' ) );

			add_action( 'woocommerce_api_' . $gateway_class,
				array( $gateway, 'process_payment_verify' ) );
		}

		public function unsanitie_fields( $fields ) {

			$unsanitie_fields = array();
			foreach ( (array) $fields as $key => $value ) {
				if ( substr( $key, - 3 ) == '___' ) {
					$unsanitie_fields[] = $key;
				}
			}

			if ( ! empty( $unsanitie_fields ) ) {
				foreach ( $_POST as $key => $value ) {
					foreach ( $unsanitie_fields as $item ) {
						if ( stripos( $key, $item ) !== false ) {
							$fields[ $item ] = trim( $value );
						}
					}
				}
			}

			return $fields;
		}

		public function init_form_fields() {

			$main = array(
				'enabled'         => array(
					'title'       => 'فعالسازی',
					'type'        => 'checkbox',
					'label'       => 'فعالسازی درگاه',
					'description' => 'برای فعالسازی این درگاه پرداخت باید چک باکس را تیک بزنید',
					'default'     => 'yes',
					'desc_tip'    => true,
				),
				'title'           => array(
					'title'       => 'عنوان درگاه',
					'type'        => 'text',
					'description' => 'عنوان این درگاه که در طی خرید به مشتری نمایش داده میشود',
					'default'     => $this->method_title,
					'desc_tip'    => true,
				),
				'description'     => array(
					'title'       => 'توضیحات درگاه',
					'type'        => 'text',
					'desc_tip'    => true,
					'description' => 'توضیحاتی که در طی عملیات پرداخت برای این درگاه نمایش داده خواهد شد',
					'default'     => sprintf( 'پرداخت امن به وسیله کلیه کارت های عضو شتاب از طریق درگاه %s', $this->method_title )
				),
				'direct_redirect' => array(
					'title'       => 'هدایت مستقیم به درگاه',
					'type'        => 'checkbox',
					'label'       => 'در صورتی که قصد دارید کاربر مستقیما به درگاه هدایت شود و در صفحه پیشفاکتور گزینه پرداحت را کلیک نکند، این گزینه را فعال نمایید.',
					'description' => 'به صورت پیشفرض (غیرفعال بودن این گزینه) خریدار قبل از هدایت به درگاه ابتدا شماره سفارش و قیمت نهایی را مشاهده میکند و سپس با زدن دکمه تایید به درگاه هدایت میشود.',
					'default'     => 'no',
					'desc_tip'    => true,
				),
			);

			$fields = $this->fields();

			$shortcodes = array();
			foreach ( $this->fields_shortcodes() as $shortcode => $title ) {
				$shortcode    = '{' . trim( $shortcode, '\{\}' ) . '}';
				$shortcodes[] = "$shortcode:$title";
			}
			$shortcodes = '<br>' . implode( ' - ', $shortcodes );

			unset( $fields['shortcodes'] );

			$messages = array(
				'completed_massage' => array(
					'title'       => 'پیام پرداخت موفق',
					'type'        => 'textarea',
					'description' => 'متن پیامی که میخواهید بعد از پرداخت موفق به کاربر نمایش دهید را وارد نمایید. همچنین می توانید از شورت کدهای زیر نیز استفاده نمایید.' . $shortcodes,
					'default'     => 'با تشکر از شما. سفارش شما با موفقیت پرداخت شد.',
				),
				'failed_massage'    => array(
					'title'       => 'پیام پرداخت ناموفق',
					'type'        => 'textarea',
					'description' => 'متن پیامی که میخواهید بعد از پرداخت ناموفق به کاربر نمایش دهید را وارد نمایید. همچنین می توانید از شورت کد {fault} برای نمایش دلیل خطای رخ داده استفاده نمایید. این دلیل خطا از سایت درگاه ارسال میگردد.',
					'default'     => 'پرداخت شما ناموفق بوده است. لطفا مجددا تلاش نمایید یا در صورت بروز اشکال با مدیر سایت تماس بگیرید.',
				),
				'cancelled_massage' => array(
					'title'       => 'پیام انصراف از پرداخت',
					'type'        => 'textarea',
					'description' => 'متن پیامی که میخواهید بعد از انصراف کاربر از پرداخت نمایش دهید را وارد نمایید. این پیام بعد از بازگشت از بانک نمایش داده خواهد شد.',
					'default'     => 'پرداخت به دلیل انصراف شما ناتمام باقی ماند.',
				),
			);

			$this->form_fields = array_merge( $main, $fields, $messages );
			foreach ( array_merge( $main, $messages ) as $key => $value ) {
				if ( isset( $fields[ $key ] ) ) {
					$this->form_fields[ $key ] = $fields[ $key ];
				}
			}
			$this->form_fields = array_filter( $this->form_fields );
		}

		public function process_payment( $order ) {

			$order = $this->get_order( $order );

			return array(
				'result'   => 'success',
				'redirect' => $order->get_checkout_payment_url( true )
			);
		}

		public function process_payment_request( $order_id ) {

			$this->order_id = $order_id;
			$this->session( 'set', 'order_id', $order_id );
			$order = $this->get_order( $order_id );
			$form  = $this->option( 'direct_redirect' ) != '1';

			if ( $form ) {
				echo '<form action="" method="POST" class="pw-gateway-checkout-form" id="pw-gateway-checkout-form-' . $this->id . '">
						<input type="submit" name="pw-gateway-submit" class="pw-gateway-submit button alt" value="پرداخت"/>
						<a class="pw-gateway-cancel button cancel" href="' . $this->get_checkout_url() . '">بازگشت</a>
					 </form><br/>';
			}

			if ( ! $form || isset( $_POST['pw-gateway-submit'] ) ) {
				$error = $this->request( $order );
				$this->set_message( 'failed', $error, true, false );
				$order->add_order_note( sprintf( 'در هنگام اتصال به درگاه %s خطای زیر رخ داده است.', $this->title ) . "<br>{$error}" );
			}
		}

		public function process_payment_verify() {

			$redirect = $this->get_checkout_url();

			$order_id = ! empty( $_GET['wc_order'] ) ? $_GET['wc_order'] : $this->session( 'get', 'order_id' );
			if ( empty( $order_id ) ) {
				$this->set_message( 'failed', 'شماره سفارش وجود ندارد.', true, $redirect );
			}

			$order = $this->get_order( $order_id );

			if ( ! $this->needs_payment( $order ) ) {
				$this->set_message( 'failed', 'وضعیت تراکنش قبلا مشخص شده است.', true, $redirect, true );
			}

			$this->order_id = $order_id;

			$result = $this->verify( $order );

			if ( ! is_array( $result ) ) {
				$error = is_string( $result ) && strlen( $result ) > 5 ? $result : 'اطلاعات صحت سنجی تراکنش صحیح نیست.';
				$this->set_message( 'failed', $error, true, $redirect, true );
			}

			$error          = '';
			$status         = ! empty( $result['status'] ) ? $result['status'] : '';
			$transaction_id = ! empty( $result['transaction_id'] ) ? $result['transaction_id'] : '';

			if ( $status == 'completed' ) {

				$redirect = $this->get_return_url( $order );

				$order->payment_complete( $transaction_id );
				$this->empty_cart();
				$this->set_verification();

				$shortcodes = $this->get_shortcodes_values();
				$note       = array( 'تراکنش موفق بود.' );
				foreach ( $this->fields_shortcodes() as $key => $value ) {
					$key    = trim( $key, '\{\}' );
					$note[] = "$value : {$shortcodes[$key]}";
				}
				$order->add_order_note( implode( "<br>", $note ), 1 );

			} elseif ( $status == 'cancelled' ) {
				$order->add_order_note( 'تراکنش به بعلت انصراف کاربر ناتمام باقی ماند.', 1 );
			} else {
				$error = ! empty( $result['error'] ) ? $result['error'] : 'در حین پرداخت خطایی رخ داده است.';
				$order->add_order_note( sprintf( 'در هنگام بازگشت از درگاه %s خطای زیر رخ داده است.', $this->title ) . "<br>{$error}", 1 );
			}

			$this->set_message( $status, $error, true, $redirect );
			exit;
		}

		/*
		 * ---------------------------------------------------
		 * */
		protected function order_id( $order ) {

			if ( is_numeric( $order ) ) {
				$order_id = $order;
			} elseif ( method_exists( $order, 'get_id' ) ) {
				$order_id = $order->get_id();
			} elseif ( ! ( $order_id = absint( get_query_var( 'order-pay' ) ) ) ) {
				$order_id = $order->id;
			}

			if ( ! empty( $order_id ) ) {
				$this->order_id = $order_id;
			}

			return $order_id;
		}

		protected function get_order( $order = 0 ) {

			if ( empty( $order ) ) {
				$order = $this->order_id;
			}

			if ( empty( $order ) ) {
				return (object) array();
			}

			if ( is_numeric( $order ) ) {
				$this->order_id = $order;

				$order = new WC_Order( $order );
			}

			return $order;
		}

		protected function get_order_props( $prop, $default = '' ) {

			if ( empty( $this->order_id ) ) {
				return '';
			}

			$order = $this->get_order();

			$method = 'get_' . $prop;

			if ( method_exists( $order, $method ) ) {
				$prop = $order->$method();
			} elseif ( ! empty( $order->{$prop} ) ) {
				$prop = $order->{$prop};
			} else {
				$prop = '';
			}

			return ! empty( $prop ) ? $prop : $default;
		}

		protected function get_order_items( $product = false ) {

			if ( empty( $this->order_id ) ) {
				return array();
			}

			$order = $this->get_order();
			$items = $order->get_items();

			if ( $product ) {
				$products = array();
				foreach ( (array) $items as $item ) {
					$products[] = $item['name'] . ' (' . $item['qty'] . ') ';
				}

				return implode( ' - ', $products );
			}

			return $items;
		}

		protected function get_order_mobile() {

			$Mobile = $this->get_order_props( 'billing_phone' );
			$Mobile = $this->get_order_props( 'billing_mobile', $Mobile );

			$Mobile = str_ireplace( array( '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹' ),
				array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' ), $Mobile ); //farsi

			$Mobile = str_ireplace( array( '٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩' ),
				array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' ), $Mobile ); //arabi

			$Mobile = preg_replace( '/\D/is', '', $Mobile );
			$Mobile = ltrim( $Mobile, '0' );
			$Mobile = substr( $Mobile, 0, 2 ) == '98' ? substr( $Mobile, 2 ) : $Mobile;

			return '0' . $Mobile;
		}

		protected function get_currency() {

			if ( empty( $this->order_id ) ) {
				return '';
			}

			$order = $this->get_order();

			$currency = method_exists( $order, 'get_currency' ) ? $order->get_currency() : $order->get_order_currency();

			$irt = array( 'irt', 'toman', 'tomaan', 'iran toman', 'iranian toman', 'تومان', 'تومان ایران' );
			if ( in_array( strtolower( $currency ), $irt ) ) {
				$currency = 'IRT';
			}

			$irr = array( 'irr', 'rial', 'iran rial', 'iranian rial', 'ریال', 'ریال ایران' );
			if ( in_array( strtolower( $currency ), $irr ) ) {
				$currency = 'IRR';
			}

			return $currency;
		}

		protected function get_total( $to_currency = 'IRR' ) {

			if ( empty( $this->order_id ) ) {
				return 0;
			}

			$order = $this->get_order();

			if ( method_exists( $order, 'get_total' ) ) {
				$price = $order->get_total();
			} else {
				$price = intval( $order->order_total );
			}

			$currency    = strtoupper( $this->get_currency() );
			$to_currency = strtoupper( $to_currency );

			if ( in_array( $currency, array( 'IRHR', 'IRHT' ) ) ) {
				$currency = str_ireplace( 'H', '', $currency );
				$price    *= 1000;
			}

			if ( $currency == 'IRR' && $to_currency == 'IRT' ) {
				$price /= 10;
			}

			if ( $currency == 'IRT' && $to_currency == 'IRR' ) {
				$price *= 10;
			}

			return $price;
		}

		protected function needs_payment( $order = 0 ) {

			if ( empty( $order ) && empty( $this->order_id ) ) {
				return true;
			}

			$order = $this->get_order( $order );

			if ( method_exists( $order, 'needs_payment' ) ) {
				return $order->needs_payment();
			}

			if ( empty( $this->order_id ) && ! empty( $order ) ) {
				$this->order_id = $this->order_id( $order );
			}

			return ! in_array( $this->get_order_props( 'status' ), array( 'completed', 'processing' ) );
		}

		protected function get_verify_url() {
			return add_query_arg( 'wc_order', $this->order_id, WC()->api_request_url( get_class( $this ) ) );
		}

		protected function get_checkout_url() {
			if ( function_exists( 'wc_get_checkout_url' ) ) {
				return wc_get_checkout_url();
			} else {
				global $woocommerce;

				return $woocommerce->cart->get_checkout_url();
			}
		}

		protected function empty_cart() {
			if ( function_exists( 'wc_empty_cart' ) ) {
				wc_empty_cart();
			} elseif ( function_exists( 'WC' ) && ! empty( WC()->cart ) && method_exists( WC()->cart, 'empty_cart' ) ) {
				WC()->cart->empty_cart();
			} else {
				global $woocommerce;
				$woocommerce->cart->empty_cart();
			}
		}

		protected function fields_shortcodes( $fields = array() ) {

			$fields = ! empty( $fields ) ? $fields : $this->fields();

			return ! empty( $fields['shortcodes'] ) && is_array( $fields['shortcodes'] ) ? $fields['shortcodes'] : array();
		}

		protected function get_shortcodes_values() {

			$shortcodes = array();
			foreach ( $this->fields_shortcodes() as $key => $value ) {
				$key                = trim( $key, '\{\}' );
				$shortcodes[ $key ] = get_post_meta( $this->order_id, '_' . $key, true );
			}

			return $shortcodes;
		}

		protected function set_shortcodes( $shortcodes ) {

			$fields_shortcodes = $this->fields_shortcodes();

			foreach ( $shortcodes as $key => $value ) {

				if ( is_numeric( $key ) ) {
					$key = $fields_shortcodes[ $key ];
				}

				if ( ! empty( $key ) && ! is_array( $key ) ) {
					$key = trim( $key, '\{\}' );
					update_post_meta( $this->order_id, '_' . $key, $value );
				}
			}
		}

		protected function set_message( $status, $error = '', $notice = true, $redirect = false, $failed_note = false ) {

			if ( ! in_array( $status, array( 'completed', 'cancelled', 'failed' ) ) ) {
				$status = 'failed';
			}

			if ( ! empty( $error ) && $failed_note && ( $order = $this->get_order() ) && ! empty( $order ) ) {
				$order->add_order_note( 'خطا: ' . $error, 1 );
			}

			$shortcodes = array_merge( $this->get_shortcodes_values(), array( '{fault}' => $error ) );

			$message = $this->option( $status . '_massage' );
			$find    = array_map( function ( $value ) {
				return '{' . trim( $value, '\{\}' ) . '}';
			}, array_keys( $shortcodes ) );
			$message = str_ireplace( $find, array_values( $shortcodes ), $message );
			$message = wpautop( wptexturize( trim( $message ) ) );

			if ( $notice ) {
				wc_add_notice( $message, $status == 'completed' ? 'success' : 'error' );
			}

			if ( $redirect ) {
				wp_redirect( $redirect );
				exit;
			}

			return $message;
		}

		protected function check_verification( $params ) {

			if ( function_exists( 'func_get_args' ) ) {
				$args = func_get_args();
				if ( count( $args ) > 1 ) {
					$params = array_merge( array_values( $args ), $params );
					$params = implode( '_', array_unique( $params ) );
				}
			}

			if ( is_array( $params ) ) {
				$params = implode( '_', $params );
			}
			$params = $this->id . '_' . $params;

			global $wpdb;
			$query = "SELECT * FROM {$wpdb->prefix}postmeta WHERE meta_key='_verification_params' AND meta_value='%s'";
			$check = $wpdb->get_row( $wpdb->prepare( $query, $params ) );
			if ( ! empty( $check ) ) {
				return $this->set_message( 'failed', 'این تراکنش قبلا یکبار وریفای شده بود.', true, $this->get_checkout_url(), true );
			}
			$this->verification_params = $params;
		}

		protected function set_verification() {
			if ( ! empty( $this->verification_params ) ) {
				update_post_meta( $this->order_id, '_verification_params', $this->verification_params );
			}
		}

		/*
		 * Helpers
		 * */
		protected function option( $name ) {

			$option = '';
			if ( method_exists( $this, 'get_option' ) ) {
				$option = $this->get_option( $name );
			} elseif ( ! empty( $this->settings[ $name ] ) ) {
				$option = $this->settings[ $name ];
			}

			if ( in_array( strtolower( $option ), array( 'yes', 'on', 'true' ) ) ) {
				$option = '1';
			}
			if ( in_array( strtolower( $option ), array( 'no', 'off', 'false' ) ) ) {
				$option = false;
			}

			return $option;
		}

		protected function get( $name, $default = '' ) {
			return ! empty( $_GET[ $name ] ) ? sanitize_text_field( $_GET[ $name ] ) : $default;
		}

		protected function post( $name, $default = '' ) {
			return ! empty( $_POST[ $name ] ) ? sanitize_text_field( $_POST[ $name ] ) : $default;
		}

		protected function store_date( $key, $value ) {
			$this->session( 'set', $key, $value );
			update_post_meta( $this->order_id, '_' . $this->id . '_' . $key, $value );
		}

		protected function get_stored( $key ) {

			$value = get_post_meta( $this->order_id, '_' . $this->id . '_' . $key, true );

			return ! empty( $value ) ? $value : $this->session( 'get', $key );
		}

		protected function session( $action, $name, $value = '' ) {

			global $woocommerce;

			$name = $this->id . '_' . $name;

			$wc_session = function_exists( 'WC' ) && ! empty( WC()->session );

			if ( $action == 'set' ) {

				if ( $wc_session && method_exists( WC()->session, 'set' ) ) {
					WC()->session->set( $name, $value );
				} else {
					$woocommerce->session->{$name} = $value;
				}

			} elseif ( $action == 'get' ) {

				if ( $wc_session && method_exists( WC()->session, 'get' ) ) {
					$value = WC()->session->get( $name );
					unset( WC()->session->{$name} );
				} else {
					$value = $woocommerce->session->{$name};
					unset( $woocommerce->session->{$name} );
				}

				return $value;
			}

			return '';
		}

		protected function redirect( $url ) {
			if ( ! headers_sent() ) {
				header( 'Location: ' . trim( $url ) );
			} else {
				echo "<script type='text/javascript'>window.onload = function () { top.location.href = '" . $url . "'; };</script>";
			}
			exit;
		}

		protected function submit_form( $form ) {

			$name = 'pw_gateway_name_' . $this->id;

			$form    = explode( '>', $form );
			$form[0] = preg_replace( '/name=[\'\"].*?[\'\"]/i', '', $form[0] );
			$form    = implode( '>', $form );
			$form    = str_ireplace( "<form", "<form name=\"{$name}\"", $form );

			echo 'در حال هدایت به درگاه ....';
			$function = "document.{$name}.submit();";
			if ( headers_sent() ) {
				$script = "<script type=\"text/javascript\">function PWformSubmit(){ $function } PWformSubmit();";
				$script .= $function;
				$script .= "</script>";
				echo $script . $form;
			} else {
				$script = "<script type=\"text/javascript\">$function</script>";
				echo $form . $script;
			}
			die();
		}

		protected function nusoap() {
			if ( ! class_exists( 'nusoap_client' ) ) {
				include_once( 'lib/nusoap.php' );
			}
		}

		/*
		 * Abstract methods and must be override
		 * */

		abstract protected function fields();

		abstract protected function request( $order );

		abstract protected function verify( $order );

	}
endif;
include 'gateways/class-payir.php';