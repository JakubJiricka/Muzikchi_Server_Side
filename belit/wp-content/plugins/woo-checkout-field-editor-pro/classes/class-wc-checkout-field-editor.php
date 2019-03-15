<?php
if(!defined( 'ABSPATH' )) exit;

/**
 * WC_Checkout_Field_Editor class.
 */
class WC_Checkout_Field_Editor {

	/**
	 * __construct function.
	 */
	public function __construct() {
		// Validation rules are controlled by the local fields and can't be changed
		$this->locale_fields = array(
			'billing_address_1', 'billing_address_2', 'billing_state', 'billing_postcode', 'billing_city',
			'shipping_address_1', 'shipping_address_2', 'shipping_state', 'shipping_postcode', 'shipping_city',
			'order_comments'
		);

		add_action('admin_menu', array($this, 'admin_menu'));
		add_filter('woocommerce_screen_ids', array($this, 'add_screen_id'));
		add_filter('plugin_action_links_'.TH_WCFD_BASE_NAME, array($this, 'add_settings_link'));
		add_action('woocommerce_checkout_update_order_meta', array($this, 'save_data'), 10, 2);
	}
	
	/**
	 * menu function.
	 */
	public function admin_menu() {
		$this->screen_id = add_submenu_page('woocommerce', __('WooCommerce Checkout Form Designer', 'thwcfd'), __('Checkout Form', 'thwcfd'), 
		'manage_woocommerce', 'checkout_form_designer', array($this, 'the_designer'));

		add_action('admin_print_scripts-'. $this->screen_id, array($this, 'enqueue_admin_scripts'));
	}
	
	/**
	 * add_screen_id function.
	 */
	public function add_screen_id($ids){
		$ids[] = 'woocommerce_page_checkout_form_designer';
		$ids[] = strtolower(__('WooCommerce', 'thwcfd')) .'_page_checkout_form_designer';

		return $ids;
	}
	
	public function add_settings_link($links) {
		$settings_link = '<a href="'.admin_url('admin.php?page=checkout_form_designer').'">'. __('Settings') .'</a>';
		array_unshift($links, $settings_link);
		return $links;
	}
	
	/**
	 * scripts function.
	 */
	public function enqueue_admin_scripts() {
		wp_enqueue_style ('thwcfd-style', plugins_url('/assets/css/thwcfd-style.css', dirname(__FILE__)));
		wp_enqueue_script('thwcfd-admin-script', plugins_url('/assets/js/thwcfd-admin.js', dirname(__FILE__)), array('jquery', 'jquery-ui-dialog', 'jquery-ui-sortable',
		'woocommerce_admin', 'select2', 'jquery-tiptip'), TH_WCFD_VERSION, true);	
	}
	
	public function output_premium_version_notice(){
		?>
        <div id="message" class="wc-connect updated thwcfd-notice">
            <div class="squeezer">
            	<table>
                	<tr>
                    	<td width="70%">
                        	<p><strong><i>WooCommerce Checkout Field Editor Pro</i></strong> premium version provides more features to design your checkout page.</p>
                            <ul>
                            	<li>12 field types available,<br/>(<i>Text, Hidden, Password, Textarea, Radio, Checkbox, Select, Multi-select, Date Picker, Time Picker, Heading, Label</i>).</li>
                                <li>Conditionally display fields based on cart items and other field(s) values.</li>
                                <li>Add an extra cost to the cart total based on field selection.</li>
                                <li>Option to add more sections in addition to the core sections (billing, shipping and additional) in checkout page.</li>
                            </ul>
                        </td>
                        <td>
                        	<a target="_blank" href="https://www.themehigh.com/product/woocommerce-checkout-field-editor-pro/" class="">
                            	<img src="<?php echo plugins_url( '../assets/css/upgrade-btn.png', __FILE__ ); ?>" />
                            </a>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <?php
	}

	/**
	 * Reset checkout fields.
	 */
	public function reset_checkout_fields() {
		delete_option('wc_fields_billing');
		delete_option('wc_fields_shipping');
		delete_option('wc_fields_additional');
		echo '<div class="updated"><p>'. __('SUCCESS: Checkout fields successfully reset', 'thwcfd') .'</p></div>';
	}
	
