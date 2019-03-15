<div class="tc-settings-wrap tc-tables-settings" id="tc-seat-labels-settings">

    <div id="tc_label_widget">
        <div class="tc-title-wrap">
            <h4><?php _e('Assign Seat Labels', 'tcsc'); ?><span id="tc-seat-labels-num"></span></h4>
        </div><!-- .tc-title-wrap -->

        <div class="tc-options-wrap" >

            <div class="tc-input-wrap tc-seat-labels" id="tc-labels-multi-select">

                <div class="tc-input-third">
                    <label><?php _e('Letter', 'tcsc'); ?></label>
                    <input type="text" class="tc_label_letter" placeholder="A" />                    
                </div><!-- .tc-input-third -->

                <div class="tc-input-third">
                    <label><?php _e('From', 'tcsc'); ?></label>
                    <input type="text" class="tc_label_from_multi" placeholder="1" />                    
                </div><!-- .tc-input-third -->

                <span class="tc_col_label_invert">↔</span>

                <div class="tc-input-third">
                    <label><?php _e('To', 'tcsc'); ?></label>
                    <input type="text" class="tc_label_to_multi" placeholder="20" />                    
                </div><!-- .tc-input-third -->

            </div><!-- .tc-input-wrap -->


            <div class="tc-input-wrap tc-seat-labels" id="tc-labels-single-select">

                <div class="tc-input-half">
                    <label><?php _e('Label', 'tcsc'); ?></label>
                    <input type="text" class="tc_label_letter" placeholder="A1" />                    
                </div><!-- .tc-input-third -->

            </div><!-- .tc-input-wrap -->

            <div class="tc-clear"></div>

            <button class="tc-change-button" type="submit"><?php _e('Assign', 'tcsc'); ?></button>
            <button class="tc-cancel-button">Unset</button>

            <div class="tc-clear"></div>

        </div> <!-- .tc-options-wrap -->
    </div>

</div>