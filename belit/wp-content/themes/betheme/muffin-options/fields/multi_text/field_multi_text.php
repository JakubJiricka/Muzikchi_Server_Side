<?php
class MFN_Options_multi_text extends MFN_Options{

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
	function render(){

		// class ----------------------------------------------------
		if( isset( $this->field['class']) ){
			$class = $this->field['class'];
		} else {
			$class = false;
		}

		// name -----------------------------------------------------
		$name = $this->prefix .'['. $this->field['id'] .'][]';

		// echo -----------------------------------------------------
		echo '<div class="mfn-multi-text-field">';

			echo '<input type="text" class="multi-text-add small-text" placeholder="'. __( 'Type sidebar title here', 'mfn-opts' ) .'">';
			echo '<a href="javascript:void(0);" class="multi-text-btn btn-blue" rel-id="'. $this->field['id'] .'-ul" rel-name="'. $name .'">'. __( 'Add sidebar', 'mfn-opts' ) .'</a>';

			if( isset( $this->field['desc'] ) ){
				echo '<span class="description multi-text-desc">'. $this->field['desc'] .'</span>';
			}

			echo '<ul class="multi-text-ul" id="'.$this->field['id'].'-ul">';

				if( isset( $this->value ) && is_array( $this->value ) ){
					foreach( $this->value as $k => $value ){

						if( $value != '' ){
							echo '<li>';
								echo '<input type="hidden" id="'. $this->field['id'] .'-'. $k .'" name="'. $name .'" value="'. esc_attr( $value ) .'" class="'. $class .'" />';
								echo '<span>'. esc_attr( $value ) .'</span>';
								echo '<a href="" class="multi-text-remove"><em>delete</em></a>';
							echo '</li>';
						}

					}
				}

				echo '<li class="multi-text-default">';
					echo '<input type="hidden" name="" value="" class="'. $class .'" />';
					echo '<span></span>';
					echo '<a href="" class="multi-text-remove"><em>delete</em></a>';
				echo '</li>';

			echo '</ul>';

		echo '</div>';
	}

	/**
	 * Enqueue Function.
	*/
	function enqueue(){
		wp_enqueue_script( 'mfn-opts-field-multi-text-js', MFN_OPTIONS_URI .'fields/multi_text/field_multi_text.js', array('jquery'), THEME_VERSION, true );
	}

}
