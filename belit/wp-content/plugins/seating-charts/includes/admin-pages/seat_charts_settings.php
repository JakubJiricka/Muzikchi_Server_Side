<?php
global $TC_Seat_Chart;

$error_message = '';

if (isset($_POST['save_seat_charts_settings_nonce']) && wp_verify_nonce($_POST['save_seat_charts_settings_nonce'], 'save_seat_charts_settings')) {
    update_option('tc_seat_charts_settings', $_POST['tc_seat_charts']);

    $tc_seat_charts_settings = TC_Seat_Chart::get_settings();
}

$tc_seat_charts_settings = TC_Seat_Chart::get_settings();
?>
<div class="wrap tc_wrap">
    <?php if (!empty($error_message)) {
        ?>
        <div class="error"><p><?php echo $error_message; ?></p></div>
    <?php }
    ?>

    <div id="poststuff">
        <form action="" method="post" enctype="multipart/form-data">
            <div class="postbox">
                <h3 class="hndle"><span><?php _e('General Settings', 'tcsc'); ?></span></h3>
                <div class="inside">
                    <table class="form-table">
                        <tbody>
                            <tr>
                                <th scope="row"><label for="unavailable_seat_color"><?php _e('Unavailable Seat Color', 'tcsc') ?></label></th>
                                <td>
                                    <input name="tc_seat_charts[unavailable_seat_color]" type="text" id="unavailable_seat_color" value="<?php echo isset($tc_seat_charts_settings['unavailable_seat_color']) ? $tc_seat_charts_settings['unavailable_seat_color'] : '#aaaaaa'; ?>" class="regular-text">
                                    <p class="description"><?php _e('Color of unavailable seats', 'tcsc'); ?></p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row"><label for="reserved_seat_color"><?php _e('Reserved Seat Color', 'tcsc') ?></label></th>
                                <td>
                                    <input name="tc_seat_charts[reserved_seat_color]" type="text" id="reserved_seat_color" value="<?php echo isset($tc_seat_charts_settings['reserved_seat_color']) ? $tc_seat_charts_settings['reserved_seat_color'] : '#DCCBCB'; ?>" class="regular-text">
                                    <p class="description"><?php _e('Color of reserved seats', 'tcsc'); ?></p>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row"><label for="in_cart_seat_color"><?php _e('In Cart Seat Color', 'tcsc') ?></label></th>
                                <td>
                                    <input name="tc_seat_charts[in_cart_seat_color]" type="text" id="in_cart_seat_color" value="<?php echo isset($tc_seat_charts_settings['in_cart_seat_color']) ? $tc_seat_charts_settings['in_cart_seat_color'] : '#4187C9'; ?>" class="regular-text">
                                    <p class="description"><?php _e('Color of seats added in cart', 'tcsc'); ?></p>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row"><label for="in_others_cart_seat_color"><?php _e('In Other\'s Cart Seat Color', 'tcsc') ?></label></th>
                                <td>
                                    <input name="tc_seat_charts[in_others_cart_seat_color]" type="text" id="in_others_cart_seat_color" value="<?php echo isset($tc_seat_charts_settings['in_others_cart_seat_color']) ? $tc_seat_charts_settings['in_others_cart_seat_color'] : '#ec1244'; ?>" class="regular-text">
                                    <p class="description"><?php _e('Color of seats added in cart (by someone else)', 'tcsc'); ?></p>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row"><label for="checkedin_seat_color"><?php _e('Checked-in Seat Color', 'tcsc') ?></label></th>
                                <td>
                                    <input name="tc_seat_charts[checkedin_seat_color]" type="text" id="checkedin_seat_color" value="<?php echo isset($tc_seat_charts_settings['checkedin_seat_color']) ? $tc_seat_charts_settings['checkedin_seat_color'] : '#0085ba'; ?>" class="regular-text">
                                    <p class="description"><?php _e('Color of checked-in seats', 'tcsc'); ?></p>
                                </td>
                            </tr>


                        </tbody>
                    </table>
                </div>
            </div>

            <div class="postbox">
                <h3 class="hndle"><span><?php _e('Firebase Settings', 'tcsc'); ?></span></h3>
                <div class="inside">
                    <p class="description"><?php printf(__('Seating Charts add-on integrates with %sGoogle\'s Firebase service%s to do a realtime check and indication of seats in cart. Using Firebase integration is not required. Check the %ssetup instructions here%s.', 'tcsc'), '<a href="https://firebase.google.com/" target="_blank">', '</a>', '<a target="_blank" href="https://tickera.com/tickera-documentation/seating-charts/">', '</a>'); ?></p>
                    <table class="form-table">
                        <tbody>

                            <tr>
                                <?php
                                $use_firebase_integration = isset($tc_seat_charts_settings['user_firebase_integration']) ? $tc_seat_charts_settings['user_firebase_integration'] : '0';
                                ?>
                                <th scope="row"><label for="user_firebase_integration"><?php _e('Use Firebase Integration', 'tcsc') ?></label></th>
                                <td>
                                    <input type="radio" class="tc-seatings-chart-firebase" id="tc-seatings-firebase-checked" name="tc_seat_charts[user_firebase_integration]" <?php checked($use_firebase_integration, '1', true); ?> value="1"> <?php _e('Yes', 'tcsc'); ?>
                                    <input type="radio" class="tc-seatings-chart-firebase" name="tc_seat_charts[user_firebase_integration]" <?php checked($use_firebase_integration, '0', true); ?> value="0"> <?php _e('No', 'tcsc'); ?>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row"><label for="authDomain"><?php _e('Authentication Domain', 'tcsc') ?></label></th>
                                <td>
                                    <input name="tc_seat_charts[authDomain]" type="text" id="authDomain" value="<?php echo isset($tc_seat_charts_settings['authDomain']) ? $tc_seat_charts_settings['authDomain'] : ''; ?>" placeholder="<?php echo esc_attr(__('yourapp.firebaseapp.com', 'tcsc')); ?>" class="regular-text">
                                </td>
                            </tr>

                            <tr>
                                <th scope="row"><label for="authDomain"><?php _e('Database URL', 'tcsc') ?></label></th>
                                <td>
                                    <input name="tc_seat_charts[databaseURL]" type="text" id="databaseURL" value="<?php echo isset($tc_seat_charts_settings['databaseURL']) ? $tc_seat_charts_settings['databaseURL'] : ''; ?>" placeholder="<?php echo esc_attr(__('https://yourapp.firebaseio.com', 'tcsc')); ?>" class="regular-text">
                                </td>
                            </tr>

                            <tr>
                                <th scope="row"><label for="apiKey"><?php _e('API Key', 'tcsc') ?></label></th>
                                <td>
                                    <input name="tc_seat_charts[apiKey]" type="text" id="apiKey" value="<?php echo isset($tc_seat_charts_settings['apiKey']) ? $tc_seat_charts_settings['apiKey'] : ''; ?>" placeholder="<?php echo esc_attr(__('Your API Key here', 'tcsc')); ?>" class="regular-text">
                                </td>
                            </tr>

                            <tr>
                                <th scope="row"><label for="secret"><?php _e('Database Secret Key', 'tcsc') ?></label></th>
                                <td>
                                    <input name="tc_seat_charts[secret]" type="password" id="secret" value="<?php echo isset($tc_seat_charts_settings['secret']) ? $tc_seat_charts_settings['secret'] : ''; ?>" placeholder="<?php echo esc_attr(__('Your Secret Key here', 'tcsc')); ?>" class="regular-text">
                                </td>
                            </tr>
                        
                        </tbody>
                    </table>
                </div>
            </div>
            <?php wp_nonce_field('save_seat_charts_settings', 'save_seat_charts_settings_nonce');
            ?>
            <?php submit_button(); ?>
        </form>
    </div>
</div>