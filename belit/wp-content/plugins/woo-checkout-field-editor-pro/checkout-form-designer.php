<?php
/**
 * Plugin Name: Woo Checkout Field Editor Pro
 * Description: Customize WooCommerce checkout fields(Add, Edit, Delete and re-arrange fields).
 * Author:      ThemeHiGH
 * Version:     1.2.7
 * Author URI:  https://www.themehigh.com
 * Plugin URI:  https://www.themehigh.com
 * Text Domain: thwcfd
 * Domain Path: /languages
 * WC requires at least: 3.0.0
 * WC tested up to: 3.4.0
 */
 
if(!defined( 'ABSPATH' )) exit;

if (!function_exists('is_woocommerce_active')){
	function is_woocommerce_active(){
	    $active_plugins = (array) get_option('active_plugins', array());
	    if(is_multisite()){
		   $active_plugins = array_merge($active_plugins, get_site_option('active_sitewide_plugins', array()));
	    }
	    return in_array('woocommerce/woocommerce.php', $active_plugins) || array_key_exists('woocommerce/woocommerce.php', $active_plugins);
	}
}

if(is_woocommerce_active()) {
	load_plugin_textdomain( 'thwcfd', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	/**
	 * woocommerce_init_checkout_field_editor function.
	 */
	function thwcfd_init_checkout_field_editor_lite() {
		global $supress_field_modification;
		$supress_field_modification = false;
		
		define('TH_WCFD_VERSION', '1.2.7');
		!defined('TH_WCFD_BASE_NAME') && define('TH_WCFD_BASE_NAME', plugin_basename( __FILE__ ));
		!defined('TH_WCFD_URL') && define('TH_WCFD_URL', plugins_url( '/', __FILE__ ));
		!defined('TH_WCFD_ASSETS_URL') && define('TH_WCFD_ASSETS_URL', TH_WCFD_URL . 'assets/');

		if(!class_exists('WC_Checkout_Field_Editor')){
			require_once('classes/class-wc-checkout-field-editor.php');
		}

		$GLOBALS['WC_Checkout_Field_Editor'] = new WC_Checkout_Field_Editor();
	}
	add_action('init', 'thwcfd_init_checkout_field_editor_lite');
	
	function thwcfd_is_locale_field( $field_name ){
		if(!empty($field_name) && in_array($field_name, array(
			'billing_address_1', 'billing_address_2', 'billing_state', 'billing_postcode', 'billing_city',
			'shipping_address_1', 'shipping_address_2', 'shipping_state', 'shipping_postcode', 'shipping_city',
		))){
			return true;
		}
		return false;
	}
	 
	function thwcfd_woocommerce_version_check( $version = '3.0' ) {
	  	if(function_exists( 'is_woocommerce_active' ) && is_woocommerce_active() ) {
			global $woocommerce;
			if( version_compare( $woocommerce->version, $version, ">=" ) ) {
		  		return true;
			}
	  	}
	  	return false;
	}
	
	function thwcfd_enqueue_scripts(){	
		global $wp_scripts;

		if(is_checkout()){
			$in_footer = apply_filters( 'thwcfd_enqueue_script_in_footer', true );

			wp_register_script('thwcfd-field-editor-script', TH_WCFD_ASSETS_URL.'js/thwcfd-checkout-field-editor-frontend.js', 
			array('jquery', 'select2'), TH_WCFD_VERSION, $in_footer);
			
			wp_enqueue_script('thwcfd-field-editor-script');	
		}
	}
	add_action('wp_enqueue_scripts', 'thwcfd_enqueue_scripts');
	
	/**
	 * Hide Additional Fields title if no fields available.
	 *
	 * @param mixed $old
	 */
	function thwcfd_enable_order_notes_field() {
		global $supress_field_modification;

		if($supress_field_modification){
			return $fields;
		}

		$additional_fields = get_option('wc_fields_additional');
		if(is_array($additional_fields)){
			$enabled = 0;
			foreach($additional_fields as $field){
				if($field['enabled']){
					$enabled++;
				}
			}
			return $enabled > 0 ? true : false;
		}
		return true;
	}
	add_filter('woocommerce_enable_order_notes_field', 'thwcfd_enable_order_notes_field', 1000);
		
	function thwcfd_woo_default_address_fields( $fields ) {
		$sname = apply_filters('thwcfd_address_field_override_with', 'billing');
		
		if($sname === 'billing' || $sname === 'shipping'){
			$address_fields = get_option('wc_fields_'.$sname);
			
			if(is_array($address_fields) && !empty($address_fields) && !empty($fields)){
				$override_required = apply_filters( 'thwcfd_address_field_override_required', true );
				
				foreach($fields as $name => $field) {
					$fname = $sname.'_'.$name;
					
					if(thwcfd_is_locale_field($fname) && $override_required){
						$custom_field = isset($address_fields[$fname]) ? $address_fields[$fname] : false;
						
						if($custom_field && !( isset($custom_field['enabled']) && $custom_field['enabled'] == false )){
							$fields[$name]['required'] = isset($custom_field['required']) && $custom_field['required'] ? true : false;
						}
					}
				}
			}
		}
		
		return $fields;
	}	
	add_filter('woocommerce_default_address_fields' , 'thwcfd_woo_default_address_fields' );
	
	function thwcfd_prepare_country_locale($fields) {
		if(is_array($fields)){
			foreach($fields as $key => $props){
				$override_ph = apply_filters('thwcfd_address_field_override_placeholder', true);
				$override_label = apply_filters('thwcfd_address_field_override_label', true);
				$override_required = apply_filters('thwcfd_address_field_override_required', false);
				$override_priority = apply_filters('thwcfd_address_field_override_priority', true);
				
				if($override_ph && isset($props['placeholder'])){
					unset($fields[$key]['placeholder']);
				}
				if($override_label && isset($props['label'])){
					unset($fields[$key]['label']);
				}
				if($override_required && isset($props['required'])){
					unset($fields[$key]['required']);
				}
				
				if($override_priority && isset($props['priority'])){
					unset($fields[$key]['priority']);
					//unset($fields[$key]['order']);
				}
			}
		}
		return $fields;
	} 
	add_filter('woocommerce_get_country_locale_default', 'thwcfd_prepare_country_locale');
	add_filter('woocommerce_get_country_locale_base', 'thwcfd_prepare_country_locale');
	
	function thwcfd_woo_get_country_locale($locale) {
		if(is_array($locale)){
			foreach($locale as $country => $fields){
				$locale[$country] = thwcfd_prepare_country_locale($fields);
			}
		}
		return $locale;
	}
	add_filter('woocommerce_get_country_locale', 'thwcfd_woo_get_country_locale');
	
	/**
	 * wc_checkout_fields_modify_billing_fields function.
	 *
	 * @param mixed $fields
	 */
	function thwcfd_billing_fields_lite($fields, $country){
		global $supress_field_modification;

		if($supress_field_modification){
			return $fields;
		}
		if(is_wc_endpoint_url('edit-address')){
			return $fields;
		}else{
			return thwcfd_prepare_address_fields(get_option('wc_fields_billing'), $fields, 'billing', $country);
		}
	}
	add_filter('woocommerce_billing_fields', 'thwcfd_billing_fields_lite', 1000, 2);

	/**
	 * wc_checkout_fields_modify_shipping_fields function.
	 *
	 * @param mixed $old
	 */
	function thwcfd_shipping_fields_lite($fields, $country){
		global $supress_field_modification;

		if ($supress_field_modification){
			return $fields;
		}
		if(is_wc_endpoint_url('edit-address')){
			return $fields;
		}else{
			return thwcfd_prepare_address_fields(get_option('wc_fields_shipping'), $fields, 'shipping', $country);
		}
	}
	add_filter('woocommerce_shipping_fields', 'thwcfd_shipping_fields_lite', 1000, 2);

	/**
	 * wc_checkout_fields_modify_shipping_fields function.
	 *
	 * @param mixed $old
	 */
	function thwcfd_checkout_fields_lite( $fields ) {
		global $supress_field_modification;

		if($supress_field_modification){
			return $fields;
		}

		if($additional_fields = get_option('wc_fields_additional')){
			if( isset($fields['order']) && is_array($fields['order']) ){
				$fields['order'] = $additional_fields + $fields['order'];
			}

			// check if order_comments is enabled/disabled
			if(isset($additional_fields) && !$additional_fields['order_comments']['enabled']){
				unset($fields['order']['order_comments']);
			}
		}
				
		if(isset($fields['order']) && is_array($fields['order'])){
			$fields['order'] = thwcfd_prepare_checkout_fields_lite($fields['order'], false);
		}
		
		return $fields;
	}
	add_filter('woocommerce_checkout_fields', 'thwcfd_checkout_fields_lite', 1000);
	
	/**
	 *
	 */
	function thwcfd_prepare_address_fields($fieldset, $original_fieldset = false, $sname = 'billing', $country){
		if(is_array($fieldset) && !empty($fieldset)) {
			$locale = WC()->countries->get_country_locale();
			if(isset($locale[ $country ]) && is_array($locale[ $country ])) {
				foreach($locale[ $country ] as $key => $value){
					if(is_array($value) && isset($fieldset[$sname.'_'.$key])){
						if(isset($value['required'])){
							$fieldset[$sname.'_'.$key]['required'] = $value['required'];
						}
					}
				}
			}
			$fieldset = thwcfd_prepare_checkout_fields_lite($fieldset, $original_fieldset);
			return $fieldset;
		}else {
			return $original_fieldset;
		}
	}

	/**
	 * checkout_fields_modify_fields function.
	 *
	 * @param mixed $data
	 * @param mixed $old
	 */
	 function thwcfd_prepare_checkout_fields_lite($fields, $original_fields) {
		if(is_array($fields) && !empty($fields)) {
			foreach($fields as $name => $field) {
				if(isset($field['enabled']) && $field['enabled'] == false ) {
					unset($fields[$name]);
				}else{
					$new_field = false;
					$allow_override = apply_filters('thwcfd_allow_default_field_override_'.$name, false);
					
					if($original_fields && isset($original_fields[$name]) && !$allow_override){
						$new_field = $original_fields[$name];
						
						$new_field['label'] = isset($field['label']) ? $field['label'] : '';
						$new_field['placeholder'] = isset($field['placeholder']) ? $field['placeholder'] : '';
						
						$new_field['class'] = isset($field['class']) && is_array($field['class']) ? $field['class'] : array();
						$new_field['label_class'] = isset($field['label_class']) && is_array($field['label_class']) ? $field['label_class'] : array();
						$new_field['validate'] = isset($field['validate']) && is_array($field['validate']) ? $field['validate'] : array();
						
						/*if(!thwcfd_is_locale_field($name)){
							$new_field['required'] = isset($field['required']) ? $field['required'] : 0;
						}*/
						$new_field['required'] = isset($field['required']) ? $field['required'] : 0;
						$new_field['clear'] = isset($field['clear']) ? $field['clear'] : 0;
					}else{
						$new_field = $field;
					}
					
					if(isset($new_field['type']) && $new_field['type'] === 'select'){
						if(apply_filters('thwcfd_enable_select2_for_select_fields', true)){
							$new_field['input_class'][] = 'thwcfd-enhanced-select';
						}
					}
										
					$new_field['order'] = isset($field['order']) && is_numeric($field['order']) ? $field['order'] : 0;
					if(isset($new_field['order']) && is_numeric($new_field['order'])){
						$priority = ($new_field['order']+1)*10;
						$new_field['priority'] = $priority;
						//$new_field['priority'] = $new_field['order'];
					}
					
					if(isset($new_field['label'])){
						$new_field['label'] = __($new_field['label'], 'woocommerce');
					}
					if(isset($new_field['placeholder'])){
						$new_field['placeholder'] = __($new_field['placeholder'], 'woocommerce');
					}
					
					$fields[$name] = $new_field;
				}
			}								
			return $fields;
		}else {
			return $original_fields;
		}
	}
	
	/*****************************************
	 ----- Display Field Values - START ------
	 *****************************************/
	
	/**
	 * Display custom fields in emails
	 *
	 * @param array $keys
	 * @return array
	 */
	function thwcfd_display_custom_fields_in_emails_lite($ofields, $sent_to_admin, $order){
		$custom_fields = array();
		$fields = array_merge(WC_Checkout_Field_Editor::get_fields('billing'), WC_Checkout_Field_Editor::get_fields('shipping'), 
		WC_Checkout_Field_Editor::get_fields('additional'));

		// Loop through all custom fields to see if it should be added
		foreach( $fields as $key => $options ) {
			if(isset($options['show_in_email']) && $options['show_in_email']){
				$value = '';
				if(thwcfd_woo_version_check()){
					$value = get_post_meta( $order->get_id(), $key, true );
				}else{
					$value = get_post_meta( $order->id, $key, true );
				}
				
				if(!empty($value)){
					$label = isset($options['label']) && $options['label'] ? $options['label'] : $key;
					$label = esc_attr($label);
					
					$custom_field = array();
					$custom_field['label'] = $label;
					$custom_field['value'] = $value;
					
					$custom_fields[$key] = $custom_field;
				}
			}
		}

		return array_merge($ofields, $custom_fields);
	}	
	add_filter('woocommerce_email_order_meta_fields', 'thwcfd_display_custom_fields_in_emails_lite', 10, 3);
	
	/**
	 * Display custom checkout fields on view order pages
	 *
	 * @param  object $order
	 */
	function thwcfd_order_details_after_customer_details_lite($order){
		if(thwcfd_woocommerce_version_check()){
			$order_id = $order->get_id();	
		}else{
			$order_id = $order->id;
		}
		
		$fields = array();		
		if(!wc_ship_to_billing_address_only() && $order->needs_shipping_address()){
			$fields = array_merge(WC_Checkout_Field_Editor::get_fields('billing'), WC_Checkout_Field_Editor::get_fields('shipping'), 
			WC_Checkout_Field_Editor::get_fields('additional'));
		}else{
			$fields = array_merge(WC_Checkout_Field_Editor::get_fields('billing'), WC_Checkout_Field_Editor::get_fields('additional'));
		}
		
		if(is_array($fields) && !empty($fields)){
			$fields_html = '';
			// Loop through all custom fields to see if it should be added
			foreach($fields as $name => $options){
				$enabled = (isset($options['enabled']) && $options['enabled'] == false) ? false : true;
				$is_custom_field = (isset($options['custom']) && $options['custom'] == true) ? true : false;
			
				if(isset($options['show_in_order']) && $options['show_in_order'] && $enabled && $is_custom_field){
					$value = get_post_meta($order_id, $name, true);
					
					if(!empty($value)){
						$label = isset($options['label']) && !empty($options['label']) ? __( $options['label'], 'woocommerce' ) : $name;
						
						if(is_account_page()){
							if(apply_filters( 'thwcfd_view_order_customer_details_table_view', true )){
								$fields_html .= '<tr><th>'. esc_attr($label) .':</th><td>'. wptexturize($value) .'</td></tr>';
							}else{
								$fields_html .= '<br/><dt>'. esc_attr($label) .':</dt><dd>'. wptexturize($value) .'</dd>';
							}
						}else{
							if(apply_filters( 'thwcfd_thankyou_customer_details_table_view', true )){
								$fields_html .= '<tr><th>'. esc_attr($label) .':</th><td>'. wptexturize($value) .'</td></tr>';
							}else{
								$fields_html .= '<br/><dt>'. esc_attr($label) .':</dt><dd>'. wptexturize($value) .'</dd>';
							}
						}
					}
				}
			}
			
			if($fields_html){
				do_action( 'thwcfd_order_details_before_custom_fields_table', $order ); 
				?>
				<table class="woocommerce-table woocommerce-table--custom-fields shop_table custom-fields">
					<?php
						echo $fields_html;
					?>
				</table>
				<?php
				do_action( 'thwcfd_order_details_after_custom_fields_table', $order ); 
			}
		}
	}
	add_action('woocommerce_order_details_after_order_table', 'thwcfd_order_details_after_customer_details_lite', 20, 1);
	
	/*****************************************
	 ----- Display Field Values - END --------
	 *****************************************/

	function thwcfd_woo_version_check( $version = '3.0' ) {
	  	if(function_exists( 'is_woocommerce_active' ) && is_woocommerce_active() ) {
			global $woocommerce;
			if( version_compare( $woocommerce->version, $version, ">=" ) ) {
		  		return true;
			}
	  	}
	  	return false;
	}
	 
}
