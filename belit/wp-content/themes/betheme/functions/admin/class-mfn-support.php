<?php
if( ! defined( 'ABSPATH' ) ){
	exit; // Exit if accessed directly
}

class Mfn_Support extends Mfn_API {

	/**
	 * Mfn_Support constructor
	 */
	public function __construct(){
		
		parent::__construct();

		// It runs after the basic admin panel menu structure is in place.
		add_action( 'admin_menu', array( $this, 'init' ), 15 );
		
	}

	/**
	 * Add admin page & enqueue styles
	 */
	public function init(){
		
		$title = __( 'Manual & Support','mfn-opts' );
		
		$this->page = add_submenu_page(
			'betheme',
			$title,
			$title,
			'edit_theme_options',
			'be-support',
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
			include_once LIBS_DIR . '/admin/templates/support.php';
		}
		
	}
	
	/**
	 * Enqueue styles and scripts
	 */
	public function enqueue(){
		wp_enqueue_style( 'mfn-dashboard', LIBS_URI. '/admin/assets/dashboard.css', array(), THEME_VERSION );
		wp_enqueue_style( 'mfn-magnific-popup', LIBS_URI. '/admin/assets/plugins/magnific-popup/magnific-popup.css', array(), THEME_VERSION );
		
		wp_enqueue_script( 'mfn-dashboard.js', LIBS_URI. '/admin/assets/dashboard.js', false, THEME_VERSION, true );
		wp_enqueue_script( 'mfn-magnific-popup', LIBS_URI. '/admin/assets/plugins/magnific-popup/magnific-popup.min.js', false, THEME_VERSION, true );
	}
	
}

$mfn_support = new Mfn_Support();
