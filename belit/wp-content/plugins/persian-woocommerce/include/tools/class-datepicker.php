<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'PW_Tools_DatePicker' ) ) :

	class PW_Tools_DatePicker {

		public function __construct() {

			if ( PW()->get_options( 'enable_jalali_datepicker' ) == 'yes' ) {
				add_action( 'admin_enqueue_scripts', array( $this, 'datepicker' ), 1000 );
				add_action( 'admin_print_footer_scripts', array( $this, 'inline_js' ) );
			}

		}

		public function datepicker() {

			if ( get_current_screen()->id == 'product' ) {
				wp_enqueue_style( 'pw-datepicker', PW()->plugin_url( 'assets/css/datepicker.css' ) );
				wp_enqueue_script( 'pw-datepicker-js', PW()->plugin_url( 'assets/js/datepicker.js' ) );
				// wp_dequeue_script( 'jquery-ui-datepicker' );
				// wp_deregister_script( 'jquery-ui-datepicker' );
			}
		}

		public function inline_js() {

			if ( get_current_screen()->id !== 'product' ) {
				return false;
			}

			?>
            <style type="text/css">
                #ui-datepicker-div {
                    display: none !important;
                }
            </style>
            <script type="text/javascript">
                if (!window.jQuery) {
                    alert("Error in load jQuery!");
                }
                jQuery(function ($) {
                    var start = Calendar.setup({
                        inputField: "_sale_price_dates_from",   // id of the input field
                        button: "_sale_price_dates_from",   // trigger for the calendar (button ID)
                        ifFormat: "%Y-%m-%d",       // format of the input field
                        dateType: 'jalali',
                        ifDateType: 'gregorian',
                        weekNumbers: false
                    });

                    var end = Calendar.setup({
                        inputField: "_sale_price_dates_to",   // id of the input field
                        button: "_sale_price_dates_to",   // trigger for the calendar (button ID)
                        ifFormat: "%Y-%m-%d",       // format of the input field
                        dateType: 'jalali',
                        ifDateType: 'gregorian',
                        dateStatusFunc: getDateStatus,
                        weekNumbers: false
                    });

                    function getDateStatus(date, y, m, d) {
                        var s = new Date(start.date);
                        var f = new Date(date);
                        var timeDiff = f.getTime() - s.getTime();

                        if (timeDiff >= 0)
                            return false;
                        else return 'disabled';
                    }

                });
            </script>
			<?php
		}
	}
endif;
PW()->tools->datepicker = new PW_Tools_DatePicker();
