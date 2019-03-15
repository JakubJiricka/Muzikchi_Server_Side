<?php
class MFN_Options_checkbox_pseudo extends MFN_Options{

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

		$name = 'name="'. $this->field[ 'id' ] .'"';

    // prepare values array

    $this->value = preg_replace( '/\s+/', ' ', $this->value );
    $values = explode( ' ', $this->value );

		if ( is_array ( $this->field[ 'options' ] ) ) {
			// Multi Checkboxes

			echo '<div class="mfnf-checkbox pseudo multi">';

        echo '<input class="value" type="text" '. $name .' value="'. esc_attr( $this->value ) .'"/>';

				echo '<ul>';
					foreach( $this->field[ 'options' ] as $key => $val ){

						if( in_array( $key, $values ) ){
              $check = $key;
            } else {
              $check = false;
            }

						echo '<li>';
							echo '<label>';
								echo '<input type="checkbox" value="'. $key .'" '. checked( $check, $key, false ) .' />';
								echo '<span class="label">'. $val .'</span>';
							echo '</label>';
						echo '</li>';

					}
				echo '</ul>';

				if( isset( $this->field['desc'] ) ){
					echo '<span class="description">'. $this->field['desc'] .'</span>';
				}

			echo '</div>';


		} else {
			// Single Checkbox
			echo 'please use "switch" field for single checkbox';
		}

		$this->enqueue();
	}

	/**
	 * Enqueue Function.
	 */
	function enqueue(){
		wp_enqueue_script( 'mfn-opts-field-checkbox-pseudo-js', MFN_OPTIONS_URI .'fields/checkbox_pseudo/field_checkbox_pseudo.js', array( 'jquery' ), THEME_VERSION, true );
	}

}
