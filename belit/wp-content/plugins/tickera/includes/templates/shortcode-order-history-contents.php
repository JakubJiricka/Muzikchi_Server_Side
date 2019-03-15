<?php
if (!is_user_logged_in()) {
    printf(__('Please %s to see your order history.', 'tc'), '<a href="' . apply_filters('tc_force_login_url', wp_login_url(), wp_login_url()) . '">' . __('log in', 'tc') . '</a>');
} else {
    $user_orders = TC_Orders::get_user_orders();
    ?>
    <div class="tc-container">
        <?php
        if (count($user_orders) == 0) {
            _e('No Orders Found', 'tc');
        } else {
            ?>
            <table cellspacing="0" class="tickera_table" cellpadding="10">
                <tr>
                    <th><?php _e('Status', 'tc'); ?></th>
                    <?php do_action('tc_order_history_col_after_status'); ?>
                    <th><?php _e('Date', 'tc'); ?></th>
                    <?php do_action('tc_order_history_col_after_date'); ?>
                    <th><?php _e('Total', 'tc'); ?></th>
                    <?php do_action('tc_order_history_col_after_total'); ?>
                    <th><?php _e('Details', 'tc'); ?></th>
                    <?php do_action('tc_order_history_col_after_details'); ?>
                </tr>
                <?php
                foreach ($user_orders as $user_order) {
                    $order = new TC_Order($user_order->ID);
                    ?>
                    <tr>
                        <td>
                            <?php
                            $post_status = $order->details->post_status;
                            $init_post_status = $post_status;

                            if ($post_status == 'order_fraud') {
                                $color = "tc_order_fraud";
                            } else if ($post_status == 'order_received') {
                                $color = "tc_order_recieved"; //yellow
                            } else if ($post_status == 'order_paid') {
                                $color = "tc_order_paid";
                            } else if ($post_status == 'order_cancelled') {
                                $color = "tc_order_cancelled";
                            }
                            if ($post_status == 'order_fraud') {
                                $post_status = __('Held for Review', 'tc');
                            }

                            $post_status = ucwords(str_replace('_', ' ', $post_status));
                            echo sprintf(__('%1$s %2$s %3$s', 'tc'), '<span class="' . apply_filters('tc_order_history_color', $color, $init_post_status) . '">', __(ucwords($post_status), 'tc'), '</font>');
                            ?>
                        </td>
                        <?php do_action('tc_order_history_td_after_status', $user_order); ?>
                        <td>
                            <?php
                            echo tc_format_date($order->details->tc_order_date, true);
                            ?>
                        </td>
                        <?php do_action('tc_order_history_td_after_date', $user_order); ?>
                        <td>
                            <?php
                            echo apply_filters('tc_cart_currency_and_format', $order->details->tc_payment_info['total']);
                            ?>
                        </td>
                        <?php do_action('tc_order_history_td_after_total', $user_order); ?>
                        <td>
                            <?php
                            $order_status_url = $tc->tc_order_status_url($order, $order->details->tc_order_date, '', false);
                            ?>
                            <a href="<?php echo $order_status_url; ?>"><?php _e('Order Details', 'tc'); ?></a>
                        </td>
                        <?php do_action('tc_order_history_td_after_details', $user_order); ?>
                    </tr>
                    <?php
                }
                ?>
            </table>
        </div>
        <?php
    }
}
?>
