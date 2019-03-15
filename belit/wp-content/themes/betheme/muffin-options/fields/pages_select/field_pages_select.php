<?php
class MFN_Options_pages_select extends MFN_Options{

	/**
	 * Constructor
	 */
	function __construct( $field = array(), $value = '', $prefix = false ){

		$this->field = $field;
		$this->value = $value;

		// theme options 'opt_name'
		$this->prefix = $prefix;

	}

	/**
	 * Render
	 */
	function render( $meta = false ){

		// class ----------------------------------------------------
		if( isset( $this->field['class']) ){
			$class = $this->field['class'];
		} else {
			$class = false;
		}

		// name -----------------------------------------------------
		if( $meta ){

			// page mata & builder existing items
			$name = 'name="'. $this->field['id'] .'"';

		} else {

			// theme options
			$name = 'name="'. $this->prefix .'['. $this->field['id'] .']"';

		}

		$pages = get_pages( 'sort_column=post_title&hierarchical=0' );

		echo '<select '. $name .' '. $class .' rows="6">';
			echo '<option value="">'. __( '-- select --', 'mfn-opts' ) .'</option>';
			foreach ( $pages as $page ) {
				echo '<option value="'. $page->ID .'" '. selected( $this->value, $page->ID, false ). '>'. $page->post_title .'</option>';
			}
		echo '</select>';

		if( isset( $this->field['desc'] ) ){
			echo '<span class="description">'. $this->field['desc'] .'</span>';
		}

	}

}
