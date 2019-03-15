<?php
global $tc;
update_option( 'tc_wizard_step', 'checkin-apps' );
?>
<div class="tc-wiz-wrapper">

	<div class="tc-wiz-screen-wrap tc-finish-setup <?php echo tc_wizard_wrapper_class(); ?>">

		<h1><?php echo $tc->title; ?></h1>

		<?php tc_wizard_progress(); ?>

		<div class="tc-clear"></div>

		<div class="tc-wiz-screen">

			<div class="tc-wiz-screen-header">
				<h2><?php _e( 'Check-in Applications', 'tc' ); ?></h2>
			</div><!-- .tc-wiz-screen-header -->

			<div class="tc-wiz-screen-content">

				<p>
					<?php _e( 'If you want to check in attendees, you will need one of these apps:', 'tc' ); ?>
				</p>
				
				<ul>
					<li><?php printf( __( '%siOS check-in app%s', 'tc' ), '<a href="https://itunes.apple.com/us/app/ticket-checkin/id958838933" target="_blank">', '</a>' ); ?></li>
					<li><?php printf( __( '%sAndroid check-in app%s', 'tc' ), '<a href="https://play.google.com/store/apps/details?id=com.tickera.tickeraapp" target="_blank">', '</a>' ); ?></li>
					<li><?php printf( __( '%sCross-platform Chrome Desktop check-in app%s', 'tc' ), '<a href="https://chrome.google.com/webstore/detail/check-in/iblommpedaboemnefjildfmpjpmjapnp?hl=en-US" target="_blank">', '</a>' ); ?> <?php
						/*if ( tc_iw_is_pr() ) {
							_e( '(available with premium version of the plugin only)', 'tc' );
						}*/
						?></li>
					<li><?php printf( __( '%sBarcode Reader add-on%s', 'tc' ), '<a href="https://tickera.com/addons/barcode-reader/?utm_source=plugin&utm_medium=upsell&utm_campaign=wizard" target="_blank">', '</a>' ); ?></li>
				</ul>
				

				<p>
					<?php printf( __( 'Check-in apps could be translated easily with this %scheck-in app translation add-on%s.', 'tc' ), '<a href="https://tickera.com/addons/check-in-app-translation/?utm_source=plugin&utm_medium=upsell&utm_campaign=wizard" target="_blank">', '</a>' ); ?>
                                        <?php _e( 'Alternatively, you can check-in attendees manually from within the admin panel via attendee details screen.', 'tc' ); ?>
				</p>


				<?php
				tc_wizard_navigation();
				?>

				<div class="tc-clear"></div>

			</div><!-- .tc-wiz-screen-content -->

		</div><!-- tc-wiz-screen -->

	</div><!-- .tc-wiz-screen-wrap -->

</div><!-- .tc-wiz-wrapper -->