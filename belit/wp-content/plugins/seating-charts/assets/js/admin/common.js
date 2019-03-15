window.tc_seat_zoom_level_prev = 1;
window.tc_seat_zoom_level = 1;

jQuery(document).ready(function ($) {

    setTimeout(function () {
        tc_hide_updated_notice();
    }, 3000);

    function tc_hide_updated_notice() {
        $('.notice.tc-tickera-show:not(.tc-donothide)').fadeTo(250, 0);
    }


    $('body').on('click', '.tc-save-button', function (e) {
        e.preventDefault();
        $('#tc_current_screen_width').val($(window).width());
        tc_controls.save();

    });

    $('body').on('click', '.ui-tabs-nav li, .ui-tabs-nav li a, .ui-tabs-nav li span', function (e) {
        tc_controls.set_default_settings_values();
        tc_controls.unselect_all();
        tc_controls.hide_ticket_type_box();
        tc_controls.hide_labels();
    });

    /**
     * Prevent form submission on enter key press while input field is in the focus
     */
    $(document).keypress(function (e) {
        if (e.which == 13) {
            e.preventDefault();
        }
    });

    $('html').keydown(function (e) {

        if (e.keyCode == 40 && e.srcElement.id == 'wpbody-content') {//down
            tc_controls.position_pan_wrapper('down');
            e.preventDefault();
        }

        if (e.keyCode == 38 && e.srcElement.id == 'wpbody-content') {//up
            tc_controls.position_pan_wrapper('up');
            e.preventDefault();
        }

        if (e.keyCode == 37 && e.srcElement.id == 'wpbody-content') {//left
            tc_controls.position_pan_wrapper('left');
            e.preventDefault();
        }

        if (e.keyCode == 39 && e.srcElement.id == 'wpbody-content') {//right
            tc_controls.position_pan_wrapper('right');
            e.preventDefault();
        }

    });

    var tc_seats_key_press_listener = new window.keypress.Listener();

    tc_seats_key_press_listener.register_many([
        {
            "keys": "meta s",
            "is_exclusive": true,
            "on_keydown": function () {
                tc_controls.save();
            },
        }
    ]);

    $('.tc-wrapper').bind("focus", function () {
        tc_seats_key_press_listener.stop_listening();
    }).bind("blur", function () {
        tc_seats_key_press_listener.listen();
    });

    $(window).resize(function () {
        tc_controls.set_wrapper_height();
    });

    $('body').on('click', 'div.tc-wrapper', function (event) {
        tc_controls.set_tabs_inactive(event);
        /*if ($(event.target).attr('class') == 'tc-wrapper') {
         tc_controls.remove_edit_mode();
         }*/
    });

    $('body').on('click', 'div.tc-wrapper', function (event) {
        if ($(event.target).attr('class') == 'tc-wrapper') {
            tc_controls.unselect_all();
            tc_controls.hide_labels();
        }
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

    /**
     * Tools sidebar
     */
    /*
     $(window).resize(function() {
     var tc_window_height = $(window).height();
     $('.tc-sidebar').height(tc_window_height);
     
     });
     */


    $('body').on('mouseenter', '.tc-seat-group, .tc-table-group, .tc-element-group, .tc-caption-group', function (e) {
        $(this).parent().css('z-index', 20);
    }).on('mouseleave', '.tc-seat-group, .tc-table-group, .tc-element-group, .tc-caption-group', function (e) {
        $(this).parent().css('z-index', 1);
    });

    /**
     * Tabs
     */
    $(".tc-sidebar").tabs();

    /**
     * Sliders
     */
    $("#tc_seating_group_widget .tc-seat-rows-slider .tc-number-slider").slider({
        value: 10,
        min: 1,
        max: 50,
        slide: function (event, ui) {
            $(this).parent().find('.tc-slider-value').val(ui.value);
        },
        create: function (event, ui) {
            var bar = $(this).slider('value');
            $(this).parent().find('.tc-slider-value').val(bar);
        }
    });

    $("#tc_seating_group_widget .tc-seat-cols-slider .tc-number-slider").slider({
        value: 10,
        min: 1,
        max: 50,
        slide: function (event, ui) {
            $(this).parent().find('.tc-slider-value').val(ui.value);
        },
        create: function (event, ui) {
            var bar = $(this).slider('value');
            $(this).parent().find('.tc-slider-value').val(bar);
        }
    });

    $(".tc-zoom-slider").slider({
        value: 1,
        orientation: "horizontal",
        min: 0.30,
        max: 1,
        step: 0.10,
        slide: function (event, ui) {
            var init_zoom = window.tc_seat_zoom_level;
            //console.log('Init Zoom:' + init_zoom);

            $(this).parent().find('.tc-slider-value').val(ui.value);

            window.tc_seat_zoom_level = ui.value;

            tc_controls.zoom();
        },
        create: function (event, ui) {
            var bar = $(this).slider('value');
            $(this).parent().find('.tc-slider-value').val(bar);
        }
    });

    $("#tc_text_widget .tc-number-slider").slider({
        value: 25,
        min: 10,
        max: 100,
        slide: function (event, ui) {
            $(this).parent().find('.tc-slider-value').val(ui.value);
        },
        create: function (event, ui) {
            var bar = $(this).slider('value');
            $(this).parent().find('.tc-slider-value').val(bar);
        }
    });

    $("#tc-table .tc-number-slider.tc_table_seats_num").slider({
        value: 4,
        min: 2,
        max: 50,
        slide: function (event, ui) {
            $(this).parent().find('.tc_table_seats_num_value').val(ui.value);

            var max_end_seats = Math.floor(ui.value / 2);

            $("#tc-table .tc-number-slider.tc_table_end_seats").slider('option', {max: max_end_seats});

            if ($('.tc_table_end_seats_value').val() > max_end_seats) {
                $("#tc-table .tc-number-slider.tc_table_end_seats").slider('value', max_end_seats);
                $('.tc_table_end_seats_value').val(max_end_seats);
            }

        },
        create: function (event, ui) {
            var bar = $(this).slider('value');
            $(this).parent().find('.tc_table_seats_num_value').val(bar);
        },
    });

    $("#tc-table .tc-number-slider.tc_table_end_seats").slider({
        value: 0,
        min: 0,
        max: 2,
        slide: function (event, ui) {
            $(this).parent().find('.tc_table_end_seats_value').val(ui.value);
        },
        create: function (event, ui) {
            var bar = $(this).slider('value');
            $(this).parent().find('.tc_table_end_seats_value').val(bar);
        }
    });

    /* Slider for elements */

    $('.tc-select-elements').unslider({
        arrows: false,
        keys: false,
        speed: 180
    });

    /**
     * Initialize Components
     */
    tc_controls.position_zoom_controls();
    tc_controls.get_event_ticket_types();
    tc_controls.set_wrapper_height();

    tc_controls.init();
    tc_seats.init();
    tc_text.init();
    tc_table.init();
    tc_element.init();
    tc_standing.init();


    $('.tc_col_label_invert').click(function (e) {
        var col_sign_from = $('#tc_seat_sign_settings_multi_seat_col_sign_from').val();
        var col_sign_to = $('#tc_seat_sign_settings_multi_seat_col_sign_to').val();

        $('#tc_seat_sign_settings_multi_seat_col_sign_from').val(col_sign_to);
        $('#tc_seat_sign_settings_multi_seat_col_sign_to').val(col_sign_from);
    });
});