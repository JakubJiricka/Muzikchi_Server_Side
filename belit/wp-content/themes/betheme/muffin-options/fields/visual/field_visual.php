<?php
class MFN_Options_visual extends MFN_Options{

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
			$class = 'image';
		}

		// name -----------------------------------------------------
		if( $meta == 'new' ){

			// builder new
			$name = 'data-name="'. $this->field['id'] .'"';

		} elseif( $meta ){

			// page mata & builder existing items
			$name = 'name="'. $this->field['id'] .'"';

		} else {

			// theme options
			$name = 'name="'. $this->prefix .'['. $this->field['id'] .']"';

		}

		// value is empty -------------------------------------------
		if( $this->value == '' ){
			$remove = 'style="display:none;"';
			$visual = '';
		} else {
			$remove = '';
			$visual = 'style="display:none;"';
		}

		// echo -----------------------------------------------------
		echo '<div class="mfnf-visual">';

		    echo '<div class="wp-core-ui wp-editor-wrap tmce-active">';

				echo '<div class="wp-editor-tools hide-if-no-js">';

					echo '<div class="wp-media-buttons">';
						echo '<button type="button" class="button insert-media add_media" data-editor="mfn-editor"><span class="wp-media-buttons-icon"></span> Add Media</button>';
					echo '</div>';

					echo '<div class="wp-editor-tabs">';
						echo '<button type="button" class="wp-switch-editor switch-tmce" data-wp-editor-id="mfn-editor">Visual</button>';
						echo '<button type="button" class="wp-switch-editor switch-html" data-wp-editor-id="mfn-editor">Text</button>';
					echo '</div>';

				echo '</div>';

				echo '<div class="wp-editor-container">';

					echo '<textarea '. $name .' class="editor wp-editor-area" rows="8">' .esc_attr( $this->value ). '</textarea>';

				echo '</div>';

			echo '</div>';

		echo '</div>';
	}

  /**
   * Enqueue
   */
  function enqueue() {

  	wp_enqueue_media();

  }
}
