<?php
global $tc;
?>
<div class="tc-wiz-wrapper">

	<div class="tc-wiz-screen-wrap tc-license-key <?php echo tc_wizard_wrapper_class(); ?>">

		<h1><?php echo $tc->title; ?></h1>

		<?php tc_wizard_progress(); ?>

		<div class="tc-clear"></div>

		<div class="tc-wiz-screen">

			<div class="tc-wiz-screen-header">
				<h2><?php _e( 'License Key', 'tc' ); ?></h2>
			</div><!-- .tc-wiz-screen-header -->

			<div class="tc-wiz-screen-content">

				<p>
					<?php printf( __( 'You can obtain your license key %1$shere%2$s. You\'ll need it in order to receive automatic updates for the plugin and add-ons and/or if you want to use check-in applications.', 'tc' ), '<a href="https://tickera.com/downloads/" target="_blank">', '</a>' ); ?>
				</p>
				<?php
				$tc_general_settings = get_option( 'tc_general_setting', false );
				?>
				<input type="text" placeholder="<?php echo esc_attr( __( 'License Key', 'tc' ) ); ?>" name="tc-license-key" id="tc-license-key" value="<?php echo esc_attr( isset( $tc_general_settings[ 'license_key' ] ) ? $tc_general_settings[ 'license_key' ] : ''  ); ?>" />

				<?php
				tc_wizard_navigation();
				?>

				<div class="tc-clear"></div>

			</div><!-- .tc-wiz-screen-content -->

		</div><!-- tc-wiz-screen -->

	</div><!-- .tc-wiz-screen-wrap -->

</div><!-- .tc-wiz-wrapper -->