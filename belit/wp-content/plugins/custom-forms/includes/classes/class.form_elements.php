<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

if (!class_exists('TC_Form_Elements')) {

    class TC_Form_Elements {

        var $id = '';
        var $form_metas = '';
        var $element_title = '';
        var $post_type = 'tc_form_fields';

        function __construct($id = '') {
            $this->id = $id;

            if ($id !== '') {
                $this->form_metas = tc_get_post_meta_all($id);
            }

            $this->on_creation();
        }

        function on_creation() {
            
        }

        function ticket_content() {
            
        }

        function save($form_id) {
            
        }

        function maybe_fix_form_element_name($element_name) {
            if (substr($element_name, 0, 6) == "tc_ff_") {
                //do nothing, we already have a field name with required prefix
            } else {
                //prepend required prefix
                $element_name = 'tc_ff_' . $element_name;
            }
            return $element_name;
        }

        function get_the_content_by_id($post_id = 0, $more_link_text = null, $stripteaser = false) {
            global $post;
            $post = get_post($post_id);
            setup_postdata($post, $more_link_text, $stripteaser);
            return strip_tags(get_the_content());
            wp_reset_postdata($post);
        }

        function standard_fields($element_name) {
            $this->standard_field_name($element_name);
            $this->standard_field_label($element_name);
            $this->standard_field_placeholder($element_name);
            $this->standard_field_description($element_name);
            $this->standard_field_required($element_name);
            $this->standard_field_export($element_name);
            $this->standard_field_as_ticket_template($this->element_name);
        }

        function common_fields($element_name) {
            ?>
            <input type="hidden" name="<?php echo $element_name; ?>[field_row][]" class="field_row" value="<?php echo esc_attr(isset($this->id) ? get_post_meta($this->id, 'row', true) : '' ); ?>" />
            <input type="hidden" name="<?php echo $element_name; ?>[field_order][]" class="field_order" value="<?php echo esc_attr(isset($this->id) ? get_post_meta($this->id, 'order', true) : '' ); ?>" />
            <input type="hidden" name="<?php echo $element_name; ?>[field_post_id][]" class="field_post_id" value="<?php echo esc_attr(isset($this->id) ? $this->id : '' ); ?>" />
            <?php
            //$this->standard_field_admin_order_column($element_name);
            $this->standard_field_admin_order_details($element_name);
            $this->standard_field_show_in_checkin_app($element_name);
            //$this->standard_field_ios_app_details($element_name);
        }

        function standard_field_label($element_name, $front = false) {
            global $wp_query;
            //if ( (isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'tc_ticket_templates') ) {
            $post = get_post($this->id);
            $title = isset($post->post_title) ? $post->post_title : '';
            if ($front === false) {
                ?>
                <label><?php _e('Field Label', 'tc'); ?></label>
                <input type="text" name="<?php echo $element_name; ?>[field_label][]" value="<?php echo esc_attr(isset($this->id) ? $title : '' ); ?>" />
                <?php
            } else {
                return isset($this->id) ? $title : '';
            }
            //}
        }

        function standard_field_name($element_name, $front = false) {
            if ($front === false) {
                ?>
                <!--<label><?php _e('Field Name', 'tc'); ?><span class="required_field">*</span></label>-->
                <input type="hidden" name="<?php echo $element_name; ?>[field_name][]" value="<?php echo esc_attr(isset($this->id) ? get_post_meta($this->id, 'name', true) : '' ); ?>" />
                <?php
            } else {
                return isset($this->id) ? get_post_meta($this->id, 'name', true) : '';
            }
        }

        function standard_field_placeholder($element_name, $front = false) {
            if ($front === false) {
                ?>
                <label><?php _e('Field Placeholder', 'tc'); ?></label>
                <input type="text" name="<?php echo $element_name; ?>[field_placeholder][]" value="<?php echo esc_attr(isset($this->id) ? get_post_meta($this->id, 'placeholder', true) : '' ); ?>" />
                <?php
            } else {
                return isset($this->id) ? get_post_meta($this->id, 'placeholder', true) : '';
            }
        }

        function standard_field_description($element_name, $front = false) {
            if ($front === false) {
                ?>
                <label><?php _e('Field Description', 'tc'); ?></label>
                <input type="text" name="<?php echo $element_name; ?>[field_description][]" value="<?php echo esc_attr(isset($this->id) ? get_post_meta($this->id, 'description', true) : '' ); ?>" />
                <?php
            } else {
                return isset($this->id) ? get_post_meta($this->id, 'description', true) : '';
            }
        }

        function standard_field_required($element_name, $front = false) {
            $checked_val = get_post_meta($this->id, 'required', true);
            if (isset($checked_val) && $checked_val == 1) {
                $checked = 1;
            } else {
                $checked = 0;
            }
            if ($front === false) {
                ?>
                <label>
                    <input type="checkbox" class="required_check"  <?php checked(1, $checked, true); ?>><span class="tc-login-image"></span>
                    <input type="hidden" class="field_required" name="<?php echo $element_name; ?>[field_required][]" value="<?php echo $checked; ?>" />
                    <?php _e('Required Input', 'tc'); ?>
                </label>
                <?php
            } else {
                if ($checked == 1) {
                    return true;
                } else {
                    return false;
                }
            }
        }

        function standard_field_export($element_name, $front = false) {
            $checked_val = get_post_meta($this->id, 'export', true);
            if (isset($checked_val) && $checked_val == 1) {
                $checked = 1;
            } else {
                $checked = 0;
            }
            if ($front === false) {
                ?>
                <label>
                    <input type="checkbox" class="export_check" <?php checked(1, $checked, true); ?> /><span class="tc-login-image"></span>
                    <input type="hidden" class="field_export" name="<?php echo $element_name; ?>[field_export][]" value="<?php echo $checked; ?>" />
                    <?php _e('Allow Field Export', 'tc'); ?>
                </label>
                <?php
            } else {
                if ($checked == 1) {
                    return true;
                } else {
                    return false;
                }
            }
        }

        function standard_field_show_in_checkin_app($element_name, $front = false) {
            $checked_val = get_post_meta($this->id, 'show_in_checkin_app', true);
            if (isset($checked_val) && $checked_val == 1) {
                $checked = 1;
            } else {
                $checked = 0;
            }
            if ($front === false) {
                ?>
                <label>
                    <input type="checkbox" class="export_check" <?php checked(1, $checked, true); ?> /><span class="tc-login-image"></span>
                    <input type="hidden" class="field_export" name="<?php echo $element_name; ?>[field_show_in_checkin_app][]" value="<?php echo $checked; ?>" />
                    <?php _e('Show in check-in app', 'tc'); ?>
                </label>
                <?php
            } else {
                if ($checked == 1) {
                    return true;
                } else {
                    return false;
                }
            }
        }

        function standard_field_as_ticket_template($element_name, $front = false) {
            $checked_val = get_post_meta($this->id, 'as_ticket_template', true);
            if (isset($checked_val) && $checked_val == 1) {
                $checked = 1;
            } else {
                $checked = 0;
            }
            if ($front === false) {
                ?>
                <label>
                    <input type="checkbox" class="as_ticket_template_check" <?php checked(1, $checked, true); ?> /><span class="tc-login-image"></span>
                    <input type="hidden" class="field_as_ticket_template" name="<?php echo $element_name; ?>[field_as_ticket_template][]" value="<?php echo $checked; ?>" />
                    <?php _e('Create Ticket Template Element', 'tc'); ?>
                </label>
                <?php
            } else {
                if ($checked == 1) {
                    return true;
                } else {
                    return false;
                }
            }
        }

        function standard_field_choice_values($element_name, $front = false) {
            if ($front === false) {
                ?>
                <label><?php _e('Values (separated by comma)', 'tc'); ?><span class="required_field">*</span></label>
                <input type="text" name="<?php echo $element_name; ?>[field_values][]" value="<?php echo esc_attr(isset($this->id) && !empty($this->id) ? $this->get_the_content_by_id($this->id) : '' ); ?>" />
                <?php
            } else {
                return isset($this->id) ? $this->get_the_content_by_id($this->id) : 'test';
            }
        }

        function standard_field_choice_default_values($element_name, $front = false) {
            if ($front === false) {
                ?>
                <label><?php _e('Default Value (which will be selected by default)', 'tc'); ?></label><span class="tc-login-image"></span>
                <input type="text" name="<?php echo $element_name; ?>[field_default_values][]" value="<?php echo esc_attr(isset($this->id) ? get_post_meta($this->id, 'default_value', true) : '' ); ?>" />
                <?php
            } else {
                return isset($this->id) ? get_post_meta($this->id, 'default_value', true) : '';
            }
        }

        function standard_field_select_function($element_name, $front = false) {
            if ($front === false) {
                ?>
                <label><?php _e('Function Name (PHP function to retrieve values)<br /> <i>Use only if you do not want to put Values manually.</i>', 'tc'); ?></label>
                <input type="text" name="<?php echo $element_name; ?>[field_function_name][]" value="<?php echo esc_attr(isset($this->id) ? get_post_meta($this->id, 'select_function_name', true) : '' ); ?>" />
                <?php
            } else {
                return isset($this->id) ? get_post_meta($this->id, 'select_function_name', true) : '';
            }
        }

        function standard_field_admin_order_column($element_name, $front = false) {
            $checked_val = get_post_meta($this->id, 'order_column', true);
            if (isset($checked_val) && $checked_val == 1) {
                $checked = 1;
            } else {
                $checked = 0;
            }
            if ($front === false) {
                ?>
                <label><?php _e('Show in the admin order list as a new column', 'tc'); ?></label>
                <input type="checkbox" class="order_column_check" <?php checked(1, $checked, true); ?> />
                <input type="hidden" class="field_order_column" name="<?php echo $element_name; ?>[field_order_column][]" value="<?php echo $checked; ?>" />
                <?php
            } else {
                if ($checked == 1) {
                    return true;
                } else {
                    return false;
                }
            }
        }

        function standard_field_admin_order_details($element_name, $front = false) {
            $checked_val = get_post_meta($this->id, 'order_details', true);
            if (isset($checked_val) && $checked_val == 1) {
                $checked = 1;
            } else {
                $checked = 0;
            }
            if ($front === false) {
                ?>
                <label>
                    <input type="checkbox" class="order_details_check" <?php checked(1, $checked, true); ?> /><span class="tc-login-image"></span>
                    <input type="hidden" class="field_order_details" name="<?php echo $element_name; ?>[field_order_details][]" value="<?php echo $checked; ?>" />
                    <?php _e('Show on the admin order detail page', 'tc'); ?>
                </label>
                <?php
            } else {
                if ($checked == 1) {
                    return true;
                } else {
                    return false;
                }
            }
        }

        function standard_field_ios_app_details($element_name, $front = false) {
            $checked_val = get_post_meta($this->id, 'ios_app', true);
            if (isset($checked_val) && $checked_val == 1) {
                $checked = 1;
            } else {
                $checked = 0;
            }
            if ($front === false) {
                ?>
                <label><?php _e('Show in the iPhone app', 'tc'); ?></label>
                <input type="checkbox" class="ios_app_check" <?php checked(1, $checked, true); ?> />
                <input type="hidden" class="field_ios_app" name="<?php echo $element_name; ?>[field_ios_app][]" value="<?php echo $checked; ?>" />
                <?php
            } else {
                if ($checked == 1) {
                    return true;
                } else {
                    return false;
                }
            }
        }

    }

}

function tc_register_form_element($class_name, $element_title) {
    global $tc_form_elements;

    if (!is_array($tc_form_elements)) {
        $tc_form_elements = array();
    }

    if (class_exists($class_name)) {
        $tc_form_elements[] = array($class_name, $element_title);
    } else {
        return false;
    }
}
?>