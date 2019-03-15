<?php

/*
  Addon Name: Tickera Delete Info
  Description: Delete tickera plugin and addons data
 */


if ( !defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

if ( !is_admin() ) {
	return;
}

if ( !current_user_can( 'manage_options' ) ) {
	return;
}

if ( !class_exists( 'TC_Delete_Info' ) ) {

	class TC_Delete_Info {

		var $version		 = '1.1';
		var $title		 = 'Delete Info';
		var $name		 = 'tc';
		var $dir_name	 = 'delete-info';
		var $plugin_dir	 = '';
		var $plugin_url	 = '';

		function __construct() {
			$this->title = __( 'Delete Info', 'tc' );
			add_filter( 'tc_settings_new_menus', array( &$this, 'tc_settings_new_menus_additional' ) );
			add_action( 'tc_settings_menu_tickera_delete_info', array( &$this, 'tc_settings_menu_tickera_delete_info_show_page' ) );
		}

		function tc_settings_new_menus_additional( $settings_tabs ) {
			$settings_tabs[ 'tickera_delete_info' ] = __( 'Delete Info', 'tc' );
			return $settings_tabs;
		}

		function tc_settings_menu_tickera_delete_info_show_page() {
			require_once( $this->plugin_dir . 'includes/admin-pages/settings-tickera_delete_info.php' );
		}

	}

}

$TC_Delete_Info = new TC_Delete_Info();
?>