	public function is_reserved_field_name( $field_name ){
		if($field_name && in_array($field_name, array(
			'billing_first_name', 'billing_last_name', 'billing_company', 'billing_address_1', 'billing_address_2', 'billing_city', 'billing_state', 
			'billing_country', 'billing_postcode', 'billing_phone', 'billing_email',
			'shipping_first_name', 'shipping_last_name', 'shipping_company', 'shipping_address_1', 'shipping_address_2', 'shipping_city', 'shipping_state', 
			'shipping_country', 'shipping_postcode', 'customer_note', 'order_comments'
		))){
			return true;
		}
		return false;
	}
	
	public function is_default_field_name($field_name){
		if($field_name && in_array($field_name, array(
			'billing_first_name', 'billing_last_name', 'billing_company', 'billing_address_1', 'billing_address_2', 'billing_city', 'billing_state', 
			'billing_country', 'billing_postcode', 'billing_phone', 'billing_email',
			'shipping_first_name', 'shipping_last_name', 'shipping_company', 'shipping_address_1', 'shipping_address_2', 'shipping_city', 'shipping_state', 
			'shipping_country', 'shipping_postcode', 'customer_note', 'order_comments'
		))){
			return true;
		}
		return false;
	}
	
	/**
	 * Save Data function.
	 */
	public function save_data($order_id, $posted){
		$types = array('billing', 'shipping', 'additional');

		foreach($types as $type){
			$fields = $this->get_fields($type);
			
			foreach($fields as $name => $field){
				if(isset($field['custom']) && $field['custom'] && isset($posted[$name])){
					$value = wc_clean($posted[$name]);
					if($value){
						update_post_meta($order_id, $name, $value);
					}
				}
			}
		}
	}
	
	public static function get_fields($key){
		$fields = array_filter(get_option('wc_fields_'. $key, array()));

		if(empty($fields) || sizeof($fields) == 0){
			if($key === 'billing' || $key === 'shipping'){
				$fields = WC()->countries->get_address_fields(WC()->countries->get_base_country(), $key . '_');

			} else if($key === 'additional'){
				$fields = array(
					'order_comments' => array(
						'type'        => 'textarea',
						'class'       => array('notes'),
						'label'       => __('Order Notes', 'woocommerce'),
						'placeholder' => _x('Notes about your order, e.g. special notes for delivery.', 'placeholder', 'woocommerce')
					)
				);
			}
		}
		return $fields;
	}
			
	public function sort_fields_by_order($a, $b){
	    if(!isset($a['order']) || $a['order'] == $b['order']){
	        return 0;
	    }
	    return ($a['order'] < $b['order']) ? -1 : 1;
	}
	
	public function get_field_types(){
		return array(
			'text' => 'Text',
			'select' => 'Select',				
		);
	}

