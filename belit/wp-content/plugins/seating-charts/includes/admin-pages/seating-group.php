<div class="tc-settings-wrap tc-tables-settings" id="tc-seats">

    <div class="tc-settings-wrap  tc-tables-settings" id="tc_seating_group_widget">

        <div class="tc-title-wrap">
            <h4><?php _e('Seating Group', 'tcsc') ?></h4>
        </div><!-- .tc-title-wrap -->

        <div class="tc-options-wrap">

            <div class="tc-input-wrap">
                <label>
                    <?php _e('Title', 'tcsc') ?>
                </label>
                <input type="text" id="tc_seating_group_title" />
            </div><!-- .tc-input-wrap -->

            <div class="tc-input-wrap tc-input-slider tc-seat-rows-slider">

                <label><?php _e('Rows', 'tcsc'); ?></label>

                <div class="tc-number-slider"></div>
                <input type="text" id="tc_seat_add_seats_rows" class="tc-slider-value" />                

            </div><!-- .tc-input-slider -->


            <div class="tc-input-wrap tc-input-slider tc-seat-cols-slider">

                <label><?php _e('Columns', 'tcsc'); ?></label>

                <div class="tc-number-slider"></div>
                <input type="text" id="tc_seat_add_seats_cols" class="tc-slider-value" />                

            </div><!-- .tc-input-slider -->

            <div class="tc-clear"></div>

            <div class="tc_seat_add_controls">
                <button class="tc-change-button" type="button" id="tc_add_seats_button"><?php _e('Create', 'tcsc') ?></button>
            </div>

            <div class="tc_seat_edit_controls">
                <button class="tc-edit-button" type="button" id="tc_edit_seats_button"><?php _e('Edit', 'tcsc'); ?></button>
                <button class="tc-cancel-button" id='tc_cancel_seat_button'><?php _e('Cancel', 'tcsc'); ?></button>
            </div>

            <div class="tc-clear"></div>

        </div> <!-- .tc-options-wrap -->

    </div><!-- .tc-settings-wrap -->


</div><!-- .tc-settings-wrap -->