<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Persian_Woocommerce_Currencies' ) ) :

	class Persian_Woocommerce_Currencies extends Persian_Woocommerce_Core {

		public $currencies;

		public function __construct() {

			$this->currencies = array(
				'IRR'  => __( 'ریال', 'woocommerce' ),
				'IRHR' => __( 'هزار ریال', 'woocommerce' ),
				'IRT'  => __( 'تومان', 'woocommerce' ),
				'IRHT' => __( 'هزار تومان', 'woocommerce' )
			);

			add_filter( 'woocommerce_currencies', array( $this, 'currencies' ) );
			add_filter( 'woocommerce_currency_symbol', array( $this, 'currencies_symbol' ), 10, 2 );
		}

		public function currencies( $currencies ) {

			foreach ( $this->currencies as $key => $value ) {
				unset( $currencies[ $key ] );
			}

			return array_merge( $currencies, $this->currencies );
		}

		public function currencies_symbol( $currency_symbol, $currency ) {

			if ( in_array( $currency, array_keys( $this->currencies ) ) ) {
				return $this->currencies[ $currency ];
			}

			return $currency_symbol;
		}
	}
endif;
PW()->currencies = new Persian_Woocommerce_Currencies();