	/*
	 * New field form popup
	 */	
	public function wcfd_new_field_form_pp(){
		$field_types = $this->get_field_types();
		?>
        <div id="wcfd_new_field_form_pp" title="New Checkout Field" class="wcfd_popup_wrapper">
          <form>
          	<table>
            	<tr>                
                	<td colspan="2" class="err_msgs"></td>
				</tr>
            	<tr>                    
                	<td width="40%">Type</td>
                    <td>
                    	<select name="ftype" style="width:150px;" onchange="fieldTypeChangeListner(this)">
                        <?php foreach($field_types as $value=>$label){ ?>
                        	<option value="<?php echo trim($value); ?>"><?php echo $label; ?></option>
                        <?php } ?>
                        </select>
                    </td>
				</tr>
            	<tr>                
                	<td>Name</td>
                    <td><input type="text" name="fname" style="width:250px;"/></td>
				</tr>                
                <tr>
                    <td>Label</td>
                    <td><input type="text" name="flabel" style="width:250px;"/></td>
				</tr>
                <tr class="rowPlaceholder">                    
                    <td>Placeholder</td>
                    <td><input type="text" name="fplaceholder" style="width:250px;"/></td>
				</tr>
                <tr class="rowOptions">                    
                    <td>Options</td>
                    <td><input type="text" name="foptions" placeholder="Seperate options with pipe(|)" style="width:250px;"/></td>
				</tr>
                <tr class="rowClass">
                    <td>Class</td>
                    <td><input type="text" name="fclass" placeholder="Seperate classes with comma" style="width:250px;"/></td>
				</tr>
                <!--<tr class="rowLabelClass">
                    <td>Label Class</td>
                    <td><input type="text" name="flabelclass" placeholder="Seperate classes with comma" style="width:250px;"/></td>
				</tr>-->                                   
                <tr class="rowValidate">                    
                    <td>Validation</td>
                    <td>
                    	<select multiple="multiple" name="fvalidate" placeholder="Select validations" class="thwcfd-enhanced-multi-select" 
                        style="width: 250px; height:30px;">
                            <option value="email">Email</option>
                            <option value="phone">Phone</option>
                            <option value="postcode">Postcode</option>
                            <option value="state">State</option>
                        </select>
                    </td>
				</tr>  
                <tr class="rowRequired">
                	<td>&nbsp;</td>                     
                    <td>                    	
                    	<input type="checkbox" name="frequired" value="yes" checked/>
                        <label>Required</label><br/>
                                                
                    	<input type="checkbox" name="fclearRow" value="yes" checked/>
                        <label>Clear Row</label><br/>
                                                
                    	<input type="checkbox" name="fenabled" value="yes" checked/>
                        <label>Enabled</label>
                    </td>
                </tr>      
                <tr class="rowShowInEmail"> 
                	<td>&nbsp;</td>                   
                    <td>                    	
                    	<input type="checkbox" name="fshowinemail" value="email" checked/>
                        <label>Display in Emails</label>
                    </td>
                </tr> 
                <tr class="rowShowInOrder"> 
                	<td>&nbsp;</td>                   
                    <td>                    	
                    	<input type="checkbox" name="fshowinorder" value="order-review" checked/>
                        <label>Display in Order Detail Pages</label>
                    </td>
            	</tr>                           
            </table>
          </form>
        </div>
        <?php
	}
	
	/*
	 * New field form popup
	 */	
	public function wcfd_edit_field_form_pp(){
		$field_types = $this->get_field_types();
		?>
        <div id="wcfd_edit_field_form_pp" title="Edit Checkout Field" class="wcfd_popup_wrapper">
          <form>
          	<table>
            	<tr>                
                	<td colspan="2" class="err_msgs"></td>
				</tr>
            	<tr>                
                	<td width="40%">Name</td>
                    <td>
                    	<input type="hidden" name="rowId"/>
                    	<input type="hidden" name="fname"/>
                    	<input type="text" name="fnameNew" style="width:250px;"/>
                    </td>
				</tr>
                <tr>                   
                    <td>Type</td>
                    <td>
                    	<select name="ftype" style="width:150px;" onchange="fieldTypeChangeListner(this)">
                        <?php foreach($field_types as $value=>$label){ ?>
                        	<option value="<?php echo trim($value); ?>"><?php echo $label; ?></option>
                        <?php } ?>
                        </select>
                    </td>
				</tr>                
                <tr>
                    <td>Label</td>
                    <td><input type="text" name="flabel" style="width:250px;"/></td>
				</tr>
                <tr class="rowPlaceholder">                    
                    <td>Placeholder</td>
                    <td><input type="text" name="fplaceholder" style="width:250px;"/></td>
				</tr>
                <tr class="rowOptions">                    
                    <td>Options</td>
                    <td><input type="text" name="foptions" placeholder="Seperate options with pipe(|)" style="width:250px;"/></td>
				</tr>                
                <tr class="rowClass">
                    <td>Class</td>
                    <td><input type="text" name="fclass" placeholder="Seperate classes with comma" style="width:250px;"/></td>
				</tr>
                <!--<tr class="rowLabelClass">
                    <td>Label Class</td>
                    <td><input type="text" name="flabelclass" placeholder="Seperate classes with comma" style="width:250px;"/></td>
				</tr>-->                                   
                <tr class="rowValidate">                    
                    <td>Validation</td>
                    <td>
                    	<select multiple="multiple" name="fvalidate" placeholder="Select validations" class="thwcfd-enhanced-multi-select" 
                        style="width: 250px; height:30px;">
                            <option value="email">Email</option>
                            <option value="phone">Phone</option>
                            <option value="postcode">Postcode</option>
                            <option value="state">State</option>
                        </select>
                    </td>
				</tr>  
                <tr class="rowRequired">  
                	<td>&nbsp;</td>                     
                    <td>                    	
                    	<input type="checkbox" name="frequired" value="yes" checked/>
                        <label>Required</label><br/>
                                                
                    	<input type="checkbox" name="fclearRow" value="yes" checked/>
                        <label>Clear Row</label><br/>
                                                
                    	<input type="checkbox" name="fenabled" value="yes" checked/>
                        <label>Enabled</label>
                    </td>                    
                </tr>  
                <tr class="rowShowInEmail"> 
                	<td>&nbsp;</td>                   
                    <td>                    	
                    	<input type="checkbox" name="fshowinemail" value="email" checked/>
                        <label>Display in Emails</label>
                    </td>
                </tr> 
                <tr class="rowShowInOrder"> 
                	<td>&nbsp;</td>                   
                    <td>                    	
                    	<input type="checkbox" name="fshowinorder" value="order-review" checked/>
                        <label>Display in Order Detail Pages</label>
                    </td>
                </tr> 
            </table>
          </form>
        </div>
        <?php
	}
	
