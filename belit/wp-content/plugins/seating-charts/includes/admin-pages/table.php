<div class="tc-settings-wrap tc-tables-settings" id="tc-table">

    <div class="tc-title-wrap">
        <h4><?php _e('Tables', 'tcsc'); ?></h4>
    </div><!-- .tc-title-wrap -->

    <div class="tc-options-wrap">

        <div class="tc-input-wrap">
            <label>
                <?php _e('Title', 'tcsc'); ?>
            </label>
            <input type="text" class="tc_table_title" />
        </div><!-- .tc-input-wrap -->

        <div class="tc-input-wrap tc-input-slider">

            <label><?php _e('Number of Seats', 'tcsc'); ?></label>

            <div class="tc-number-slider tc_table_seats_num"></div>
            <input type="text" id="tc_table_seats_num" class="tc-slider-value tc_table_seats_num_value" />  

            <div class="tc_end_seats_holder">
                <label><?php _e('End Seats', 'tcsc'); ?></label>

                <div class="tc-number-slider tc_table_end_seats"></div>
                <input type="text" id="tc_table_end_seats" class="tc-slider-value tc_table_end_seats_value" />  
            </div>

        </div><!-- .tc-input-slider -->

        <div class="tc-input-wrap tc-select-shape">

            <label><?php _e('Shape', 'tcsc'); ?></label>

            <div class="tc-shape-wrap">

                <input type="radio" id="c1" name="tc_seat_table_type" class="tc_seat_table_type tc_seat_table_type_circle" value="circle" checked />
                <label for="c1" class="tc-select-shape-round"><span class="tc-span-sizer"></span><span class="tc-span-active"></span></label>

                <input type="radio" id="c2" name="tc_seat_table_type" class="tc_seat_table_type tc_seat_table_type_square" value="square" />
                <label for="c2" class="tc-select-shape-box"><span class="tc-span-sizer"></span><span class="tc-span-active"></span></label>

                <div class="tc-clear"></div>

            </div>


        </div><!-- .tc-input-slider -->

        <div class="tc-color-picker-wrap tc-table-color-picker">                    
            <label><?php _e('Table Color', 'tcsc'); ?></label>
            <div class="tc-color-picker-element">                        
                <input class="tc-color-picker icon-color" type="text" value="#6b5f89" />
            </div><!-- .tc-color-picker-element -->                                        
        </div><!-- .tc-color-picker-wrap -->


        <div class="tc-clear"></div>

        <div class="tc_table_add_controls">
            <button class="tc-change-button" type="button" id="tc_add_table_button">Create</button>
        </div>

        <div class="tc_table_edit_controls">
            <button class="tc-edit-button" type="button" id="tc_edit_table_button"><?php _e('Edit', 'tcsc'); ?></button>
            <button class="tc-cancel-button" id="tc_cancel_table_button"><?php _e('Cancel', 'tcsc'); ?></button>
        </div>

        <div class="tc-clear"></div>

    </div> <!-- .tc-options-wrap -->

</div><!-- .tc-settings-wrap -->