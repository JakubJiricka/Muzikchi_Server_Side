<?php

if ( !defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

if ( !class_exists( 'TC_Tickets' ) ) {

	class TC_Tickets {

		var $form_title				 = '';
		var $valid_admin_fields_type	 = array( 'text', 'textarea', 'textarea_editor', 'image', 'function' );

		function __construct() {
			$this->form_title				 = __( 'Tickets', 'tc' );
			$this->valid_admin_fields_type	 = apply_filters( 'tc_valid_admin_fields_type', $this->valid_admin_fields_type );
		}

		function TC_Tickets() {
			$this->__construct();
		}

		public static function get_ticket_fields() {
			$tc_general_settings = get_option( 'tc_general_setting', false );

			$default_fields = array(
				array(
					'field_name'		 => 'ID',
					'field_title'		 => __( 'ID', 'tc' ),
					'field_type'		 => 'ID',
					'field_description'	 => '',
					'table_visibility'	 => false,
					'post_field_type'	 => 'ID'
				),
				array(
					'field_name'		 => 'event_name',
					'field_title'		 => __( 'Event', 'tc' ),
					'placeholder'		 => '',
					'field_type'		 => 'function',
					'function'			 => 'tc_get_events',
					'tooltip'			 => sprintf( __( 'Select an associated event for this ticket type. You can create new events %shere%s.', 'tc' ), '<a href="' . admin_url( 'edit.php?post_type=tc_events' ) . '" target="_blank">', '</a>' ),
					'table_visibility'	 => true,
					'post_field_type'	 => 'post_meta',
					'metabox_context'	 => 'side'
				),
				array(
					'field_name'		 => 'ticket_type_name',
					'field_title'		 => __( 'Ticket Type', 'tc' ),
					'placeholder'		 => '',
					'field_type'		 => 'text',
					'tooltip'			 => __( 'Example: Standard ticket, VIP, Early Bird, Student, Regular Admission, etc.', 'tc' ),
					'table_visibility'	 => false,
					'post_field_type'	 => 'post_title',
					'required'			 => true,
				),
				/* array(
				  'field_name'		 => 'ticket_description',
				  'field_title'		 => __( 'Ticket Description', 'tc' ),
				  'placeholder'		 => '',
				  'field_type'		 => 'textarea_editor',
				  'field_description'	 => __( 'Example: Access to the whole Congress, all business networking lounges excluding the Platinum Lounge and the Official Dinner.', 'tc' ),
				  'table_visibility'	 => false,
				  'post_field_type'	 => 'post_content'
				  ), */
				array(
					'field_name'		 => 'price_per_ticket',
					'field_title'		 => __( 'Price', 'tc' ),
					'placeholder'		 => '',
					'field_type'		 => 'text',
					'tooltip'			 => __( 'Price per ticket. Example: 29.59', 'tc' ),
					'table_visibility'	 => true,
					'post_field_type'	 => 'post_meta',
					'required'			 => true,
					'number'			 => true,
					'metabox_context'	 => 'side'
				),
				array(
					'field_name'		 => 'quantity_available',
					'field_title'		 => __( 'Quantity', 'tc' ),
					'placeholder'		 => __( 'Unlimited', 'tc' ),
					'field_type'		 => 'text',
					'tooltip'			 => __( 'Number of available tickets. ', 'tc' ),
					'table_visibility'	 => true,
					'post_field_type'	 => 'post_meta',
					'number'			 => true,
					'metabox_context'	 => 'side'
				),
				array(
					'field_name'			 => 'quantity_sold',
					'field_title'			 => __( 'Sold', 'tc' ),
					'placeholder'			 => '',
					'field_type'			 => 'function',
					'function'				 => 'tc_get_quantity_sold',
					'field_description'		 => '',
					'table_visibility'		 => true,
					'post_field_type'		 => 'post_meta',
					'table_edit_invisible'	 => true
				),
				array(
					'field_name'		 => 'min_tickets_per_order',
					'field_title'		 => __( 'Min. tickets per order', 'tc' ),
					'placeholder'		 => __( 'No Minimum', 'tc' ),
					'field_type'		 => 'text',
					'tooltip'			 => __( 'Minimum tickets client has to put in the cart in order to complete a purchase.', 'tc' ),
					'table_visibility'	 => false,
					'post_field_type'	 => 'post_meta',
					'number'			 => true
				),
				array(
					'field_name'		 => 'max_tickets_per_order',
					'field_title'		 => __( 'Max. tickets per order', 'tc' ),
					'placeholder'		 => __( 'No Maximum', 'tc' ),
					'field_type'		 => 'text',
					'tooltip'			 => __( 'Maximum tickets client could buy per one order.', 'tc' ),
					'table_visibility'	 => false,
					'post_field_type'	 => 'post_meta',
					'number'			 => true
				),
				array(
					'field_name'		 => 'available_checkins_per_ticket',
					'field_title'		 => __( 'Check-ins per ticket', 'tc' ),
					'placeholder'		 => __( 'Unlimited', 'tc' ),
					'field_type'		 => 'text',
					'tooltip'			 => __( 'It is useful if the event last more than one day. For instance, if duration of your event is 5 day, you should choose 5 or more available check-ins', 'tc' ),
					'table_visibility'	 => true,
					'post_field_type'	 => 'post_meta',
					'number'			 => true,
				),
				array(
					'field_name'		 => 'ticket_template',
					'field_title'		 => __( 'Ticket Template', 'tc' ),
					'field_type'		 => 'function',
					'function'			 => 'tc_get_ticket_templates',
					'tooltip'			 => __( 'Select look of the PDF ticket. You can create additional ticket templates <a href="' . admin_url( 'edit.php?post_type=tc_events&page=tc_ticket_templates' ) . '" target="_blank">here</a>', 'tc' ),
					'table_visibility'	 => false,
					'post_field_type'	 => 'post_meta',
					'metabox_context'	 => 'side'
				),
			);

			$use_global_fees = isset( $tc_general_settings[ 'use_global_fees' ] ) ? $tc_general_settings[ 'use_global_fees' ] : 'no';

			if ( $use_global_fees == 'no' ) {
				$default_fields[] = array(
					'field_name'		 => 'ticket_fee',
					'field_title'		 => __( 'Ticket Fee', 'tc' ),
					'placeholder'		 => __( 'No Fees', 'tc' ),
					'field_type'		 => 'text',
					'tooltip'			 => __( 'Ticket / Service Fee (you can add additional fee per ticket in order to cover payment gateway, service or any other type of cost)', 'tc' ),
					'table_visibility'	 => true,
					'post_field_type'	 => 'post_meta',
					'number'			 => true
				);

				$default_fields[] = array(
					'field_name'		 => 'ticket_fee_type',
					'field_title'		 => __( 'Ticket Fee Type', 'tc' ),
					'field_type'		 => 'function',
					'function'			 => 'tc_get_ticket_fee_type',
					'field_description'	 => '',
					'table_visibility'	 => false,
					'post_field_type'	 => 'post_meta'
				);
			}

			$default_fields[] = array(
				'field_name'		 => '_ticket_availability',
				'field_title'		 => __( 'Available dates for tickets selling', 'tc' ),
				'field_type'		 => 'function',
				'function'			 => 'tc_get_ticket_availability_dates',
				'tooltip'			 => __( 'Choose if you want to limit ticket sales availability for certain date range or leave it as open-ended.', 'tc' ),
				'table_visibility'	 => false,
				'post_field_type'	 => 'post_meta',
			);

			$default_fields[] = array(
				'field_name'		 => '_ticket_checkin_availability',
				'field_title'		 => __( 'Available dates / times for check-in', 'tc' ),
				'field_type'		 => 'function',
				'function'			 => 'tc_get_ticket_checkin_availability_dates',
				'tooltip'			 => __( 'Choose if you want to limit ticket check-ins for certain date range or leave it as open-ended.', 'tc' ),
				'table_visibility'	 => false,
				'post_field_type'	 => 'post_meta',
			);

			if ( current_user_can( apply_filters( 'tc_ticket_type_activation_capability', 'edit_others_ticket_types' ) ) || current_user_can( 'manage_options' ) ) {
				$default_fields[] = array(
					'field_name'			 => 'ticket_active',
					'field_title'			 => __( 'Active', 'tc' ),
					'placeholder'			 => '',
					'field_type'			 => 'text',
					'field_description'		 => '',
					'table_visibility'		 => true,
					'post_field_type'		 => 'read-only',
					'table_edit_invisible'	 => true
				);
			}

			return apply_filters( 'tc_ticket_fields', $default_fields );
		}

		function get_columns() {
			$fields	 = $this->get_ticket_fields();
			$results = search_array( $fields, 'table_visibility', true );

			$columns = array();

			foreach ( $results as $result ) {
				$columns[ $result[ 'field_name' ] ] = $result[ 'field_title' ];
			}

			$columns[ 'edit' ]	 = __( 'Edit', 'tc' );
			$columns[ 'delete' ] = __( 'Delete', 'tc' );

			return $columns;
		}

		function check_field_property( $field_name, $property ) {
			$fields	 = $this->get_ticket_fields();
			$result	 = search_array( $fields, 'field_name', $field_name );
			return $result[ 0 ][ 'post_field_type' ];
		}

		function is_valid_ticket_field_type( $field_type ) {
			if ( in_array( $field_type, $this->valid_admin_fields_type ) ) {
				return true;
			} else {
				return false;
			}
		}

		function restore_all_ticket_types() {
			$args = array(
				'posts_per_page' => -1,
				'post_type'		 => 'tc_tickets',
				'post_status'	 => 'trash'
			);

			$ticket_types = get_posts( $args );

			foreach ( $ticket_types as $ticket_type ) {
				wp_untrash_post( $ticket_type->ID );
			}
		}

		function add_new_ticket() {
			global $user_id, $post;

			if ( isset( $_POST[ 'add_new_ticket' ] ) ) {

				$metas = array();

				foreach ( $_POST as $field_name => $field_value ) {
					if ( preg_match( '/_post_title/', $field_name ) ) {
						$title = sanitize_text_field( $field_value );
					}

					if ( preg_match( '/_post_excerpt/', $field_name ) ) {
						$excerpt = sanitize_text_field( $field_value );
					}

					if ( preg_match( '/_post_content/', $field_name ) ) {
						$content = sanitize_text_field( $field_value );
					}

					if ( preg_match( '/_post_meta/', $field_name ) ) {
						$metas[ sanitize_key( str_replace( '_post_meta', '', $field_name ) ) ] = sanitize_text_field( $field_value );
					}

					do_action( 'tc_after_ticket_post_field_type_check' );
				}

				$metas = apply_filters( 'tickets_metas', $metas );

				$arg = array(
					'post_author'	 => (int) $user_id,
					'post_excerpt'	 => (isset( $excerpt ) ? $excerpt : ''),
					'post_content'	 => (isset( $content ) ? $content : ''),
					'post_status'	 => 'publish',
					'post_title'	 => (isset( $title ) ? $title : ''),
					'post_type'		 => 'tc_tickets',
				);

				if ( isset( $_POST[ 'post_id' ] ) ) {
					$arg[ 'ID' ] = (int) $_POST[ 'post_id' ]; //for edit 
				}

				$post_id = @wp_insert_post( $arg, true );

				//Update post meta
				if ( $post_id !== 0 ) {
					if ( isset( $metas ) ) {
						foreach ( $metas as $key => $value ) {
							update_post_meta( $post_id, $key, $value );
						}
					}
				}

				return $post_id;
			}
		}

	}

}
?>
