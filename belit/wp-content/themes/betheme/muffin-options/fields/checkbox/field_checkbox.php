<?php
class MFN_Options_checkbox extends MFN_Options{

	/**
	 * Constructor
	 */
	function __construct( $field = array(), $value ='', $prefix = false ){

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
			$class = '';
		}

		// name -----------------------------------------------------
		if( $meta ){

			// page mata & builder existing items
			$name = $this->field['id'];

		} else {

			// theme options
			$name = $this->prefix .'['. $this->field['id'] .']';

		}

		// echo -----------------------------------------------------

		if ( is_array ( $this->field[ 'options' ] ) ) {
			// Multi Checkboxes

			if ( ! isset( $this->value ) ) {
				$this->value = array();
			}

			if ( ! is_array( $this->value ) ){
				$this->value = array();
			}

			echo '<div class="mfnf-checkbox multi '. $class .'">';

				// FIX | Post Meta Save | All values unchecked
				echo '<input type="hidden" name="'. $name. '[post-meta]" value="1" checked="checked" />';

				echo '<ul>';
					foreach( $this->field['options'] as $k => $v ){

						if( ! key_exists( $k, $this->value ) ) $this->value[$k] = '';

						echo '<li>';
							echo '<label>';
								echo '<input type="checkbox" name="'. $name. '['.$k.']" value="'. $k .'" '. checked( $this->value[$k], $k, false ) .' />';
								echo '<span class="label">'. $v .'</span>';
							echo '</label>';
						echo '</li>';

					}
				echo '</ul>';

				if( isset( $this->field['desc'] ) && ! empty( $this->field['desc'] ) ) echo '<span class="description">'. $this->field['desc'] .'</span>';
			echo '</div>';


		} else {
			// Single Checkbox
			echo 'please use "switch" field for single checkbox';
		}
	}

}
