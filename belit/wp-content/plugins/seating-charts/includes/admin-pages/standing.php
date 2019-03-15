<div class="tc-settings-wrap tc-tables-settings" id="tc-standing">

    <div class="tc-settings-wrap  tc-tables-settings" id="tc_standing_widget">

        <div class="tc-title-wrap">
            <h4><?php _e('Standing Area', 'tcsc') ?></h4>
        </div><!-- .tc-title-wrap -->

        <div class="tc-options-wrap">

            <div class="tc-input-wrap">
                <label>
                    <?php _e('Title', 'tcsc') ?>
                </label>
                <input type="text" id="tc_standing_group_title" />
            </div><!-- .tc-input-wrap -->

            <div class="tc-input-wrap tc-assign-ticket-type">

                <label><?php _e('Ticket Type', 'tcsc'); ?></label>

                <div class="tc-ticket-type-wrap">        
                    <select name="ticket_type_id" class="ticket_type_id">

                    </select>            
                </div><!-- .tc-ticket-type-wrap -->

            </div><!-- .tc-input-wrap -->

            <div class="tc-clear"></div>

            <div class="tc_seat_add_controls">
                <button class="tc-change-button" type="button" id="tc_add_standing_button"><?php _e('Create', 'tcsc'); ?></button>
            </div>

            <div class="tc_seat_edit_controls">
                <button class="tc-change-button" type="button" id="tc_edit_standing_button"><?php _e('Edit', 'tcsc'); ?></button>
                <button class="tc-cancel-button" id="tc_cancel_standing_button"><?php _e('Cancel', 'tcsc'); ?></button>
            </div>

            <div class="tc-clear"></div>

        </div> <!-- .tc-options-wrap -->

    </div><!-- .tc-settings-wrap -->


</div><!-- .tc-settings-wrap -->