<?php
/*
  Plugin Name: Tickera Terms & Conditions
  Plugin URI: http://tickera.com/
  Description: Set Terms and Conditions that ticket buyer needs to check in order to purchase ticket.
  Author: Tickera.com
  Author URI: http://tickera.com/
  Version: 1.1.0.5

  Copyright 2015 Tickera (http://tickera.com/)
 */


if (!defined('ABSPATH'))
    exit; // Exit if accessed directly


if (!class_exists('TC_Terms')) {

    class TC_Terms {

        var $version = '1.1.0.6';
        var $title = 'Tickera Terms & Conditions';
        var $name = 'tc';
        var $dir_name = 'terms-and-conditions';
        var $location = 'plugins';
        var $plugin_dir = '';
        var $plugin_url = '';

        function __construct() {
            add_filter('tc_settings_new_menus', array(&$this, 'tc_settings_new_menus_additional'));
            add_action('tc_settings_menu_tickera_terms', array(&$this, 'tc_settings_menu_tickera_terms_show_page'));
            add_action('tc_before_cart_submit', array(&$this, 'add_terms_and_conditions_checkbox'));

            add_filter('tc_delete_info_plugins_list', array($this, 'tc_delete_info_plugins_list'), 10, 1);
            add_action('tc_delete_plugins_data', array($this, 'tc_delete_plugins_data'), 10, 1);
        }

        function tc_delete_info_plugins_list($plugins) {
            $plugins[$this->name] = $this->title;
            return $plugins;
        }

        function tc_delete_plugins_data($submitted_data) {
            if (array_key_exists($this->name, $submitted_data)) {
                global $wpdb;

                //Delete options
                $options = array('tc_terms_settings');


                foreach ($options as $option) {
                    delete_option($option);
                }
            }
        }

        function tc_settings_new_menus_additional($settings_tabs) {
            $settings_tabs['tickera_terms'] = __('Terms & Conditions', 'tc');
            return $settings_tabs;
        }

        //set sendlooop options
        function tc_settings_menu_tickera_terms_show_page() {
            require_once( $this->plugin_dir . 'includes/admin-pages/settings-tickera_terms.php' );
        }

        function add_terms_and_conditions_checkbox() {

            $tc_terms_settings = get_option('tc_terms_settings');
            $tc_link_title = $tc_terms_settings['link_title'];
            $tc_terms_content = $tc_terms_settings['terms'];
            $tc_terms_error = $tc_terms_settings['error_text'];
            $tc_term_display = $tc_terms_settings['term_display'];
            $tc_term_page = $tc_terms_settings['terms_page'];

            if (!isset($tc_terms_settings['disable_terms'])) {

                //fill variables if they are empty              
                if (empty($tc_terms_error)) {
                    $tc_terms_error = __('You must agree to the terms and conditions before proceeding to the checkout', 'tc');
                }

                if (empty($tc_link_title)) {
                    $tc_link_title = __('I agree to the Terms and Conditions', 'tc');
                }

                // hook the js files
                if (!function_exists('terms_js')) {

                    function terms_js() {
                        wp_enqueue_script('tc-terms-js', plugin_dir_url(__FILE__) . '/includes/javascript.js');
                    }

                }

                add_action('wp_footer', 'terms_js');

                if ($tc_term_display == 'p') {
                    //calling thickbox
                    add_thickbox();
                    ?>

                    <label>
                        <input type="checkbox" name="check_terms" id="tc_terms_and_conditions" /> <a href="#TB_inline?width=600&height=550&inlineId=tc_terms_content" class="thickbox"><?php echo $tc_link_title; ?></a>
                        <div class="tc_term_error" style="display: none; color: red;">
                            <?php echo $tc_terms_error; ?>
                        </div><!-- .tc_term_error -->
                    </label>


                    <div id="tc_terms_content" style="display:none;">
                        <?php
                        if (!empty($tc_terms_content)) {
                            echo '<p>' . $tc_terms_content . '</p>';
                        }
                        ?>
                    </div><!-- #tc_terms_content -->


                <?php } else {
                    ?>
                    <!-- display link to a page with terms and conditions -->
                    <label>
                        <input type="checkbox" name="check_terms" id="tc_terms_and_conditions" /><a target="_blank" href="<?php echo get_permalink($tc_term_page); ?>"><?php echo $tc_link_title; ?></a>
                        <div class="tc_term_error" style="display: none; color: red;">
                            <?php echo $tc_terms_error; ?>
                        </div><!-- .tc_term_error -->
                    </label>
                    <?php
                } //if($tc_term_display == 'p'){
            } //add_terms_and_conditions_checkbox
        }

    }

    //class TC_Terms
}

$tc_terms = new TC_Terms();

//HOOK ADMIN JS FILE
function terms_js_script() {
    wp_enqueue_script('tc_admin_js', plugin_dir_url(__FILE__) . 'includes/admin-js.js');
}

add_action('admin_enqueue_scripts', 'terms_js_script');

//Addon updater 
if (function_exists('tc_plugin_updater')) {
    tc_plugin_updater('terms-and-conditions', __FILE__);
}
?>