	public function render_tabs_and_sections(){
		$tabs = array( 'fields' => 'Checkout Fields' );
		$tab  = isset( $_GET['tab'] ) ? esc_attr( $_GET['tab'] ) : 'fields';
		
		$sections = ''; $section  = '';
		if($tab === 'fields'){
			$sections = array( 'billing', 'shipping', 'additional' );
			$section  = isset( $_GET['section'] ) ? esc_attr( $_GET['section'] ) : 'billing';
		}
		
		echo '<h2 class="nav-tab-wrapper woo-nav-tab-wrapper">';
		foreach( $tabs as $key => $value ) {
			$active = ( $key == $tab ) ? 'nav-tab-active' : '';
			echo '<a class="nav-tab '.$active.'" href="'.admin_url('admin.php?page=checkout_form_designer&tab='.$key).'">'.$value.'</a>';
		}
		echo '</h2>';
		
		if(!empty($sections)){
			echo '<ul class="thwcfd-sections">';
			$size = sizeof($sections); $i = 0;
			foreach( $sections as $key ) {
				$i++;
				$active = ( $key == $section ) ? 'current' : '';
				$url = 'admin.php?page=checkout_form_designer&tab=fields&section='.$key;
				echo '<li>';
				echo '<a href="'.admin_url($url).'" class="'.$active.'" >'.ucwords($key).' '.__('Fields', 'thwcfd').'</a>';
				echo ($size > $i) ? ' | ' : '';
				echo '</li>';				
			}
			echo '</ul>';
		}
		
		$this->output_premium_version_notice();
	}
	
	public function get_current_tab(){
		return isset( $_GET['tab'] ) ? esc_attr( $_GET['tab'] ) : 'fields';
	}
	
	public function get_current_section(){
		$tab = $this->get_current_tab();
		$section = '';
		if($tab === 'fields'){
			$section = isset( $_GET['section'] ) ? esc_attr( $_GET['section'] ) : 'billing';
		}
		return $section;
	}

	public function render_checkout_fields_heading_row(){
		?>
		<th class="sort"></th>
		<th class="check-column" style="padding-left:0px !important;"><input type="checkbox" style="margin-left:7px;" onclick="thwcfdSelectAllCheckoutFields(this)"/></th>
		<th class="name">Name</th>
		<th class="id">Type</th>
		<th>Label</th>
		<th>Placeholder</th>
		<th>Validation Rules</th>
        <th class="status">Required</th>
		<th class="status">Clear Row</th>
		<th class="status">Enabled</th>	
        <th class="status">Edit</th>	
        <?php
	}
	
