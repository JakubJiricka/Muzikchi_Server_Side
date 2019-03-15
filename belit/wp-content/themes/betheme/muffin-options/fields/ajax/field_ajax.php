<?php
class MFN_Options_ajax extends MFN_Options{

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

		$action = isset( $this->field['action'] ) ? $this->field['action'] : '';
		$param 	= isset( $this->field['param'] ) ? $this->field['param'] : '';

		echo '<a href="javascript:void(0);" class="btn-blue mfn-opts-ajax" data-ajax="'. admin_url( 'admin-ajax.php' ) .'" data-action="'. $action .'" data-param="'. $param .'">'. __( 'Randomize', 'mfn-opts' ) .'</a>';

		if( isset( $this->field['desc'] ) ){
			echo '<span class="description">'. $this->field['desc'] .'</span>';
		}

	}

	/**
	 * Enqueue
	 */
	function enqueue(){
		wp_enqueue_script( 'mfn-opts-field-ajax-js', MFN_OPTIONS_URI .'fields/ajax/field_ajax.js', array( 'jquery' ), THEME_VERSION, true );
	}

}
