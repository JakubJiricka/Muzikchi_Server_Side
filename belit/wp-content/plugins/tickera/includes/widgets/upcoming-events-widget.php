<?php

class TC_Upcoming_Events_Widget extends WP_Widget {

	function __construct() {
		$widget_ops = array( 'classname' => 'widget widget_recent_entries tc_upcoming_events_widget', 'description' => __( 'Shows upcoming events', 'tc' ) );
		parent::__construct( 'TC_Upcoming_Events_Widget', __( 'Upcoming Events', 'tc' ), $widget_ops );
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );

		$title			 = $instance[ 'title' ];
		$events_count	 = $instance[ 'events_count' ];
		?>
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'tc' ); ?>: <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo (!isset( $title ) ? __( 'Upcoming Events' ) : esc_attr( $title )); ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id( 'events_count' ); ?>"><?php _e( 'Events Count', 'tc' ); ?>: <input class="widefat" id="<?php echo $this->get_field_id( 'events_count' ); ?>" name="<?php echo $this->get_field_name( 'events_count' ); ?>" type="text" value="<?php echo (!isset( $events_count ) ? 10 : esc_attr( $events_count )); ?>" /></label></p>
		<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance					 = $old_instance;
		$instance[ 'title' ]		 = $new_instance[ 'title' ];
		$instance[ 'events_count' ]	 = $new_instance[ 'events_count' ];
		return $instance;
	}

	function widget( $args, $instance ) {
		global $tc;

			extract( $args, EXTR_SKIP );

			echo $before_widget;

			$title			 = empty( $instance[ 'title' ] ) ? ' ' : apply_filters( 'tc_cart_widget_title', $instance[ 'title' ] );
			$events_count	 = empty( $instance[ 'events_count' ] ) ? 10 : apply_filters( 'tc_events_count_widget_value', $instance[ 'events_count' ] );

			if ( !empty( $title ) ) {
				echo $before_title . $title . $after_title;
			}

			//event_date_time
			$tc_events_args = array(
				'posts_per_page' => (int) $events_count,
				'meta_query'	 => array(
					array(
						'key'		 => 'event_date_time',
						'value'		 => date( 'Y-m-d h:i' ),
						'type'		 => 'DATETIME',
						'compare'	 => '>='
					),
					'orderby' => 'event_date_time',
				),
				'order'			 => 'ASC',
				'orderby'		 => 'meta_value',
				'post_type'		 => 'tc_events',
				'post_status'	 => 'publish'
			);

			$tc_events = get_posts( $tc_events_args );
			?>

			<?php
			// Cart Contents
			if ( !empty( $tc_events ) ) {
				do_action( 'tc_upcoming_events_before_ul', $tc_events );
				?>
				<ul class='tc_upcoming_events_ul'>
					<?php
					foreach ( $tc_events as $tc_event ) {
						$event_content = '<li id="tc_upcoming_event_' . $tc_event->ID . '">
							<a href="' . get_post_permalink( $tc_event->ID ) . '">' . get_the_title( $tc_event->ID ) . '</a>
							<span class="tc_event_data_widget">' . do_shortcode( '[tc_event_date id="' . $tc_event->ID . '"]' ) . '</span>
						</li>';
						echo apply_filters( 'tc_upcoming_events_widget_event_content', $event_content, $tc_event->ID );
					}
					?>
				</ul><!--tc_cart_ul-->
				<?php
				do_action( 'tc_upcoming_events_after_ul', $tc_events );
			} else {
				do_action( 'tc_upcoming_events_before_empty' );
				?>
				<span class='tc_empty_upcoming_events'><?php _e( 'There are no upcoming events at this time.', 'tc' ); ?></span>
				<?php
				do_action( 'tc_upcoming_events_after_empty' );
			}
			?>

			<div class='tc-clearfix'></div>

			<?php
			echo $after_widget;
		
	}

}


if (version_compare(phpversion(), '7.2', '<')) {
    add_action( 'widgets_init', create_function( '', 'return register_widget("TC_Upcoming_Events_Widget");' ) );
} else {    
    add_action( 'widgets_init', function(){
        register_widget( 'TC_Upcoming_Events_Widget' );
    });
}

?>