	public function render_actions_row($section){
		?>
        <th colspan="7">
            <button type="button" class="button button-primary" onclick="openNewFieldForm('<?php echo $section; ?>')"><?php _e( '+ Add field', 'thwcfd' ); ?></button>
            <button type="button" class="button" onclick="removeSelectedFields()"><?php _e( 'Remove', 'thwcfd' ); ?></button>
            <button type="button" class="button" onclick="enableSelectedFields()"><?php _e( 'Enable', 'thwcfd' ); ?></button>
            <button type="button" class="button" onclick="disableSelectedFields()"><?php _e( 'Disable', 'thwcfd' ); ?></button>
        </th>
        <th colspan="4">
        	<input type="submit" name="save_fields" class="button-primary" value="<?php _e( 'Save changes', 'thwcfd' ) ?>" style="float:right" />
            <input type="submit" name="reset_fields" class="button" value="<?php _e( 'Reset to default fields', 'thwcfd' ) ?>" style="float:right; margin-right: 5px;" 
			onclick="return confirm('Are you sure you want to reset to default fields? all your changes will be deleted.');"/>
        </th>  
    	<?php 
	}
	
	public function the_designer() {
		$tab = $this->get_current_tab();
		if($tab === 'fields'){
			$this->checkout_form_field_editor();
		}
	}
	
