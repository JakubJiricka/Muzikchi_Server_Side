<?php

/*
  Addon Name: Tickera System Info
  Description: Get essential system information
 */


if ( !defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

if ( !is_admin() ) {
	return;
}

if ( !current_user_can( 'manage_options' ) ) {
	return;
}

if ( !class_exists( 'TC_System_Info' ) ) {

	class TC_System_Info {

		var $version		 = '1.1';
		var $title		 = 'System Info';
		var $name		 = 'tc';
		var $dir_name	 = 'system-info';
		var $plugin_dir	 = '';
		var $plugin_url	 = '';

		function __construct() {
			$this->title = __( 'System', 'tc' );
			add_filter( 'tc_settings_new_menus', array( &$this, 'tc_settings_new_menus_additional' ) );
			add_action( 'tc_settings_menu_tickera_system_info', array( &$this, 'tc_settings_menu_tickera_system_info_show_page' ) );
		}

		function tc_settings_new_menus_additional( $settings_tabs ) {
			$settings_tabs[ 'tickera_system_info' ] = __( 'System', 'tc' );
			return $settings_tabs;
		}

		function tc_settings_menu_tickera_system_info_show_page() {
			require_once( $this->plugin_dir . 'includes/admin-pages/settings-tickera_system_info.php' );
		}

	}

}

$TC_System_Info = new TC_System_Info();
?>