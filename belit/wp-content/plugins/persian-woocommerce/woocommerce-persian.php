<?php
/*
Plugin Name: ووکامرس فارسی
Plugin URI: http://woocommerce.ir
Description: بسته فارسی ساز ووکامرس پارسی به راحتی سیستم فروشگاه ساز ووکامرس را فارسی می کند. با فعال سازی افزونه ، بسیاری از قابلیت های مخصوص ایران به افزونه افزوده می شوند. پشتیبانی در <a href="http://www.woocommerce.ir/" target="_blank">ووکامرس پارسی</a>.
Version: 3.4.2
Author: ووکامرس فارسی
Author URI: http://woocommerce.ir
WC requires at least: 3.0.0
WC tested up to: 3.4.3
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'PW_VERSION' ) ) {
	define( 'PW_VERSION', '3.4.2' );
}

require_once( 'include/class-core.php' );
require_once( 'include/class-widget.php' );

if ( PW()->wc_is_active ) {
	require_once( 'include/class-translate.php' );
	require_once( 'include/class-tools.php' );
	require_once( 'include/class-address.php' );
	require_once( 'include/class-currencies.php' );
}

function PW() {
	return Persian_Woocommerce_Core::instance( __FILE__ );
}

$GLOBALS['PW'] = PW();