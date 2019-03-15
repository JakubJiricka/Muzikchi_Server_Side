<?php
global $tc;
?>
<div class="tc-wiz-wrapper">


	<div class="tc-wiz-screen-wrap tc-installation-page-setup <?php echo tc_wizard_wrapper_class(); ?>">

		<h1><?php echo $tc->title; ?></h1>

		<?php tc_wizard_progress(); ?>

		<div class="tc-clear"></div>

		<div class="tc-wiz-screen">

			<div class="tc-wiz-screen-header">
				<h2><?php _e( 'Pages Setup', 'tc' ); ?></h2>
			</div><!-- .tc-wiz-screen-header -->

			<div class="tc-wiz-screen-content">

				<p><?php _e( 'Your event ticketing store needs some important pages. If you click "Continue", the following pages will be created automatically:', 'tc' ); ?></p>

				<div class="tc-pages-wrap">                                
					<div class="tc-page-title">
						<span><?php _e( 'Cart Page', 'tc' ); ?></span>
					</div><!-- .tc-page-title -->

					<div class="tc-page-description">
						<p><?php printf( __( 'Your clients will be able to see their cart contents on this page, insert buyer and attendees\' info. You can add this page to the %ssite menu%s later for easy accessibility.', 'tc' ), '<a href="' . admin_url( 'nav-menus.php' ) . '" target="_blank">', '</a>' ); ?></p>
					</div><!-- .tc-setting-field -->                                
				</div><!-- .tc-setting-wrap -->


				<div class="tc-pages-wrap">                                
					<div class="tc-page-title">
						<span><?php _e( 'Payment Page', 'tc' ); ?></span>
					</div><!-- .tc-page-title -->
					<div class="tc-page-description">
						<p><?php _e( 'Your clients will choose payment method on this page. Do NOT add this page directly to the site menu.', 'tc' ); ?></p>
					</div><!-- .tc-setting-field -->                                
				</div><!-- .tc-setting-wrap -->


				<div class="tc-pages-wrap">                                
					<div class="tc-page-title">
						<span><?php _e( 'Payment Confirmation Page', 'tc' ); ?></span>
					</div><!-- .tc-page-title -->
					<div class="tc-page-description">
						<p><?php _e( 'This page will be shown after completed payment. Information about payment status and link to order page will be visible on this page. Do NOT add this page directly to the site menu.', 'tc' ); ?></p>
					</div><!-- .tc-setting-field -->                                
				</div><!-- .tc-setting-wrap -->


				<div class="tc-pages-wrap">                                
					<div class="tc-page-title">
						<span><?php _e( 'Order Details Page', 'tc' ); ?></span>
					</div><!-- .tc-page-title -->
					<div class="tc-page-description">
						<p><?php _e( 'The page where buyers will be able to check order status and / or download their ticket(s). Do NOT add this page directly to the site menu.', 'tc' ); ?></p>
					</div><!-- .tc-setting-field -->                                
				</div><!-- .tc-setting-wrap -->

				<div class="tc-pages-wrap">                                
					<div class="tc-page-title">
						<span><?php _e( 'Process Payment Page', 'tc' ); ?></span>
					</div><!-- .tc-page-title -->
					<div class="tc-page-description">
						<p><?php _e( 'This page is used by the plugin internally to process payments. Do NOT add this page directly to the site menu.', 'tc' ); ?></p>
					</div><!-- .tc-setting-field -->                                
				</div><!-- .tc-setting-wrap -->

				<div class="tc-pages-wrap">                                
					<div class="tc-page-title">
						<span><?php _e( 'IPN Page (Instant Payment Notification)', 'tc' ); ?></span>
					</div><!-- .tc-page-title -->
					<div class="tc-page-description">
						<p><?php _e( 'This page is used by the plugin internally to receive payment statuses from various payment gateways like PayPal, 2Checkout and similar. Do NOT add this page directly to the site menu.', 'tc' ); ?></p>
					</div><!-- .tc-setting-field -->                                
				</div><!-- .tc-setting-wrap -->

				<?php
				tc_wizard_navigation();
				?>

				<div class="tc-clear"></div>

			</div><!-- .tc-wiz-screen-content -->


		</div><!-- tc-wiz-screen -->

	</div><!-- .tc-wiz-screen-wrap -->


</div><!-- .tc-wiz-wrapper -->