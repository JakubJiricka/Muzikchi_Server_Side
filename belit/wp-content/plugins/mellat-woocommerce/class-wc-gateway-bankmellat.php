<?php
if (!defined('ABSPATH') ) exit;
function Load_BankMellat_Gateway() {
	
	if ( class_exists( 'WC_Payment_Gateway' ) && !class_exists( 'WC_Gateway_Bankmellat' ) && !function_exists('Woocommerce_Add_BankMellat_Gateway') ) {
		
		add_filter('woocommerce_payment_gateways', 'Woocommerce_Add_BankMellat_Gateway' );
		function Woocommerce_Add_BankMellat_Gateway($methods) {
			$methods[] = 'WC_Gateway_Bankmellat';
			return $methods;
		}
		
		class WC_Gateway_Bankmellat extends WC_Payment_Gateway {
			
			public function __construct(){
				
				//by Woocommerce.ir
				$this->author = 'Woocommerce.ir';
				//by Woocommerce.ir
				
				
				$this->id = 'bankmellat';
				$this->method_title = __('بانک ملت', 'woocommerce');
				$this->method_description = __( 'تنظیمات درگاه پرداخت بانک ملت برای افزونه فروشگاه ساز ووکامرس', 'woocommerce');
				$this->icon = apply_filters('WC_BankMellat_logo', WP_PLUGIN_URL . "/" . plugin_basename(dirname(__FILE__)) . '/assets/images/logo.png');
				$this->has_fields = false;
				
				$this->init_form_fields();
				$this->init_settings();
				
				$this->title = $this->settings['title'];
				$this->description = $this->settings['description'];
				
				$this->terminal = $this->settings['terminal'];
				$this->username = $this->settings['username'];	
				$this->password = $this->settings['password'];	
				
				
				$this->success_massage = $this->settings['success_massage'];
				$this->failed_massage = $this->settings['failed_massage'];
				$this->cancelled_massage = $this->settings['cancelled_massage'];
				
				if ( version_compare( WOOCOMMERCE_VERSION, '2.0.0', '>=' ) )
					add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
				else
					add_action( 'woocommerce_update_options_payment_gateways', array( $this, 'process_admin_options' ) );	
				add_action('woocommerce_receipt_'.$this->id.'', array($this, 'Send_to_BankMellat_Gateway_By_HANNANStd'));
				add_action('woocommerce_api_'.strtolower(get_class($this)).'', array($this, 'Return_from_BankMellat_Gateway_By_HANNANStd') );
				
			}

		
			public function admin_options(){
				$action = $this->author;
				do_action( 'WC_Gateway_Payment_Actions', $action );
				parent::admin_options();
			}
		
			public function init_form_fields(){
				$this->form_fields = apply_filters('WC_BankMellat_Config', 
					array(
						'base_confing' => array(
							'title'       => __( 'تنظیمات پایه ای', 'woocommerce' ),
							'type'        => 'title',
							'description' => '',
						),
						'enabled' => array(
							'title'   => __( 'فعالسازی/غیرفعالسازی', 'woocommerce' ),
							'type'    => 'checkbox',
							'label'   => __( 'فعالسازی درگاه بانک ملت', 'woocommerce' ),						
							'description' => __( 'برای فعالسازی درگاه پرداخت بانک ملت باید چک باکس را تیک بزنید', 'woocommerce' ),
							'default' => 'yes',
							'desc_tip'    => true,
						),
						'title' => array(
							'title'       => __( 'عنوان درگاه', 'woocommerce' ),
							'type'        => 'text',
							'description' => __( 'عنوان درگاه که در طی خرید به مشتری نمایش داده میشود', 'woocommerce' ),
							'default'     => __( 'بانک ملت', 'woocommerce' ),
							'desc_tip'    => true,
						),
						'description' => array(
							'title'       => __( 'توضیحات درگاه', 'woocommerce' ),
							'type'        => 'text',
							'desc_tip'    => true,
							'description' => __( 'توضیحاتی که در طی عملیات پرداخت برای درگاه نمایش داده خواهد شد', 'woocommerce' ),
							'default'     => __( 'پرداخت امن به وسیله کلیه کارت های عضو شتاب از طریق درگاه بانک ملت', 'woocommerce' )
						),
						'account_confing' => array(
							'title'       => __( 'تنظیمات حساب بانک ملت', 'woocommerce' ),
							'type'        => 'title',
							'description' => '',
						),
						'terminal' => array(
							'title'       => __( 'ترمینال آیدی', 'woocommerce' ),
							'type'        => 'text',
							'description' => __( 'شماره ترمینال درگاه بانک ملت', 'woocommerce' ),
							'default'     => '',
							'desc_tip'    => true
						),
						'username' => array(
							'title'       => __( 'نام کاربری', 'woocommerce' ),
							'type'        => 'text',
							'description' => __( 'نام کاربری درگاه بانک ملت', 'woocommerce' ),
							'default'     => '',
							'desc_tip'    => true
						),
						'password' => array(
							'title'       => __( 'کلمه عبور', 'woocommerce' ),
							'type'        => 'text',
							'description' => __( 'کلمه عبور درگاه بانک ملت', 'woocommerce' ),
							'default'     => '',
							'desc_tip'    => true
						),
						'payment_confing' => array(
							'title'       => __( 'تنظیمات عملیات پرداخت', 'woocommerce' ),
							'type'        => 'title',
							'description' => '',
						),
						'success_massage' => array(
							'title'       => __( 'پیام پرداخت موفق', 'woocommerce' ),
							'type'        => 'textarea',
							'description' => __( 'متن پیامی که میخواهید بعد از پرداخت موفق به کاربر نمایش دهید را وارد نمایید . همچنین می توانید از شورت کد {transaction_id} برای نمایش کد رهگیری ( کد مرجع تراکنش ) و از شرت کد {SaleOrderId} برای شماره درخواست تراکنش بانک ملت استفاده نمایید .', 'woocommerce' ),
							'default'     => __( 'با تشکر از شما . سفارش شما با موفقیت پرداخت شد .', 'woocommerce' ),
						),
						'failed_massage' => array(
							'title'       => __( 'پیام پرداخت ناموفق', 'woocommerce' ),
							'type'        => 'textarea',
							'description' => __( 'متن پیامی که میخواهید بعد از پرداخت ناموفق به کاربر نمایش دهید را وارد نمایید . همچنین می توانید از شورت کد {fault} برای نمایش دلیل خطای رخ داده استفاده نمایید . این دلیل خطا از سایت بانک ملت ارسال میگردد .', 'woocommerce' ),
							'default'     => __( 'پرداخت شما ناموفق بوده است . لطفا مجددا تلاش نمایید یا در صورت بروز اشکال با مدیر سایت تماس بگیرید .', 'woocommerce' ),
						),
						'cancelled_massage' => array(
							'title'       => __( 'پیام انصراف از پرداخت', 'woocommerce' ),
							'type'        => 'textarea',
							'description' => __( 'متن پیامی که میخواهید بعد از انصراف کاربر از پرداخت نمایش دهید را وارد نمایید . این پیام بعد از بازگشت از بانک نمایش داده خواهد شد .', 'woocommerce' ),
							'default'     => __( 'پرداخت به دلیل انصراف شما ناتمام باقی ماند .', 'woocommerce' ),
						),
					)
				);
			}

			public function process_payment( $order_id ) {
				$order = new WC_Order( $order_id );	
				return array(
					'result'   => 'success',
					'redirect' => $order->get_checkout_payment_url(true)
				);
			}

			public function Send_to_BankMellat_Gateway_By_HANNANStd($order_id){
				global $woocommerce;
				$woocommerce->session->order_id_bankmellat = $order_id;
				$order = new WC_Order( $order_id );
				$currency = $order->get_order_currency();
				$currency = apply_filters( 'WC_BankMellat_Currency', $currency, $order_id );
				$action = $this->author;
				do_action( 'WC_Gateway_Payment_Actions', $action );
				$form = '<form action="" method="POST" class="bankmellat-checkout-form" id="bankmellat-checkout-form">
						<input type="submit" name="bankmellat_submit" class="button alt" id="bankmellat-payment-button" value="'.__( 'پرداخت', 'woocommerce' ).'"/>
						<a class="button cancel" href="' . $woocommerce->cart->get_checkout_url() . '">' . __( 'بازگشت', 'woocommerce' ) . '</a>
					 </form><br/>';
				$form = apply_filters( 'WC_BankMellat_Form', $form, $order_id, $woocommerce );				
				
				do_action( 'WC_BankMellat_Gateway_Before_Form', $order_id, $woocommerce );	
				echo $form;
				do_action( 'WC_BankMellat_Gateway_After_Form', $order_id, $woocommerce );
					
				if ( isset($_POST["bankmellat_submit"]) ) {
					
					$Amount = intval($order->order_total);
					$Amount = apply_filters( 'woocommerce_order_amount_total_IRANIAN_gateways_before_check_currency', $Amount, $currency );
					if ( strtolower($currency) == strtolower('IRT') || strtolower($currency) == strtolower('TOMAN')
						|| strtolower($currency) == strtolower('Iran TOMAN') || strtolower($currency) == strtolower('Iranian TOMAN')
						|| strtolower($currency) == strtolower('Iran-TOMAN') || strtolower($currency) == strtolower('Iranian-TOMAN')
						|| strtolower($currency) == strtolower('Iran_TOMAN') || strtolower($currency) == strtolower('Iranian_TOMAN')
						|| strtolower($currency) == strtolower('تومان') || strtolower($currency) == strtolower('تومان ایران')
					)
						$Amount = $Amount*10;
					else if ( strtolower($currency) == strtolower('IRHT') )							
						$Amount = $Amount*1000*10;
					else if ( strtolower($currency) == strtolower('IRHR') )							
						$Amount = $Amount*1000;
					
					$Amount = apply_filters( 'woocommerce_order_amount_total_IRANIAN_gateways_after_check_currency', $Amount, $currency );
					$Amount = apply_filters( 'woocommerce_order_amount_total_IRANIAN_gateways_irr', $Amount, $currency );
					$Amount = apply_filters( 'woocommerce_order_amount_total_Mellat_gateway', $Amount, $currency );
			
					//include NuSoap
					if ( !class_exists( 'nusoap_client' ) ) 
						include_once("nusoap.php");
					//----
		
					do_action( 'WC_BankMellat_Gateway_Payment', $order_id );

					$terminalId = $this->terminal;
					$userName = $this->username; 
					$userPassword = $this->password; 
	
					$orderId = date('ymdHis');
					$additionalData = 'Order_Number : '.$order->get_order_number();
					$callBackUrl = add_query_arg( 'wc_order', $order_id , WC()->api_request_url('WC_Gateway_Bankmellat') );
	
					$client = new nusoap_client('https://bpm.shaparak.ir/pgwchannel/services/pgw?wsdl');
					$namespace='http://interfaces.core.sw.bps.com/';
					$localDate = date("Ymd");
					$localTime = date("His");
					$payerId = '0';
					$is_error = 'no';
					$err = $client->getError();
					if ($err) {
						$is_error = 'yes';
						$error_code= $err;
					}
					$parameters = array(
						'terminalId' => $terminalId,
						'userName' => $userName,
						'userPassword' => $userPassword,
						'orderId' => $orderId,
						'amount' => $Amount,
						'localDate' => $localDate,
						'localTime' => $localTime,
						'additionalData' => $additionalData,
						'callBackUrl' => $callBackUrl,
						'payerId' => $payerId
					);
					$result = $client->call('bpPayRequest', $parameters, $namespace);
					if ($client->fault) {
						$is_error = 'yes';
						$error_code = $_POST['ResCode'];
					} 
					else {
						$resultStr  = $result;
						$err = $client->getError();
						if ($err) {
							$is_error = 'yes';
							$error_code =  $err;
						}
						else {
							$res = explode (',',$resultStr);
							$ResCode = $res[0];		
							if ($ResCode == "0") {							
								$Notice = __( 'در حال اتصال به بانک .....', 'woocommerce' );
								$Notice = apply_filters( 'WC_BankMellat_Before_Send_to_Gateway_Notice', $Notice, $order_id );
								if ( $Notice )
									wc_add_notice( $Notice , 'success' );
								do_action( 'WC_BankMellat_Before_Send_to_Gateway', $order_id );
								
								echo '<form id="redirect_to_mellat" method="post" action="https://bpm.shaparak.ir/pgwchannel/startpay.mellat" style="display:none !important;"  >
										<input type="hidden"  name="RefId" value="'.$res[1].'" />
										<input type="submit" value="Pay"/>
									</form>
									<script language="JavaScript" type="text/javascript">
										document.getElementById("redirect_to_mellat").submit();
									</script>';
							}
							else {
								$is_error = 'yes';
								$error_code = $ResCode;
							}
						}
					}

				
					if ($is_error == 'yes') {
						
						$fault = $error_code;
						
						$Note = sprintf( __( 'خطا در هنگام ارسال به بانک : %s', 'woocommerce'), $this->Fault_BankMellat($fault) );
						$Note = apply_filters( 'WC_BankMellat_Send_to_Gateway_Failed_Note', $Note, $order_id, $fault );
						$order->add_order_note( $Note );
						
						
						$Notice = sprintf( __( 'در هنگام اتصال به بانک خطای زیر رخ داده است : <br/>%s', 'woocommerce'), $this->Fault_BankMellat($fault) );
						$Notice = apply_filters( 'WC_BankMellat_Send_to_Gateway_Failed_Notice', $Notice, $order_id, $fault );
						if ( $Notice )
							wc_add_notice( $Notice , 'error' );
						
						do_action( 'WC_BankMellat_Send_to_Gateway_Failed', $order_id, $fault );
					}			
				}
			}

			public function Return_from_BankMellat_Gateway_By_HANNANStd(){
				
				global $woocommerce;
				$action = $this->author;
				do_action( 'WC_Gateway_Payment_Actions', $action );
				
				if ( isset($_GET['wc_order']) ) 
					$order_id = $_GET['wc_order'];
				else
					$order_id = $woocommerce->session->order_id_bankmellat;
				if ( $order_id ) {
				
					
					$order = new WC_Order($order_id);
					$currency = $order->get_order_currency();		
					$currency = apply_filters( 'WC_BankMellat_Currency', $currency, $order_id );
						
					if($order->status !='completed'){
						
						$Amount = intval($order->order_total);
						$Amount = apply_filters( 'woocommerce_order_amount_total_IRANIAN_gateways_before_check_currency', $Amount, $currency );
						if ( strtolower($currency) == strtolower('IRT') || strtolower($currency) == strtolower('TOMAN')
							|| strtolower($currency) == strtolower('Iran TOMAN') || strtolower($currency) == strtolower('Iranian TOMAN')
							|| strtolower($currency) == strtolower('Iran-TOMAN') || strtolower($currency) == strtolower('Iranian-TOMAN')
							|| strtolower($currency) == strtolower('Iran_TOMAN') || strtolower($currency) == strtolower('Iranian_TOMAN')
							|| strtolower($currency) == strtolower('تومان') || strtolower($currency) == strtolower('تومان ایران')
						)
							$Amount = $Amount*10;
						else if ( strtolower($currency) == strtolower('IRHT') )							
							$Amount = $Amount*1000*10;
						else if ( strtolower($currency) == strtolower('IRHR') )							
							$Amount = $Amount*1000;
					
						$Amount = apply_filters( 'woocommerce_order_amount_total_IRANIAN_gateways_after_check_currency', $Amount, $currency );
						$Amount = apply_filters( 'woocommerce_order_amount_total_IRANIAN_gateways_irr', $Amount, $currency );
						$Amount = apply_filters( 'woocommerce_order_amount_total_Mellat_gateway', $Amount, $currency );
			
						//include NuSoap
						if ( !class_exists( 'nusoap_client' ) ) 
							include_once("nusoap.php");
						//----
		
							
						$terminalId = $this->terminal;
						$userName = $this->username; 
						$userPassword = $this->password; 
						
						//need for settle
						$orderid = $_POST['SaleOrderId'];
						if ($_POST['SaleOrderId'])
							update_post_meta( $order_id, 'WC_BankMellat_settleSaleOrderId', $_POST['SaleOrderId'] );
						if ($_POST['SaleReferenceId'])
							update_post_meta( $order_id, 'WC_BankMellat_settleSaleReferenceId', $_POST['SaleReferenceId'] );
						//------
						
						$client = new nusoap_client('https://bpm.shaparak.ir/pgwchannel/services/pgw?wsdl');
						$namespace='http://interfaces.core.sw.bps.com/';

						if($_POST['ResCode']!=0){
							if( $_POST['ResCode'] == 17 || $_POST['ResCode'] == '17'){ 
								$status = 'cancelled';
								$transaction_id = $_POST['SaleReferenceId'];
								$fault = 0;
							}
							else {
								$status = 'failed'; 
								$transaction_id = $_POST['SaleReferenceId'];
								$fault = $_POST['ResCode'];
							}
						}
						else {
							$status_bm  = "";
							$reverse = "";
							$rev_to_u = 0;	
							$err = $client->getError();
							if ($err){
								$status_bm = 0;
								$reverse = 1;
							}
							else
							{
								$orderId 				= $_POST['SaleOrderId'];
								$verifySaleOrderId 		= $_POST['SaleOrderId'];
								$verifySaleReferenceId 	= $_POST['SaleReferenceId'];
									$parameters = array(
										'terminalId' => $terminalId,
										'userName' => $userName,
										'userPassword' => $userPassword,
										'orderId' => $orderId,
										'saleOrderId' => $verifySaleOrderId,
										'saleReferenceId' => $verifySaleReferenceId
									);
									$result = $client->call('bpVerifyRequest', $parameters, $namespace);
									if ($client->fault){
										$status_bm = 0;
										$reverse = 1;
									}
									else{
										$err = $client->getError();
										if ($err)
										{
											$status_bm = 0;
											$reverse = 1;
										}
										else {
											if($result == 0){
												$inquirySaleOrderId = $_POST['SaleOrderId'];
												$inquirySaleReferenceId = $_POST['SaleReferenceId'];
												$err = $client->getError();
												if ($err){
													$status_bm = 0;
													$reverse = 1;
												}
												else{
													$parameters = array(
														'terminalId' => $terminalId,
														'userName' => $userName,
														'userPassword' => $userPassword,
														'orderId' => $orderId,
														'saleOrderId' => $inquirySaleOrderId,
														'saleReferenceId' => $inquirySaleReferenceId
													);
													$result = $client->call('bpInquiryRequest', $parameters, $namespace);
													if($result == 0){
														if ($client->fault){
															$status_bm = 0;
															$reverse = 1;
														}
														else{
															$err = $client->getError();
															if ($err){
																$status_bm = 0;
																$reverse = 1;
															}
															else{
																$status_bm = 1;
															}
														}
													}
													else{
														$status_bm = 0;
														$reverse = 0;
													}
												}
											}
										else{
											$status_bm = 0;
											$reverse = 0;
										}
									}
								}
							}
							if ($status_bm == 1){
								$settleSaleOrderId 		= $_POST['SaleOrderId'];
								$settleSaleReferenceId 	= $_POST['SaleReferenceId'];
								$err = $client->getError();
								if ($err) {
									$status_bm = 0;
								}
								else{
									$parameters = array(
										'terminalId' => $terminalId,
										'userName' => $userName,
										'userPassword' => $userPassword,
										'orderId' => $orderId,
										'saleOrderId' => $settleSaleOrderId,
										'saleReferenceId' => $settleSaleReferenceId
									);
									$result = $client->call('bpSettleRequest', $parameters, $namespace);
									if($result == 0){
										if ($client->fault){
											$status_bm = 0;
										}
										else{
											$err = $client->getError();
											if ($err){
												$reverse = 1;
												$status_bm = 0;
											} 
											else {
												$status_bm = 1;
												$status = 'completed';
												$transaction_id = $_POST['SaleReferenceId'];
												$fault = 0;
												$verify_id = $verifySaleReferenceId;
											}
										}
									}
									else{
										$status_bm = 0;
										$reverse = 1;
									}
								}
							}
							if ($reverse == 1){
								$orderId 					= $_POST['SaleOrderId'];
								$reversalSaleOrderId 		= $_POST['SaleOrderId'];
								$reversalSaleReferenceId 	= $_POST['SaleReferenceId'];
								$err = $client->getError();
								if ($err){
									$status_bm = 0;
								}
								else{
									$parameters = array(
										'terminalId' => $terminalId,
										'userName' => $userName,
										'userPassword' => $userPassword,
										'orderId' => $orderId,
										'saleOrderId' => $reversalSaleOrderId,
										'saleReferenceId' => $reversalSaleReferenceId
									);
									$result = $client->call('bpReversalRequest', $parameters, $namespace);
									if ($client->fault){
										$status_bm = 0;
									}
									else{
										$err = $client->getError();
										if ($err) {
											$status_bm = 0;
										}
										else
										{
											$status = 'failed';
											$transaction_id = $_POST['SaleReferenceId'];
											$fault = $result;
											if($result == 0){						
												$rev_to_u = 2; 
											} 
											else{
												$rev_to_u = 1; 
											}
										}
									}
								}
							}
						}
						if ( $status != 'cancelled' && $status != 'completed' ) {
							$status = 'failed';
						}
						if ($status == 'failed') {
							$transaction_id = $_POST['SaleReferenceId'];
							$fault = $_POST['ResCode'];
							if ($_POST['ResCode'] == 17 || $_POST['ResCode'] == '17' ) {
								$status = 'cancelled';
								$transaction_id = $_POST['SaleReferenceId'];
								$fault = 0;
							}
						}
						
						
						
							
						$SaleOrderId = isset($orderid) ? $orderid : 0;		
						if ( $status == 'completed') {
							$action = $this->author;
							do_action( 'WC_Gateway_Payment_Actions', $action );
							
							if ( $transaction_id && ( $transaction_id !=0 ) )
								update_post_meta( $order_id, '_transaction_id', $transaction_id );
							
						
														
							$order->payment_complete($transaction_id);
							$woocommerce->cart->empty_cart();
							
							
							$Note = sprintf( __('پرداخت موفقیت آمیز بود .<br/> کد رهگیری (کد مرجع تراکنش) : %s <br/> شماره درخواست تراکنش : %s', 'woocommerce' ), $transaction_id, $SaleOrderId );
							$Note = apply_filters( 'WC_BankMellat_Return_from_Gateway_Success_Note', $Note, $order_id, $transaction_id, $SaleOrderId );
							if ($Note)
								$order->add_order_note( $Note , 1 );
							
							$Notice = wpautop( wptexturize($this->success_massage));
							
							$Notice = str_replace("{transaction_id}",$transaction_id,$Notice);
							$Notice = str_replace("{SaleOrderId}",$SaleOrderId,$Notice);
							
							$Notice = apply_filters('WC_BankMellat_Return_from_Gateway_Success_Notice', $Notice, $order_id, $transaction_id, $SaleOrderId );
							if ($Notice)
								wc_add_notice( $Notice , 'success' );
							
							
							do_action( 'WC_BankMellat_Return_from_Gateway_Success', $order_id, $transaction_id , $SaleOrderId);
							
							wp_redirect( add_query_arg( 'wc_status', 'success', $this->get_return_url( $order ) ) );
							exit;
						}
						elseif ( $status == 'cancelled') {
							$action = $this->author;
							do_action( 'WC_Gateway_Payment_Actions', $action );
							
							$tr_id = ( $transaction_id && $transaction_id != 0 ) ? ('<br/>کد رهگیری (کد مرجع تراکنش) : '.$transaction_id) : '';
							$sale_order_id = ( $SaleOrderId && $SaleOrderId != 0 ) ? ('<br/>شماره درخواست تراکنش : '.$SaleOrderId) : '';
							
							$Note = sprintf( __('کاربر در حین تراکنش از پرداخت انصراف داد . %s %s', 'woocommerce' ) , $tr_id , $sale_order_id);
							$Note = apply_filters( 'WC_BankMellat_Return_from_Gateway_Cancelled_Note', $Note, $order_id, $transaction_id , $SaleOrderId);
							if ( $Note )
								$order->add_order_note( $Note, 1 );
							
							
							$Notice =  wpautop( wptexturize($this->cancelled_massage));
							
							$Notice = str_replace("{transaction_id}",$transaction_id,$Notice);
							$Notice = str_replace("{SaleOrderId}",$SaleOrderId,$Notice);
							
							$Notice = apply_filters( 'WC_BankMellat_Return_from_Gateway_Cancelled_Notice', $Notice, $order_id, $transaction_id, $SaleOrderId );
							if ($Notice)
								wc_add_notice( $Notice , 'error' );
							
							do_action( 'WC_BankMellat_Return_from_Gateway_Cancelled', $order_id, $transaction_id, $SaleOrderId );
							
							wp_redirect( $woocommerce->cart->get_checkout_url() );
							exit;
							
						}
						else {
							$action = $this->author;
							do_action( 'WC_Gateway_Payment_Actions', $action );
							
							$tr_id = ( $transaction_id && $transaction_id != 0 ) ? ('<br/>کد رهگیری (کد مرجع تراکنش) : '.$transaction_id) : '';
							$sale_order_id = ( $SaleOrderId && $SaleOrderId != 0 ) ? ('<br/>شماره درخواست تراکنش : '.$SaleOrderId) : '';
							
							$Note = sprintf( __( 'خطا در هنگام بازگشت از بانک : %s %s %s', 'woocommerce'), $this->Fault_BankMellat($fault), $tr_id , $sale_order_id );
							$Note = apply_filters( 'WC_BankMellat_Return_from_Gateway_Failed_Note', $Note, $order_id, $transaction_id, $SaleOrderId, $fault );
							if ($Note)
								$order->add_order_note( $Note , 1 );
							
							
							$Notice = wpautop( wptexturize($this->failed_massage));
							
							
							$Notice = str_replace("{transaction_id}",$transaction_id,$Notice);
							$Notice = str_replace("{SaleOrderId}",$SaleOrderId,$Notice);
							
							
							$Notice = str_replace("{fault}",$this->Fault_BankMellat($fault),$Notice);
							$Notice = apply_filters( 'WC_BankMellat_Return_from_Gateway_Failed_Notice', $Notice, $order_id, $transaction_id, $SaleOrderId, $fault );
							if ($Notice)
								wc_add_notice( $Notice , 'error' );
							
							
							
							if ( $rev_to_u == 2 ) {
								$Rev_Note =  __('مبلغ پرداختی کاربر از طریق بانک برگشت خورد .', 'woocommerce' );
								$order->add_order_note( $Rev_Note , 1 );
							}
									
							if ( $rev_to_u == 1 ) {
								$Rev_Note =  __('مبلغ پرداختی باید به حساب کاربر برگشت بخورد زیرا در حین برگشت زدن مبلغ ، خطای سیستمی رخ داده است .', 'woocommerce' );
								$order->add_order_note( $Rev_Note, 1 );
							}
							
							
							
							do_action( 'WC_BankMellat_Return_from_Gateway_Failed', $order_id, $transaction_id, $SaleOrderId, $fault );
							
							wp_redirect(  $woocommerce->cart->get_checkout_url()  );
							exit;
						}
				
				
					}
					else {
						
						$action = $this->author;
						do_action( 'WC_Gateway_Payment_Actions', $action );
							
						$transaction_id = get_post_meta( $order_id, '_transaction_id', true );		
						$SaleOrderId = get_post_meta( $order_id, 'WC_BankMellat_settleSaleOrderId', true );
						
						$Notice = wpautop( wptexturize($this->success_massage));
						
						$Notice = str_replace("{transaction_id}",$transaction_id,$Notice);
						$Notice = str_replace("{SaleOrderId}",$SaleOrderId,$Notice);
						
						$Notice = apply_filters( 'WC_BankMellat_Return_from_Gateway_ReSuccess_Notice', $Notice, $order_id, $transaction_id, $SaleOrderId );
						if ($Notice)
							wc_add_notice( $Notice , 'success' );
						
						
						do_action( 'WC_BankMellat_Return_from_Gateway_ReSuccess', $order_id, $transaction_id, $SaleOrderId );
							
						wp_redirect( add_query_arg( 'wc_status', 'success', $this->get_return_url( $order ) ) );
						exit;
					}
				}
				else {
					
					$action = $this->author;
					do_action( 'WC_Gateway_Payment_Actions', $action );
							
					$fault = __('شماره سفارش وجود ندارد .', 'woocommerce' );
					$Notice = wpautop( wptexturize($this->failed_massage));
					$Notice = str_replace("{fault}",$fault, $Notice);
					$Notice = apply_filters( 'WC_BankMellat_Return_from_Gateway_No_Order_ID_Notice', $Notice, $order_id, $fault );
					if ($Notice)
						wc_add_notice( $Notice , 'error' );		
					
					do_action( 'WC_BankMellat_Return_from_Gateway_No_Order_ID', $order_id, $transaction_id, $fault );
						
					wp_redirect( $woocommerce->cart->get_checkout_url() );
					exit;
				}
			}
			
			private static function Fault_BankMellat($err_code){
				
				$message = __('در حین پرداخت خطای سیستمی رخ داده است .', 'woocommerce' );
				switch($err_code){
							
					case 'settle':
						$message =  __('عملیات Settel دستی با موفقیت انجام شد .', 'woocommerce' );
					break;
					case '-2':
					case -2:
						$message =  __('شکست در ارتباط با بانک .', 'woocommerce' );
					break;
					case '-1':
					case -1:
						$message =  __('شکست در ارتباط با بانک .', 'woocommerce' );
					break;
					//case '0':
						//$message =  __('تراکنش با موفقیت انجام شد .', 'woocommerce' );
					//break;
					case '11':
					case 11:
						$message =  __('شماره کارت معتبر نیست .', 'woocommerce' );
					break;
					case '12':
					case 12:
						$message =  __('موجودی کافی نیست .', 'woocommerce' );
					break;
					case '13':
					case 13:
						$message =  __('رمز دوم شما صحیح نیست .', 'woocommerce' );
					break;
					case '14':
					case 14:
						$message =  __('دفعات مجاز ورود رمز بیش از حد است .', 'woocommerce' );
					break;
					case '15':
					case 15:
						$message =  __('کارت معتبر نیست .', 'woocommerce' );
					break;
					case '16':
					case 16:
						$message =  __('دفعات برداشت وجه بیش از حد مجاز است .', 'woocommerce' );
					break;
					case '17':
					case 17:
						$message =  __('شما از انجام تراکنش منصرف شده اید .', 'woocommerce' );
					break;
					case '18':
					case 18:
						$message =  __('تاریخ انقضای کارت گذشته است .', 'woocommerce' );
					break;
					case '19':
					case 19:
						$message =  __('مبلغ برداشت وجه بیش از حد مجاز است .', 'woocommerce' );
					break;
					case '111':
					case 111:
						$message =  __('صادر کننده کارت نامعتبر است .', 'woocommerce' );
					break;
					case '112':
					case 112:
						$message =  __('خطای سوییچ صادر کننده کارت رخ داده است .', 'woocommerce' );
					break;
					case '113':
					case 113:
						$message =  __('پاسخی از صادر کننده کارت دریافت نشد .', 'woocommerce' );
					break;
					case '114':
					case 114:
						$message =  __('دارنده کارت مجاز به انجام این تراکنش نمی باشد .', 'woocommerce' );
					break;
					case '21':
					case 21:
						$message =  __('پذیرنده معتبر نیست .', 'woocommerce' );
					break;
					case '23':
					case 23:
						$message =  __('خطای امنیتی رخ داده است .', 'woocommerce' );
					break;
					case '24':
					case 24:
						$message =  __('اطلاعات کاربری پذیرنده معتبر نیست .', 'woocommerce' );
					break;
					case '25':
					case 25:
						$message =  __('مبلغ نامعتبر است .', 'woocommerce' );
					break;
					case '31':
					case 31:
						$message =  __('پاسخ نامعتبر است .', 'woocommerce' );
					break;
					case '32':
					case 32:
						$message =  __('فرمت اطلاعات وارد شده صحیح نیست .', 'woocommerce' );
					break;
					case '33':
					case 33:
						$message =  __('حساب نامعتبر است .', 'woocommerce' );
					break;
					case '34':
					case 34:
						$message =  __('خطای سیستمی رخ داده است .', 'woocommerce' );
					break;
					case '35':
					case 35:
						$message =  __('تاریخ نامعتبر است .', 'woocommerce' );
					break;
					case '41':
					case 41:
						$message =  __('شماره درخواست تکراری است .', 'woocommerce' );
					break;
					case '42':
					case 42:
						$message =  __('همچین تراکنشی وجود ندارد .', 'woocommerce' );
					break;
					case '43':
					case 43:
						$message =  __('قبلا درخواست Verify داده شده است', 'woocommerce' );
					break;
					case '44':
					case 44:
						$message =  __('درخواست Verify یافت نشد .', 'woocommerce' );
					break;
					case '45':
					case 45:
						$message =  __('تراکنش قبلا Settle شده است .', 'woocommerce' );
					break;
					case '46':
					case 46:
						$message =  __('تراکنش Settle نشده است .', 'woocommerce' );
					break;
					case '47':
					case 47:
						$message =  __('تراکنش Settle یافت نشد .', 'woocommerce' );
					break;
					case '48':
					case 48:
						$message =  __('تراکنش قبلا Reverse شده است .', 'woocommerce' );
					break;
					case '49':
					case 49:
						$message =  __('تراکنش Refund یافت نشد .', 'woocommerce' );
					break;
					case '412':
					case 412:
						$message =  __('شناسه قبض نادرست است .', 'woocommerce' );
					break;
					case '413':
					case 413:
						$message =  __('شناسه پرداخت نادرست است .', 'woocommerce' );
					break;
					case '414':
					case 414:
						$message =  __('سازمان صادر کننده قبض معتبر نیست .', 'woocommerce' );
					break;
					case '415':
					case 415:
						$message =  __('زمان جلسه کاری به پایان رسیده است .', 'woocommerce' );
					break;
					case '416':
					case 416:
						$message =  __('خطا در ثبت اطلاعات رخ داده است .', 'woocommerce' );
					break;
					case '417':
					case 417:
						$message =  __('شناسه پرداخت کننده نامعتبر است .', 'woocommerce' );
					break;
					case '418':
					case 418:
						$message =  __('اشکال در تعریف اطلاعات مشتری رخ داده است .', 'woocommerce' );
					break;
					case '419':
					case 419:
						$message =  __('تعداد دفعات ورود اطلاعات بیش از حد مجاز است .', 'woocommerce' );
					break;
					case '421':
					case 421:
						$message =  __('IP معتبر نیست .', 'woocommerce' );
					break;
					case '51':
					case 51:
						$message =  __('تراکنش تکراری است .', 'woocommerce' );
					break;
					case '54':
					case 54:
						$message =  __('تراکنش مرجع موجود نیست .', 'woocommerce' );
					break;
					case '55':
					case 55:
						$message =  __('تراکنش نامعتبر است .', 'woocommerce' );
					break;
					case '61':
					case 61:
						$message =  __('خطا در واریز رخ داده است .', 'woocommerce' );
					break;	
				}
				return $message;
			}
		}
	}
}
add_action('plugins_loaded', 'Load_BankMellat_Gateway', 0);