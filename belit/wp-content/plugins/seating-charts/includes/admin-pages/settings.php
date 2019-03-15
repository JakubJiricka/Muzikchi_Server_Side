<div class="tc-settings-wrap tc-tables-settings" id="tc-settings">

    <div class="tc-title-wrap">
        <h4><?php _e('Settings', 'tcsc') ?></h4>
    </div><!-- .tc-title-wrap -->

    <div class="tc-options-wrap">

        <div class="tc-input-wrap">
            <label>
                <?php _e('Chart Title', 'tcsc'); ?>
            </label>
            <?php
            $chart_title = get_the_title($post->ID);
            ?>
            <input type="text" id="tc_chart_title" name="tc_chart_title" value="<?php echo esc_attr($chart_title); ?>" />
        </div><!-- .tc-input-wrap -->

        <div class="tc-input-wrap tc-seat-select">

            <div class="tc-event-wrap">   
                <input type="hidden" name="tc_init_event_id" id="tc_init_event_id" value="<?php echo esc_attr(get_post_meta($post->ID, 'event_name', true)); ?>" />
                <?php
                tc_get_events('event_name', $post->ID);
                ?>          
            </div><!-- .tc-event-wrap -->

        </div><!-- .tc-seat-select -->


        <div class="tc-clear"></div>
        
        <button class="tc-change-button" type="submit"><?php _e('Change', 'tcsc'); ?></button>

        <div class="tc-clear"></div>

    </div> <!-- .tc-options-wrap -->

    <?php
    TC_Seat_Chart::get_reserved_seats($post->ID);
    TC_Seat_Chart::set_event_ticket_types_colors($post->ID);
    ?>

</div><!-- .tc-settings-wrap -->