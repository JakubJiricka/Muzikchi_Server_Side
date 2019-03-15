<div class="tc-bottom-controls">

    <div class="tc-bottom-controls-inside">

        <div class="tc-zoom-wrap">

            <div class="tc-minus-wrap">
                <div class="tc-minus"></div>
            </div>

            <div class="tc-zoom-slider"></div>

            <div class="tc-plus-wrap">
                <div class="tc-plus-vertical"></div>
                <div class="tc-plus-horizontal"></div>
            </div>

        </div><!-- .tc-zoom-wrap -->


        <div class="tc-seating-tooltips">
            <p></p>
        </div>
        <a href="<?php the_permalink(); ?>" target="_blank" class="tc-view-chart">View Chart</a>
        <button name="tc_save_button" class="tc-save-button"><?php _e('Save', 'tcsc'); ?></button>

    </div><!-- .tc-bottom-controls-inside -->

</div><!-- .tc-bottom-controls -->

<?php
TC_Seat_Chart::maybe_duplicate_chart();
?>