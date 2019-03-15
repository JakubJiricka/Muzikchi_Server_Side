<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Persian_Woocommerce_Translate' ) ) :

	class Persian_Woocommerce_Translate extends Persian_Woocommerce_Core {

		public $table, $translates;

		public function __construct() {

			$this->translates();

			add_filter( 'override_unload_textdomain', array( $this, 'unload_textdomain' ), 9999, 2 );
			add_filter( 'load_textdomain_mofile', array( $this, 'load_textdomain' ), 10, 2 );

			add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
			add_action( 'admin_init', array( $this, 'search_delete' ) );

			add_action( 'wp_ajax_woocommerce_persian_save_translates', array( $this, 'save_translates' ) );
			add_action( 'wp_ajax_nopriv_woocommerce_persian_save_translates', array( $this, 'save_translates' ) );
		}

		public function unload_textdomain( $override, $domain ) {
			return get_locale() == 'fa_IR' && $domain === 'woocommerce' ? true : $override;
		}

		public function load_textdomain( $mo_file, $domain ) {
			if ( get_locale() == 'fa_IR' && $domain === 'woocommerce' ) {
				$mo_file = dirname( plugin_dir_path( __FILE__ ) ) . '/languages/woocommerce-fa_IR.mo';
			}

			return $mo_file;
		}

		public function translates() {

			global $wpdb;
			$this->table = $wpdb->prefix . 'woocommerce_ir';

			if ( ! is_array( $this->translates ) ) {
				$wpdb->suppress_errors = true;
				$result                = $wpdb->get_results( "SELECT * FROM {$this->table};" );
				$wpdb->suppress_errors = false;

				if ( ! $wpdb->last_error ) {
					$this->translates = wp_list_pluck( $result, 'text2', 'text1' );
				}
			}

			if ( is_array( $this->translates ) && count( $this->translates ) ) {
				add_filter( 'gettext_with_context', array( $this, 'gettext' ) );
				add_filter( 'ngettext_with_context', array( $this, 'gettext' ) );
				add_filter( 'gettext', array( $this, 'gettext' ) );
				add_filter( 'ngettext', array( $this, 'gettext' ) );
			}
		}

		public function gettext( $text ) {
			return isset( $this->translates[ $text ] ) ? $this->translates[ $text ] : $text;
		}

		public function translate_page() {
			do_action( 'add_meta_boxes', 'woocommerce_persian_translate' );
			?>
            <div class="wrap">
                <h2 id="title">حلقه های ترجمه</h2>
                <div class="fx-settings-meta-box-wrap">
                    <div id="poststuff">
                        <div id="post-body" class="metabox-holder columns-2">
                            <div id="postbox-container-1" class="postbox-container">
								<?php do_meta_boxes( 'woocommerce_persian_translate', 'side', null ); ?>
                            </div>

                            <div id="postbox-container-2" class="postbox-container">
								<?php settings_errors();
								echo '<form method="POST" id="list-project">';
								echo '<input type="hidden" name="page" value="persian-wc" />';
								wp_nonce_field( 'delete_item', 'woocommerce_persian_translate_nonce' );
								$list = new Persian_Woocommerce_Translate_List_Table();
								$list->search_box( 'جستجو', 'PW-search-input' );
								$list->prepare_items();
								$list->display();
								echo '</form>';
								?>
                                <div class="clear"></div>
                            </div>
                        </div>
                        <br class="clear">
                    </div>
                </div>
            </div>
            <script type="text/javascript">
                jQuery(document).ready(function ($) {
                    $("#woocommerce_persian_translate").submit(function () {
                        $("#save_loop_button").val("در حال ذخیره ...");
                        jQuery.post(ajaxurl, $("#woocommerce_persian_translate").serialize(), function (response) {
                            var obj = jQuery.parseJSON(response);
                            if (obj.status == "OK") {
                                $("#the-list").prepend(obj.code);
                                $(".displaying-num").html(obj.count);
                                document.getElementById("woocommerce_persian_translate").reset();
                                if (obj.count == "1 مورد") {
                                    $("tr.no-items").remove();
                                    $(".tablenav-pages").removeClass("no-pages").addClass("one-page");
                                }
                            }
                            setTimeout(function () {
                                $("#setting-error-pw_msg_" + obj.rand).slideUp('slow', function () {
                                    $("#setting-error-pw_msg_" + obj.rand).remove();
                                })
                            }, 3000);
                            $(".wrap h2#title").after(obj.msg);
                        });
                        setTimeout(function () {
                            $("#save_loop_button").val("ذخیره حلقه");
                        }, 2000);
                        return false;
                    });
                });
            </script>
			<?php
		}

		public function add_meta_box() {
			add_meta_box( 'add_form', 'افزودن حلقه ترجمه',
				array( $this, 'meta_box_form' ), 'woocommerce_persian_translate', 'side', 'high' );
		}

		public function meta_box_form() {
			?>
            <form action="" method="post" id="woocommerce_persian_translate">
                <input type="hidden" name="s"
                       value="<?php echo isset( $_GET['s'] ) && ! empty( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : ''; ?>"/>
                <input type="hidden" name="action" value="woocommerce_persian_save_translates"/>
                <input type="hidden" name="security"
                       value="<?php echo wp_create_nonce( 'woocommerce_persian_save_translates' ); ?>"/>
                <label for="input_text_1">کلمه‌ی مورد نظر :</label>
                <input type="text" class="widefat" id="input_text_1" name="text1"/>
                <br>
                <label for="input_text_2">جایگزین شود با :</label>
                <input type="text" class="widefat" id="input_text_2" name="text2"/>
				<?php echo '</div>'; ?>
                <div id="major-publishing-actions">
                    <div id="publishing-action">
                        <span class="spinner"></span>
						<?php submit_button( esc_attr( 'ذخیره حلقه' ), 'primary', 'submit', false, array( 'id' => 'save_loop_button' ) ); ?>
                    </div>
                    <div class="clear"></div>
            </form>
			<?php
		}

		public function save_translates() {

			check_ajax_referer( 'woocommerce_persian_save_translates', 'security' );

			$json = array(
				'status' => 'NO',
				'rand'   => mt_rand(),
				'msg'    => 'مشکلی هنگام افزودن حلقه رخ داد . لطفا مجددا تلاش کنید.'
			);

			if ( isset( $_POST['text1'], $_POST['text2'], $_POST['s'] ) ) {

				if ( ! empty( $_POST['text1'] ) ) {

					global $wpdb;

					$insert = $wpdb->insert( $this->table, array(
						'text1' => $_POST['text1'],
						'text2' => $_POST['text2']
					) );

					if ( $insert ) {

						$json['status'] = 'OK';
						$json['msg']    = sprintf( '<div id="setting-error-pw_msg_%d" class="updated settings-error notice is-dismissible"><p><strong>حلقه (%s => %s) با موفقیت افزوده شد.</strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">بستن این اعلان.</span></button></div>', $json['rand'], $_POST['text1'], $_POST['text2'] );

						$json['code'] = '';

						if ( empty( $_POST['s'] ) || array_search( $_POST['s'], array(
								$_POST['text1'],
								$_POST['text2']
							) ) !== false
						) {
							$json['code'] = sprintf( '<tr id="PW_item_%1$d" data-id="%1$d"><th scope="row" class="check-column"><input name="text_delete_id[]" value="%1$d" type="checkbox"></th><td class="text1 column-text1 has-row-actions column-primary" data-colname="حلقه‌ی اصلی">%2$s<button type="button" class="toggle-row"><span class="screen-reader-text">نمایش جزئیات بیشتر</span></button></td><td class="text2 column-text2" data-colname="حلقه‌ی جایگزین شده">%3$s</td></tr>', $wpdb->insert_id, $_POST['text1'], $_POST['text2'] );
						}

						$search = empty( $_POST['s'] ) ? '' : sprintf( ' WHERE text1 LIKE "%%%1$s%%" OR text2 LIKE "%%%1$s%%"', sanitize_text_field( $_POST['s'] ) );

						$json['count'] = $wpdb->get_var( "SELECT COUNT(*) FROM $this->table{$search}" ) . " مورد";

					} else {
						$json['msg'] = sprintf( '<div id="setting-error-pw_msg_%d" class="error settings-error notice is-dismissible"><p><strong>خطایی در زمان افزودن حلقه (%s => %s) به دیتابیس رخ داده است. لطفا مجددا تلاش کنید</strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">بستن این اعلان.</span></button></div>', $json['rand'], $_POST['text1'], $_POST['text2'] );
					}
				} else {
					$json['msg'] = sprintf( '<div id="setting-error-pw_msg_%d" class="error settings-error notice is-dismissible"><p><strong>پر کردن فیلد کلمه‌ی مورد نظر اجباری می باشد.</strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">بستن این اعلان.</span></button></div>', $json['rand'] );
				}
			}

			die( json_encode( $json ) );
		}

		public function search_delete() {

			if ( empty( $_GET['page'] ) || $_GET['page'] != 'persian-wc' ) {
				return;
			}

			if ( isset( $_POST['s'] ) ) {
				if ( empty( $_POST['s'] ) && isset( $_GET['s'] ) ) {
					wp_redirect( remove_query_arg( array( 's' ), stripslashes( $_SERVER['REQUEST_URI'] ) ) );
					exit;
				} else if ( ! empty( $_POST['s'] ) ) {
					wp_redirect( add_query_arg( array( 's' => $_POST['s'] ), stripslashes( $_SERVER['REQUEST_URI'] ) ) );
					exit;
				}
			}

			if ( isset( $_POST['action'], $_POST['text_delete_id'] ) && check_admin_referer( 'delete_item', 'woocommerce_persian_translate_nonce' ) ) {
				$success = $failed = 0;
				foreach ( $_POST['text_delete_id'] as $delete_id ) {
					global $wpdb;
					$delete = $wpdb->delete( $wpdb->prefix . 'woocommerce_ir', array( 'id' => intval( $delete_id ) ) );
					if ( $delete ) {
						$success ++;
					} else {
						$failed ++;
					}
				}

				if ( $success ) {
					add_settings_error( 'delete_text', 'pw_msg', sprintf( '%d حلقه با موفقیت حذف شد.', $success ), 'updated' );
				}

				if ( $failed ) {
					add_settings_error( 'delete_text', 'pw_msg', sprintf( 'حذف %d حلقه با شکست مواجه شد.', $failed ) );
				}
			}
		}
	}
