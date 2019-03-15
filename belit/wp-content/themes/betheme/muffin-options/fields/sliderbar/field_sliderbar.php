<?php
class MFN_Options_sliderbar extends MFN_Options{

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
	function render(){

		// theme options
		$name = 'name="'. $this->prefix .'['. $this->field['id'] .']"';

 		// parameters
		if( isset( $this->field['param'] ) ){
			$param = $this->field['param'];
		} else {
			$param = false;
		}

		$min 	= isset( $param['min'] ) ? $param['min'] : 1;
		$max 	= isset( $param['max'] ) ? $param['max'] : 100;


		// echo -----------------------------------------------------
		echo '<div class="mfn-slider-field clearfix">';

			echo '<div id="'. $this->field['id'] .'_sliderbar" class="sliderbar" rel="'. $this->field['id'] .'" data-min="'. $min .'" data-max="'. $max .'"></div>';

			echo '<input type="number" class="sliderbar_input" min="'. $min .'" max="'. $max .'" id="'. $this->field['id'] .'" '. $name .' value="'. esc_attr( $this->value ) .'"/>';

			echo '<div class="range">'. $min .' - '. $max .'</div>';

			if( isset( $this->field['desc'] ) ){
				echo '<span class="description">'. $this->field['desc'] .'</span>';
			}

		echo '</div>';

	}

	/**
	 * Enqueue
	 */
	function enqueue(){
		wp_enqueue_style( 'mfn-opts-jquery-ui-css' );
		// wp_enqueue_script( 'jquery-slider', MFN_OPTIONS_URI .'fields/sliderbar/jquery.ui.slider.js', array('jquery', 'jquery-ui-core', 'jquery-ui-slider' ), THEME_VERSION), true );
		wp_enqueue_script( 'mfn-opts-field-sliderbar-js', MFN_OPTIONS_URI.'fields/sliderbar/field_sliderbar.js', array('jquery', 'jquery-ui-core', 'jquery-ui-slider' ), THEME_VERSION, true );
	}
}
