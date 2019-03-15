<?php
add_action( 'admin_init', 'tc_installation_wizard' );

function tc_installation_wizard() {
	global $tc;
	if ( current_user_can( 'manage_options' ) ) {
		if ( empty( $_GET[ 'page' ] ) || 'tc-installation-wizard' !== $_GET[ 'page' ] ) {
			return;
		}
		ob_start();

		wp_enqueue_style( 'tc-open-sans-font', 'http://fonts.googleapis.com/css?family=Open+Sans:300,700', array(), $tc->version );
		wp_enqueue_style( 'tc-installation-wizard', $tc->plugin_url . 'css/installation-wizard.css', array(), $tc->version );
		wp_enqueue_style( 'tc-chosen-installation-wizard', $tc->plugin_url . 'css/chosen.min.css', array(), $tc->version );

		wp_enqueue_script( 'tc-installation-wizard-js', $tc->plugin_url . 'js/installation-wizard.js', '', $tc->version );
		wp_enqueue_script( 'tc-chosen-installation-wizard', $tc->plugin_url . 'js/chosen.jquery.min.js', '', false, false );
		wp_localize_script( 'tc-installation-wizard-js', 'tc_ajax', array(
			'ajaxUrl' => apply_filters( 'tc_ajaxurl', admin_url( 'admin-ajax.php', (is_ssl() ? 'https' : 'http' ) ) ),
		) );

		tc_setup_wizard_header();
		tc_setup_wizard_content();
		tc_setup_wizard_footer();
		exit;
	}
}

function tc_setup_wizard_header() {
	?>
	<!DOCTYPE html>
	<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
		<head>
			<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			<title><?php _e( 'Installation Wizard', 'tc' ); ?></title>
			<?php do_action( 'admin_print_styles' ); ?>
			<?php do_action( 'admin_print_scripts' ); ?>
			<?php do_action( 'admin_head' ); ?>
		</head>
		<body class="tc-installation-wizard">
			<?php
		}

		function tc_setup_wizard_content() {
			global $tc;
			$steps	 = tc_get_wizard_steps();
			$step	 = isset( $_GET[ 'step' ] ) ? sanitize_key( $_GET[ 'step' ] ) : 'start';

			if ( !in_array( $step, $steps ) ) {
				$mode_checked = get_option( 'tc_wizard_mode', 'sa' );

				$last_step = add_query_arg( array(
					'page'	 => 'tc-installation-wizard',
					'step'	 => tc_wizard_get_start_screen_next_step(),
					'mode'	 => $mode_checked
				), admin_url( 'index.php' ) );
				wp_redirect( $last_step );
				exit;
			}
			require_once( $tc->plugin_dir . 'includes/admin-pages/installation-wizard/' . $step . '.php' );
		}

		function tc_setup_wizard_footer() {
			$current_step = isset( $_GET[ 'step' ] ) ? sanitize_key( $_GET[ 'step' ] ) : 'start';
			?>
			<input type="hidden" name="tc_step" class="tc_step" value="<?php echo esc_attr( $current_step ); ?>">
		</body>
	</html>
	<?php
}

if ( !function_exists( 'tc_wizard_progress' ) ) {

	function tc_wizard_progress() {
		$steps = tc_get_wizard_steps( false );

		$steps_count	 = count( $steps );
		$current_step	 = isset( $_GET[ 'step' ] ) ? sanitize_key( $_GET[ 'step' ] ) : 'start';

		$key = array_search( $current_step, $steps );
		$key = ($key + 1); //lift the index by 1 so it can match with an i variable
		?>
		<div class="tc-steps-countdown <?php echo $current_step; ?>">
			<div class="tc-progress-bar"><div class="tc-progress-bar-inside"></div></div>
			<?php
			for ( $i = 1; $i <= $steps_count; $i++ ) {
				?>
				<div class="tc-step-no tc-step-<?php echo $i; ?> <?php echo (int) $key >= $i ? 'tc-active-step' : ''; ?>"><?php echo $i; ?></div>
				<?php
			}
			?>
		</div><!-- .tc-steps-countdown -->
		<?php
	}

}

