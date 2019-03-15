<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Persian_Woocommerce_Core' ) ) :

	class Persian_Woocommerce_Core {

		private $options;

		public $file_dir, $wc_is_active;

		//sub classess
		public $tools, $widget, $translate, $currencies, $address, $gateways;

		protected static $_instance = null;

		public static function instance( $file ) {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self( $file );
			}

			return self::$_instance;
		}

		public function __construct( $file = null ) {

			$this->file_dir = $file;
			$this->options  = get_option( 'PW_Options' );

			$this->wc_is_active = class_exists( 'woocommerce' ) || class_exists( 'WooCommerce' );
			if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
				$this->wc_is_active = true;
			}

			//add_filter( 'woocommerce_show_addons_page', '__return_false', 100 );
			add_action( 'admin_menu', array( $this, 'admin_menus' ), 59 );
			add_action( 'activated_plugin', array( $this, 'activated_plugin' ), 10, 1 );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			add_action( 'admin_notices', array( $this, 'notices_render' ) );
			add_action( 'wp_ajax_pw_notice_dismiss', array( $this, 'notices_dismiss' ) );
			add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ), 10 );
		}

		public function plugins_loaded() {
			if ( $this->wc_is_active ) {
				require_once( 'class-gateways.php' );
			}
		}

		public function admin_menus() {

			if ( ! $this->wc_is_active ) {
				return;
			}

			add_menu_page( 'ووکامرس فارسی', 'ووکامرس فارسی', 'manage_options', 'persian-wc',
				array( $this->translate, 'translate_page' ), $this->plugin_url( 'assets/images/logo.png' ), '55.6' );

			add_submenu_page( 'persian-wc', 'حلقه های ترجمه', 'حلقه های ترجمه', 'manage_options', 'persian-wc',
				array( $this->translate, 'translate_page' ) );

			if ( $this->wc_is_active ) {
				add_submenu_page( 'persian-wc', 'ابزار ها', 'ابزار ها', 'manage_options', 'persian-wc-tools',
					array( $this->tools, 'tools_page' ) );
			}

			do_action( "PW_Menu" );

			add_submenu_page( 'persian-wc', 'افزونه ها', 'افزونه ها', 'manage_woocommerce', 'persian-wc-plugins', array(
				$this,
				'plugins_page'
			) );

			add_submenu_page( 'persian-wc', 'پوسته ها', 'پوسته ها', 'manage_woocommerce', 'persian-wc-themes', array(
				$this,
				'themes_page'
			) );

			add_submenu_page( 'woocommerce', 'افزونه های پارسی', 'افزونه های پارسی', 'manage_woocommerce', 'wc-persian-plugins', array(
				$this,
				'plugins_page'
			) );

			add_submenu_page( 'woocommerce', 'پوسته های پارسی', 'پوسته های پارسی', 'manage_woocommerce', 'wc-persian-themes', array(
				$this,
				'themes_page'
			) );

			add_submenu_page( 'persian-wc', 'درباره ما', 'درباره ما', 'manage_options', 'persian-wc-about', array(
				$this,
				'about_page'
			) );
		}

		public function themes_page() {
			wp_enqueue_style( 'woocommerce_admin_styles' );
			include( 'view/html-admin-page-themes.php' );
		}

		public function plugins_page() {
			wp_enqueue_style( 'woocommerce_admin_styles' );
			include( 'view/html-admin-page-plugins.php' );
		}

		public function about_page() {
			include( 'view/html-admin-page-about.php' );
		}

		public function activated_plugin( $plugin ) {

			if ( $plugin != 'persian-woocommerce/woocommerce-persian.php' ) {
				return;
			}

			global $wpdb;
			$woocommerce_ir_sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}woocommerce_ir` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`text1` text CHARACTER SET utf8 COLLATE utf8_persian_ci NOT NULL,
			`text2` text CHARACTER SET utf8 COLLATE utf8_persian_ci,
			PRIMARY KEY (`id`)) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $woocommerce_ir_sql );

			//delete deprecated tables-----------------------------
			$deprecated_tables = array(
				'woocommerce_ir_cities',
				'Woo_Iran_Cities_By_HANNANStd',
			);

			global $wpdb;
			foreach ( $deprecated_tables as $deprecated_table ) {
				$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}{$deprecated_table}" );
			}


			//delete deprecated Options-----------------------------
			$deprecated_options = array(
				'is_cities_installed',
				'pw_delete_city_table_2_5',
				'woocommerce_persian_feed',
				'redirect_to_woo_persian_about_page',
				'enable_woocommerce_notice_dismissed',
				'Persian_Woocommerce_rename_old_table',
			);

			foreach ( $deprecated_options as $deprecated_option ) {
				delete_option( $deprecated_option );
			}

			for ( $i = 0; $i < 10; $i ++ ) {
				delete_option( 'persian_woo_notice_number_' . $i );
			}

			if ( ! headers_sent() && $this->wc_is_active ) {
				wp_redirect( admin_url( 'admin.php?page=persian-wc-about' ) );
				die();
			}
		}

		public function enqueue_scripts() {
			$pages = array(
				'persian-wc-about',
				'persian-wc-plugins',
				'wc-persian-plugins',
				'persian-wc-themes',
				'wc-persian-themes',
			);
			if ( ! empty( $_GET['page'] ) && in_array( $_GET['page'], $pages ) ) {
				wp_register_style( 'pw-admin-fonts', $this->plugin_url( 'assets/css/admin.font.css' ) );
				wp_enqueue_style( 'pw-admin-fonts' );
			}
		}

		public function plugin_url( $path = null ) {
			return untrailingslashit( plugins_url( is_null( $path ) ? '/' : $path, $this->file_dir ) );
		}

		public function get_options( $option_name = null, $default = false ) {

			if ( is_null( $option_name ) ) {
				return $this->options;
			}

			$default_options = array();

			if ( ! empty( $this->tools ) && method_exists( $this->tools, 'get_tools_default' ) ) {
				$default_options = $this->tools->get_tools_default();
			}

			if ( isset( $this->options[ $option_name ] ) ) {
				return $this->options[ $option_name ];
			} elseif ( isset( $default_options["PW_Options[$option_name]"] ) ) {
				return $default_options["PW_Options[$option_name]"];
			} else {
				return $default;
			}
		}

		public function notices_render() {
			$notices = array();
			$this->notices_texts( $notices );
			$dismissed = (array) get_option( 'persian_woocommerce_dismissed_notices' );
			foreach ( $notices as $id => $notice ) {
				if ( count( $notice ) == 3 && ! $notice[2] ) {
					continue;
				}
				if ( ! in_array( $id, $dismissed ) ) {
					printf( '<div class="notice persian_woocommerce_notice notice-%2$s is-dismissible" id="persian_woocommerce_%1$s"><p>%3$s</p></div>',
						$id, $notice[0], $notice[1]
					);
				}
			}
			?>
            <script type="text/javascript">
                jQuery(document).ready(function ($) {
                    $(document.body).on('click', '.notice-dismiss', function () {
                        var notice = $(this).closest('.persian_woocommerce_notice');
                        notice = notice.attr('id');
                        if (notice.indexOf('persian_woocommerce_') !== -1) {
                            notice = notice.replace('persian_woocommerce_', '');
                            $.ajax({
                                url: "<?php echo admin_url( 'admin-ajax.php' ) ?>",
                                type: "post",
                                data: {
                                    notice: notice,
                                    action: 'pw_notice_dismiss',
                                    security: "<?php echo wp_create_nonce( 'pw_notice_dismiss' ); ?>"
                                },
                                success: function (response) {
                                }
                            });
                        }
                        return false;
                    });
                });
            </script>
			<?php
		}

		public function notices_dismiss() {

			check_ajax_referer( 'pw_notice_dismiss', 'security' );

			if ( ! empty( $_POST['notice'] ) ) {
				$dismissed   = (array) get_option( 'persian_woocommerce_dismissed_notices' );
				$dismissed[] = $_POST['notice'];
				update_option( 'persian_woocommerce_dismissed_notices', array_unique( $dismissed ) );
			}
			die();
		}

		private function notices_texts( &$notices ) {

			$notices['wc_is_active'] = array(
				'info',
				sprintf( 'ووکامرس فارسی با موفقیت نصب و فعالسازی شده است . لطفا افزونه ووکامرس را از <a href="%s" target="_blank">اینجا</a> فعال کنید.', admin_url( 'plugins.php' ) ),
				( ! $this->wc_is_active && current_user_can( 'install_plugins' ) )
			);

			$notices['php_version'] = array(
				'error',
				'برای استفاده از ووکامرس پارسی باید نسخه PHP هاست شما حداقل 5.3 باشد.',
				version_compare( PHP_VERSION, '5.3', '<' ),
			);

		}
	}
endif;

if ( ! class_exists( 'Persian_Woocommerce_Plugin' ) ) {
	class Persian_Woocommerce_Plugin extends Persian_Woocommerce_Core {
	}
}