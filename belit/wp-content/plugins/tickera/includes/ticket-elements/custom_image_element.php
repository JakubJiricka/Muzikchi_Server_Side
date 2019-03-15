<?php

class tc_custom_image_element extends TC_Ticket_Template_Elements {

	var $element_name		 = 'tc_custom_image_element';
	var $element_title		 = 'Custom Image / Logo';
	var $font_awesome_icon	 = '<i class="fa fa-file-image-o"></i>';

	function on_creation() {
		$this->element_title = apply_filters( 'tc_custom_image_element_title', __( 'Custom Image / Logo', 'tc' ) );
	}

	function admin_content() {
		echo parent::get_cell_alignment();
		echo parent::get_element_margins();
		$this->get_custom_image_file_name();
	}

	function get_custom_image_file_name() {
		?>
		<label><?php _e( 'Custom Image / Logo URL', 'tc' ); ?></label>
		<div class="file_url_holder">
			<label>
				<input class="file_url" type="text" size="36" name="<?php echo esc_attr( $this->element_name ); ?>_custom_image_url_post_meta" value="<?php echo esc_attr( isset( $this->template_metas[ $this->element_name . '_custom_image_url' ] ) ? $this->template_metas[ $this->element_name . '_custom_image_url' ] : ''  ); ?>" />
				<input class="file_url_button button-secondary" type="button" value="<?php _e( 'Browse', 'tc' ); ?>" />
				<span class="description"><?php //echo $field[ 'field_description' ];       ?></span>
			</label>
		</div>
		<?php
	}

	function ticket_content( $ticket_instance_id = false, $ticket_type_id = false ) {
		global $tc;
		$image_url = isset( $this->template_metas[ $this->element_name . '_custom_image_url' ] ) ? $this->template_metas[ $this->element_name . '_custom_image_url' ] : '';
		return apply_filters( 'tc_custom_image_element', '<img src="' . esc_attr( $image_url ) . '" />' );
	}

}

tc_register_template_element( 'tc_custom_image_element', __( 'Custom Image / Logo', 'tc' ) );