if ( !function_exists( 'tc_wizard_navigation' ) ) {

	function tc_wizard_navigation() {
		$current_step	 = isset( $_GET[ 'step' ] ) ? sanitize_key( $_GET[ 'step' ] ) : 'start';
		$steps			 = tc_get_wizard_steps( false );

		if ( $current_step == 'start' ) {
			$skip_title	 = __( 'Skip Wizard', 'tc' );
			$skip_url	 = admin_url( 'index.php' );

			$continue_url = add_query_arg( array(
				'page'	 => 'tc-installation-wizard',
				'step'	 => $steps[ 0 ],
			), admin_url( 'index.php' ) );
		} else {
			$skip_title	 = __( 'Skip Step', 'tc' );
			$key		 = array_search( $current_step, $steps );

			$skip_url = add_query_arg( array(
				'page'	 => 'tc-installation-wizard',
				'step'	 => isset( $steps[ $key + 1 ] ) ? $steps[ $key + 1 ] : $steps[ 0 ],
				'mode'	 => isset( $_GET[ 'mode' ] ) ? sanitize_key( $_GET[ 'mode' ] ) : 'sa'
			), admin_url( 'index.php' ) );

			$continue_url = add_query_arg( array(
				'page'	 => 'tc-installation-wizard',
				'step'	 => isset( $steps[ $key + 1 ] ) ? $steps[ $key + 1 ] : $steps[ 0 ],
				'mode'	 => isset( $_GET[ 'mode' ] ) ? sanitize_key( $_GET[ 'mode' ] ) : 'sa'
			), admin_url( 'index.php' ) );
		}
		?>
		<div class="tc-wiz-screen-footer">
			<?php if ( $current_step !== 'checkin-apps' ) { ?>
				<button class="tc-skip-button tc-button" onclick="window.location.href = '<?php echo $skip_url; ?>'"><?php echo $skip_title; ?></button>
			<?php } ?>
			<?php
			if ( $current_step == 'start' ) {
				?>
				<input type="submit" class="tc-continue-button tc-button" value="<?php echo esc_attr( __( 'Continue', 'tc' ) ); ?>" />
				<?php
			} else {
				?>				
				<button class="tc-continue-button tc-button" data-href="<?php echo $continue_url; ?>" onclick="window.location.href = '<?php echo $continue_url; ?>'"><?php _e( 'Continue', 'tc' ) ?></button>
				<?php
			}
			?>

		</div><!-- tc-wiz-screen-footer -->
		<?php
	}

}
if ( !function_exists( 'tc_wizard_mode' ) ) {

	function tc_wizard_mode() {
		if ( isset( $_GET[ 'mode' ] ) && isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'tc-installation-wizard' ) {
			if ( $_GET[ 'mode' ] == 'wc' || $_GET[ 'mode' ] == 'sa' ) {
				return sanitize_key( $_GET[ 'mode' ] );
			} else {
				return 'sa'; //stand-alone
			}
		}
	}

}

if ( !function_exists( 'tc_get_wizard_steps' ) ) {

	function tc_get_wizard_steps( $include_start_step = true ) {
		$steps = array( 'start', 'license-key', 'settings', 'pages-setup', 'checkin-apps', 'finish' );

		if ( !$include_start_step ) {
			unset( $steps[ 0 ] ); //start
		}

		if ( tc_iw_is_wl() ) {
			unset( $steps[ 4 ] ); //plugin is white-labeled, don't show the check-in apps screen
		}

		if ( tc_iw_is_wl() || (defined( 'TC_LCK' ) && TC_LCK !== '') ) {//plugin is white-labeled
			//$key = array_search( 'license-key', $steps );
			unset( $steps[ 1 ] ); //'license-key'
		}

		if ( get_option( 'tc_needs_pages', 1 ) == 1 ) {
			//do nothing
		} else {
			unset( $steps[ 3 ] ); //pages-setup
		}

		if ( !tc_iw_is_pr() ) {
			unset( $steps[ 1 ] ); //'license-key'//not pr version
		}

		if ( tc_wizard_mode() == 'wc' ) {
			unset( $steps[ 2 ] ); //'settings'
			unset( $steps[ 3 ] ); //'pages-setup'
		}

		$steps = apply_filters( 'tc_wizard_steps', $steps, tc_wizard_mode() );

		return array_merge( $steps ); //array_merge to rebase indexes after unsetting elements
	}

}

