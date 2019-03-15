<?php
global $tc;
update_option( 'tc_wizard_step', 'finish' );
?>
<div class="tc-wiz-wrapper">

	<div class="tc-wiz-screen-wrap tc-finish-setup <?php echo tc_wizard_wrapper_class(); ?>">

		<h1><?php echo $tc->title; ?></h1>

		<?php tc_wizard_progress(); ?>

		<div class="tc-clear"></div>

		<div class="tc-wiz-screen">

			<div class="tc-wiz-screen-header">
				<h2><?php _e( 'ALMOST READY!', 'tc' ); ?></h2>
			</div><!-- .tc-wiz-screen-header -->

			<div class="tc-wiz-screen-content">

				<p>
					<?php printf( __( 'The initial setup steps have been completed successfully. If you want, you can tweak the other settings %shere%s later.', 'tc' ), '<a href="' . admin_url( 'edit.php?post_type=tc_events&page=tc_settings' ) . '" target="_blank">', '</a>' ); ?>
					<?php
					if ( !tc_iw_is_wl() ) {
						printf( __( 'If you\'re stuck with anything at some point, don\'t hesitate to %scontact us%s.', 'tc' ), '<a href="https://tickera.com/contact/" target="_blank">', '</a>' );
					}
					?>	
				</p>
				<p>
					<?php _e( 'Happy Ticketing!', 'tc' ); ?>
				</p>

				<div class="tc-extra-steps">
					<h3><?php _e( 'What to Do next?', 'tc' ); ?></h3>

					<a href="<?php echo esc_attr( admin_url( 'edit.php?post_type=tc_events' ) ); ?>" target="_blank" class="tc-extra-button tc-button"><?php _e( 'CREATE YOUR EVENT', 'tc' ); ?></a>
					<?php if ( tc_wizard_mode() == 'sa' ) { ?>
						<span class="tc-and-between">&</span> 
						<a href="<?php echo esc_attr( admin_url( 'edit.php?post_type=tc_events&page=tc_settings&tab=gateways' ) ); ?>" target="_blank" class="tc-extra-button tc-button"><?php _e( 'PAYMENT GATEWAY SETUP', 'tc' ); ?></a> 
					<?php } ?>
				</div><!-- .tc-extra-steps -->

				<div class="tc-wiz-screen-footer">					
                                        <button class="tc-skip-button tc-button" onclick="window.location.href='<?php echo admin_url(); ?>'"><?php _e( 'Finish', 'tc' ); ?></button>
				</div><!-- tc-wiz-screen-footer -->

				<div class="tc-clear"></div>

			</div><!-- .tc-wiz-screen-content -->

		</div><!-- tc-wiz-screen -->

	</div><!-- .tc-wiz-screen-wrap -->

</div><!-- .tc-wiz-wrapper -->