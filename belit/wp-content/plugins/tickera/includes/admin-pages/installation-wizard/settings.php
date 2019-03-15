<?php
global $tc;
?>
<div class="tc-wiz-wrapper">


	<div class="tc-wiz-screen-wrap tc-installation-settings-page <?php echo tc_wizard_wrapper_class(); ?>">

		<h1><?php echo $tc->title; ?></h1>

		<?php tc_wizard_progress(); ?>

		<div class="tc-clear"></div>

		<div class="tc-wiz-screen">

			<div class="tc-wiz-screen-header">
				<h2>Settings</h2>
			</div><!-- .tc-wiz-screen-header -->

			<div class="tc-wiz-screen-content">

				<p>
					<?php printf( __( 'Set some crucial settings for your event ticketing store bellow. All the setting could be changed later from within your %s Settings panel.', 'tc' ), $tc->title ); ?>
				</p>

				<?php
				$tc_general_settings = get_option( 'tc_general_setting', false );
				$settings			 = get_option( 'tc_settings' );
				$currencies			 = $settings[ 'gateways' ][ 'currencies' ];

				ksort( $currencies );

				if ( isset( $tc_general_settings[ 'currencies' ] ) ) {
					$checked = $tc_general_settings[ 'currencies' ];
				} else {
					$checked = 'USD';
				}
				?>

				<div class="tc-setting-wrap">                                
					<div class="tc-setting-label"><label for="tc_select_currency"><?php _e( 'Currency', 'tc' ); ?></label></div>
					<div class="tc-setting-field">
						<select id="tc_select_currency" name="tc_select_currency" class="tc_select_currency">
							<?php
							foreach ( $currencies as $currency_symbol => $title ) {
								?>
								<option value="<?php echo esc_attr( $currency_symbol ); ?>" <?php selected( $checked, $currency_symbol, true ); ?>><?php echo $title; ?></option>
								<?php
							}
							?>
						</select>
					</div><!-- .tc-setting-field -->                                
				</div><!-- .tc-setting-wrap -->


				<div class="tc-setting-wrap">                                
					<div class="tc-setting-label"><label for="tc_select_currency_symbol"><?php _e( 'Currency Symbol', 'tc' ); ?></label></div>
					<div class="tc-setting-field">
						<input type="text" id="tc_select_currency_symbol" name="currency_symbol" class="tc_currency_symbol" value="<?php echo esc_attr( isset( $tc_general_settings[ 'currency_symbol' ] ) ? $tc_general_settings[ 'currency_symbol' ] : '$'  ) ?>" />
					</div><!-- .tc-setting-field -->                                
				</div><!-- .tc-setting-wrap -->


				<div class="tc-setting-wrap">                                
					<div class="tc-setting-label"><label for="tc_select_currency_position"><?php _e( 'Currency Position', 'tc' ); ?></label></div>
					<div class="tc-setting-field">
						<?php
						if ( isset( $tc_general_settings[ 'currency_position' ] ) ) {
							$checked = $tc_general_settings[ 'currency_position' ];
						} else {
							$checked = 'pre_nospace';
						}

						$symbol = (isset( $tc_general_settings[ 'currency_symbol' ] ) && $tc_general_settings[ 'currency_symbol' ] != '' ? $tc_general_settings[ 'currency_symbol' ] : (isset( $tc_general_settings[ 'currencies' ] ) ? $tc_general_settings[ 'currencies' ] : '$'));
						?>
						<select name="currency_position" class="tc_currency_position">
							<option value="pre_space" <?php selected( $checked, 'pre_space', true ); ?>><?php echo $symbol . ' 10'; ?></option>
							<option value="pre_nospace" <?php selected( $checked, 'pre_nospace', true ); ?>><?php echo $symbol . '10'; ?></option>
							<option value="post_nospace" <?php selected( $checked, 'post_nospace', true ); ?>><?php echo '10' . $symbol; ?></option>
							<option value="post_space" <?php selected( $checked, 'post_space', true ); ?>><?php echo '10 ' . $symbol; ?></option>
							<?php do_action( 'tc_currencies_position' ); ?>
						</select>
						<?php
						?>
					</div><!-- .tc-setting-field -->                                
				</div><!-- .tc-setting-wrap -->


				<div class="tc-setting-wrap">                                
					<div class="tc-setting-label"><label for="tc_select_currency_position"><?php _e( 'Price Format', 'tc' ); ?></label></div>
					<div class="tc-setting-field">
						<?php
						if ( isset( $tc_general_settings[ 'price_format' ] ) ) {
							$checked = $tc_general_settings[ 'price_format' ];
						} else {
							$checked = 'us';
						}
						?>
						<select name="price_format" class="tc_price_format">
							<option value="us" <?php selected( $checked, 'us', true ); ?>><?php _e( '1,234.56', 'tc' ); ?></option>
							<option value="eu" <?php selected( $checked, 'eu', true ); ?>><?php _e( '1.234,56', 'tc' ); ?></option>
							<option value="french_comma" <?php selected( $checked, 'french_comma', true ); ?>><?php _e( '1 234,56', 'tc' ); ?></option>
							<option value="french_dot" <?php selected( $checked, 'french_dot', true ); ?>><?php _e( '1 234.56', 'tc' ); ?></option>
							<?php do_action( 'tc_price_formats' ); ?>
						</select>

					</div><!--.tc-setting-field -->
				</div><!--.tc-setting-wrap -->

				<?php
				if ( isset( $tc_general_settings[ 'show_tax_rate' ] ) ) {
					$checked_show_tax_rate = $tc_general_settings[ 'show_tax_rate' ];
				} else {
					$checked_show_tax_rate = 'no';
				}
				?>

				<div class="tc-setting-wrap">
					<div class="tc-setting-label"><label for="use_taxes"><?php _e( 'Use Taxes', 'tc' ); ?></label></div>
					<div class="tc-setting-field">
						<label>
							<input type="radio" class="tc_show_tax_rate" name="show_tax_rate" value="yes" checked="checked" <?php checked( $checked_show_tax_rate, 'yes', true ); ?>><?php _e( 'Yes', 'tc' ); ?>
						</label>

						<label>
							<input type="radio" class="tc_show_tax_rate" name="show_tax_rate" value="no" <?php checked( $checked_show_tax_rate, 'no', true ); ?>><?php _e( 'No', 'tc' ); ?>
						</label>
					</div><!--.tc-setting-field -->
				</div><!--.tc-setting-wrap -->


				<?php
				if ( $checked_show_tax_rate == 'no' ) {
					$show_taxes_fields = ' style="display:none"';
				} else {
					$show_taxes_fields = '';
				}
				?>

				<div class="tc-setting-wrap tc-taxes-fields-wrap"<?php echo $show_taxes_fields; ?>>
					<div class="tc-setting-label"><?php _e( 'Tax Rate (%)', 'tc' ); ?></div>
					<div class="tc-setting-field">
						<input type="text" class="tc_tax_rate" id="tax_rate" name="show_tax_rate" value="<?php echo esc_attr( isset( $tc_general_settings[ 'tax_rate' ] ) ? $tc_general_settings[ 'tax_rate' ] : 0  ); ?>">
					</div><!--.tc-setting-field -->
				</div><!--.tc-setting-wrap -->

				<?php
				if ( isset( $tc_general_settings[ 'tax_inclusive' ] ) ) {
					$checked = $tc_general_settings[ 'tax_inclusive' ];
				} else {
					$checked = 'no';
				}
				?>

				<div class="tc-setting-wrap tc-taxes-fields-wrap"<?php echo $show_taxes_fields; ?>>
					<div class="tc-setting-label"><?php _e( 'Prices inclusive of tax?', 'tc' ); ?></div>
					<div class="tc-setting-field">
						<label>
							<input type="radio" class="tc_tax_inclusive" name="tax_inclusive" value="yes" <?php checked( $checked, 'yes', true ); ?>><?php _e( 'Yes', 'tc' ); ?>
						</label>
						<label>
							<input type="radio" class="tc_tax_inclusive" name="tax_inclusive" value="no" <?php checked( $checked, 'no', true ); ?>><?php _e( 'No', 'tc' ); ?>
						</label>
					</div><!--.tc-setting-field -->
				</div><!--.tc-setting-wrap -->


				<div class="tc-setting-wrap tc-taxes-fields-wrap"<?php echo $show_taxes_fields; ?>>
					<div class="tc-setting-label"><?php _e( 'Tax Label', 'tc' ); ?></div>
					<div class="tc-setting-field">
						<input type="text" class="tc_tax_label" id="tax_label" name="tax_label" value="<?php echo esc_attr( isset( $tc_general_settings[ 'tax_label' ] ) ? $tc_general_settings[ 'tax_label' ] : 'Tax'  ); ?>" />
					</div><!--.tc-setting-field -->
				</div><!--.tc-setting-wrap -->

				<?php
				tc_wizard_navigation();
				?>

				<div class="tc-clear"></div>

			</div><!-- .tc-wiz-screen-content -->


		</div><!-- tc-wiz-screen -->

	</div><!-- .tc-wiz-screen-wrap -->


</div><!-- .tc-wiz-wrapper -->