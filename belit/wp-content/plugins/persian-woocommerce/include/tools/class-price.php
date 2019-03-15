<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'PW_Tools_Price' ) ) :

	class PW_Tools_Price {

		public function __construct() {

			if ( PW()->get_options( 'enable_call_for_price' ) == 'yes' ) {
				add_filter( 'woocommerce_empty_price_html', array( $this, 'on_empty_price' ), PHP_INT_MAX - 1 );
				add_filter( 'woocommerce_sale_flash', array( $this, 'hide_sales_flash' ), PHP_INT_MAX, 3 );
			}

			if ( PW()->get_options( 'persian_price' ) == 'yes' ) {
				add_filter( 'wc_price', array( $this, 'persian_number' ) );
				add_filter( 'woocommerce_get_price_html', array( $this, 'persian_number' ) );

				add_filter( 'woocommerce_cart_item_price', array( $this, 'persian_number' ) );
				add_filter( 'woocommerce_cart_item_subtotal', array( $this, 'persian_number' ) );
				add_filter( 'woocommerce_cart_subtotal', array( $this, 'persian_number' ) );
				add_filter( 'woocommerce_cart_totals_coupon_html', array( $this, 'persian_number' ) );
				add_filter( 'woocommerce_cart_shipping_method_full_label', array( $this, 'persian_number' ) );
				add_filter( 'woocommerce_cart_total', array( $this, 'persian_number' ) );
			}

		}

		public function hide_sales_flash( $onsale_html, $post, $product ) {
			return ( 'yes' == PW()->get_options( 'call_for_price_hide_sale_sign' ) && '' == $product->get_price() ) ? "" : $onsale_html;
		}

		public function is_related() {
			global $post;

			return is_singular() !== is_single( $post->ID );
		}

		public function on_empty_price( $price ) {
			if ( is_archive() ) {
				return PW()->get_options( 'call_for_price_text_on_archive' );
			} elseif ( $this->is_related() ) {
				return PW()->get_options( 'call_for_price_text_on_related' );
			} elseif ( is_single() ) {
				return PW()->get_options( 'call_for_price_text' );
			} elseif ( is_home() ) {
				return PW()->get_options( 'call_for_price_text_on_home' );
			}

			return $price;
		}

		public function persian_number( $price ) {
			return str_replace( range( 0, 9 ), array( '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹' ), $price );
		}
	}
endif;
PW()->tools->price = new PW_Tools_Price();
