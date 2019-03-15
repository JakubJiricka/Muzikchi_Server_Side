<div class="tc_cart_errors">
    <?php
    echo apply_filters('tc_cart_errors', '');
    if (isset($_SESSION['tc_cart_ticket_error_ids'])) {
        $tc_ticket_names = '';
        $tc_ticket_count = count($_SESSION['tc_cart_ticket_error_ids']);
        $tc_ticket_foreach = 1;
        echo '<ul>';
        foreach ($_SESSION['tc_cart_ticket_error_ids'] as $tc_ticket_id) {
            $tc_ticket_name = get_the_title($tc_ticket_id);
            echo '<li>' . sprintf(__('%s has been sold out.', 'tc'), $tc_ticket_name) . '</li>';
        }
        echo '</ul>';

        unset($_SESSION['tc_cart_ticket_error_ids']);
    }
    ?>
</div>
<?php
global $tc;

if (isset($_SESSION['tc_cart_subtotal']) && isset($_SESSION['tc_discount_code'])) {
    $discount = new TC_Discounts();
    $discount->discounted_cart_total($_SESSION['tc_cart_subtotal'], $_SESSION['tc_discount_code']);
}

if (isset($_SESSION['tc_discount_code'])) {
    $discount->discounted_cart_total(false, $_SESSION['tc_discount_code']);
}

