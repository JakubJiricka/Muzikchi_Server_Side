jQuery(document).ready(function ($) {
    var message = '';

    $(".tc-wrapper").mousemove(function (event) {
        //console.log(event.target);
        if ($(event.target).hasClass('tc-wrapper')) {
            message = tc_seatings_tooltips.pan_wrapper;
        } else

        if ($(event.target).hasClass('ui-draggable-handle') || $(event.target).parent().hasClass('ui-draggable-handle')) {
            message = tc_seatings_tooltips.draggable;
        } else

        if ($(event.target).hasClass('tc-icon-rotate')) {
            message = tc_seatings_tooltips.rotate;
        } else

        if ($(event.target).hasClass('tc-icon-trash')) {
            message = tc_seatings_tooltips.delete;
        } else

        if ($(event.target).hasClass('tc-icon-edit')) {
            message = tc_seatings_tooltips.edit;
        } else

        if ($(event.target).hasClass('tc-icon-copy')) {
            message = tc_seatings_tooltips.copy;
        } else

        if ($(event.target).hasClass('ui-resizable-handle')) {
            message = tc_seatings_tooltips.resizable;
        } else

        if ($(event.target).hasClass('ui-slider-handle')) {
            message = tc_seatings_tooltips.drag_slider;
        } else

        if ($(event.target).hasClass('tc-span-sizer') || $(event.target).hasClass('tc-select-element')) {
            message = tc_seatings_tooltips.click_option;
        } else

        if ($(event.target).hasClass('tc-seat-row') || $(event.target).hasClass('tc-table-chair') || $(event.target).hasClass('tc-table-element') || $(event.target).hasClass('tc-table-square-element')) {
            message = tc_seatings_tooltips.selectable;
        } else

        if ($(event.target).hasClass('tc-change-button') || $(event.target).hasClass('tc-seating-tooltips')) {
            message = '';
        }

        $('.tc-seating-tooltips p').html(message);
    });
});