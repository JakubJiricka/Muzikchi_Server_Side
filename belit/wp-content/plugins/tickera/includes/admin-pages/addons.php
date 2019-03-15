<?php
global $tc;

if ( false === ( $addons = get_transient( 'tc_addons_data' . $tc->version ) ) ) {
	$addons_json = wp_remote_get( 'https://tickera.com/addons.json?ts=' . time(), array( 'user-agent' => 'Tickera Addons Page', 'sslverify' => false ) );
	$addons		 = json_decode( wp_remote_retrieve_body( $addons_json ) );

	if ( !is_wp_error( $addons_json ) ) {

		$addons = json_decode( wp_remote_retrieve_body( $addons_json ) );

		if ( $addons ) {
			set_transient( 'tc_addons_data' . $tc->version, $addons, HOUR_IN_SECONDS );
		}
	}
}
?>
<div class="wrap tc_wrap">
	<h2><?php _e( 'Add-ons', 'tc' ); ?></h2>
	<div class="updated"><p><?php printf(__( 'NOTE: All add-ons are included for FREE with the <a href="%s" target="_blank">Bundle Package</a>', 'tc' ), 'https://tickera.com/pricing/?utm_source=plugin&utm_medium=upsell&utm_campaign=addons'); ?></p></div>
	<div class="tc_addons_wrap">
		<?php
		if ( count( $addons ) > 0 ) {
			foreach ( $addons as $addon ) {
				echo '<div class="tc_addon"><a target="_blank" href="' . $addon->link . '">';
				if ( !empty( $addon->image ) ) {
					echo '<div class="tc-addons-image"><img src="' . $addon->image . '"/></div>';
				} else {
					echo '<h3>' . $addon->title . '</h3>';
				}
				echo '<div class="tc-addon-content"><p>' . $addon->excerpt . '</p>';
				echo '</div></a></div>';
			}
		} else {
			printf( __( 'Something went wrong and we can\'t get a list of add-ons :( The good news is that you can check them online <a href="%s">here</a>', 'tc' ), 'https://tickera.com/tickera-events-add-ons/' );
		}
		?>
	</div>
</div>