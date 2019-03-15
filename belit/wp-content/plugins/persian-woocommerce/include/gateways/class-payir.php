<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Woocommerce_Ir_Gateway_PayIr' ) ) :

	Persian_Woocommerce_Gateways::register( 'PayIr' );

	class Woocommerce_Ir_Gateway_PayIr extends Persian_Woocommerce_Gateways {

		public function __construct() {

			$this->method_title = 'Pay.ir';
			$this->icon         = apply_filters( 'woocommerce_ir_gateway_payir_icon', PW()->plugin_url( 'assets/images/payir.png' ) );

			parent::init( $this );
		}

		public function fields() {

			return array(
				'api'               => array(
					'title'       => 'API',
					'type'        => 'text',
					'description' => 'API درگاه Pay.ir',
					'default'     => '',
					'desc_tip'    => true
				),
				'sandbox'           => array(
					'title'       => 'فعالسازی حالت آزمایشی',
					'type'        => 'checkbox',
					'label'       => 'فعالسازی حالت آزمایشی Pay.ir',
					'description' => 'برای فعال سازی حالت آزمایشی Pay.ir چک باکس را تیک بزنید.',
					'default'     => 'no',
					'desc_tip'    => true,
				),
				'cancelled_massage' => array(),
				'shortcodes'        => array(
					'transaction_id' => 'شماره تراکنش',
				)
			);
		}

		public function request( $order ) {

			if ( ! extension_loaded( 'curl' ) ) {
				return 'تابع cURL روی هاست شما فعال نیست.';
			}

			$amount       = $this->get_total( 'IRR' );
			$callback     = $this->get_verify_url();
			$mobile       = $this->get_order_mobile();
			$order_number = $this->get_order_props( 'order_number' );
			$description  = 'شماره سفارش #' . $order_number;
			$apiID        = $this->option( 'sandbox' ) == '1' ? 'test' : $this->option( 'api' );

			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_URL, 'https://pay.ir/payment/send' );
			curl_setopt( $ch, CURLOPT_POSTFIELDS, "api=$apiID&amount=$amount&redirect=$callback&factorNumber=$order_number&mobile=$mobile&description=$description&resellerId=1000000800" );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			$result = curl_exec( $ch );
			curl_close( $ch );
			$result = json_decode( $result );

			if ( ! empty( $result->status ) && $result->status ) {
				return $this->redirect( 'https://pay.ir/payment/gateway/' . $result->transId );
			} else {
				return ! empty( $result->errorMessage ) ? $result->errorMessage : ( ! empty( $result->errorCode ) ? $this->errors( $result->errorCode ) : '' );
			}
		}

		public function verify( $order ) {

			$apiID          = $this->option( 'sandbox' ) == '1' ? 'test' : $this->option( 'api' );
			$transaction_id = $this->post( 'transId' );
			//$factorNumber = $this->post( 'factorNumber' );

			$this->check_verification( $transaction_id );

			$error  = '';
			$status = 'failed';
			if ( $apiID == 'test' || $this->post( 'status' ) ) {

				$ch = curl_init();
				curl_setopt( $ch, CURLOPT_URL, 'https://pay.ir/payment/verify' );
				curl_setopt( $ch, CURLOPT_POSTFIELDS, "api=$apiID&transId=$transaction_id" );
				curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
				curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
				$result = curl_exec( $ch );
				curl_close( $ch );
				$result = json_decode( $result );

				if ( ! empty( $result->status ) && $result->status ) {
					$status = 'completed';
				} else {
					$error = ! empty( $result->errorMessage ) ? $result->errorMessage : ( ! empty( $result->errorCode ) ? $this->errors( ( $result->errorCode . '1' ) ) : '' );
				}

			} else {
				$error = $this->post( 'message' );
			}

			$this->set_shortcodes( array( 'transaction_id' => $transaction_id ) );

			return compact( 'status', 'transaction_id', 'error' );
		}

		private function errors( $error ) {

			switch ( $error ) {

				case '-1' :
					$message = 'ارسال Api الزامی می باشد.';
					break;

				case '-2' :
					$message = 'ارسال Amount (مبلغ تراکنش) الزامی می باشد.';
					break;

				case '-3' :
					$message = 'مقدار Amount (مبلغ تراکنش)باید به صورت عددی باشد.';
					break;

				case '-4' :
					$message = 'Amount نباید کمتر از 1000 باشد.';
					break;

				case '-5' :
					$message = 'ارسال Redirect الزامی می باشد.';
					break;

				case '-6' :
					$message = 'درگاه پرداختی با Api ارسالی یافت نشد و یا غیر فعال می باشد.';
					break;

				case '-7' :
					$message = 'فروشنده غیر فعال می باشد.';
					break;

				case '-8' :
					$message = 'آدرس بازگشتی با آدرس درگاه پرداخت ثبت شده همخوانی ندارد.';
					break;

				case 'failed' :
					$message = 'تراکنش با خطا مواجه شد.';
					break;

				case '-11' :
					$message = 'ارسال Api الزامی می باشد.';
					break;

				case '-21' :
					$message = 'ارسال TransId الزامی می باشد.';
					break;

				case '-31' :
					$message = 'درگاه پرداختی با Api ارسالی یافت نشد و یا غیر فعال می باشد.';
					break;

				case '-41' :
					$message = 'فروشنده غیر فعال می باشد.';
					break;

				case '-51' :
					$message = 'تراکنش با خطا مواجه شده است.';
					break;

				default:
					$message = 'خطای ناشناخته رخ داده است.';
					break;
			}

			return $message;
		}
	}
endif;