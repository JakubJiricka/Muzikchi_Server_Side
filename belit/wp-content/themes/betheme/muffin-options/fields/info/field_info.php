<?php
class MFN_Options_info extends MFN_Options{

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
		if( isset( $this->field['desc'] ) ){
			echo '<p class="mfn-field-info">'. $this->field['desc'] .'</p>';
		}
	}

}
