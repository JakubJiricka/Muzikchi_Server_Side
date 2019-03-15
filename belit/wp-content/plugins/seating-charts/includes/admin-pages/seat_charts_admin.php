<?php
global $post, $tc;

$post_object = get_page($post->ID);
$tc_chart_content = TC_Seat_Chart::get_seating_chart_html($post->ID);
$tc_admin_zoom_level = 1;
$tc_pan_position_left = 10;
$tc_pan_position_top = 10;
$tc_part_no = 0;

if (is_numeric(get_post_meta($post->ID, 'tc_admin_zoom_level', true))) {
    $tc_admin_zoom_level = get_post_meta($post->ID, 'tc_admin_zoom_level', true);
}

if (is_numeric(get_post_meta($post->ID, 'tc_pan_position_left', true))) {
    $tc_pan_position_left = get_post_meta($post->ID, 'tc_pan_position_left', true);
}

if (is_numeric(get_post_meta($post->ID, 'tc_pan_position_top', true))) {
    $tc_pan_position_top = get_post_meta($post->ID, 'tc_pan_position_top', true);
}

if (is_numeric(get_post_meta($post->ID, 'tc_part_no', true))) {
    $tc_part_no = get_post_meta($post->ID, 'tc_part_no', true);
}

//if (is_numeric(get_post_meta($post->ID, 'tc_current_screen_width', true))) {
$tc_current_screen_width = get_post_meta($post->ID, 'tc_current_screen_width', true);
//}
?>
<input type="hidden" id="tc_square_size" value="<?php echo esc_attr(TC_Seat_Chart::chart_measure()); ?>" />
<input type="hidden" id="tc_admin_zoom_level" name="tc_admin_zoom_level_post_meta" value="<?php echo esc_attr($tc_admin_zoom_level); ?>" />
<input type="hidden" id="tc_ticket_types" name="tc_ticket_types_post_meta" value="" />
<input type="hidden" id="tc_pan_position_left" name="tc_pan_position_left_post_meta" value="<?php echo esc_attr($tc_pan_position_left); ?>" />
<input type="hidden" id="tc_current_screen_width" name="tc_current_screen_width_post_meta" value="<?php echo esc_attr($tc_current_screen_width); ?>" />
<input type="hidden" id="tc_pan_position_top" name="tc_pan_position_top_post_meta" value="<?php echo esc_attr($tc_pan_position_top); ?>" />
<input type="hidden" id="tc_part_no" name="tc_part_no_post_meta" value="<?php echo esc_attr($tc_part_no); ?>" />
<textarea style="display:none;" id="tc_chart_content" name="tc_chart_content"><?php echo esc_html($tc_chart_content); ?></textarea>
<textarea style="display:none;" id="tc_chart_content_front" name="tc_chart_content_front"></textarea>
<div class="tc-sidebar">

    <div class="tc-menu-wrap">
        <ul>

            <li>
                <a href="#tc-settings">
                    <span class="tc-icon-settings"></span>
                </a>
            </li>

            <li>
                <a href="#tc-seats">
                    <span class="tc-icon-chair"></span>
                </a>
            </li>

            <li>
                <a href="#tc-standing">
                    <span class="tc-icon-feet"></span>
                </a>
            </li>

            <li>
                <a href="#tc-table">
                    <span class="tc-icon-table"></span>
                </a>
            </li>

            <li>
                <a href="#tc_element_widget">
                    <span class="tc-icon-resize"></span>
                </a>
            </li>

            <li>
                <a href="#tc_text_widget">
                    <span class="tc-icon-text"></span>
                </a>
            </li>

        </ul>

    </div>


    <?php include('ticket-type.php'); ?>
    <?php include('settings.php'); ?>
    <?php include('table.php'); ?>
    <?php include('labels.php') ?>
    <?php include('seating-group.php'); ?>
    <?php include('standing.php'); ?>
    <?php include('element.php'); ?>
    <?php include('text.php'); ?>

</div><!-- .tc-sidebar -->
<div class="tc-wrapper">
    <div id="tc-seating-dialog"><?php _e('Are you sure that you want to permanently delete the selected object?', 'tcsc'); ?></div>
    <div id="tc-seating-change-event-dialog"><?php _e('If you change the event all ticket types will be unassigned. Do you want to change the event?', 'tcsc'); ?></div>
    <div id="tc-seating-required-label-dialog"><?php _e('WARNING: Seats marked with black do not have assigned labels. Please select marked seats and add unique label to each.', 'tcsc'); ?></div>

    <div id="tc-seating-same-label-error-dialog"><?php _e('All seat labels must be unique. Please correct errors and try to assign label(s) again.', 'tcsc'); ?></div>

    <div class="tc-pan-wrapper"><?php echo $tc_chart_content; ?></div><!--tc-pan-wrapper-->

    <?php include('bottom-controls.php'); ?>

</div><!-- .tc-wrapper -->