<?php
global $tc_form_elements;

$forms				 = new TC_Forms();
$form_elements		 = new TC_Form_Elements();
$form_elements_set	 = array();
$page				 = $_GET[ 'page' ];

if ( isset( $_POST[ 'tc_custom_forms_add_new_form' ] ) ) {
	if ( check_admin_referer( 'save_form' ) ) {
		if ( current_user_can( 'manage_options' ) ) {
			$forms->add_new_form();
			$message = __( 'Form data has been successfully saved.', 'tc' );
		} else {
			$message = __( 'You do not have required permissions for this action.', 'tc' );
		}
	}
}

if ( isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] == 'edit' ) {// && check_admin_referer('save_form')
	$post_id		 = (int) $_GET[ 'ID' ];
	$form			 = new TC_Form( $post_id );
	$form_elements	 = new TC_Form_Elements( $post_id );
	//$form_elements_set	 = $form_elements->get_all_set_elements();
}

if ( isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] == 'delete' ) {
	if ( !isset( $_POST[ '_wpnonce' ] ) ) {
		check_admin_referer( 'delete_' . $_GET[ 'ID' ] );
		if ( current_user_can( 'manage_options' ) ) {
			$form	 = new TC_Form( (int) $_GET[ 'ID' ] );
			$form->delete_form();
			$message = __( 'Form has been successfully deleted.', 'tc' );
		} else {
			$message = __( 'You do not have required permissions for this action.', 'tc' );
		}
	}
}

if ( isset( $_GET[ 'page_num' ] ) ) {
	$page_num = (int) $_GET[ 'page_num' ];
} else {
	$page_num = 1;
}

if ( isset( $_GET[ 's' ] ) ) {
	$formssearch = $_GET[ 's' ];
} else {
	$formssearch = '';
}

$wp_forms_search = new TC_Forms_Search( $formssearch, $page_num );
$fields			 = $forms->get_form_col_fields();
$columns		 = $forms->get_columns();


$attendee_forms			 = $forms->get_forms( 'owner', -1 );
$attendee_forms_count	 = count( $attendee_forms );

