jQuery(document).ready(function ($) {

    var drag = {
        elem: null,
        x: 0,
        y: 0,
        state: false
    };

    var delta = {
        x: 0,
        y: 0
    };

    $(document).mousedown(function (e) {
        if (!drag.state && (e.which == 1)) {
            if ($(e.target).hasClass('tc-wrapper') || $(e.target).hasClass('tc-pan-wrapper')) {
                drag.elem = $('.tc-pan-wrapper');
                drag.x = e.pageX;
                drag.y = e.pageY;
                drag.state = true;
            }
        }


    }).mouseup(function () {
        if (drag.state) {
            drag.state = false;
        }
    });



    $(document).mousemove(function (e) {

        if (drag.state) {
            delta.x = e.pageX - drag.x;
            delta.y = e.pageY - drag.y;

            var cur_offset = $(drag.elem).offset();

            tc_controls.position_background(e);

            $(drag.elem).offset({
                left: (cur_offset.left + delta.x),
                top: (cur_offset.top + delta.y)
            });

            drag.x = e.pageX;
            drag.y = e.pageY;
        }

    });


    //for touch devices
  

    $(document).bind('touchstart', function (e) {
        if (!drag.state && e.originalEvent.touches.length !== 2) {
            
            if ($(e.target).hasClass('tc-wrapper') || $(e.target).hasClass('tc_seat_unit') || $(e.target).hasClass('tc-table-element') || $(e.target).hasClass('tc-seat-row') || $(e.target).hasClass('tc-object') || $(e.target).hasClass('tc-pan-wrapper')) {
                drag.elem = $('.tc-pan-wrapper');
                drag.x = e.originalEvent.touches[0].clientX;
                drag.y = e.originalEvent.touches[0].clientY;
                drag.state = true;
            }
        }

    }).bind('touchend', function () {
        if (drag.state) {
            drag.state = false;
        }
    });


    $(document).bind('touchmove', function (e) {
        if (drag.state && e.originalEvent.touches.length !== 2) {
            delta.x = e.originalEvent.touches[0].clientX - drag.x;
            delta.y = e.originalEvent.touches[0].clientY - drag.y;
            var cur_offset = $(drag.elem).offset();

            tc_controls.position_background(e);
            $(drag.elem).offset({
                left: (cur_offset.left + delta.x),
                top: (cur_offset.top + delta.y)
            });

            drag.x = e.originalEvent.touches[0].clientX;
            drag.y = e.originalEvent.touches[0].clientY;
        }
    });


    $(document).on('contextmenu', function () {
        //return false;
    });


});