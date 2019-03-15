window.tc_seat_zoom_level_prev = tc_common_vars.front_zoom_level;
window.tc_seat_zoom_level = tc_common_vars.front_zoom_level;

jQuery(document).ready(function ($) {
    $('body').on('click', '.tc-legend-arrow', function () {

        if ($(this).hasClass('tc-legend-close')) {

            var tc_seating_legend = $('.tc-seating-legend-wrap').outerWidth();

            $(".tc-seating-legend-wrap").animate({
                left: -Math.abs(tc_seating_legend),
            }, 400, function () {
                // Animation complete.
            });

            $('.tc-legend-arrow').removeClass('tc-legend-close');
            $('.tc-legend-arrow').addClass('tc-legend-open');
        } else {

            var tc_seating_legend = $('.tc-seating-legend-wrap').outerWidth();

            $(".tc-seating-legend-wrap").animate({
                left: 0,
            }, 400, function () {
                // Animation complete.
            });

            $('.tc-legend-arrow').removeClass('tc-legend-open');
            $('.tc-legend-arrow').addClass('tc-legend-close');
        }

    });

    var resizeTimer;

    $(window).on('resize', function (e) {

        if ($('.tc-legend-arrow').hasClass('tc-legend-open')) {
            var tc_seating_legend = $('.tc-seating-legend-wrap').outerWidth();
            $(".tc-seating-legend-wrap").css('left', -Math.abs(tc_seating_legend));
        }

        var get_window_width = $(this).width();

        if (get_window_width < 780) {
            var tc_seating_legend = $('.tc-seating-legend-wrap').outerWidth();
            $(".tc-seating-legend-wrap").animate({
                left: -Math.abs(tc_seating_legend),
            }, 0, function () {
                // Animation complete.
            });

            $('.tc-legend-arrow').removeClass('tc-legend-close');
            $('.tc-legend-arrow').addClass('tc-legend-open');

        }

    });


    $('body').on('click', '.tc_modal_close_dialog', function (e) {
        var close_obj = $("#" + $(this).parents('.tc-modal-wrap').attr('id'));
        close_obj.dialog('close');
    })

    $('body').on('click', '.tc-full-screen', function (e) {

        jQuery('html').css('overflow', 'auto');

        $('.tc_seating_map').css({
            display: 'none',
        });
    });

    $('html').keydown(function (e) {
        if (e.keyCode == 40) {//down
            tc_controls.position_pan_wrapper('down');
            e.preventDefault();
        }

        if (e.keyCode == 38) {//up
            tc_controls.position_pan_wrapper('up');
            e.preventDefault();
        }

        if (e.keyCode == 37) {//left
            tc_controls.position_pan_wrapper('left');
            e.preventDefault();
        }

        if (e.keyCode == 39) {//right
            tc_controls.position_pan_wrapper('right');
            e.preventDefault();
        }

    });

    $(window).resize(function () {
        tc_controls.set_wrapper_height();
    });

    /**
     * Zoom Controls Events
     */
    $('body').on('click', '.tc-zoom-wrap .tc-plus-wrap', function (event) {
        tc_controls.zoom_plus();
    });

    $('body').on('click', '.tc-zoom-wrap .tc-minus-wrap', function (event) {
        tc_controls.zoom_minus();
    });

    $('body').on('click', '#collapse-menu', function (event) {
        tc_controls.position_zoom_controls();
    });


    /**
     * Mouse Events
     */

    $('body').on('mousewheel DOMMouseScroll', '.tc-wrapper', function (e) {
        e.preventDefault();

        if (e.type == 'DOMMouseScroll') {//Firefox
            scroll = e.originalEvent.detail * (40 * -1);
        } else {
            scroll = e.originalEvent.wheelDelta;
        }
        if (scroll / 120 > 0) {
            tc_controls.zoom_plus();
        } else {
            tc_controls.zoom_minus();
        }
    });

    /*$('body').on('hover', '.tc-checkout-button, .tc-tickets-cart', function () {
     $('.tc-tickets-cart').css('bottom', 71);
     }, function () {
     var tc_ticket_cart_height = $('.tc-tickets-cart').height();
     $('.tc-tickets-cart').css('bottom', tc_ticket_cart_height * -1);
     });*/

    var supportsTouch = 'ontouchstart' in window || navigator.msMaxTouchPoints;

    if (supportsTouch !== true) {
        $('body').on('hover', '.tc_set_seat, .tc-object-selectable.ui-selectee', function (event) {
            if (!$(event.target).is(".tc_seat_unavailable")) {
                var ticket_type = $(this).attr('data-tt-id');
                var ticket_type_title = $('li.tt_' + ticket_type).attr('data-tt-title');
                var ticket_type_price = $('li.tt_' + ticket_type).attr('data-tt-price');

                var ticket_seat_number = $(this).find('span p').html();

                if (typeof ticket_seat_number === 'undefined' || !ticket_seat_number) {
                    ticket_seat_number = '';//seat number isn't set
                } else {
                    if (ticket_seat_number == '') {
                        ticket_seat_number = '';
                    } else {
                        ticket_seat_number = '<div class="tc-front-seat-number">' + tc_common_vars.seat_translation + ': <strong>' + ticket_seat_number + '</strong></div>';
                    }
                }

                var tc_top = event.clientY + 20;
                var tc_bottom = event.clientX;
                $(".tc-wrapper").append('<span class="tc-ticket-info-wrap" style="left:' + tc_bottom + 'px; top:' + tc_top + 'px; position: absolute";><div class="tc-arrow-up"></div><div class="tc-ticket-info-inside"><ul><li><span><strong class="tc_ticket_type_price_hover_wrapper">' + ticket_type_price + '</strong> - </span>' + ticket_type_title + '</li></ul>' + ticket_seat_number + '</div></span>');
            }
        });

        $('body').on('mouseout', '.tc_set_seat, .tc-object-selectable.ui-selectee', function () {
            $(".tc-wrapper").find('span.tc-ticket-info-wrap').remove();
        });
    }

    jQuery('.hover').bind('touchend', function (e) {
        tc_controls.set_wrapper_height();
    });


});