endif;
PW()->translate = new Persian_Woocommerce_Translate();

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Persian_Woocommerce_Translate_List_Table extends WP_List_Table {

	private $data = array();

	public function __construct() {

		global $wpdb;

		$search  = '';
		$perPage = 20;
		$db_page = ( $this->get_pagenum() - 1 ) * $perPage;

		if ( isset( $_GET['s'] ) && ! empty( $_GET['s'] ) ) {
			$s      = sanitize_text_field( $_GET['s'] );
			$search = " WHERE text1 LIKE '%{$s}%' OR text2 LIKE '%{$s}%'";
		}

		$this->data = $wpdb->get_results( "SELECT * FROM `{$wpdb->prefix}woocommerce_ir`{$search} ORDER BY id DESC LIMIT $db_page, $perPage;", ARRAY_A );

		$this->set_pagination_args( array(
			'total_items' => $wpdb->get_var( "SELECT COUNT(*) FROM `{$wpdb->prefix}woocommerce_ir`{$search};" ),
			'per_page'    => $perPage
		) );

		parent::__construct( array(
			'singular' => 'text',
			'plural'   => 'texts',
			'ajax'     => false
		) );
	}

	public function column_default( $item, $column_name ) {
		return isset( $item[ $column_name ] ) ? $item[ $column_name ] : '';
	}

	public function get_columns() {
		return array(
			'cb'    => '<input type="checkbox" />',
			'text1' => 'کلمه اصلی',
			'text2' => 'کلمه جایگزین شده',
		);
	}

	public function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="text_delete_id[]" value="%s" />', $item['id'] );
	}

	public function prepare_items() {
		$this->_column_headers = array( $this->get_columns(), array(), array() );
		$this->items           = $this->data;;
	}

	public function single_row( $item ) {
		echo "<tr id='PW_item_{$item['id']}' data-id='{$item['id']}' >";
		$this->single_row_columns( $item );
		echo '</tr>';
	}

	public function get_bulk_actions() {
		return array( 'delete' => 'حذف' );
	}

	public function search_box( $text, $input_id ) {
		?>
        <p class="search-box">
            <label class="screen-reader-text" for="<?php echo $input_id; ?>"><?php echo $text; ?>:</label>
            <input type="search" id="<?php echo $input_id; ?>" name="s" value="<?php _admin_search_query(); ?>"/>
			<?php submit_button( $text, 'button', '', false, array( 'id' => 'search-submit' ) ); ?>
        </p>
		<?php
	}
}