	public function checkout_form_field_editor() {
		$section = $this->get_current_section();
						
		echo '<div class="wrap woocommerce"><div class="icon32 icon32-attributes" id="icon-woocommerce"><br /></div>';
			$this->render_tabs_and_sections();
			
			if ( isset( $_POST['save_fields'] ) )
				echo $this->save_options( $section );
				
			if ( isset( $_POST['reset_fields'] ) )
				echo $this->reset_checkout_fields();		
	
			global $supress_field_modification;
			$supress_field_modification = false;
			?>            
                        
			<form method="post" id="wcfd_checkout_fields_form" action="">
            	<table id="wcfd_checkout_fields" class="wc_gateways widefat" cellspacing="0">
					<thead>
                    	<tr><?php $this->render_actions_row($section); ?></tr>
                    	<tr><?php $this->render_checkout_fields_heading_row(); ?></tr>						
					</thead>
                    <tfoot>
                    	<tr><?php $this->render_checkout_fields_heading_row(); ?></tr>
						<tr><?php $this->render_actions_row($section); ?></tr>
					</tfoot>
					<tbody class="ui-sortable">
                    <?php 
					$i=0;
					foreach( $this->get_fields( $section ) as $name => $options ) :	
						if ( isset( $options['custom'] ) && $options['custom'] == 1 ) {
							$options['custom'] = '1';
						} else {
							$options['custom'] = '0';
						}
											
						if ( !isset( $options['label'] ) ) {
							$options['label'] = '';
						}
						
						if ( !isset( $options['placeholder'] ) ) {
							$options['placeholder'] = '';
						}
												
						if( isset( $options['options'] ) && is_array($options['options']) ) {
							$options['options'] = implode("|", $options['options']);
						}else{
							$options['options'] = '';
						}
						
						if( isset( $options['class'] ) && is_array($options['class']) ) {
							$options['class'] = implode(",", $options['class']);
						}else{
							$options['class'] = '';
						}
						
						if( isset( $options['label_class'] ) && is_array($options['label_class']) ) {
							$options['label_class'] = implode(",", $options['label_class']);
						}else{
							$options['label_class'] = '';
						}
						
						if( isset( $options['validate'] ) && is_array($options['validate']) ) {
							$options['validate'] = implode(",", $options['validate']);
						}else{
							$options['validate'] = '';
						}
												
						if ( isset( $options['required'] ) && $options['required'] == 1 ) {
							$options['required'] = '1';
						} else {
							$options['required'] = '0';
						}
						
						if ( isset( $options['clear'] ) && $options['clear'] == 1 ) {
							$options['clear'] = '1';
						} else {
							$options['clear'] = '0';
						}
						
						if ( !isset( $options['enabled'] ) || $options['enabled'] == 1 ) {
							$options['enabled'] = '1';
						} else {
							$options['enabled'] = '0';
						}

						if ( !isset( $options['type'] ) ) {
							$options['type'] = 'text';
						} 
						
						if ( isset( $options['show_in_email'] ) && $options['show_in_email'] == 1 ) {
							$options['show_in_email'] = '1';
						} else {
							$options['show_in_email'] = '0';
						}
						
						if ( isset( $options['show_in_order'] ) && $options['show_in_order'] == 1 ) {
							$options['show_in_order'] = '1';
						} else {
							$options['show_in_order'] = '0';
						}
					?>
						<tr class="row_<?php echo $i; echo($options['enabled'] == 1 ? '' : ' thwcfd-disabled') ?>">
                        	<td width="1%" class="sort ui-sortable-handle">
                            	<input type="hidden" name="f_custom[<?php echo $i; ?>]" class="f_custom" value="<?php echo $options['custom']; ?>" />
                                <input type="hidden" name="f_order[<?php echo $i; ?>]" class="f_order" value="<?php echo $i; ?>" />
                                                                                                
                                <input type="hidden" name="f_name[<?php echo $i; ?>]" class="f_name" value="<?php echo esc_attr( $name ); ?>" />
                                <input type="hidden" name="f_name_new[<?php echo $i; ?>]" class="f_name_new" value="" />
                                <input type="hidden" name="f_type[<?php echo $i; ?>]" class="f_type" value="<?php echo $options['type']; ?>" />                                
                                <input type="hidden" name="f_label[<?php echo $i; ?>]" class="f_label" value="<?php echo $options['label']; ?>" />
                                <input type="hidden" name="f_placeholder[<?php echo $i; ?>]" class="f_placeholder" value="<?php echo $options['placeholder']; ?>" />
                                <input type="hidden" name="f_options[<?php echo $i; ?>]" class="f_options" value="<?php echo($options['options']) ?>" />
                                
                                <input type="hidden" name="f_class[<?php echo $i; ?>]" class="f_class" value="<?php echo $options['class']; ?>" />
                                <input type="hidden" name="f_label_class[<?php echo $i; ?>]" class="f_label_class" value="<?php echo $options['label_class']; ?>" />
                                                                
                                <input type="hidden" name="f_required[<?php echo $i; ?>]" class="f_required" value="<?php echo($options['required']) ?>" />
                                <input type="hidden" name="f_clear[<?php echo $i; ?>]" class="f_clear" value="<?php echo($options['clear']) ?>" />
                                                                
                                <input type="hidden" name="f_enabled[<?php echo $i; ?>]" class="f_enabled" value="<?php echo($options['enabled']) ?>" />
                                <input type="hidden" name="f_validation[<?php echo $i; ?>]" class="f_validation" value="<?php echo($options['validate']) ?>" />
                                <input type="hidden" name="f_show_in_email[<?php echo $i; ?>]" class="f_show_in_email" value="<?php echo($options['show_in_email']) ?>" />
                                <input type="hidden" name="f_show_in_order[<?php echo $i; ?>]" class="f_show_in_order" value="<?php echo($options['show_in_order']) ?>" />
                                <input type="hidden" name="f_deleted[<?php echo $i; ?>]" class="f_deleted" value="0" />
                                
                                <!--$properties = array('type', 'label', 'placeholder', 'class', 'required', 'clear', 'label_class', 'options');-->
                            </td>
                            <td class="td_select"><input type="checkbox" name="select_field"/></td>
                            <td class="td_name"><?php echo esc_attr( $name ); ?></td>
                            <td class="td_type"><?php echo $options['type']; ?></td>
                            <td class="td_label"><?php echo $options['label']; ?></td>
                            <td class="td_placeholder"><?php echo $options['placeholder']; ?></td>
                            <td class="td_validate"><?php echo $options['validate']; ?></td>
                            <td class="td_required status"><?php echo($options['required'] == 1 ? '<span class="status-enabled tips">Yes</span>' : '-' ) ?></td>
                            <td class="td_clear status"><?php echo($options['clear'] == 1 ? '<span class="status-enabled tips">Yes</span>' : '-' ) ?></td>
                            <td class="td_enabled status"><?php echo($options['enabled'] == 1 ? '<span class="status-enabled tips">Yes</span>' : '-' ) ?></td>
                            <td class="td_edit">
                            	<button type="button" class="f_edit_btn" <?php echo($options['enabled'] == 1 ? '' : 'disabled') ?> 
                                onclick="openEditFieldForm(this,<?php echo $i; ?>)"><?php _e( 'Edit', 'thwcfd' ); ?></button>
                            </td>
                    	</tr>
                    <?php $i++; endforeach; ?>
                	</tbody>
				</table> 
            </form>
            <?php
            $this->wcfd_new_field_form_pp();
			$this->wcfd_edit_field_form_pp();
			?>
    	</div>
    <?php 		
	}
						
