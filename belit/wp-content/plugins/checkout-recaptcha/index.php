<?php
/*
  Plugin Name: Checkout reCAPTCHA
  Plugin URI: http://tickera.com/
  Description: Adds reCAPTCHA to a Tickera / WooCommerce checkout (Bridge for WooCommerce compatible)
  Author: Tickera.com
  Author URI: http://tickera.com/
  Version: 1.0.1
  Copyright 2017 Tickera (http://tickera.com/)
 */

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

if (!class_exists('TC_Checkout_reCAPTCHA')) {

    class TC_Checkout_reCAPTCHA {

        var $version = '1.0.1';
        var $title = 'Checkout reCAPTCHA';
        var $name = 'checkout-recaptcha';
        var $dir_name = 'checkout-recaptcha';
        var $location = 'plugins';
        var $plugin_dir = '';
        var $plugin_url = '';

        function __construct() {

            $this->init_vars();
            add_action('plugins_loaded', array(&$this, 'localization'), 9);

            add_filter('tc_settings_new_menus', array(&$this, 'tc_settings_new_menus'), 10, 1);
            add_action('tc_settings_menu_checkout_recaptcha', array(&$this, 'tc_settings_menu_checkout_recaptcha'));

            add_action('tc_before_cart_submit', array(&$this, 'add_recaptcha'));

            add_action('tc_cart_before_error_pass_check', array($this, 'recaptcha_error_check'), 0, 2);
            add_action('woocommerce_after_checkout_validation', array(&$this, 'woo_recaptcha_error_check'), 10, 1);

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
                $options = array('tc_checkout_recaptcha_settings');


                foreach ($options as $option) {
                    delete_option($option);
                }
            }
        }

        function init_vars() {
            //setup proper directories
            if (defined('WP_PLUGIN_URL') && defined('WP_PLUGIN_DIR') && file_exists(WP_PLUGIN_DIR . '/' . $this->dir_name . '/' . basename(__FILE__))) {
                $this->location = 'subfolder-plugins';
                $this->plugin_dir = WP_PLUGIN_DIR . '/' . $this->dir_name . '/';
                $this->plugin_url = plugins_url('/', __FILE__);
            } else if (defined('WP_PLUGIN_URL') && defined('WP_PLUGIN_DIR') && file_exists(WP_PLUGIN_DIR . '/' . basename(__FILE__))) {
                $this->location = 'plugins';
                $this->plugin_dir = WP_PLUGIN_DIR . '/';
                $this->plugin_url = plugins_url('/', __FILE__);
            } else if (is_multisite() && defined('WPMU_PLUGIN_URL') && defined('WPMU_PLUGIN_DIR') && file_exists(WPMU_PLUGIN_DIR . '/' . basename(__FILE__))) {
                $this->location = 'mu-plugins';
                $this->plugin_dir = WPMU_PLUGIN_DIR;
                $this->plugin_url = WPMU_PLUGIN_URL;
            } else {
                wp_die(sprintf(__('There was an issue determining where %s is installed. Please reinstall it.', 'tcrc'), $this->title));
            }
        }

        function localization() {
            // Load up the localization file if we're using WordPress in a different language
            // Place it in this plugin's "languages" folder and name it "tcrc-[value in wp-config].mo"
            if ($this->location == 'mu-plugins') {
                load_muplugin_textdomain('tcrc', 'languages/');
            } else if ($this->location == 'subfolder-plugins') {
                load_plugin_textdomain('tc', false, dirname(plugin_basename(__FILE__)) . '/languages/');
            } else if ($this->location == 'plugins') {
                load_plugin_textdomain('tcrc', false, 'languages/');
            } else {
                
            }

            $temp_locales = explode('_', get_locale());
            $this->language = ($temp_locales[0]) ? $temp_locales[0] : 'en';
        }

        /**
         * Adds new admin menu item for the add-on
         * @param array $menus
         * @return type
         */
        function tc_settings_new_menus($menus) {
            $menus['checkout_recaptcha'] = $this->title;
            return $menus;
        }

        /**
         * Loads admin settings page for the add-on
         */
        function tc_settings_menu_checkout_recaptcha() {
            include($this->plugin_dir . 'includes/admin-pages/settings.php');
        }

        /**
         * Gets add-on settings
         * @return type
         */
        public static function get_settings() {
            $settings = get_option('tc_checkout_recaptcha_settings');
            return $settings;
        }

        function add_recaptcha() {
            $settings = TC_Checkout_reCAPTCHA::get_settings();
            $show_recaptcha = isset($settings['show_recaptcha']) ? $settings['show_recaptcha'] : '0';

            if ($show_recaptcha == '1') {
                ?>
                <div class="g-recaptcha" data-sitekey="<?php echo isset($settings['site_key']) ? $settings['site_key'] : ''; ?>"></div>
                <script type="text/javascript"
                        src="https://www.google.com/recaptcha/api.js?hl=<?php echo isset($settings['language']) ? $settings['language'] : 'en'; ?>">
                </script>
                <?php
            }
        }

        function recaptcha_error_check($cart_error_number_orig, $tc_cart_errors_orig) {
            $settings = TC_Checkout_reCAPTCHA::get_settings();
            $show_recaptcha = isset($settings['show_recaptcha']) ? $settings['show_recaptcha'] : '0';

            if ($show_recaptcha == '1') {
                require_once $this->plugin_dir . 'includes/autoload.php';
                if (isset($_POST['g-recaptcha-response'])) {
                    global $tc, $tc_cart_errors, $cart_error_number;

                    $recaptcha = new \ReCaptcha\ReCaptcha($settings['secret_key']);

                    $resp = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
                    if (!$resp->isSuccess()) {
                        $cart_error_number++;
                        $tc_cart_errors .= '<li>' . isset($settings['error_message']) ? $settings['error_message'] : __('Please complete the reCAPTCHA', 'tcrc') . '</li>';
                    }
                }
            }
        }

        function woo_recaptcha_error_check($posted) {
            $settings = TC_Checkout_reCAPTCHA::get_settings();
            $show_recaptcha = isset($settings['show_recaptcha']) ? $settings['show_recaptcha'] : '0';

            if ($show_recaptcha == '1') {
                require_once $this->plugin_dir . 'includes/autoload.php';
                if (isset($_POST['g-recaptcha-response'])) {

                    $recaptcha = new \ReCaptcha\ReCaptcha($settings['secret_key']);

                    $resp = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
                    if (!$resp->isSuccess()) {
                        wc_add_notice(isset($settings['error_message']) ? $settings['error_message'] : __('Please complete the reCAPTCHA', 'tcrc'), 'error');
                    }
                }
            }
        }

    }

    new TC_Checkout_reCAPTCHA();
}
?>