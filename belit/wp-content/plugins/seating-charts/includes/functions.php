<?php

/**
 * Shortcode option for the shortcode builder
 */
function show_tc_seat_chart_attributes() {
    ?>
    <table id="tc-seat-chart-shortcode" class="shortcode-table" style="display:none">
        <tr>
            <th scope="row"><?php _e('Select a Seating Chart', 'tcsc'); ?></th>
            <td>
                <?php
                $args = array(
                    'post_type' => 'tc_seat_charts',
                    'post_status' => 'publish',
                    'posts_per_page' => -1,
                    'no_found_rows' => true
                );

                $seat_charts = get_posts($args);
                ?>
                <select name="id">
                    <?php
                    foreach ($seat_charts as $seat_chart) {
                        ?>
                        <option value="<?php echo esc_attr($seat_chart->ID); ?>"><?php echo $seat_chart->post_title; ?></option>
                        <?php
                    }
                    ?>
                </select>
            </td>
        </tr>

        <tr>
            <th scope="row"><?php _e('Show Legend', 'tcsc'); ?></th>
            <td>
                <select name="show_legend">
                    <option value="true"><?php _e('Yes', 'tcsc'); ?></option>
                    <option value="false"><?php _e('No', 'tcsc'); ?></option>
                </select>
            </td>
        </tr>

        <tr>
            <th scope="row"><?php _e('Button Title', 'tcsc'); ?></th>
            <td>
                <input type="text" name="button_title" value="<?php _e('Pick your seat(s)', 'tcsc'); ?>">
            </td>
        </tr>

        <tr>
            <th scope="row"><?php _e('Subtotal Title', 'tcsc'); ?></th>
            <td>
                <input type="text" name="subtotal_title" value="<?php _e('Subtotal', 'tcsc'); ?>">
            </td>
        </tr>

        <tr>
            <th scope="row"><?php _e('Cart Title', 'tcsc'); ?></th>
            <td>
                <input type="text" name="cart_title" value="<?php _e('Go to Cart', 'tcsc'); ?>">
            </td>
        </tr>
    </table>
    <?php
}
?>