$tc_general_settings = get_option( 'tc_general_setting', false );
if ( !isset( $tc_general_settings[ 'show_owner_fields' ] ) || (isset( $tc_general_settings[ 'show_owner_fields' ] ) && $tc_general_settings[ 'show_owner_fields' ] == 'yes') ) {
	//all good, no warning needed
} else {
	if ( $attendee_forms_count > 0 ) {
		?>
		<div id="message_error" class="error"><p><?php printf( __( 'You have %d attendee forms. However, "Show Ticket Owner Fields" option is set to "No" which means that these forms won\'t be visible on the front-end. You can change that %shere%s', 'tc' ), $attendee_forms_count, '<a href="' . admin_url( 'edit.php?post_type=tc_events&page=tc_settings&tab=general' ) . '">', '</a>' ); ?></p></div>
		<?php
	}
}
?>
<div class="wrap tc_wrap tc_forms_wrap">
    <h2><?php _e( 'Custom Forms', 'tc' ); ?><?php if ( isset( $_GET[ 'action' ] ) && ($_GET[ 'action' ] == 'edit' || $_GET[ 'action' ] == 'add_new') ) { ?><a href="<?php echo admin_url( 'edit.php?post_type=tc_events&page=tc_custom_fields' ); ?>" class="add-new-h2"><?php _e( 'Back', 'tc' ); ?></a><?php } else { ?><a href="<?php echo admin_url( 'edit.php?post_type=tc_events&page=tc_custom_fields&action=add_new' ); ?>" class="add-new-h2"><?php _e( 'Add New', 'tc' ); ?></a><?php } ?></h2>

	<?php
	if ( isset( $message ) ) {
		?>
		<div id="message" class="updated fade"><p><?php echo $message; ?></p></div>
		<?php
	}
	?>

	<?php if ( !isset( $_GET[ 'action' ] ) || (isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] == 'delete') || (isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] == 'add_new' && isset( $_POST[ 'tc_custom_forms_add_new_form' ] )) ) { ?>
		<div class="tablenav">
			<div class="alignright actions new-actions">
				<form method="get" action="?page=<?php echo esc_attr( $page ); ?>" class="search-form">
					<p class="search-box">
						<input type='hidden' name='page' value='<?php echo esc_attr( $page ); ?>' />
						<label class="screen-reader-text"><?php _e( 'Search Forms', 'tc' ); ?>:</label>
						<input type="text" value="<?php echo esc_attr( $formssearch ); ?>" name="s">
						<input type="submit" class="button" value="<?php _e( 'Search Forms', 'tc' ); ?>">
					</p>
				</form>
			</div><!--/alignright-->

		</div><!--/tablenav-->

		<table cellspacing="0" class="widefat shadow-table">
			<thead>
				<tr>
					<?php
					$n = 1;
					foreach ( $columns as $key => $col ) {
						?>
						<th style="" class="manage-column column-<?php echo $key; ?>" width="<?php echo (isset( $col_sizes[ $n ] ) ? $col_sizes[ $n ] . '%' : ''); ?>" id="<?php echo $key; ?>" scope="col"><?php echo $col; ?></th>
						<?php
						$n++;
					}
					?>
				</tr>
			</thead>

			<tbody>
				<?php
				$style = '';

				foreach ( $wp_forms_search->get_results() as $form ) {

					$form_obj	 = new TC_Form( $form->ID );
					$form_object = apply_filters( 'tc_form_object_details', $form_obj->details );

					$style	 = ( ' class="alternate"' == $style ) ? '' : ' class="alternate"';
					?>
					<tr id='user-<?php echo $form_object->ID; ?>' <?php echo $style; ?>>
						<?php
						$n		 = 1;
						foreach ( $columns as $key => $col ) {
							if ( $key == 'edit' ) {
								?>
								<td>                    
									<a class="templates_edit_link" href="<?php echo admin_url( 'edit.php?post_type=tc_events&page=tc_custom_fields&&action=' . $key . '&ID=' . $form_object->ID, 'save_form' ); ?>"><?php _e( 'Edit', 'tc' ); ?></a>
								</td>
							<?php } elseif ( $key == 'delete' ) {
								?>
								<td>
									<a class="templates_edit_link tc_delete_link" href="<?php echo wp_nonce_url( 'edit.php?post_type=tc_events&page=tc_custom_fields&action=' . $key . '&ID=' . $form_object->ID, 'delete_' . $form_object->ID ); ?>"><?php _e( 'Delete', 'tc' ); ?></a>
								</td>
								<?php
							} else {
								?>
								<td>
									<?php echo apply_filters( 'tc_form_field_value', $form_object->{$key} ); ?>
								</td>
								<?php
							}
						}
						?>
					</tr>
					<?php
				}
				?>

				<?php
				if ( count( $wp_forms_search->get_results() ) == 0 ) {
					?>
					<tr>
						<td colspan="6"><div class="zero-records"><?php _e( 'No forms found.', 'tc' ) ?></div></td>
					</tr>
					<?php
				}
				?>
			</tbody>
		</table><!--/widefat shadow-table-->

		<div class="tablenav">
			<div class="tablenav-pages"><?php $wp_forms_search->page_links(); ?></div>
		</div><!--/tablenav-->

		<?php
	} else {
		global $wpdb;
		?>

		<form action="" method="post" enctype = "multipart/form-data">
			<input type="hidden" name="form_id" value="<?php echo esc_attr( isset( $_GET[ 'ID' ] ) ? (int) $_GET[ 'ID' ] : ''  ); ?>" />
			<?php wp_nonce_field( 'save_form' ); ?>
			<?php
			if ( isset( $post_id ) ) {
				?>
				<input type="hidden" name="post_id" value="<?php echo $post_id; ?>" />
				<?php
			}
			?>

			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-2">
					<div id="post-body-content">

						<div id="titlediv">
							<div id="titlewrap">
								<label class="" id="title-prompt-text" for="title"></label>
								<input type="text" name="form_title" size="30" value="<?php echo esc_attr( isset( $form->details->post_title ) ? $form->details->post_title : ''  ); ?>" id="title" placeholder="<?php _e( 'Form Title', 'cp' ); ?>" autocomplete="off">
							</div>
						</div>

						<div id="wp-content-wrap" class="wp-core-ui wp-editor-wrap tmce-active has-dfw form-layout">
							<h2><?php
								_e( 'Form Type', 'cp' );
								$forms				 = new TC_Forms();
								$buyer_form			 = $forms->get_forms( 'buyer' );
								$buyer_form_disabled = 'enabled';
								$form_type_val		 = 'owner';

								if ( isset( $post_id ) ) {
									$form_type_val = isset( $form->details->form_type ) ? $form->details->form_type : 'buyer';
									if ( $form_type_val == 'owner' && count( $buyer_form ) >= 1 ) {
										$buyer_form_disabled = 'disabled';
									}
								} else {
									if ( count( $buyer_form ) >= 1 ) {
										$buyer_form_disabled = 'disabled';
										$form_type_val		 = 'owner';
									}
								}
								?></h2>
                                                    <label>
							<input type="radio" name="form_type_post_meta" value="buyer" <?php echo $buyer_form_disabled; ?><?php checked( $form_type_val, 'buyer', true ); ?> /><?php
							_e( 'Buyer Form', 'tc' );
							if ( function_exists( 'tc_tooltip' ) ) {
								tc_tooltip( __( 'Buyer form will be shown for each order in the cart. Only one buyer form could be created and used.', 'tc' ), true );
							}
							?>
                                                    </label>
                                                    <label>
							<input type="radio" name="form_type_post_meta" value="owner" <?php checked( $form_type_val, 'owner', true ); ?> /><?php
							_e( 'Attendee Form', 'tc' );
							if ( function_exists( 'tc_tooltip' ) ) {
								tc_tooltip( __( 'Attendee form will be shown for each ticket in the cart for selected ticket types / ticket products. You can created multiple different attendee forms.', 'tc' ), true );
							}
							?>
                                                    </label>
							<h2><?php _e( 'Form', 'cp' ); ?></h2>
							<div class="rows tc-custom-forms-droparea">
								<?php for ( $i = 1; $i <= apply_filters( 'tc_form_row_number', 20 ); $i++ ) { ?>
									<ul id="row_<?php echo $i; ?>" class="sortables droptrue"><input type="hidden" class="rows_classes" name="rows_<?php echo $i; ?>_post_meta" value="" />
										<?php
										if ( isset( $post_id ) ) {

											$results = $wpdb->get_results(
											$wpdb->prepare(
											"SELECT *, pm2.meta_value as ord FROM $wpdb->posts p, $wpdb->postmeta pm, $wpdb->postmeta pm2
											WHERE p.ID = pm.post_id 
											AND p.ID = pm2.post_id
											AND	p.post_parent = %d
											AND (pm.meta_key = 'row' AND pm.meta_value = %d)
											AND (pm2.meta_key = 'order')
											ORDER BY ord ASC"
											, $post_id, $i ), OBJECT
											);

											if ( !empty( $results ) ) {
												foreach ( $results as $result ) {
													$post_meta			 = get_post_meta( $result->ID );
													$element_class_name	 = $post_meta[ 'field_type' ][ 0 ];
													if ( class_exists( $element_class_name ) ) {
														$element = new $element_class_name( $result->ID );
														?>
														<li class="ui-state-default cols" data-class="<?php echo $element_class_name; ?>">
															<div class="element_title"><?php echo $element->element_title; ?><a class="tc-custom-field-delete" href="#"><i class="fa fa-times"></i></a></div>
															<div class="element_content"><?php $element->admin_content(); ?></div>
                                                                                                                        <div style="clear:both;"></div>
														</li>
														<?php
													}
												}
											}
										}
										?>
									</ul>
								<?php } ?>

							</div>
							<input type="hidden" name="rows_number_post_meta" value="<?php echo apply_filters( 'tc_ticket_template_row_number', 20 ); ?>" />

						</div><!--wp-content-wrap-->
					</div><!--post-body-content-->

					<div id="sticky-anchor"></div>

					<div id="postbox-container-1" class="postbox-container tc-custom-forms-dragables-wrap">
						<div id="side-sortables" class="meta-box-sortables ui-sortable" style="">
							<div id="submitdiv" class="postbox ">
								<h3 class="hndle"><span><?php _e( 'Form Elements', 'cp' ); ?></span></h3>
								<div class="inside">
									<div class="submitbox" id="submitpost">

										<div id="minor-publishing">
											<div id="minor-publishing-actions">
												<div class="misc-pub-section">
													<ul class="draggable droptrue sortables" id="form_elements">
														<?php
														foreach ( $tc_form_elements as $element ) {
															$element_class = new $element[ 0 ];
															if ( !in_array( $element[ 0 ], $tc_form_elements ) ) {
																?>
																<li class="ui-state-default <?php echo $element[ 0 ]; ?>" data-class="<?php echo $element[ 0 ]; ?>">
																	<div class="element_title"><?php echo $element[ 1 ]; ?><a class="tc-custom-field-delete" href="#"><i class="fa fa-times"></i></a></div>

																	<div class="element_content">
																		<?php echo $element_class->admin_content(); ?>
																	</div>
																</li>
																<?php
															}
														}
														?>
													</ul>
												</div>
											</div>

											<div class="submitbox" id="submitpost">
												<div id="major-publishing-actions">
													<div id="publishing-action">
														<?php submit_button( __( 'Save', 'cp' ), 'primary', 'tc_custom_forms_add_new_form', false ); ?>
													</div>
													<div class="clear"></div>
												</div>
											</div>

											<div class="clear"></div>
										</div>
									</div>

								</div>
							</div>

						</div>
					</div>



				</div><!--post-body-->

			</div><!--post stuff-->
		</form>


	</div>
<?php } ?>