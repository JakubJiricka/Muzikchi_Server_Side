<?php
class MFN_Options_upload_multi {

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
		if( ! $this->value ){
			$remove = 'style="display:none;"';
		} else {
			$remove = '';
		}

		// echo -----------------------------------------------------
		echo '<div class="mfnf-upload multi">';

			echo '<input type="text" class="upload-input" '. $name .' value="'. $this->value .'" autocomplete=off />';

			echo ' <a href="javascript:void(0);" class="upload-add btn-blue" data-button="'. __( 'Add Images', 'mfn-opts' ) .'"  ><span></span>'. __('Browse', 'mfn-opts') .'</a>';
			echo ' <a href="javascript:void(0);" class="upload-remove all" '. $remove .'>'.__('Remove All Uploads', 'mfn-opts').'</a>';

			echo '<section class="gallery-container clearfix">';
				echo $this->loop_over_the_images();
			echo '</section>';

			if( isset( $this->field['desc'] ) && ! empty( $this->field['desc'] ) ){
				echo '<span class="description">'. $this->field['desc'] .'</span>';
			}

		echo '</div>';

		$this->enqueue();
	}

	private function loop_over_the_images() {
		$unsplited_string  = $this->value;

		if ( $unsplited_string === '' ) { return; }

		$array_of_img_ids = explode( ",", $unsplited_string );

		foreach ( $array_of_img_ids as $img_id ) {

			$img_src = wp_get_attachment_image_src( $img_id, 'thumbnail' );
			$img_src = $img_src[0];

			echo '<div class="image-container">';
				echo '<img class="screenshot image" data-pic-id="'. $img_id .'" src="'. $img_src .'" />';
				echo '<a href="#" class="upload-remove single dashicons dashicons-no"></a>';
			echo '</div>';
		}
	}

  /**
   * Enqueue
   */
  function enqueue() {

  	wp_enqueue_media();
		wp_enqueue_script( 'mfn-opts-field-upload-multi-js', MFN_OPTIONS_URI .'fields/upload_multi/field_upload_multi.js', array( 'jquery' ), THEME_VERSION, true );

	}
}
