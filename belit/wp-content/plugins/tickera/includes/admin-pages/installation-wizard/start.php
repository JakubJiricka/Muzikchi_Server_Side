<?php
global $tc;
?>
<div class="tc-wiz-wrapper">


	<div class="tc-wiz-screen-wrap <?php echo tc_wizard_wrapper_class(); ?>">
		<form name="tc-step-wrap-0" method="get" id="tc_wizard_start_form" action="<?php echo admin_url( 'index.php' ); ?>">
			<h1><?php echo $tc->title; ?></h1>

			<input type="hidden" name="page" value="tc-installation-wizard" />
			<input type="hidden" name="step" value="<?php echo tc_wizard_get_start_screen_next_step(); ?>" />
			<div class="tc-wiz-screen">

				<div class="tc-wiz-screen-header">
					<h2><?php _e( 'I will use...', 'tc' ); ?></h2>
				</div><!-- .tc-wiz-screen-header -->

				<div class="tc-wiz-screen-content">

					<div class="tc-wiz-screen-half tc-standalone-tickera">  

						<?php
						$mode_checked = get_option( 'tc_wizard_mode', 'sa' );
						?>

						<input type="radio" id="tc-standalone" <?php checked( $mode_checked, 'sa', true ); ?> name="mode" class="tc_mode" value="sa" />
						<label for="tc-standalone"><span></span>                            
							<h3><?php printf( __( 'Standalone %s', 'tc' ), $tc->title ); ?></h3>                            
							<p>
								<?php printf( __( 'Great choice! %s is packed with a number of features (including payment gateways) which will help you out to sell tickets for your next event.', 'tc' ), $tc->title ); ?>
								<?php
								if ( !tc_iw_is_wl() ) {//Show only if the plugin isn't white-labeled at this point
									printf( __( 'If that\'s not enought, make sure to check out our %sadd-ons%s section as well' ), '<a href="https://tickera.com/tickera-events-add-ons/">', '</a>' );
								}
								?>
							</p>
						</label>

					</div><!-- tc-wiz-screen-half -->

					<div class="tc-wiz-screen-half tc-standalone-tickera">  

						<input type="radio" id="tc-woocommerce" <?php checked( $mode_checked, 'wc', true ); ?> name="mode" class="tc_mode" value="wc" />
						<label for="tc-woocommerce"><span></span>
							<h3><?php printf( __( 'WooCommerce + %s', 'tc' ), $tc->title ); ?></h3>                            
							<p>
								<?php
								if ( !tc_iw_is_wl() ) {
									printf( __( 'With more than 10.000.000 downloads, %1$sWooCommerce%2$s is certainly the most popular e-commerce system for the WordPress platform. %3$sBridge for WooCommerce%2$s add-on is required for this mode. You can install it later.', 'tc' ), '<a href="https://www.woothemes.com/woocommerce/" target="_blank">', '</a>', '<a href="https://tickera.com/addons/bridge-for-woocommerce/?utm_source=plugin&utm_medium=upsell&utm_campaign=wizard" target="_blank">' );
								} else {//if the plugin is white-labeled, don't show Bridge for WooCommerce link
									printf( __( 'With more than 10.000.000 downloads, %1$sWooCommerce%2$s is certainly the most popular e-commerce system for the WordPress platform. Bridge for WooCommerce add-on is required for this mode. You can install it later.', 'tc' ), '<a href="https://www.woothemes.com/woocommerce/" target="_blank">', '</a>', '<a href="https://tickera.com/addons/bridge-for-woocommerce/?utm_source=plugin&utm_medium=upsell&utm_campaign=wizard" target="_blank">' );
								}
								?>
							</p>
						</label>    

					</div><!-- tc-wiz-screen-half -->

					<?php
					tc_wizard_navigation();
					?>

					<div class="tc-clear"></div>

				</div><!-- .tc-wiz-screen-content -->


			</div><!-- tc-wiz-screen -->
		</form>
	</div><!-- .tc-wiz-screen-wrap -->


</div><!-- .tc-wiz-wrapper -->