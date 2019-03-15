<?php
if( ! defined( 'ABSPATH' ) ){
	exit; // Exit if accessed directly
}

class Mfn_Changelog extends Mfn_API {

	/**
	 * Mfn_Changelog constructor
	 */
	public function __construct(){
		
		parent::__construct();

		// It runs after the basic admin panel menu structure is in place.
		add_action( 'admin_menu', array( $this, 'init' ), 16 );
		
	}

	/**
	 * Add admin page & enqueue styles
	 */
	public function init(){
		
		$title = __( 'Changelog','mfn-opts' );
		
		$this->page = add_submenu_page(
			'betheme',
			$title,
			$title,
			'edit_theme_options',
			'be-changelog',
			array( $this, 'template' )
		);
		
		// Fires when styles are printed for a specific admin page based on $hook_suffix.
		add_action( 'admin_print_styles-'. $this->page, array( $this, 'enqueue' ) );
	}
	
	/**
	 * Status template
	 */
	public function template(){
		
		if( WHITE_LABEL ){
			include_once LIBS_DIR . '/admin/templates/parts/white-label.php';
		} else {
			include_once LIBS_DIR . '/admin/templates/changelog.php';
		}
		
	}
	
	/**
	 * Enqueue styles and scripts
	 */
	public function enqueue(){
		wp_enqueue_style( 'mfn-dashboard', LIBS_URI. '/admin/assets/dashboard.css', array(), THEME_VERSION );
	}
	
}

$mfn_changelog = new Mfn_Changelog();
