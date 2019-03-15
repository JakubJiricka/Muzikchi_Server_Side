<?php

if ( !defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

if ( !class_exists( 'TC_Order' ) ) {

	class TC_Order {

		var $id		 = '';
		var $output	 = 'OBJECT';
		var $ticket	 = array();
		var $details;

		function __construct( $id = '', $output = 'OBJECT' ) {

			//$this			 = new stdClass();
			$this->id		 = $id;
			$this->output	 = $output;
			$this->details	 = get_post( $this->id, $this->output );

			$tickets = new TC_Orders();
			$fields	 = TC_Orders::get_order_fields();

			foreach ( $fields as $field ) {

				if ( !isset( $this->details ) ) {
					$this->details = new stdClass();
				}

				if ( !isset( $this->details->{$field[ 'field_name' ]} ) ) {
					$this->details->{$field[ 'field_name' ]} = get_post_meta( $this->id, $field[ 'field_name' ], true );
				}
			}
		}

		function TC_Order( $id = '', $output = 'OBJECT' ) {
			$this->__construct( $id, $output );
		}

		function get_order() {
			$order = get_post_custom( $this->id, $this->output );
			return $order;
		}

		function delete_order( $force_delete = true, $id = false ) {
			$id = $id ? $id : $this->id;
			if ( $force_delete ) {
				wp_delete_post( $id );
			} else {
				wp_trash_post( $id );
			}

			//Delete associated ticket instances
			$args = array(
				'posts_per_page' => -1,
				'post_type'		 => 'tc_tickets_instances',
				'post_status'	 => 'any',
				'post_parent'	 => $id
			);

			$ticket_instances = get_posts( $args );

			foreach ( $ticket_instances as $ticket_instance ) {
				$ticket_instance_instance = new TC_Ticket_Instance( $ticket_instance->ID );
				$ticket_instance_instance->delete_ticket_instance( $force_delete );
			}
		}

		function untrash_order( $id = false ) {
			$id = $id ? $id : $this->id;

			wp_untrash_post( $id );

			//Restore associated ticket instances
			$args = array(
				'posts_per_page' => -1,
				'post_type'		 => 'tc_tickets_instances',
				'post_status'	 => 'trash',
				'post_parent'	 => $id
			);

			$ticket_instances = get_posts( $args );

			foreach ( $ticket_instances as $ticket_instance ) {
				wp_untrash_post( $ticket_instance->ID );
			}
		}

		public static function add_order_note( $order_id, $note ) {
			global $tc;

			if ( !defined( 'TC_TURN_OFF_NOTES' ) ) {
				if ( !TC_Order::same_order_note_exist( $order_id, $note ) ) {
					$comment_author			 = $tc->title;
					$comment_author_email	 = strtolower( $tc->title ) . '@';
					$comment_author_email .= isset( $_SERVER[ 'HTTP_HOST' ] ) ? str_replace( 'www.', '', $_SERVER[ 'HTTP_HOST' ] ) : 'noreply.com';
					$comment_author_email	 = sanitize_email( $comment_author_email );


					$comment_post_ID	 = $order_id;
					$comment_author_url	 = '';
					$comment_content	 = $note;
					$comment_agent		 = $tc->title;
					$comment_type		 = 'tc_order_note';
					$comment_parent		 = 0;
					$comment_approved	 = 1;
					$commentdata		 = apply_filters( 'tc_new_order_note_data', compact( 'comment_post_ID', 'comment_author', 'comment_author_email', 'comment_author_url', 'comment_content', 'comment_agent', 'comment_type', 'comment_parent', 'comment_approved' ), array( 'order_id' => $order_id ) );

					$comment_id = wp_insert_comment( $commentdata );

					return $comment_id;
				}
			}
		}

		public static function same_order_note_exist( $order_id, $note ) {
			global $wpdb;

			$comments_count = $wpdb->get_var( $wpdb->prepare(
			'SELECT COUNT(*) FROM ' . $wpdb->comments . '
				WHERE comment_content = %s
				AND comment_post_ID = %s', $note, $order_id
			) );

			if ( $comments_count > 0 ) {
				return true;
			} else {
				return false;
			}
		}

		public static function get_order_notes( $order_id ) {

			$args = array(
				'post_id'	 => $order_id,
				'approve'	 => 'approve',
				'type'		 => 'tc_order_note'
			);

			$notes = get_comments( $args );

			$order_notes = array();

			foreach ( $notes as $note ) {
				$order_notes[] = array(
					'id'			 => $note->comment_ID,
					'created_at'	 => tc_format_date( strtotime( $note->comment_date_gmt ) ),
					'note'			 => $note->comment_content,
					'note_author'	 => $note->comment_author
				);
			}

			return array( 'tc_order_notes' => apply_filters( 'tc_order_notes_response', $order_notes, $order_id, $notes ) );
		}

	}

}
?>