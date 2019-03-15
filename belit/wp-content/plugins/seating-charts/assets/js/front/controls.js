jQuery(document).ready(function ($) {

    window.tc_controls = {
        centerPoint: function (seating_map_id) {

            if (typeof window.current_left === 'undefined') {
                window.current_left = parseFloat($('.tc-pan-wrapper').css('left'));
            }

            data_csw = $('.tc_seating_map_' + seating_map_id + ' .tc-wrapper').attr('data-csw');

            if (data_csw == '') {
                tc_controls.centerPointLegacy();
            } else {
                scr_width = $(window).width();

                movement = window.current_left - ((data_csw - scr_width) / 2);

                $('.tc-pan-wrapper').css({
                    left: movement,
                });
            }
        },
        centerPointLegacy: function () {
            min_x = false;
            max_x = false;
            min_y = false;
            max_y = false;
            width = 0;
            height = 0;

            $.each($(".tc-group-wrap"), function () {
                if (($(this).position().top < min_y) || min_y == false) {
                    min_y = $(this).position().top;
                }
                if ((($(this).position().top + $(this).height()) > max_y) || max_y == false) {
                    max_y = ($(this).position().top + $(this).height());
                }

                height = max_y - min_y;

                if (($(this).position().left < min_x) || min_x == false) {
                    min_x = $(this).position().left;
                    //console.log(min_x);
                }
                if ((($(this).position().left + $(this).width()) > max_x) || max_x == false) {
                    max_x = ($(this).position().left + $(this).width());
                }

                width = max_x - min_x;
            })

            if (min_x == false) {
                min_x = 0;
            }
            if (max_x == false) {
                max_x = 0;
            }
            if (min_y == false) {
                min_y = 0;
            }
            if (max_y == false) {
                max_y = 0;
            }

            mid_point_x = (width / 2);
            mid_point_y = (height / 2);

            wrapper_mid_point_x = $('.tc-wrapper').width() / 2;
            wrapper_mid_point_y = $('.tc-wrapper').height() / 2;

            $('.tc-pan-wrapper').css({left: (0 - min_x) + 'px'});
            $('.tc-pan-wrapper').css({left: ((wrapper_mid_point_x - min_x) - mid_point_x) + 'px'});

            $('.tc-pan-wrapper').css({top: (0 - min_y) + 'px'});
            $('.tc-pan-wrapper').css({top: ((wrapper_mid_point_y - min_y) - mid_point_y) + 'px'});

        },
        zoom: function () {
            if (window.tc_seat_zoom_level <= 1 && window.tc_seat_zoom_level >= 0.30) {
                var prev_value = $(".tc-zoom-slider").slider("option", "value");
                window.tc_seat_zoom_level_prev = prev_value;
                if (window.tc_seat_zoom_level_prev < window.tc_seat_zoom_level) {
                    zoom_level = ((window.tc_seat_zoom_level - window.tc_seat_zoom_level_prev)) + 1;
                } else {
                    zoom_level = ((window.tc_seat_zoom_level_prev - window.tc_seat_zoom_level)) + 1;
                }

                $('.tc-pan-wrapper').css(
                        {
                            'transform': 'scale(' + window.tc_seat_zoom_level + ')',
                            'transform-origin': 'center center',
                            'transition': 'transform 0.3s linear',
                        });

                $(".tc-zoom-slider").slider('value', window.tc_seat_zoom_level);
                $(".tc-wrapper").css({
                    'background-size': (80 * window.tc_seat_zoom_level),
                    'transition': 'transform 0.3s linear',
                });
            }
        },
        zoom_plus: function () {
            if (window.tc_seat_zoom_level < 1) {
                window.tc_seat_zoom_level = window.tc_seat_zoom_level + 0.10;
                tc_controls.zoom();
            }
        },
        zoom_minus: function () {
            if (window.tc_seat_zoom_level > 0.3) {
                window.tc_seat_zoom_level = window.tc_seat_zoom_level - 0.10;
                tc_controls.zoom();
            }
        },
        position_zoom_controls: function () {
            var admin_menu_width = $('#adminmenuwrap').width();
            $('.tc-zoom-wrap').css('left', admin_menu_width + 15)
        },
        set_wrapper_height: function () {
            $('.tc-wrapper').height($.windowHeight());
        },
        position_pan_wrapper: function (position) {
            var move_val = 50;

            switch (position) {
                case 'up':
                    $('.tc-pan-wrapper').css('top', ($('.tc-pan-wrapper').position().top) - move_val);
                    tc_controls.position_background();
                    break;
                case 'right':
                    $('.tc-pan-wrapper').css('left', ($('.tc-pan-wrapper').position().left) + move_val);
                    tc_controls.position_background();
                    break;
                case 'down':
                    $('.tc-pan-wrapper').css('top', ($('.tc-pan-wrapper').position().top) + move_val);
                    tc_controls.position_background();
                    break;
                case 'left':
                    $('.tc-pan-wrapper').css('left', ($('.tc-pan-wrapper').position().left) - move_val);
                    tc_controls.position_background();
                    break;
            }
        },
        position_background: function () {
            $(".tc-wrapper").css('background-position-x', $('.tc-pan-wrapper').position().left);
            $(".tc-wrapper").css('background-position-y', $('.tc-pan-wrapper').position().top);
        },
        init: function () {
            window.tc_seat_zoom_level_prev = parseFloat(tc_controls_vars.front_zoom_level);
            window.tc_seat_zoom_level = parseFloat(tc_controls_vars.front_zoom_level);
            tc_controls.zoom();
            tc_controls.position_background();

            $('.tc-wrapper').fadeTo(600, 1, function () {
                // Animation completed.
            });
        },
        reposition: function () {
            window.tc_seat_zoom_level = parseFloat(tc_controls_vars.front_zoom_level);
            tc_controls.position_background();
        },
        set_default_colors: function () {
            $.each($(".tc_set_seat, .tc-object-selectable"), function () {
                var ticket_type_id = $(this).data('tt-id');

                var tc_seat_color = tc_seat_default_colors[ticket_type_id];

                if (typeof tc_seat_color == typeof undefined || tc_seat_color == '') {
                    tc_seat_color = '#0085BA ';
                }

                $(this).css({'background-color': tc_seat_color});

            })
        },
    }
});