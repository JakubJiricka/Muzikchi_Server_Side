jQuery(document).ready(function ($) {
    var message = '';

    $(".tc-wrapper").mousemove(function (event) {
        //console.log(event.target);
        if ($(event.target).hasClass('tc-wrapper')) {
            message = tc_seatings_tooltips.pan_wrapper;
        } else
        if ($(event.target).hasClass('tc-seat-row') || $(event.target).hasClass('tc-table-chair') || $(event.target).hasClass('tc-table-element') || $(event.target).hasClass('tc-table-square-element')) {
            message = tc_seatings_tooltips.selectable;
        } else{
            message = '';
        }

        $('.tc-seating-tooltips p').html(message);
    });
});