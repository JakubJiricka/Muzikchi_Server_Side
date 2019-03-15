<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Persian_Woocommerce_Address' ) ) :

	class Persian_Woocommerce_Address extends Persian_Woocommerce_Core {

		//public
		public $country, $states, $class;

		//private
		private $fields = array();

		private $selected_city = array();

		//private static
		private static $action_priority = 0;

		private static $iran_cities_page = false;

		private static $inline_script_printed = false;

		public function __construct() {

			$this->country = 'IR';
			$this->states  = array(
				'AL' => 'البرز',
				'AR' => 'اردبیل',
				'AE' => 'آذربایجان شرقی',
				'AW' => 'آذربایجان غربی',
				'BU' => 'بوشهر',
				'CM' => 'چهارمحال و بختیاری',
				'FA' => 'فارس',
				'GI' => 'گیلان',
				'GO' => 'گلستان',
				'HD' => 'همدان',
				'HG' => 'هرمزگان',
				'IL' => 'ایلام',
				'IS' => 'اصفهان',
				'KE' => 'کرمان',
				'BK' => 'کرمانشاه',
				'KS' => 'خراسان شمالی',
				'KV' => 'خراسان رضوی',
				'KJ' => 'خراسان جنوبی',
				'KZ' => 'خوزستان',
				'KB' => 'کهگیلویه و بویراحمد',
				'KD' => 'کردستان',
				'LO' => 'لرستان',
				'MK' => 'مرکزی',
				'MN' => 'مازندران',
				'QZ' => 'قزوین',
				'QM' => 'قم',
				'SM' => 'سمنان',
				'SB' => 'سیستان و بلوچستان',
				'TE' => 'تهران',
				'YA' => 'یزد',
				'ZA' => 'زنجان'
			);

			add_filter( 'woocommerce_get_country_locale', array( $this, 'locales' ) );
			add_filter( 'woocommerce_localisation_address_formats', array( $this, 'address_formats' ) );
			add_filter( 'woocommerce_states', array( $this, 'iran_states' ), 10, 1 );

			if ( PW()->get_options( 'enable_iran_cities' ) == 'yes' ) {

				if ( ( $class = array( 'first', 'last' ) ) && PW()->get_options( 'flip_state_city' ) == 'yes' ) {
					$class = array_reverse( $class );
				}
				$this->class = array( 'state' => $class[0], 'city' => $class[1] );

				add_filter( 'woocommerce_checkout_fields', array( $this, 'checkout_fields' ) );
				add_filter( 'woocommerce_billing_fields', array( $this, 'billing_fields' ) );
				add_filter( 'woocommerce_shipping_fields', array( $this, 'shipping_fields' ) );

				add_filter( 'woocommerce_form_field_billing_iran_cities', array( $this, 'iran_cities_field' ), 11, 4 );
				add_filter( 'woocommerce_form_field_shipping_iran_cities', array( $this, 'iran_cities_field' ), 11, 4 );

				add_action( 'wp_enqueue_scripts', array( $this, 'external_js' ) );
				add_action( 'wp_footer', array( $this, 'inline_js' ), 0 );
				add_action( 'wp_footer', array( $this, 'force_inline_js' ), 999999 );
			}
		}

		public function locales( $locales ) {
			$locales[ $this->country ] = array(
				'state'    => array( 'label' => __( 'Province', 'woocommerce' ) ),
				'postcode' => array( 'label' => __( 'Postcode', 'woocommerce' ) )
			);

			return $locales;
		}

		public function address_formats( $formats ) {
			$formats[ $this->country ] = "{first_name} {last_name}\n{company}\n{country}\n{state}\n{city}\n{address_1}\n{address_2}\n{postcode}";

			return $formats;
		}

		public function iran_states( $states ) {

			$states[ $this->country ] = $this->states;

			if ( PW()->get_options( 'allowed_states' ) == 'all' ) {
				return $states;
			}

			$selections = PW()->get_options( 'specific_allowed_states' );

			if ( is_array( $selections ) ) {
				$states[ $this->country ] = array_intersect_key( $this->states, array_flip( $selections ) );
			}

			return $states;
		}

		//--------------------------------------------
		public function checkout_fields( $fields ) {

			$this->fields = $fields;

			if ( is_checkout() ) {
				$types = array( 'billing', 'shipping' );
				foreach ( $types as $type ) {

					if ( ! empty( $fields[ $type ][ $type . '_city' ] ) ) {
						$fields[ $type ][ $type . '_city' ] = $this->modify_city_field( $fields[ $type ][ $type . '_city' ], $type );
					}

					if ( ! empty( $fields[ $type ][ $type . '_state' ] ) ) {
						$fields[ $type ][ $type . '_state' ] = $this->modify_state_field( $fields[ $type ][ $type . '_state' ], $type );
					}
				}
			}

			return $fields;
		}

		public function billing_fields( $fields ) {

			if ( is_wc_endpoint_url( 'edit-address' ) ) {
				$type = 'billing';
				if ( ! empty( $fields[ $type . '_city' ] ) ) {
					$fields[ $type . '_city' ] = $this->modify_city_field( $fields[ $type . '_city' ], $type );
				}
				if ( ! empty( $fields[ $type . '_state' ] ) ) {
					$fields[ $type . '_state' ] = $this->modify_state_field( $fields[ $type . '_state' ], $type );
				}
			}

			return $fields;
		}

		public function shipping_fields( $fields ) {

			if ( is_wc_endpoint_url( 'edit-address' ) ) {
				$type = 'shipping';
				if ( ! empty( $fields[ $type . '_city' ] ) ) {
					$fields[ $type . '_city' ] = $this->modify_city_field( $fields[ $type . '_city' ], $type );
				}
				if ( ! empty( $fields[ $type . '_state' ] ) ) {
					$fields[ $type . '_state' ] = $this->modify_state_field( $fields[ $type . '_state' ], $type );
				}
			}

			return $fields;
		}

		public function modify_state_field( $fields, $type ) {
			$classes = '';
			if ( ! empty( $fields['class'] ) && $classes = $fields['class'] ) {
				$classes = is_array( $classes ) ? implode( ',', $classes ) : $classes;
				$classes = str_ireplace( 'form-row-wide', 'form-row-' . $this->class['state'], $classes );
			}
			$fields['class'] = apply_filters( $type . '_iran_state_class', explode( ',', $classes ), $fields, $type );

			return $fields;
		}

		public function modify_city_field( $fields, $type ) {
			$classes = '';
			if ( ! empty( $fields['class'] ) && $classes = $fields['class'] ) {
				$classes = is_array( $classes ) ? implode( ',', $classes ) : $classes;
				$classes = str_ireplace( 'form-row-wide', 'form-row-' . $this->class['city'], $classes );
			}
			$fields['class']   = apply_filters( $type . '_iran_city_class', explode( ',', $classes ), $fields, $type );
			$fields['type']    = apply_filters( $type . '_iran_city_type', $type . '_iran_cities', $fields, $type );
			$fields['options'] = apply_filters( $type . '_iran_city_options', array( '' => '' ), $fields, $type );

			return $fields;
		}

		public function iran_cities_field( $field, $key, $args, $value ) {

			$type = explode( '_', $args['type'] );
			if ( ! empty( $value ) ) {
				$this->selected_city[] = $value . '_vsh_' . $type[0];
			}

			$required = $args['required'] ? ' <abbr class="required" title="' . esc_attr__( 'required', 'woocommerce' ) . '">*</abbr>' : '';

			$args['label_class'] = array();
			if ( is_string( $args['label_class'] ) ) {
				$args['label_class'] = array( $args['label_class'] );
			}

			if ( is_null( $value ) ) {
				$value = ! empty( $args['default'] ) ? $args['default'] : '';
			}

			$selected_value = $args['type'] . '_selected_value';
			global ${$selected_value};
			${$selected_value} = $value;

			$custom_attributes = array();
			if ( ! empty( $args['custom_attributes'] ) && is_array( $args['custom_attributes'] ) ) {
				foreach ( $args['custom_attributes'] as $attribute => $attribute_value ) {
					$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
				}
			}

			if ( ! empty( $args['validate'] ) ) {
				foreach ( $args['validate'] as $validate ) {
					$args['class'][] = 'validate-' . $validate;
				}
			}

			$args['placeholder'] = __( 'یک شهر انتخاب کنید', 'woocommerce' );

			$label_id        = $args['id'];
			$field_container = '<p class="form-row %1$s" id="%2$s">%3$s</p>';

			$field = '<select name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" class="state_select ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" ' . implode( ' ', $custom_attributes ) . ' placeholder="' . esc_attr( $args['placeholder'] ) . '"></select>';

			$field_html = '';

			if ( $args['label'] && 'checkbox' != $args['type'] ) {
				$field_html .= '<label for="' . esc_attr( $label_id ) . '" class="' . esc_attr( implode( ' ', $args['label_class'] ) ) . '">' . $args['label'] . $required . '</label>';
			}

			$field_html .= $field;

			if ( $args['description'] ) {
				$field_html .= '<span class="description">' . esc_attr( $args['description'] ) . '</span>';
			}

			$container_class = 'form-row ' . esc_attr( implode( ' ', $args['class'] ) );
			$container_id    = esc_attr( $args['id'] ) . '_field';

			$after = ! empty( $args['clear'] ) ? '<div class="clear"></div>' : '';

			$iran_cities = sprintf( $field_container, $container_class, $container_id, $field_html ) . $after;

			return apply_filters( 'iran_cities_field_select_input', $iran_cities, $field_container, $container_class, $container_id, $field_html, $field, $key, $args, $value, $after );
		}

		public function external_js() {
			wp_dequeue_script( 'pw-iran-cities' );
			wp_deregister_script( 'pw-iran-cities' );
			wp_register_script( 'pw-iran-cities', apply_filters( 'persian_woo_iran_cities', PW()->plugin_url( 'assets/js/iran_cities.min.js' ) ), false, PW_VERSION, true );

			if ( self::$iran_cities_page = ( is_checkout() || is_wc_endpoint_url( 'edit-address' ) ) ) {
				wp_enqueue_script( 'pw-iran-cities' );
			}
		}

		public function inline_js( $force_inline_js = false ) {
			if ( ! $force_inline_js ) {

				if ( self::$inline_script_printed == 'yes' || ! self::$iran_cities_page || self::$action_priority > 100 ) {
					return;
				}

				if ( ! ( wp_script_is( 'jquery', 'done' ) && ! wp_script_is( 'wc-country-select', 'done' ) ) ) {
					self::$action_priority += 5;
					add_action( 'wp_footer', array( $this, 'inline_js' ), self::$action_priority );

					return;
				}
			}

			self::$inline_script_printed = 'yes';
			$value_index                 = apply_filters( 'iran_cities_value_index', 0 );
			$types                       = array( 'billing', 'shipping' );
			?>
            <script type="text/javascript">
                if (!window.jQuery) {
                    alert("کتابخانه جیکوئری قبل از کدهای مربوط به شهرهای ایران لود نشده است!");
                }
                jQuery(function ($) {
					<?php foreach ($types as $type) :
					$selected_value = $type . '_iran_cities_selected_value';
					global ${$selected_value};
					$value = ! empty( ${$selected_value} ) ? ${$selected_value} : '';
					$placeholder = isset( $this->fields[ $type ][ $type . '_city' ]['placeholder'] ) ? $this->fields[ $type ][ $type . '_city' ]['placeholder'] : __( 'City', 'woocommerce' );

					$countries = 'get_' . str_replace( 'billing', 'allowed', $type ) . '_countries';
					$countries = WC()->countries->$countries();
					$iran_exist = isset( $countries[ strtoupper( $this->country ) ] ) || isset( $countries[ strtolower( $this->country ) ] ) || isset( $countries[ ucfirst( $this->country ) ] ) ? 'yes' : 'no';
					$just_iran = count( $countries ) == 1 && $iran_exist == 'yes' ? 'yes' : 'no';
					?>

                    var <?php echo $type; ?>_iran_exist = '<?php echo $iran_exist; ?>';
                    var <?php echo $type; ?>_just_iran = '<?php echo $just_iran; ?>';

                    $(document.body).on('change', '#<?php echo $type; ?>_state', function () {

                        if (<?php echo $type; ?>_iran_exist == 'yes' && (<?php echo $type; ?>_just_iran || $('#<?php echo $type; ?>_country').val() == '<?php echo $this->country ?>')) {

							<?php echo $type; ?>_cities = [];
							<?php echo $type; ?>_cities[0] = new Array('خطا در دریافت شهرها', '0');

                            if (typeof Persian_Woo_iranCities === "function")
								<?php echo $type; ?>_cities = Persian_Woo_iranCities('' + $('#<?php echo $type; ?>_state').val() + '');
                            else {
                                alert('تابع مربوط به شهرهای ایران یافت نمیشود. با مدیریت در میان بگذارید.');
                            }

							<?php echo $type; ?>_cities.sort(function (a, b) {
                                if (a[0] == b[0])
                                    return 0;
                                if (a[0] > b[0])
                                    return 1;
                                else
                                    return -1;
                            });
                            var options = '<option value="-1">انتخاب کنید</option>';
                            var j;
							<?php echo $type; ?>_selected = '';
                            for (j in <?php echo $type; ?>_cities) {
                                selected = '';
                                if (<?php echo $type; ?>_cities[j][<?php echo $value_index; ?>] == '<?php echo $value;?>') {
                                    selected = "selected";
									<?php echo $type; ?>_selected = '<?php echo $value;?>';
                                }
                                options += "<option value='" + <?php echo $type; ?>_cities[j][<?php echo $value_index; ?>] + "' " + selected + ">" + <?php echo $type; ?>_cities[j][0] + "</option>";
                            }
                            $('#<?php echo $type; ?>_city').empty();
                            if ($("#<?php echo $type; ?>_city").is('select')) {
                                $('#<?php echo $type; ?>_city').append(options);
                            }
                            $('#<?php echo $type; ?>_city').val(<?php echo $type; ?>_selected).trigger("change");
                        }
                    });
                    $('#<?php echo $type; ?>_state').trigger('change');

                    var <?php echo $type; ?>_city_select = $('#<?php echo $type; ?>_city_field').html();
                    var <?php echo $type; ?>_city_input = '<input id="<?php echo $type; ?>_city" name="<?php echo $type; ?>_city" type="text" class="input-text" value="<?php echo $value;?>" placeholder="<?php echo $placeholder;?>" />';

                    $(document.body).on('change', '#<?php echo $type; ?>_country', function () {
                        var is_iran = $('#<?php echo $type; ?>_country').val() == '<?php echo $this->country ?>' ? 'yes' : 'no';
                        set_iran_cities_field('<?php echo $type; ?>', is_iran);
                    });
                    $('#<?php echo $type; ?>_country').trigger('change');

                    if (!$('#<?php echo $type; ?>_country').length) {
                        set_iran_cities_field('<?php echo $type; ?>', <?php echo $type; ?>_just_iran);
                    }

                    $(document.body).on('change', '#<?php echo $type; ?>_city', function () {
                        if ($('#<?php echo $type; ?>_city').val() == 'لطفا استان خود را انتخاب کنید') {
                            if ($().select2 && $('#<?php echo $type; ?>_state').data('select2'))
                                $('#<?php echo $type; ?>_state').select2('open');
                        }
                    });

					<?php if ( is_checkout() ) {
					wc_enqueue_js( "if ($().select2 && $('#" . $type . "_city').data('select2') && !$('#" . $type . "_state').data('select2'))
                        $('#" . $type . "_state').select2();" );
				} ?>

					<?php endforeach; ?>
                    function set_iran_cities_field(type, iran) {
                        if (iran == 'yes') {
                            if (!$('#' + type + '_city').is('select')) {
                                $('#' + type + '_city_field').empty();
                                $('#' + type + '_city_field').html(eval(type + '_city_select'));
                                $('#' + type + '_state').val('').trigger("change");
                                $('#' + type + '_city').val('').trigger("change");
                            }
                        }
                        else {
                            $('#' + type + '_city_field').find('*').not('label').remove();
                            $('#' + type + '_city_field').append(eval(type + '_city_input'));
                            $('#' + type + '_state').val('').trigger("change");
                            $('#' + type + '_city').val('').trigger("change");
                        }
                    }

                });
            </script>
			<?php
		}

		public function force_inline_js() {
			if ( self::$inline_script_printed != 'yes' && self::$iran_cities_page && wp_script_is( 'jquery', 'done' ) ) {
				$this->inline_js( true );
			}
		}
	}
endif;
PW()->address = new Persian_Woocommerce_Address();