	public function save_options( $section ) {
		$o_fields      = $this->get_fields( $section );
		$fields        = $o_fields;
		//$core_fields   = array_keys( WC()->countries->get_address_fields( WC()->countries->get_base_country(), $section . '_' ) );
		//$core_fields[] = 'order_comments';
		
		$f_order       = ! empty( $_POST['f_order'] ) ? $_POST['f_order'] : array();
		
		$f_names       = ! empty( $_POST['f_name'] ) ? $_POST['f_name'] : array();
		$f_names_new   = ! empty( $_POST['f_name_new'] ) ? $_POST['f_name_new'] : array();
		$f_types       = ! empty( $_POST['f_type'] ) ? $_POST['f_type'] : array();
		$f_labels      = ! empty( $_POST['f_label'] ) ? $_POST['f_label'] : array();
		$f_placeholder = ! empty( $_POST['f_placeholder'] ) ? $_POST['f_placeholder'] : array();
		$f_options     = ! empty( $_POST['f_options'] ) ? $_POST['f_options'] : array();
		
		$f_class       = ! empty( $_POST['f_class'] ) ? $_POST['f_class'] : array();
		$f_label_class = ! empty( $_POST['f_label_class'] ) ? $_POST['f_label_class'] : array();
		
		$f_required    = ! empty( $_POST['f_required'] ) ? $_POST['f_required'] : array();
		$f_clear       = ! empty( $_POST['f_clear'] ) ? $_POST['f_clear'] : array();		
		$f_enabled     = ! empty( $_POST['f_enabled'] ) ? $_POST['f_enabled'] : array();
		
		$f_show_in_email = ! empty( $_POST['f_show_in_email'] ) ? $_POST['f_show_in_email'] : array();
		$f_show_in_order = ! empty( $_POST['f_show_in_order'] ) ? $_POST['f_show_in_order'] : array();
		
		$f_validation  = ! empty( $_POST['f_validation'] ) ? $_POST['f_validation'] : array();
		$f_deleted     = ! empty( $_POST['f_deleted'] ) ? $_POST['f_deleted'] : array();
						
		$f_position        = ! empty( $_POST['f_position'] ) ? $_POST['f_position'] : array();				
		$f_display_options = ! empty( $_POST['f_display_options'] ) ? $_POST['f_display_options'] : array();
		$max               = max( array_map( 'absint', array_keys( $f_names ) ) );
			
		for ( $i = 0; $i <= $max; $i ++ ) {
			$name     = empty( $f_names[$i] ) ? '' : urldecode( sanitize_title( wc_clean( stripslashes( $f_names[$i] ) ) ) );
			$new_name = empty( $f_names_new[$i] ) ? '' : urldecode( sanitize_title( wc_clean( stripslashes( $f_names_new[$i] ) ) ) );
			$allow_override = apply_filters('thwcfd_allow_default_field_override_'.$new_name, false);
			
			if(!empty($f_deleted[$i]) && $f_deleted[$i] == 1){
				unset( $fields[$name] );
				continue;
			}
						
			// Check reserved names
			if($this->is_reserved_field_name( $new_name ) && !$allow_override){
				continue;
			}
						
			//if update field
			if( $name && $new_name && $new_name !== $name ){
				if ( isset( $fields[$name] ) ) {
					$fields[$new_name] = $fields[$name];
				} else {
					$fields[$new_name] = array();
				}

				unset( $fields[$name] );
				$name = $new_name;
			} else {
				$name = $name ? $name : $new_name;
			}

			if(!$name){
				continue;
			}
						
			//if new field
			if ( !isset( $fields[$name] ) ) {
				$fields[$name] = array();
			}

			$o_type  = isset( $o_fields[$name]['type'] ) ? $o_fields[$name]['type'] : 'text';
			
			//$o_class = isset( $o_fields[$name]['class'] ) ? $o_fields[$name]['class'] : array();
			//$classes = array_diff( $o_class, array( 'form-row-first', 'form-row-last', 'form-row-wide' ) );

			$fields[$name]['type']    	  = empty( $f_types[$i] ) ? $o_type : wc_clean( $f_types[$i] );
			$fields[$name]['label']   	  = empty( $f_labels[$i] ) ? '' : wp_kses_post( trim( stripslashes( $f_labels[$i] ) ) );
			$fields[$name]['placeholder'] = empty( $f_placeholder[$i] ) ? '' : wc_clean( stripslashes( $f_placeholder[$i] ) );
			$fields[$name]['options'] 	  = empty( $f_options[$i] ) ? array() : array_map( 'wc_clean', explode( '|', trim(stripslashes($f_options[$i])) ) );
			
			$fields[$name]['class'] 	  = empty( $f_class[$i] ) ? array() : array_map( 'wc_clean', explode( ',', $f_class[$i] ) );
			$fields[$name]['label_class'] = empty( $f_label_class[$i] ) ? array() : array_map( 'wc_clean', explode( ',', $f_label_class[$i] ) );
			
			$fields[$name]['required']    = empty( $f_required[$i] ) ? false : true;
			$fields[$name]['clear']   	  = empty( $f_clear[$i] ) ? false : true;
			
			$fields[$name]['enabled']     = empty( $f_enabled[$i] ) ? false : true;
			$fields[$name]['order']       = isset($f_order[$i]) && is_numeric($f_order[$i]) ? wc_clean( $f_order[$i] ) : '';
					
			if (!empty( $fields[$name]['options'] )) {
				$fields[$name]['options'] = array_combine( $fields[$name]['options'], $fields[$name]['options'] );
			}

			if (!in_array( $name, $this->locale_fields ) || apply_filters('thwcfd_allow_address_field_validation_override', false, $name)){
				$fields[$name]['validate'] = empty( $f_validation[$i] ) ? array() : explode( ',', $f_validation[$i] );
			}

			if (!$this->is_default_field_name( $name )){
				$fields[$name]['custom'] = true;
				$fields[$name]['show_in_email'] = empty( $f_show_in_email[$i] ) ? false : true;
				$fields[$name]['show_in_order'] = empty( $f_show_in_order[$i] ) ? false : true;
			} else {
				$fields[$name]['custom'] = false;
			}
			
			$fields[$name]['label']   	  = __($fields[$name]['label'], 'woocommerce');
			$fields[$name]['placeholder'] = __($fields[$name]['placeholder'], 'woocommerce');
		}
		
		uasort( $fields, array( $this, 'sort_fields_by_order' ) );
		$result = update_option( 'wc_fields_' . $section, $fields );

		if ( $result == true ) {
			echo '<div class="updated"><p>' . __( 'Your changes were saved.', 'thwcfd' ) . '</p></div>';
		} else {
			echo '<div class="error"><p> ' . __( 'Your changes were not saved due to an error (or you made none!).', 'thwcfd' ) . '</p></div>';
		}
	}

}