if ( !function_exists( 'tc_wizard_wrapper_class' ) ) {

	function tc_wizard_wrapper_class() {
		$steps		 = tc_get_wizard_steps( false );
		$steps_count = count( $steps );
		echo esc_attr( 'tc-wizard-steps-count-' . $steps_count );
	}

}

if ( !function_exists( 'tc_wizard_get_start_screen_next_step' ) ) {

	function tc_wizard_get_start_screen_next_step() {
		$steps = tc_get_wizard_steps( false );
		return $steps[ 0 ];
	}

}

add_action( 'wp_ajax_tc_installation_wizard_save_step_data', 'tc_ajax_installation_wizard_save_step_data' );

if ( !function_exists( 'tc_ajax_installation_wizard_save_step_data' ) ) {

	function tc_ajax_installation_wizard_save_step_data() {
		global $tc;

		$step = isset( $_POST[ 'data' ][ 'step' ] ) ? sanitize_key( $_POST[ 'data' ][ 'step' ] ) : 'start';

		switch ( $step ) {
			case 'start':
				update_option( 'tc_wizard_step', $step );
				update_option( 'tc_wizard_mode', isset( $_POST[ 'data' ][ 'mode' ] ) ? sanitize_text_field( $_POST[ 'data' ][ 'mode' ] ) : 'sa'  );
				break;
			case 'license-key':
				update_option( 'tc_wizard_step', $step );
				$tc_general_settings									 = get_option( 'tc_general_setting', false );
				$tc_general_settings[ 'license_key' ]					 = sanitize_text_field( $_POST[ 'data' ][ 'license_key' ] );
				update_option( 'tc_general_setting', $tc_general_settings );
				break;
			case 'settings':
				update_option( 'tc_wizard_step', $step );
				$tc_general_settings									 = get_option( 'tc_general_setting', false );
				$tc_general_settings[ 'currencies' ]					 = sanitize_text_field( $_POST[ 'data' ][ 'currencies' ] );
				$tc_general_settings[ 'currency_symbol' ]				 = sanitize_text_field( $_POST[ 'data' ][ 'currency_symbol' ] );
				$tc_general_settings[ 'currency_position' ]				 = sanitize_text_field( $_POST[ 'data' ][ 'currency_position' ] );
				$tc_general_settings[ 'price_format' ]					 = sanitize_text_field( $_POST[ 'data' ][ 'price_format' ] );
				$tc_general_settings[ 'show_tax_rate' ]					 = sanitize_text_field( $_POST[ 'data' ][ 'show_tax_rate' ] );
				$tc_general_settings[ 'tax_rate' ]						 = sanitize_text_field( $_POST[ 'data' ][ 'tax_rate' ] );
				$tc_general_settings[ 'tax_inclusive' ]					 = sanitize_text_field( $_POST[ 'data' ][ 'tax_inclusive' ] );
				$tc_general_settings[ 'tax_label' ]						 = sanitize_text_field( $_POST[ 'data' ][ 'tax_label' ] );
				update_option( 'tc_general_setting', $tc_general_settings );
				break;
			case 'pages-setup':
				$tc_general_settings									 = get_option( 'tc_general_setting', false );
				update_option( 'tc_wizard_step', $step );
				$tc->create_pages();
				$tc_general_settings[ 'tc_process_payment_use_virtual' ] = 'no';
				$tc_general_settings[ 'tc_ipn_page_use_virtual' ]		 = 'no';
				update_option( 'tc_general_setting', $tc_general_settings );
				break;
		}
                exit;
	}

}
?>