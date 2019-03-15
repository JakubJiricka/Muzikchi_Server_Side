<?php
global $tc_general_settings, $wp_rewrite;

if ( isset( $_POST[ 'save_tc_settings' ] ) ) {

	if ( check_admin_referer( 'save_settings' ) ) {
		if ( current_user_can( 'manage_options' ) || current_user_can( 'save_settings_cap' ) ) {
			update_option( 'tc_general_setting', tc_sanitize_array( $_POST[ 'tc_general_setting' ] ) );

			do_action( 'tc_save_tc_general_settings' );
			tc_save_page_ids();

			$wp_rewrite->flush_rules();
			$message = __( 'Settings data has been successfully saved.', 'tc' );
		} else {
			$message = __( 'You do not have required permissions for this action.', 'tc' );
		}
	}
}

$tc_general_settings = get_option( 'tc_general_setting', false );
?>
<div class="wrap tc_wrap">
	<?php
	if ( isset( $message ) ) {
		?>
		<div id="message" class="updated fade"><p><?php echo $message; ?></p></div>
		<?php
	}
	?>

    <div id="poststuff" class="metabox-holder tc-settings">
		<?php
		$general_setting_url = add_query_arg( array(
			'post_type'	 => 'tc_events',
			'page'		 => $_GET[ 'page' ],
			'tab'		 => isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : '',
		), admin_url( 'edit.php' ) );
		?>
        <form id="tc-general-settings" method="post" action="<?php echo $general_setting_url; ?>">
			<?php wp_nonce_field( 'save_settings' ); ?>

			<?php
			$general_settings	 = new TC_Settings_General();
			$sections			 = $general_settings->get_settings_general_sections();
			?>

			<?php foreach ( $sections as $section ) {
				?>
				<div id="<?php echo esc_attr( $section[ 'name' ] ); ?>" class="postbox">
					<h3 class='hndle'><span><?php echo esc_attr( $section[ 'title' ] ); ?></span></h3>
					<div class="inside">
						<span class="description"><?php echo $section[ 'description' ]; ?></span>
						<table class="form-table">
							<?php
							$fields = $general_settings->get_settings_general_fields();

							foreach ( $fields as $field ) {
								if ( isset( $field[ 'section' ] ) && $field[ 'section' ] == $section[ 'name' ] ) {
									?>
									<tr valign="top" id="<?php echo esc_attr( $field[ 'field_name' ] . '_holder' ); ?>" <?php TC_Fields::conditionals( $field ); ?>>
										<th scope="row"><label for="<?php echo esc_attr( $field[ 'field_name' ] ); ?>"><?php echo $field[ 'field_title' ]; ?><?php (isset( $field[ 'tooltip' ] ) ? tc_tooltip( $field[ 'tooltip' ] ) : ''); ?></label></th>
										<td>
											<?php do_action( 'tc_before_settings_general_field_type_check', $field ); ?>
											<?php
											TC_Fields::render_field( $field, 'tc_general_setting' );
											?>
											<?php do_action( 'tc_after_settings_general_field_type_check', $field ); ?>
										</td>
									</tr>
									<?php
								}
							}
							?>
						</table>
					</div>
				</div>
			<?php } ?>
			<?php submit_button( __( 'Save Settings' ), 'primary', 'save_tc_settings' ); ?>
        </form>
    </div>
</div>