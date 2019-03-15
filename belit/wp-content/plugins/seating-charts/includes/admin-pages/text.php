<div class="tc-settings-wrap  tc-tables-settings" id="tc_text_widget">

    <div class="tc-title-wrap">
        <h4><?php _e('Text Element', 'tcsc'); ?></h4>
    </div><!-- .tc-title-wrap -->

    <div class="tc-options-wrap">

        <div class="tc-input-wrap">
            <label>
                <?php _e('Text', 'tcsc'); ?>
            </label>
            <input type="text" class="tc_text_title" />
        </div><!-- .tc-input-wrap -->


        <div class="tc-clear"></div>


        <div class="tc-color-picker-wrap">                    
            <label><?php _e('Color', 'tcsc'); ?></label>
            <div class="tc-color-picker-element">                        
                <input class="tc-color-picker tc_text_color" type="text" value="#000000" />
            </div><!-- .tc-color-picker-element -->                                        
        </div><!-- .tc-color-picker-wrap -->

        <div class="tc-input-wrap tc-input-slider">

            <label><?php _e('Font Size', 'tcsc'); ?></label>

            <div class="tc-number-slider"></div>
            <input type="text" class="tc-slider-value tc_text_size" />                

        </div><!-- .tc-input-slider -->

        <div class="tc_text_add_controls">
            <button class="tc-change-button" type="button" id="tc_add_text_button">Create</button>
        </div>

        <div class="tc_text_edit_controls">
            <button class="tc-edit-button" type="button" id="tc_edit_text_button"><?php _e('Edit', 'tcsc'); ?></button>
            <button class="tc-cancel-button" id="tc_cancel_text_button"><?php _e('Cancel', 'tcsc'); ?></button>
        </div>

        <div class="tc-clear"></div>

    </div> <!-- .tc-options-wrap -->

</div><!-- .tc-settings-wrap -->