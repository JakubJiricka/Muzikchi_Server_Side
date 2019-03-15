jQuery(document).ready(function ($) {

    $(".post-type-tc_seat_charts form#post").validate();

    /*
     * Hide controls in the admin if chart has orders made (prevent editing number of rows, cols, event etc) 
     */
    if ($("#tc_chart_has_orders").length > 0) {
        $('#seat_chart_rows, #seat_chart_cols, #event_name_label select').prop('disabled', true);
    }

    function tcHasAttr(object, attr) {
        var attr = $(object).attr(attr);
        if (typeof attr !== typeof undefined && attr !== false) {
            return true;
        } else {
            return false;
        }
    }

    function tcHasClass(object, class_name) {
        var obj_class = $(object).hasClass(class_name);
        if (typeof obj_class !== typeof undefined && obj_class !== false) {
            return true;
        } else {
            return false;
        }
    }

    $(function ( ) {

        var selected_assigned_ticket_type_num = 0;
        var has_reserved_tickets = false;

        $(".selectable_row").selectable({
            filter: '.tc_seat_unit',
            stop: function (event, ui) {
                $('#tc_seat_sign_settings_multi_seat_row_sign').val('');
                $('#tc_seat_sign_settings_multi_seat_col_sign_from').val('1');
                $('#tc_seat_sign_settings_multi_seat_col_sign_to').val(selected_assigned_ticket_type_num);

                if (has_reserved_tickets) {
                    $('#tc_seat_chart_unset_ticket_type_settings_button').hide();
                    $('#tc_seat_chart_change_ticket_type_settings_button').hide();
                    $('#ticket_type_id').prop('disabled', true);
                } else {
                    $('#tc_seat_chart_unset_ticket_type_settings_button').show();
                    $('#tc_seat_chart_change_ticket_type_settings_button').show();
                    $('#ticket_type_id').prop('disabled', false);
                }

                //Ticket type box
                tc_maybe_show_ticket_type_unset_button();

                //Seat sign box
                tc_show_seat_sign_box_and_value(selected_assigned_ticket_type_num);

                selected_assigned_ticket_type_num = 0;
                has_reserved_tickets = false;
            },
            selected: function (event, ui) {

                if (tcHasClass(ui.selected, 'tc_set_seat')) {
                    selected_assigned_ticket_type_num++;
                }

                if (tcHasClass(ui.selected, 'tc_reserved_seat')) {
                    has_reserved_tickets = true;
                }

            }
        });
        
        //on load
        tc_create_seat_chart_floor_map( );
        tc_get_event_ticket_types();
    });

    function tc_maybe_show_ticket_type_unset_button() {
        if (tcHasAttr(".ui-selected", 'data-ticket-type-id')) {
            $('#tc_seat_chart_unset_ticket_type_settings_button').show( );
        } else {
            $('#tc_seat_chart_unset_ticket_type_settings_button').hide( );
        }
    }

    function tc_show_seat_sign_box_and_value(selected_assigned_ticket_type_num) {
        if (selected_assigned_ticket_type_num == 0) {
            $('#tc_seat_chart_seat_direction_settings .tc_box_overlay').show();
            $('#tc_seat_chart_seat_sign_settings .tc_box_overlay').show();
            $('.tc_seat_sign_settings_single_holder').show( );
            $('.tc_seat_sign_settings_multi_holder').hide( );
        } else if (selected_assigned_ticket_type_num == 1) {
            $('#tc_seat_chart_seat_sign_settings .tc_box_overlay').hide();
            $('#tc_seat_chart_seat_direction_settings .tc_box_overlay').hide();

            if (tcHasAttr($(".ui-selected"), 'data-seat-sign')) {
                $('#tc_seat_sign_settings_single_seat_row_sign').val($(".ui-selected").attr('data-seat-sign'));
            } else {
                $('#tc_seat_sign_settings_single_seat_row_sign').val('');
            }

            $('.tc_seat_sign_settings_single_holder').show( );
            $('.tc_seat_sign_settings_multi_holder').hide( );
        } else {//multiple selected
            $('#tc_seat_chart_seat_direction_settings .tc_box_overlay').hide();
            $('#tc_seat_chart_seat_sign_settings .tc_box_overlay').hide();
            $('.tc_seat_sign_settings_single_holder').hide( );
            $('.tc_seat_sign_settings_multi_holder').show( );
        }
    }

    $('.tc_col_label_invert').click(function (e) {
        var col_sign_from = $('#tc_seat_sign_settings_multi_seat_col_sign_from').val();
        var col_sign_to = $('#tc_seat_sign_settings_multi_seat_col_sign_to').val();

        $('#tc_seat_sign_settings_multi_seat_col_sign_from').val(col_sign_to);
        $('#tc_seat_sign_settings_multi_seat_col_sign_to').val(col_sign_from);
    });

    $('#tc_seat_chart_change_settings_button').click(function (e) {
        e.preventDefault( );
        $('#publishing-action input[type="submit"]').click();
    });

    $('#tc_seat_chart_change_ticket_type_settings_button').click(function (e) {
        e.preventDefault( );
        tc_change_ticket_type_settings( );
    });


    $('#tc_seat_sign_settings_single_set_button').click(function (e) {
        e.preventDefault( );
        tc_change_seat_sign_single_settings( );
    });

    $('#tc_seat_sign_settings_multi_set_button').click(function (e) {
        e.preventDefault( );
        tc_change_seat_sign_multi_settings( );
    });

    $('#tc_seat_chart_unset_ticket_type_settings_button').click(function (e) {
        e.preventDefault( );
        tc_unset_ticket_type_settings( );
    });

    function tc_change_seat_sign_multi_settings() {
        

    }

    function tc_change_seat_sign_single_settings() {
        
    }

    function tc_set_seat_attributes(element, values) {

        var tc_seat_color = tc_seat_colors[values[0]];
        if (typeof attr !== typeof undefined && attr !== false) {
            tc_seat_color = '#0085BA';
        }

        $(element).attr('data-ticket-type-id', values[0]);
        $(element).attr('data-seat-sign', values[1]);

        $(element).find('.tc-add-font').addClass(values[2]);

        $(element).find('.tc-add-font').css('color', tc_seat_color);
        $(element).removeClass('tc_set_seat');
        $(element).addClass('tc_set_seat');

        if (values[1] == '') {
            $(element).addClass('tc-now-show-before');
        }

    }

    function tc_mark_reserved_seats(seat_chart_id) {
        if (typeof tc_reserved_seats !== typeof undefined) {
            for (var k in tc_reserved_seats[seat_chart_id]) {
                if (tc_reserved_seats[seat_chart_id].hasOwnProperty(k)) {

                    $('#tc_seat_' + k).find('.tc-add-font').css('color', tc_seat_chart_ajax.tc_reserved_seat_color);
                    $('#tc_seat_' + k).addClass('tc_reserved_seat');
                }
            }
        }
    }

    function tc_mark_seats( ) {
        for (var k in tc_seats) {
            if (tc_seats.hasOwnProperty(k)) {
                tc_set_seat_attributes('#tc_seat_' + k, tc_seats[k]);
            }
        }
    }

});