$cart_contents = $tc->get_cart_cookie();
if(isset($_SESSION['tc_remove_from_cart'])){
    foreach($_SESSION['tc_remove_from_cart'] as $tc_remove_id){
        unset($cart_contents[ (int) $tc_remove_id]);
        unset($_SESSION['tc_remove_from_cart']);
    }
}
$tc_general_settings = get_option('tc_general_setting', false);
if (isset($tc_general_settings['force_login']) && $tc_general_settings['force_login'] == 'yes' && !is_user_logged_in()) {
    ?>
    <div class="force_login_message"><?php printf(__('Please %s to see this page', 'tc'), '<a href="' . apply_filters('tc_force_login_url', wp_login_url($tc->get_cart_slug(true)), $tc->get_cart_slug(true)) . '">' . __('Log In', 'tc') . '</a>'); ?></div>
    <?php
} else {
    if (!empty($cart_contents)) {
        ?>
        <form id="tickera_cart" method="post" class="tickera" name="tickera_cart">
            <input type="hidden" name="cart_action" id="cart_action" value="update_cart" />
            <div class="tc-container">
                <div class="tickera-checkout">
                    <table cellspacing="0" class="tickera_table" cellpadding="10">
                        <thead>
                            <tr>
                                <?php do_action('tc_cart_col_title_before_ticket_type'); ?>
                                <th><?php _e('Ticket Type', 'tc'); ?></th>
                                <?php do_action('tc_cart_col_title_before_ticket_price'); ?>
                                <th class="ticket-price-header"><?php _e('Ticket Price', 'tc'); ?></th>
                                <?php do_action('tc_cart_col_title_before_quantity'); ?>
                                <th><?php _e('Quantity', 'tc'); ?></th>
                                <?php do_action('tc_cart_col_title_before_total_price'); ?>
                                <th><?php _e('Subtotal', 'tc'); ?></th>
                                <?php do_action('tc_cart_col_title_after_total_price'); ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $cart_subtotal = 0;
                            foreach ($cart_contents as $ticket_type => $ordered_count) {
                                $ticket = new TC_Ticket($ticket_type);
                                $cart_subtotal = $cart_subtotal + (tc_get_ticket_price($ticket->details->ID) * $ordered_count);

                                if (!isset($_SESSION)) {
                                    session_start();
                                }
                                $_SESSION['cart_subtotal_pre'] = $cart_subtotal;
                                $editable_qty = apply_filters('tc_editable_quantity', true, $ticket_type, $ordered_count);
                                ?>
                                <tr>
                                    <?php do_action('tc_cart_col_value_before_ticket_type', $ticket_type, $ordered_count, tc_get_ticket_price($ticket->details->ID)); ?>
                                    <td class="ticket-type"><?php echo $ticket->details->post_title; ?> <?php do_action('tc_cart_col_after_ticket_type', $ticket); ?><input type="hidden" name="ticket_cart_id[]" value="<?php echo (int) $ticket_type; ?>"></td>
                                    <?php do_action('tc_cart_col_value_before_ticket_price', $ticket_type, $ordered_count, tc_get_ticket_price($ticket->details->ID)); ?>
                                    <td class="ticket-price"><span class="ticket_price"><?php echo apply_filters('tc_cart_currency_and_format', apply_filters('tc_cart_price_per_ticket', tc_get_ticket_price($ticket->details->ID), $ticket_type)); ?></span></td>
                                    <?php do_action('tc_cart_col_value_before_quantity', $ticket_type, $ordered_count, tc_get_ticket_price($ticket->details->ID)); ?>
                                    <td class="ticket-quantity ticket_quantity"><?php echo $editable_qty ? '' : $ordered_count; ?><?php if ($editable_qty) { ?><input class="tickera_button minus" type="button" value="-"><?php } ?><input type="<?php echo $editable_qty ? 'text' : 'hidden'; ?>" name="ticket_quantity[]" value="<?php echo (int) $ordered_count; ?>" class="quantity">  <?php if ($editable_qty) { ?><input class="tickera_button plus" type="button" value="+" /><?php } ?></td>
                                    <?php do_action('tc_cart_col_value_before_total_price', $ticket_type, $ordered_count, tc_get_ticket_price($ticket->details->ID)); ?>
                                    <td class="ticket-total"><span class="ticket_total"><?php echo apply_filters('tc_cart_currency_and_format', apply_filters('tc_cart_price_per_ticket_and_quantity', (tc_get_ticket_price($ticket->details->ID) * $ordered_count), $ticket_type, $ordered_count)); ?></span></td>
                                    <?php do_action('tc_cart_col_value_after_total_price', $ticket_type, $ordered_count, tc_get_ticket_price($ticket->details->ID)); ?>
                                </tr>
                            <?php } ?>
                            <tr class="last-table-row">
                                <td class="ticket-total-all" colspan="<?php echo apply_filters('tc_cart_table_colspan', '5'); ?>">
                                    <?php do_action('tc_cart_col_value_before_total_price_subtotal', apply_filters('tc_cart_subtotal', $cart_subtotal)); ?>
                                    <span class="total_item_title"><?php _e('SUBTOTAL: ', 'tc'); ?></span><span class="total_item_amount"><?php echo apply_filters('tc_cart_currency_and_format', apply_filters('tc_cart_subtotal', $cart_subtotal)); ?></span>
                                    <?php do_action('tc_cart_col_value_before_total_price_discount', apply_filters('tc_cart_discount', 0)); ?>
                                    <?php
                                    if (!isset($tc_general_settings['show_discount_field']) || (isset($tc_general_settings['show_discount_field']) && $tc_general_settings['show_discount_field'] == 'yes')) {
                                        ?>
                                        <span class="total_item_title"><?php _e('DISCOUNT: ', 'tc'); ?></span><span class="total_item_amount"><?php echo apply_filters('tc_cart_currency_and_format', apply_filters('tc_cart_discount', 0)); ?></span>
                                    <?php } ?>
                                    <?php do_action('tc_cart_col_value_before_total_price_total', apply_filters('tc_cart_total', $cart_subtotal)); ?>
                                    <span class="total_item_title cart_total_price_title"><?php _e('TOTAL: ', 'tc'); ?></span><span class="total_item_amount cart_total_price"><?php echo apply_filters('tc_cart_currency_and_format', apply_filters('tc_cart_total', $cart_subtotal)); ?></span>
                                    <?php do_action('tc_cart_col_value_after_total_price_total'); ?>
                                </td>
                                <?php do_action('tc_cart_col_value_after_total_price_total'); ?>
                            </tr>
                            <tr>
                                <td class="actions" colspan="<?php echo apply_filters('tc_cart_table_colspan', '5'); ?>">
                                    <?php do_action('tc_cart_before_discount_field'); ?>
                                    <?php
                                    if (!isset($tc_general_settings['show_discount_field']) || (isset($tc_general_settings['show_discount_field']) && $tc_general_settings['show_discount_field'] == 'yes')) {
                                        ?>
                                        <span class="coupon-code"><input type="text" name="coupon_code" id="coupon_code" placeholder="<?php _e("Discount Code", "tc"); ?>" class="coupon_code tickera-input-field" value="<?php echo esc_attr((isset($_POST['coupon_code']) && !empty($_POST['coupon_code']) ? esc_attr($_POST['coupon_code']) : (isset($_SESSION['tc_discount_code']) ? $_SESSION['tc_discount_code'] : ''))); ?>" /></span> <input type="submit" id="apply_coupon" value="<?php _e("Apply", "tc"); ?>" class="apply_coupon tickera-button"><span class="coupon-code-message"><?php echo apply_filters('tc_discount_code_message', ''); ?></span><?php do_action('tc_cart_after_discount_field'); ?>
                                    <?php } ?>
                                    <input type="submit" id="update_cart" value="<?php _e("Update Cart", "tc"); ?>" class="tickera_update tickera-button">
                                    <?php do_action('tc_cart_after_update_cart'); ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div><!-- tickera-checkout -->
            </div><!-- tc-container -->

            <div class="tickera_additional_info">

                <div class="tickera_buyer_info info_section">
                    <h3><?php _e('Buyer Info', 'tc'); ?></h3>
                    <?php
                    $buyer_form = new TC_Cart_Form();

                    $buyer_form_fields = $buyer_form->get_buyer_info_fields();

                    foreach ($buyer_form_fields as $field) {
                        if ($field['field_type'] == 'function') {
                            eval($field['function'] . '();');
                        }
                        ?><?php if ($field['field_type'] == 'text') { ?><div class="fields-wrap <?php
                            if (isset($field['field_class'])) {
                                echo $field['field_class'];
                            }
                            if (isset($field['validation_type'])) {
                                $validation_class = 'tc_validate_field_type_' . $field['validation_type'];
                            } else {
                                $validation_class = '';
                            }
                            ?>"><label><span><?php echo ($field['required'] ? '*' : ''); ?><?php echo $field['field_title']; ?></span><input type="<?php echo $field['field_type']; ?>" <?php
                                         if (isset($field['field_placeholder'])) {
                                             echo 'placeholder="' . esc_attr($field['field_placeholder']) . '"';
                                         }
                                         ?> class="buyer-field-<?php echo $field['field_type'] . ' ' . $validation_class; ?> tickera-input-field" value="<?php echo (isset($_POST['buyer_data_' . $field['field_name'] . '_' . $field['post_field_type']]) ? stripslashes(esc_attr($_POST['buyer_data_' . $field['field_name'] . '_' . $field['post_field_type']])) : $buyer_form->get_default_value($field)); ?>" name="<?php echo esc_attr('buyer_data_' . $field['field_name'] . '_' . $field['post_field_type']); ?>"></label><span class="description"><?php echo $field['field_description']; ?></span></div><!-- fields-wrap --><?php } ?>
                        <?php if ($field['field_type'] == 'textarea') { ?><div class="fields-wrap <?php
                            if (isset($field['field_class'])) {
                                echo $field['field_class'];
                            }
                            if (isset($field['validation_type'])) {
                                $validation_class = 'tc_validate_field_type_' . $field['validation_type'];
                            } else {
                                $validation_class = '';
                            }
                            ?>"><label><span><?php echo ($field['required'] ? '*' : ''); ?><?php echo $field['field_title']; ?></span><textarea class="buyer-field-<?php echo $field['field_type'] . ' ' . $validation_class; ?> tickera-input-field" <?php
                                 if (isset($field['field_placeholder'])) {
                                     echo 'placeholder="' . esc_attr($field['field_placeholder']) . '"';
                                 }
                                 ?> name="<?php echo esc_attr('buyer_data_' . $field['field_name'] . '_' . $field['post_field_type']); ?>"><?php echo (isset($_POST['buyer_data_' . $field['field_name'] . '_' . $field['post_field_type']]) ? stripslashes(esc_attr($_POST['buyer_data_' . $field['field_name'] . '_' . $field['post_field_type']])) : $buyer_form->get_default_value($field)); ?></textarea></label><span class="description"><?php echo $field['field_description']; ?></span></div><!-- fields-wrap --><?php } ?>

                        <?php if ($field['field_type'] == 'radio') { ?><div class="fields-wrap <?php
                            if (isset($field['field_class'])) {
                                echo $field['field_class'];
                            }
                            if (isset($field['validation_type'])) {
                                $validation_class = 'tc_validate_field_type_' . $field['validation_type'];
                            } else {
                                $validation_class = '';
                            }
                            ?>"><span><?php echo ($field['required'] ? '*' : ''); ?><?php echo $field['field_title']; ?></span>
                                     <?php
                                     if (isset($field['field_values'])) {
                                         $field_values = explode(',', $field['field_values']);
                                         foreach ($field_values as $field_value) {
                                             ?>
                                        <label><input type="<?php echo $field['field_type']; ?>" class="buyer-field-<?php echo $field['field_type'] . ' ' . $validation_class; ?> tickera-input-field" value="<?php echo trim(esc_attr($field_value)); ?>" name="<?php echo esc_attr('buyer_data_' . $field['field_name'] . '_' . $field['post_field_type']); ?>" <?php
                                            if (tc_cart_field_get_radio_value_cheched($field, $field_value, $field_values, esc_attr('buyer_data_' . $field['field_name'] . '_' . $field['post_field_type']))) {
                                                echo 'checked';
                                            }
                                            ?>><?php echo trim($field_value); ?></label><?php
                                        }
                                    }
                                    ?>
                                <span class="description"><?php echo $field['field_description']; ?></span></div><!-- fields-wrap --><?php } ?>

                        <?php if ($field['field_type'] == 'checkbox') { ?><div class="fields-wrap <?php
                            if (isset($field['field_class'])) {
                                echo $field['field_class'];
                            }
                            if (isset($field['validation_type'])) {
                                $validation_class = 'tc_validate_field_type_' . $field['validation_type'];
                            } else {
                                $validation_class = '';
                            }
                            ?>"><span><?php echo ($field['required'] ? '*' : ''); ?><?php echo $field['field_title']; ?></span>
                                     <?php
                                     if (isset($field['field_values'])) {
                                         $field_values = explode(',', $field['field_values']);
                                         foreach ($field_values as $field_value) {
                                             ?><label><input type="<?php echo $field['field_type']; ?>" class="buyer-field-<?php echo $field['field_type'] . ' ' . $validation_class; ?> tickera-input-field" value="<?php echo trim(esc_attr($field_value)); ?>" <?php
                                            if (tc_cart_field_get_checkbox_value_cheched($field, $field_value, $field_values, esc_attr('buyer_data_' . $field['field_name'] . '_' . $field['post_field_type']))) {
                                                echo 'checked';
                                            }
                                            ?>><?php echo trim($field_value); ?></label><?php
                                        }
                                        ?>
                                    <input type="hidden" class="checkbox_values" name="<?php echo esc_attr('buyer_data_' . $field['field_name'] . '_' . $field['post_field_type']); ?>" value="<?php echo tc_cart_field_get_checkbox_values(esc_attr('buyer_data_' . $field['field_name'] . '_' . $field['post_field_type'])); ?>" />
                                    <?php
                                }
                                ?>
                                <span class="description"><?php echo $field['field_description']; ?></span></div><!-- fields-wrap --><?php } ?>

                        <?php if ($field['field_type'] == 'select') { ?><div class="fields-wrap <?php
                            if (isset($field['field_class'])) {
                                echo $field['field_class'];
                            }
                            if (isset($field['validation_type'])) {
                                $validation_class = 'tc_validate_field_type_' . $field['validation_type'];
                            } else {
                                $validation_class = '';
                            }
                            ?>"><label><span><?php echo ($field['required'] ? '*' : ''); ?><?php echo $field['field_title']; ?></span>
                                    <select class="buyer-field-<?php echo $field['field_type'] . ' ' . $validation_class; ?> tickera-input-field" name="<?php echo esc_attr('buyer_data_' . $field['field_name'] . '_' . $field['post_field_type']); ?>">
                                        <option value=""><?php echo isset($field['field_placeholder']) ? esc_attr($field['field_placeholder']) : ''; ?></option>		
                                        <?php
                                        if (isset($field['field_values'])) {
                                            $field_values = explode(',', $field['field_values']);
                                            foreach ($field_values as $field_value) {
                                                ?>
                                                <option value="<?php echo trim(esc_attr($field_value)); ?>" <?php
                                                if (tc_cart_field_get_option_value_selected($field, $field_value, esc_attr('buyer_data_' . $field['field_name'] . '_' . $field['post_field_type']))) {
                                                    echo 'selected';
                                                }
                                                ?>><?php echo trim($field_value); ?>
                                                </option>
                                                <?php
                                            }
                                        }
                                        ?>
                                    </select>	
                                </label><span class="description"><?php echo $field['field_description']; ?></span></div><!-- fields-wrap --><?php } ?>

                        <?php if ($field['required']) { ?><input type="hidden" name="tc_cart_required[]" value="<?php echo esc_attr('buyer_data_' . $field['field_name'] . '_' . $field['post_field_type']); ?>" /><?php } ?>
                    <?php }//buyer fields         ?>

                </div><!-- tickera_buyer_info -->  


                <?php
                if (!isset($tc_general_settings['show_owner_fields']) || (isset($tc_general_settings['show_owner_fields']) && $tc_general_settings['show_owner_fields'] == 'yes')) {
                    $show_owner_fields = true;
                } else {
                    $show_owner_fields = false;
                }
                ?>   
                <div class="tickera_owner_info info_section" <?php
                if (!$show_owner_fields) {
                    echo 'style="display: none"';
                }
                ?>>
                         <?php
                         $ticket_type_order = 1;
                         foreach ($cart_contents as $ticket_type => $ordered_count) {
                             $owner_form = new TC_Cart_Form($ticket_type);
                             $owner_form_fields = $owner_form->get_owner_info_fields($ticket_type);

                             $ticket = new TC_Ticket($ticket_type);
                             ?>
                        <h2><?php
                            do_action('tc_before_checkout_owner_info_ticket_title', $ticket_type, $cart_contents);
                            echo apply_filters('tc_checkout_owner_info_ticket_title', $ticket->details->post_title, $ticket_type, $cart_contents, false);
                            do_action('tc_after_checkout_owner_info_ticket_title', $ticket_type, $cart_contents);
                            ?></h2>
                        <?php
                        for ($i = 1; $i <= $ordered_count; $i++) {
                            $owner_index = $i - 1;
                            ?>																																																																											
                            <h5><?php
                                echo apply_filters('tc_cart_attendee_info_caption', sprintf(__('%s. Attendee Info', 'tc'), $i), $ticket, $owner_index);
                                ?></h5>
                            <?php do_action('tc_cart_before_attendee_info_wrap', $ticket, $owner_index); ?>
                            <div class="owner-info-wrap">
                                <?php foreach ($owner_form_fields as $field) { ?>

                                    <?php
                                    if ($field['field_type'] == 'function') {
                                        eval($field['function'] . '("' . $field['field_name'] . '"' . (isset($field['post_field_type']) ? ', "' . $field['post_field_type'] . '"' : '') . (isset($ticket_type) ? ',' . $ticket_type : '') . (isset($ordered_count) ? ',' . $ordered_count : '') . ');');
                                    }
                                    if ($show_owner_fields) {
                                        ?>
                                        <?php if ($field['field_type'] == 'text') { ?>
                                            <?php if ((isset($tc_general_settings['show_owner_email_field']) && $tc_general_settings['show_owner_email_field'] == 'yes' && $field['field_name'] == 'owner_email' ) || $field['field_name'] !== 'owner_email') { ?><div class="fields-wrap <?php
                                                if (isset($field['field_class'])) {
                                                    echo $field['field_class'];
                                                }
                                                if (isset($field['validation_type'])) {
                                                    $validation_class = 'tc_validate_field_type_' . $field['validation_type'];
                                                } else {
                                                    $validation_class = '';
                                                }
                                                $posted_name = esc_attr('owner_data_' . $field['field_name'] . '_' . $field['post_field_type']);
                                                if (isset($_POST[$posted_name])) {
                                                    $posted_value = $_POST[$posted_name];
                                                    $posted_value = isset($posted_value[$ticket_type][$owner_index]) ? $posted_value[$ticket_type][$owner_index] : '';
                                                } else {
                                                    $posted_value = '';
                                                }
                                                ?>"><label><span><?php echo ($field['required'] ? '*' : ''); ?><?php echo $field['field_title']; ?></span><input type="<?php echo $field['field_type']; ?>" <?php
                                                             if (isset($field['field_placeholder'])) {
                                                                 echo 'placeholder="' . esc_attr($field['field_placeholder']) . '"';
                                                             }
                                                             ?> class="owner-field-<?php echo $field['field_type'] . ' ' . $validation_class; ?> tickera-input-field tc-owner-field <?php if ($field['field_name'] == 'owner_email') { ?>tc_owner_email<?php } ?>" value="<?php echo stripslashes(esc_attr($posted_value)); ?>" name="<?php echo esc_attr('owner_data_' . $field['field_name'] . '_' . $field['post_field_type']); ?>[<?php echo $ticket_type; ?>][<?php echo $owner_index; ?>]"></label><span class="description"><?php echo $field['field_description']; ?></span></div><!-- fields-wrap --><?php } ?><?php } ?>

                                        <?php if ($field['field_type'] == 'textarea') { ?><div class="fields-wrap <?php
                                            if (isset($field['field_class'])) {
                                                echo $field['field_class'];
                                            }
                                            if (isset($field['validation_type'])) {
                                                $validation_class = 'tc_validate_field_type_' . $field['validation_type'];
                                            } else {
                                                $validation_class = '';
                                            }
                                            ?>"><label><span><?php echo ($field['required'] ? '*' : ''); ?><?php echo $field['field_title']; ?></span><textarea class="owner-field-<?php echo $field['field_type'] . ' ' . $validation_class; ?> tickera-input-field" <?php
                                                 if (isset($field['field_placeholder'])) {
                                                     echo 'placeholder="' . esc_attr($field['field_placeholder']) . '"';
                                                 }
                                                 $posted_name = esc_attr('owner_data_' . $field['field_name'] . '_' . $field['post_field_type']);
                                                 if (isset($_POST[$posted_name])) {
                                                     $posted_value = $_POST[$posted_name];
                                                     $posted_value = isset($posted_value[$ticket_type][$owner_index]) ? $posted_value[$ticket_type][$owner_index] : '';
                                                 } else {
                                                     $posted_value = '';
                                                 }
                                                 ?> name="<?php echo esc_attr('owner_data_' . $field['field_name'] . '_' . $field['post_field_type']); ?>[<?php echo $ticket_type; ?>][<?php echo $owner_index; ?>]"><?php echo stripslashes(esc_textarea($posted_value)); ?></textarea></label><span class="description"><?php echo $field['field_description']; ?></span></div><!-- fields-wrap --><?php } ?>

                                        <?php if ($field['field_type'] == 'radio') { ?><div class="fields-wrap <?php
                                            if (isset($field['field_class'])) {
                                                echo $field['field_class'];
                                            }
                                            if (isset($field['validation_type'])) {
                                                $validation_class = 'tc_validate_field_type_' . $field['validation_type'];
                                            } else {
                                                $validation_class = '';
                                            }
                                            ?>"><span><?php echo ($field['required'] ? '*' : ''); ?><?php echo $field['field_title']; ?></span>
                                                     <?php
                                                     if (isset($field['field_values'])) {
                                                         $field_values = explode(',', $field['field_values']);
                                                         foreach ($field_values as $field_value) {
                                                             ?>
                                                        <label><input type="<?php echo $field['field_type']; ?>" class="owner-field-<?php echo $field['field_type'] . ' ' . $validation_class; ?> tickera-input-field" value="<?php echo esc_attr(trim($field_value)); ?>" name="<?php echo esc_attr('owner_data_' . $field['field_name'] . '_' . $field['post_field_type']); ?>[<?php echo $ticket_type; ?>][<?php echo $owner_index; ?>]" <?php
                                                            if (tc_cart_field_get_radio_value_cheched($field, $field_value, $field_values, (esc_attr('owner_data_' . $field['field_name'] . '_' . $field['post_field_type'])), $ticket_type, $owner_index)) {
                                                                echo 'checked';
                                                            }
                                                            ?>><?php echo trim($field_value); ?></label><?php
                                                        }
                                                    }
                                                    ?>
                                                <span class="description"><?php echo $field['field_description']; ?></span></div><!-- fields-wrap --><?php } ?>

                                        <?php if ($field['field_type'] == 'checkbox') { ?><div class="fields-wrap <?php
                                            if (isset($field['field_class'])) {
                                                echo $field['field_class'];
                                            }
                                            if (isset($field['validation_type'])) {
                                                $validation_class = 'tc_validate_field_type_' . $field['validation_type'];
                                            } else {
                                                $validation_class = '';
                                            }
                                            ?>"><span><?php echo ($field['required'] ? '*' : ''); ?><?php echo $field['field_title']; ?></span>
                                                     <?php
                                                     if (isset($field['field_values'])) {
                                                         $field_values = explode(',', $field['field_values']);
                                                         foreach ($field_values as $field_value) {
                                                             ?><label><input type="<?php echo $field['field_type']; ?>" class="owner-field-<?php echo $field['field_type'] . ' ' . $validation_class; ?> tickera-input-field" value="<?php echo esc_attr(trim($field_value)); ?>" <?php
                                                            if (tc_cart_field_get_checkbox_value_cheched($field, $field_value, $field_values, esc_attr('owner_data_' . $field['field_name'] . '_' . $field['post_field_type']), $ticket_type, $owner_index)) {
                                                                echo 'checked';
                                                            }
                                                            ?>><?php echo trim($field_value); ?></label><?php
                                                        }
                                                        ?>
                                                    <input type="hidden" class="checkbox_values" name="<?php echo esc_attr('owner_data_' . $field['field_name'] . '_' . $field['post_field_type']); ?>[<?php echo $ticket_type; ?>][<?php echo $owner_index; ?>]" value="<?php echo tc_cart_field_get_checkbox_values(esc_attr('owner_data_' . $field['field_name'] . '_' . $field['post_field_type']), $ticket_type, $owner_index); ?>" />
                                                    <?php
                                                }
                                                ?>
                                                <span class="description"><?php echo $field['field_description']; ?></span></div><!-- fields-wrap --><?php } ?>

                                        <?php if ($field['field_type'] == 'select') { ?><div class="fields-wrap <?php
                                            if (isset($field['field_class'])) {
                                                echo $field['field_class'];
                                            }
                                            if (isset($field['validation_type'])) {
                                                $validation_class = 'tc_validate_field_type_' . $field['validation_type'];
                                            } else {
                                                $validation_class = '';
                                            }
                                            ?>"><label><span><?php echo ($field['required'] ? '*' : ''); ?><?php echo $field['field_title']; ?></span>
                                                    <select class="owner-field-<?php echo $field['field_type'] . ' ' . $validation_class; ?> tickera-input-field" name="<?php echo esc_attr('owner_data_' . $field['field_name'] . '_' . $field['post_field_type']); ?>[<?php echo $ticket_type; ?>][<?php echo $owner_index; ?>]">
                                                        <option value=""><?php echo isset($field['field_placeholder']) ? esc_attr($field['field_placeholder']) : ''; ?></option>
                                                        <?php
                                                        if (isset($field['field_values'])) {
                                                            $field_values = explode(',', $field['field_values']);
                                                            foreach ($field_values as $field_value) {
                                                                ?>
                                                                <option value="<?php echo trim(esc_attr($field_value)); ?>" <?php
                                                                if (tc_cart_field_get_option_value_selected($field, $field_value, esc_attr('owner_data_' . $field['field_name'] . '_' . $field['post_field_type']), $ticket_type, $owner_index)) {
                                                                    echo 'selected';
                                                                }
                                                                ?>><?php echo trim($field_value); ?>
                                                                </option>
                                                                <?php
                                                            }
                                                        }
                                                        ?>
                                                    </select>	
                                                </label><span class="description"><?php echo $field['field_description']; ?></span></div><!-- fields-wrap --><?php } ?>

                                        <?php
                                        if ($field['required'] && $show_owner_fields) {
                                            if ($show_owner_fields) {
                                                ?>
                                                <input type="hidden" name="tc_cart_required[]" value="<?php echo esc_attr('owner_data_' . $field['field_name'] . '_' . $field['post_field_type']); ?>" />
                                                <?php
                                            }
                                        }
                                        ?>                      																																																																																																																																																													                                                                
                                        <!--<div class="tc-clearfix"></div>-->
                                        <?php
                                    } //if ( $show_owner_fields )
                                }
                                ?>		
                            </div><!-- owner-info-wrap -->																																																															                                                                                
                        <?php } $i++; ?>
                        <div class="tc-clearfix"></div>     



                    <?php } //foreach ( $cart_contents as $ticket_type => $ordered_count )       ?>


                        
                        
                </div><!-- tickera_owner_info -->
                <?php
                do_action('before_cart_submit');
                do_action('tc_before_cart_submit');
                do_action('tc_only_before_cart_submit');
                ?>
                <p><input type="submit" id="proceed_to_checkout" name="proceed_to_checkout" value="<?php _e("Proceed to Checkout", "tc"); ?>" class="tickera_checkout tickera-button"></p>
            </div><!-- tickera_additional_info -->
            <?php
        } else {
            do_action('tc_empty_cart');
            ?><div class="cart_empty_message"><?php _e("The cart is empty.", "tc"); ?></div>
            <?php
        }
    }
    ?>
    <?php wp_nonce_field('page_cart'); ?>
</form>