<?php

class tc_google_map_element extends TC_Ticket_Template_Elements {

	var $element_name		 = 'tc_google_map_element';
	var $element_title		 = 'Google Map';
	var $font_awesome_icon	 = '<i class="fa fa-map-marker"></i>';

	function on_creation() {
		$this->element_title = apply_filters( 'tc_google_map_element_title', __( 'Google Map', 'tc' ) );
	}

	function admin_content() {
		echo parent::get_cell_alignment();
		echo parent::get_element_margins();
		$this->get_google_settings();
	}

	function get_google_settings() {
		?>
		<label><?php _e( 'Address or Coordinates', 'tc' ); ?></label>
		<input type="text" name="<?php echo esc_attr( $this->element_name ); ?>_google_map_address_post_meta" value="<?php echo esc_attr( isset( $this->template_metas[ $this->element_name . '_google_map_address' ] ) ? $this->template_metas[ $this->element_name . '_google_map_address' ] : ''  ); ?>" />
		<span class="description"><?php _e( 'For instance: Grosvenor Square, Mayfair, London or 51.5122468,-0.1517072', 'tc' ) ?></span>

		<label><?php _e( 'Map Size', 'tc' ); ?></label>
		<?php _e( 'Width (px)', 'tc' ); ?> <input class="ticket_element_padding" type="text" name="<?php echo esc_attr( $this->element_name ); ?>_google_map_width_post_meta" value="<?php echo esc_attr( isset( $this->template_metas[ $this->element_name . '_google_map_width' ] ) ? $this->template_metas[ $this->element_name . '_google_map_width' ] : 600  ); ?>" />
		<?php _e( 'Height (px)', 'tc' ); ?> <input class="ticket_element_padding" type="text" name="<?php echo esc_attr( $this->element_name ); ?>_google_map_height_post_meta" value="<?php echo esc_attr( isset( $this->template_metas[ $this->element_name . '_google_map_height' ] ) ? $this->template_metas[ $this->element_name . '_google_map_height' ] : 300  ); ?>" />

		<label><?php _e( 'Zoom Level', 'tc' ); ?></label>
		<?php
		$selected_zoom = isset( $this->template_metas[ $this->element_name . '_google_map_zoom' ] ) ? $this->template_metas[ $this->element_name . '_google_map_zoom' ] : '13';
		?>
		<select name="<?php echo esc_attr( $this->element_name ); ?>_google_map_zoom_post_meta">
			<?php for ( $i = apply_filters( 'tc_google_map_element_minimum_zoom_level', 10 ); $i <= 22; $i++ ) { ?>
				<option value="<?php echo esc_attr( $i ); ?>" <?php selected( $selected_zoom, $i, true ); ?>><?php echo $i; ?></option>
			<?php } ?>
		</select>


		<label><?php _e( 'Map Type', 'tc' ); ?></label>
		<?php
		$selected_map_type = isset( $this->template_metas[ $this->element_name . '_google_map_type' ] ) ? $this->template_metas[ $this->element_name . '_google_map_type' ] : 'roadmap';
		?>
		<select name="<?php echo esc_attr( $this->element_name ); ?>_google_map_type_post_meta">
			<option value="roadmap" <?php selected( $selected_map_type, 'roadmap', true ); ?>><?php _e( 'Roadmap', 'tc' ); ?></option>
			<option value="terrain" <?php selected( $selected_map_type, 'terrain', true ); ?>><?php _e( 'Terrain', 'tc' ); ?></option>
			<option value="satellite" <?php selected( $selected_map_type, 'satellite', true ); ?>><?php _e( 'Satellite', 'tc' ); ?></option>
			<option value="hybrid" <?php selected( $selected_map_type, 'hybrid', true ); ?>><?php _e( 'Hybrid', 'tc' ); ?></option>
		</select>
		<?php
	}

	function ticket_content( $ticket_instance_id = false, $ticket_type_id = false ) {
		global $tc;

		$address	 = isset( $this->template_metas[ $this->element_name . '_google_map_address' ] ) ? $this->template_metas[ $this->element_name . '_google_map_address' ] : '';
		$width		 = isset( $this->template_metas[ $this->element_name . '_google_map_width' ] ) ? $this->template_metas[ $this->element_name . '_google_map_width' ] : '600';
		$height		 = isset( $this->template_metas[ $this->element_name . '_google_map_height' ] ) ? $this->template_metas[ $this->element_name . '_google_map_height' ] : '300';
		$zoom		 = isset( $this->template_metas[ $this->element_name . '_google_map_zoom' ] ) ? $this->template_metas[ $this->element_name . '_google_map_zoom' ] : '13';
		$map_type	 = isset( $this->template_metas[ $this->element_name . '_google_map_type' ] ) ? $this->template_metas[ $this->element_name . '_google_map_type' ] : 'roadmap';

		$google_map_url = 'http://maps.googleapis.com/maps/api/staticmap?center=' . urlencode( $address ) . '&zoom=' . $zoom . '&scale=2&size=' . $width . 'x' . $height . '&maptype=' . $map_type . '&format=jpg&visual_refresh=false&markers=size:mid%7Ccolor:' . apply_filters( 'tc_google_map_element_marker_color', '0xff0000' ) . '%7Clabel:1%7C' . urlencode( $address );

		return apply_filters( 'tc_google_map_image_element', '<img width="' . $width . '" src="' . $google_map_url . '">' );
	}

}

tc_register_template_element( 'tc_google_map_element', __( 'Google Map', 'tc' ) );
