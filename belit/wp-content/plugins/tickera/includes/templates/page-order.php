<?php
global $wp, $tc;

if (isset($_POST['action']) && $_POST['action'] == 'heartbeat') {
    return;
}

//$tc->remove_order_session_data();
//prevent search engine to index order pages for security reasons
add_action( 'wp_head', 'tc_no_index_no_follow' );

if ( (isset( $wp->query_vars[ 'tc_order' ] ) || isset( $_GET[ 'tc_order' ] )) && isset( $wp->query_vars[ 'tc_order_key' ] ) || isset( $_GET[ 'tc_order_key' ] ) ) {
	$order_id	 = isset($wp->query_vars[ 'tc_order' ]) ? $wp->query_vars[ 'tc_order' ] : $_GET[ 'tc_order' ];
	$order_key	 = isset($wp->query_vars[ 'tc_order_key' ]) ? $wp->query_vars[ 'tc_order_key' ] : $_GET[ 'tc_order_key' ];
	$order		 = tc_get_order_id_by_name( $order_id );

	$tc_general_settings = get_option( 'tc_general_setting', false );

	if ( isset( $tc_general_settings[ 'force_login' ] ) && $tc_general_settings[ 'force_login' ] == 'yes' && !is_user_logged_in() ) {
		?>
		<div class="force_login_message"><?php printf( __( 'Please %s to see this page', 'tc' ), '<a href="' . apply_filters( 'tc_force_login_url', wp_login_url( tc_current_url() ), tc_current_url() ) . '">' . __( 'Log In', 'tc' ) . '</a>' ); ?></div>
		<?php
	} else {
		?>
		<div class="tc-container">
			<?php if ( $order ) { ?>
				<div class="tickera">
					<?php
					tc_get_order_details_front( $order->ID, $order_key );
                                        $tc->remove_order_session_data();
					?>
				</div><!-- tickera -->

				<?php
			} else {
				_e( 'Order cannot be found.', 'tc' );
			}
			?>
		</div>
		<?php
	}
} else {
	_e( 'Order cannot be found.', 'tc' );
}?>