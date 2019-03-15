jQuery(document).ready(function ($) {
    window.tc_controls = {
        /**
         * Creates new element
         * @returns {undefined}
         */
        change_event_confirmation: function () {

            var selected_event_id = $("#tc-settings .tc-event-wrap option:selected").val();
            var selected_event_id_orig = $('#tc_init_event_id').val();

            if ((selected_event_id !== selected_event_id_orig)) {
                if ($('.tc-pan-wrapper').html() !== '') {
                    $("#tc-seating-change-event-dialog").dialog({
                        bgiframe: true,
                        closeOnEscape: false,
                        draggable: false,
                        resizable: false,
                        dialogClass: "no-close",
                        modal: true,
                        title: tc_controls_vars.are_you_sure,
                        closeText: "<i class='fa fa-times'></i>",
                        buttons: [
                            {
                                text: tc_controls_vars.no,
                                click: function () {
                                    $(this).dialog("close");
                                    tc_controls.change_event_confirmation_callback(false);
                                }
                            },
                            {
                                text: tc_controls_vars.yes,
                                click: function () {
                                    $(this).dialog("close");
                                    tc_controls.change_event_confirmation_callback(true);
                                }
                            }
                        ]
                    });
                } else {
                    $('#tc_init_event_id').val(selected_event_id);
                    tc_controls.get_event_ticket_types();
                }
            } else {
                //tc_controls.change_event_confirmation_callback(true);
            }
        },
        change_event_confirmation_callback: function (value) {
            if (value == true) {
                var selected_event_id = $("#tc-settings .tc-event-wrap option:selected").val();

                $('#tc_init_event_id').val(selected_event_id);

                tc_controls.get_event_ticket_types();

                $.each($(".tc_set_seat, .tc-object-selectable"), function () {
                    if (!$(this).hasClass('tc_seat_reserved')) {
                        $(this).removeClass('tc_set_seat');
                        $(this).removeAttr('data-tt-id');
                        $(this).css("background-color", "");
                        $(this).find('span').remove();
                    }
                });
            }
        },
        delete_confirmation: function (obj_to_delete) {
            $("#tc-seating-dialog").dialog({
                bgiframe: true,
                closeOnEscape: false,
                draggable: false,
                resizable: false,
                dialogClass: "no-close",
                modal: true,
                title: tc_controls_vars.are_you_sure,
                closeText: "<i class='fa fa-times'></i>",
                buttons: [
                    {
                        text: tc_controls_vars.no,
                        click: function () {
                            $(this).dialog("close");
                            tc_controls.delete_confirmation_callback(false, obj_to_delete);
                        }
                    },
                    {
                        text: tc_controls_vars.yes,
                        click: function () {
                            $(this).dialog("close");
                            tc_controls.delete_confirmation_callback(true, obj_to_delete);
                        }
                    }
                ]
            });
        },
        delete_confirmation_callback: function (value, obj_to_delete) {
            if (value == true) {
                obj_to_delete.remove();
                $(".tc-sidebar").tabs({collapsible: true, active: false});
                tc_controls.hide_ticket_type_box();
                $(".tc-group-wrap").removeClass('tc-edit-mode');
            }
        },
        rgb2hex: function (rgb) {
            rgb = rgb.match(/^rgba?[\s+]?\([\s+]?(\d+)[\s+]?,[\s+]?(\d+)[\s+]?,[\s+]?(\d+)[\s+]?/i);
            return (rgb && rgb.length === 4) ? "#" +
                    ("0" + parseInt(rgb[1], 10).toString(16)).slice(-2) +
                    ("0" + parseInt(rgb[2], 10).toString(16)).slice(-2) +
                    ("0" + parseInt(rgb[3], 10).toString(16)).slice(-2) : '';
        },
        set_default_settings_values: function () {
            //Seatings
            $('#tc_seating_group_title').val('');
            $('#tc_seating_group_widget .tc_seat_add_controls').show();
            $('#tc_seating_group_widget .tc_seat_edit_controls').hide();
            $('#tc_seating_group_widget .tc-seat-rows-slider').show();
            $('#tc_seating_group_widget .tc-seat-cols-slider').show();
            //Standing
            $('#tc_standing_group_title').val('');
            $('#tc_standing_widget .tc_seat_add_controls').show();
            $('#tc_standing_widget .tc_seat_edit_controls').hide();
            $('#tc_standing_widget .tc-assign-ticket-type').show();
            //Table
            $('.tc_table_title').val('');
            $('#tc-table .tc_table_add_controls').show();
            $('#tc-table .tc_table_edit_controls').hide();
            $('#tc-table .tc-input-slider').show();
            //Element
            $('.tc_element_title').val('');
            $('#tc_element_widget .tc_element_add_controls').show();
            $('#tc_element_widget .tc_element_edit_controls').hide();
            //Text
            $('.tc_text_title').val('');
            $('#tc_text_widget .tc_text_add_controls').show();
            $('#tc_text_widget .tc_text_edit_controls').hide();
        },
        next_part_number: function () {
            var next_part_no = $('#tc_part_no').val();
            next_part_no = parseInt(next_part_no) + 1;
            $('#tc_part_no').val(next_part_no);
            return next_part_no;
        },
        set_tabs_inactive: function (event)
        {
            if ($(event.target).attr('class') == 'tc-wrapper') {
//unset all active tabs
                $(".tc-sidebar").tabs({collapsible: true, active: false});
                tc_controls.hide_ticket_type_box();
                $(".tc-group-wrap").removeClass('tc-edit-mode');
            }
        },
        unselect_all: function () {
            //unselect all
            $(".tc-group-wrap *").removeClass('ui-selected');
            $(".tc-group-wrap").removeClass('tc-edit-mode');
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
                            '-webkit-transform': 'scale(' + window.tc_seat_zoom_level + ')',
                            'webkit-transform-origin': '0 0',
                            '-moz-transform': 'scale(' + window.tc_seat_zoom_level + ')',
                            'moz-transform-origin': '0 0',
                            '-ms-transform': 'scale(' + window.tc_seat_zoom_level + ')',
                            '-o-transform': 'scale(' + window.tc_seat_zoom_level + ')',
                            'o-transform-origin': '0 0',
                            'transform': 'scale(' + window.tc_seat_zoom_level + ')',
                            'transform-origin': '0 0'
                        });
//$(".tc-zoom-slider").slider("option", window.tc_seat_zoom_level)
                $('#tc_admin_zoom_level').val(window.tc_seat_zoom_level);
                $(".tc-zoom-slider").slider('value', window.tc_seat_zoom_level);
                $(".tc-wrapper").css('background-size', 80 * window.tc_seat_zoom_level);
            }
        },
        zoom_plus: function () {
            if (window.tc_seat_zoom_level < 1) {
                //window.tc_seat_zoom_level_prev = window.tc_seat_zoom_level;
                window.tc_seat_zoom_level = window.tc_seat_zoom_level + 0.10;
                tc_controls.zoom();
            }
        },
        zoom_minus: function () {
            if (window.tc_seat_zoom_level > 0.3) {
                //window.tc_seat_zoom_level_prev = window.tc_seat_zoom_level;
                window.tc_seat_zoom_level = window.tc_seat_zoom_level - 0.10;
                tc_controls.zoom();
            }
        },
        position_zoom_controls: function () {
            var admin_menu_width = $('#adminmenuwrap').width();
            $('.tc-zoom-wrap').css('left', admin_menu_width + 15)
        },
        set_wrapper_height: function () {
            $('.tc-wrapper').height($(window).height() - $('#wpadminbar').height());
        },
        center: function (element) {
            element.css("position", "absolute");
            var position = jQuery('.tc-pan-wrapper').position();
            if (position.left < 0 && position.top < 0) {
                var top = Math.abs(position.top);
                var left = Math.abs(position.left);
            } else if (position.left > 0 && position.top > 0) {
                var top = -position.top;
                var left = -position.left;
            } else if (position.left > 0 && position.top < 0) {
                var top = Math.abs(position.top);
                var left = -position.left;
            } else if (position.left < 0 && position.top > 0) {
                var top = -position.top;
                var left = Math.abs(position.left);
            }

            var tc_wrap_width = $('.tc-wrapper').width();
            var tc_wrap_width = tc_wrap_width / 2;
            var tc_wrap_height = $('.tc-wrapper').height();
            var tc_wrap_height = tc_wrap_height / 2;
            element.css("top", (top + tc_wrap_height - 50) + "px");
            element.css("left", (left + tc_wrap_width) + "px");
            return element;
        },
        position_pan_wrapper: function (position) {
            var move_val = 50;
            switch (position) {
                case 'up':
                    $('.tc-pan-wrapper').css('top', $('.tc-pan-wrapper').position().top - move_val);
                    tc_controls.position_background();
                    break;
                case 'right':
                    $('.tc-pan-wrapper').css('left', $('.tc-pan-wrapper').position().left + move_val);
                    tc_controls.position_background();
                    break;
                case 'down':
                    $('.tc-pan-wrapper').css('top', $('.tc-pan-wrapper').position().top + move_val);
                    tc_controls.position_background();
                    break;
                case 'left':
                    $('.tc-pan-wrapper').css('left', $('.tc-pan-wrapper').position().left - move_val);
                    tc_controls.position_background();
                    break;
            }
        },
        position_background: function () {
            $(".tc-wrapper").css('background-position-x', $('.tc-pan-wrapper').position().left);
            $(".tc-wrapper").css('background-position-y', $('.tc-pan-wrapper').position().top);
        },
        save_distinct_ticket_types: function () {
            var items = {};
            $('.tc_set_seat').each(function () {
                items[$(this).attr('data-tt-id')] = true;
            });
            var result = new Array();
            for (var i in items)
            {
                result.push(i);
            }
            var results = result.toString();
            $('#tc_ticket_types').val(results);
        },
        save_confirmation: function () {

            var unassigned_seats = 0;

            $.each($(".tc_set_seat"), function () {
                if ($(this).find('span').length == 0) {
                    if (!$(this).hasClass('tc_seat_reserved') && !$(this).hasClass('tc-object-selectable')) {
                        $(this).removeClass('tc_need_seat_label');
                        $(this).addClass('tc_need_seat_label');
                        unassigned_seats++;
                    }
                }
            });

            if (unassigned_seats > 0) {
                $("#tc-seating-required-label-dialog").dialog({
                    bgiframe: true,
                    closeOnEscape: false,
                    draggable: false,
                    resizable: false,
                    dialogClass: "no-close",
                    modal: true,
                    title: tc_controls_vars.are_you_sure,
                    closeText: "<i class='fa fa-times'></i>",
                    buttons: [
                        {
                            text: tc_controls_vars.ok,
                            click: function () {
                                $(this).dialog("close");
                                tc_controls.save_confirmation_callback(false);
                            }
                        },
                                /*{
                                 text: tc_controls_vars.yes,
                                 click: function () {
                                 $(this).dialog("close");
                                 tc_controls.save_confirmation_callback(true);
                                 }
                                 }*/
                    ]
                });
            } else {
                tc_controls.save_confirmation_callback(true);
            }
        },
        save_confirmation_callback: function (value) {
            if (value == true) {
                $('.tc-wrapper').prepend("<div class='tc-chart-preloader'><div class='tc-loader'></div></div>");

                tc_controls.unselect_all();
                tc_controls.save_distinct_ticket_types();

                $('.tc_need_seat_label').removeClass('tc_need_seat_label');
                $('.tc_seat_reserved').removeClass('tc_seat_reserved');

                $('#title').val($('#tc_chart_title').val());
                $('#tc_chart_content').val($('.tc-pan-wrapper').html());
                $('#tc_chart_content_front').val('<div class="tc-pan-wrapper" style="' + $('.tc-pan-wrapper').attr('style') + '">' + $('.tc-pan-wrapper').html() + '</div>');

                //console.log($(".tc-zoom-slider").slider("option", "value"));
                $('#tc_pan_position_left').val($('.tc-pan-wrapper').position().left);
                $('#tc_pan_position_top').val($('.tc-pan-wrapper').position().top);
                $('#publishing-action input[type="submit"]').click();


            }
        },
        save: function () {
            tc_controls.save_confirmation();
        },
        init: function () {
            $('#content-tmce').click();
            window.tc_seat_zoom_level_prev = parseFloat($('#tc_admin_zoom_level').val());
            window.tc_seat_zoom_level = parseFloat($('#tc_admin_zoom_level').val());
            tc_controls.zoom();
            $('.tc-pan-wrapper').css({top: $('#tc_pan_position_top').val() + 'px', left: $('#tc_pan_position_left').val() + 'px', position: 'absolute'});
            tc_controls.position_background();
            tc_controls.set_default_colors();
            tc_controls.tc_mark_reserved_seats();
            tc_controls.tc_mark_reserved_seats($('#post_ID').val());
            $('.tc-wrapper, .tc-sidebar').fadeTo(600, 1, function () {
                // Animation completed.
            });
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
        tc_mark_reserved_seats: function (seat_chart_id) {
            for (var k in tc_reserved_seats[seat_chart_id]) {
                if (tc_reserved_seats[seat_chart_id].hasOwnProperty(k)) {
                    $('#' + k).css({'background-color': tc_controls_vars.tc_reserved_seat_color});
                    $('#' + k).addClass('tc_seat_reserved');
                    $('#' + k).removeClass('ui-selected ui-selectee');
                }
            }
        },
        get_event_ticket_types: function () {
            $('.tc-icon-edit').hide();
            $('.tc-ticket-type-wrap select').prop("disabled", true);
            var event_id = $("select[name*='event_name_post_meta']").val();
            $.post(tc_controls_vars.ajaxUrl, {action: "tc_get_event_ticket_types", event_id: event_id}, function (data) {
                $('.tc-ticket-type-wrap select').remove();
                $('.tc-ticket-type-wrap').append(data);
                $('.tc-icon-edit').show();
            });
        },
        show_ticket_type_box: function (event, ui, show_labels) {
            $('.tc_seat_reserved').removeClass('ui-selected');
            if (show_labels !== false) {
                tc_controls.show_labels(event, ui);
            }
            if ($('.ui-selected').length) {
                $('#tc_ticket_type_widget').show();
                $(".tc-sidebar").tabs({collapsible: true, active: false});
            } else {
                tc_controls.hide_ticket_type_box();
            }

        },
        show_labels: function (event, ui) {
            if ($('.tc_set_seat.ui-selected:not(.tc-object-selectable)').length) {
                $('#tc-seat-labels-num').html(' (' + $('.tc_set_seat.ui-selected:not(.tc-object-selectable)').length + ')');
                $('#tc-seat-labels-settings').show();
                var selected_assigned_ticket_type_num = $('.tc_set_seat.ui-selected:not(.tc-object-selectable)').length;
                if (selected_assigned_ticket_type_num == 1) {
                    var single_label = $('.tc_set_seat.ui-selected span p').html();
                    $('#tc-labels-single-select .tc_label_letter').val(single_label);
                    $('#tc-labels-single-select').show();
                    $('#tc-labels-multi-select').hide();
                } else {//multiple selected
                    $('.tc_label_from_multi').val('1');
                    $('.tc_label_to_multi').val(selected_assigned_ticket_type_num);
                    $('#tc-labels-multi-select').show();
                    $('#tc-labels-single-select').hide();
                }
            } else {
                tc_controls.hide_labels();
            }
        },
        hide_labels: function () {
            $('#tc-seat-labels-settings').hide();
        },
        hide_ticket_type_box: function () {
            $('#tc_ticket_type_widget').hide();
        },
        change_ticket_type: function () {
            
            if ($('#ticket_type_id').val() !== undefined && $('#ticket_type_id').val() != null && $('#ticket_type_id').val().length > 0) {
                $.each($(".ui-selected"), function () {
                    var tc_seat_color = tc_seat_colors[$('#ticket_type_id').val()];
                    if (typeof attr !== typeof undefined && attr !== false) {
                        tc_seat_color = '#0085BA ';
                    }

                    $(this).attr('data-tt-id', $('#ticket_type_id').val());
                    if ($(this).hasClass('tc-table-chair') || $(this).hasClass('tc-object-selectable')) {
                        $(this).animate({'background-color': tc_seat_color}, 250);
                    }

                    if ($(this).hasClass('tc_seat_unit')) {
                        $(this).animate({'background-color': tc_seat_color}, 250);
                    }

                    $(this).removeClass('tc_set_seat');
                    $(this).addClass('tc_set_seat');
                    $(this).removeClass('ui-selected');
                });
            }
            $('#tc_ticket_type_widget').hide();
            $('#tc-seat-labels-settings').hide();
        },
        unset_ticket_type: function () {
            $.each($(".ui-selected"), function () {

                $(this).removeAttr('data-tt-id');
                if ($(this).hasClass('tc-table-chair') || $(this).hasClass('tc_seat_unit')) {
                    $(this).css({'background-color': ''});
                }

                if ($(this).hasClass('tc-icon-seat')) {
                    $(this).css('color', '');
                }

                $(this).removeClass('tc_set_seat');
                $(this).removeClass('ui-selected');
            });
            $('#tc_ticket_type_widget').hide();
            $('#tc-seat-labels-settings').hide();
        }
    }
});