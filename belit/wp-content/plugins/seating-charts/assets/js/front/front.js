jQuery(document).ready(function ($) {

    $('body').on('click', '.tc_seating_map_button', function (e) {

        $('.tc_seating_map').html('');
        $('.tc-modal-wrap').remove();

        var seating_map_id = $(this).data('seating-map-id');
        var show_legend = $(this).data('show_legend');
        var button_title = $(this).data('button_title');
        var subtotal_title = $(this).data('subtotal_title');
        var cart_title = $(this).data('cart_title');

        var seating_map_html = $('.tc_seating_map_' + seating_map_id).html();

        $(window).on('resize', function (e) {
            tc_controls.centerPoint(seating_map_id);
            tc_controls.reposition();
        });

        $('.tc_seating_map_' + seating_map_id).css({
            position: 'fixed',
            top: 0,
            right: 0,
            bottom: 0,
            left: 0,
            zIndex: 999999,
            display: 'block'
        });

        $('body').prepend('<div class="tc-chart-preloader"><div class="tc-loader"></div></div>');

        $.post(tc_common_vars.ajaxUrl, {
            action: "tc_load_seating_map",
            chart_id: seating_map_id,
            show_legend: show_legend,
            button_title: button_title,
            subtotal_title: subtotal_title,
            cart_title: cart_title
        },
                function (data) {

                    $('.tc_seating_map_' + seating_map_id).html(data);
                    $('html').css('overflow', 'hidden');

                    function tc_cart_hover() {
                        var tc_ticket_cart_height = $('.tc-tickets-cart').height();
                        $('.tc-tickets-cart').css('bottom', tc_ticket_cart_height * -1);
                    }

                    /* MARK SEATS */
                    tc_mark_in_cart_seats(seating_map_id);
                    tc_mark_reserved_seats(seating_map_id);
                    tc_mark_reserved_standings(seating_map_id);
                    tc_mark_unavailable_seats(seating_map_id);

                    /*REMOVE UNNEEDED CLASSES*/
                    $('.tc-group-wrap').removeClass('ui-draggable');
                    $(".tc-group-wrap *").removeClass('ui-draggable-handle');
                    $(".tc-group-wrap").find('.tc-group-controls').remove();
                    $(".tc-group-wrap").find('.ui-resizable-handle').remove();
                    $(".tc-group-wrap").find('.ui-resizable-autohide').removeClass('ui-resizable-autohide');
                    $(".tc-group-wrap").find('.ui-resizable').removeClass('ui-resizable');

                    /* SELECTABLES */
                    tc_front_selectables();

                    /* SET WRAPPER HEIGHT */
                    tc_controls.set_wrapper_height();

                    /* INITIALIZE ZOOM SLIDER */
                    $(".tc-zoom-slider").slider({
                        value: tc_common_vars.front_zoom_level,
                        orientation: "horizontal",
                        min: 0.30,
                        max: 1,
                        step: 0.10,
                        slide: function (event, ui) {
                            var init_zoom = window.tc_seat_zoom_level;

                            $(this).parent().find('.tc-slider-value').val(ui.value);

                            window.tc_seat_zoom_level = ui.value;

                            tc_controls.zoom();
                        },
                        create: function (event, ui) {
                            var bar = $(this).slider('value');
                            $(this).parent().find('.tc-slider-value').val(bar);
                        }
                    });

                    /* INIT CONROLS */

                    tc_controls.init();
                    tc_controls.centerPoint();
                    tc_controls.reposition();
                    tc_controls.centerPoint();

                    //tc_cart_hover();
                    $('.tc-chart-preloader').remove();

                });

        //check requirement of minimum tickets
        $('body').on('click', '.tc-checkout-button', function (event) {
            tc_check_minimum_tickets(event);
        });

    });

    $('body').on('click', '#tc-regular-modal .tc_cart_button', function (e) {
        tc_seat_chart_add_to_cart($(this));
    });

    $('body').on('click', '#tc-modal-added-to-cart .tc_remove_from_cart_button', function (e) {
        tc_seat_chart_remove_from_cart($(this));
    });

    function tc_seat_chart_remove_from_cart(button) {

        button.prop("disabled", true);
        $('#tc-modal-added-to-cart button.tc_remove_from_cart_button').html(tc_seat_chart_ajax.tc_removing_from_cart_title);

        $.each($(".ui-selected"), function () {
            $(this).removeClass('tc_seat_in_cart');
            var ticket_type = $(this).attr('data-tt-id');
            var color = $('li.tt_' + ticket_type).css('color');
            $(this).css({'background-color': color});
        })

        var chart_id = button.parent().find('.tc_regular_modal_seating_chart_id').val();
        var ticket_type_id = button.parent().find('.tc_regular_modal_ticket_type_id').val();
        var seat_id = button.parent().find('.tc_regular_modal_seat_id').val();
        var seat_label = $('.tc_regular_modal_seat_label').html();

        $.post(tc_seat_chart_ajax.ajaxUrl, {action: "tc_remove_seat_from_firebase_cart", seat_id: seat_id, chart_id: chart_id}, function (data) {
        });

        $.post(tc_seat_chart_ajax.ajaxUrl, {action: "tc_remove_seat_from_cart", seat_ticket_type_id: ticket_type_id, seat_sign: seat_label, seat_id: seat_id, chart_id: chart_id}, function (data) {
            var response = jQuery.parseJSON(data);
            if (response) {
                $('#tc-modal-added-to-cart .tc_remove_from_cart_button').prop("disabled", false);
                $("#tc-modal-added-to-cart").dialog("close");
                $('.tc-seatchart-subtotal strong').html(response.total);
                $('.tc-seatchart-in-cart-count').val(response.in_cart_count);
                $('#tc-modal-added-to-cart button.tc_remove_from_cart_button').html(tc_seat_chart_ajax.tc_remove_from_cart_button_title);
                tc_mark_in_cart_seat();
            }
        });
    }

    /* Function checks if requirement of minimum tickets is met*/

    function tc_check_minimum_tickets(event) {
        jQuery('.tc-seating-legend ul li.tc-ticket-listing').each(function () {

            var tc_min_tickets_per_order = jQuery(this).attr('data-min-tickets-per-order');
            var tc_max_tickets_per_order = jQuery(this).attr('data-max-tickets-per-order');

            var tc_ticket_type_id = jQuery(this).attr('data-ticket-type-id');

            if (tc_min_tickets_per_order != 0 || tc_max_tickets_per_order != 0 || tc_min_tickets_per_order != '' || tc_max_tickets_per_order != '') {

                i = 0;

                jQuery('.tc_seat_unit.tc_seat_in_cart').each(function () {
                    var tc_this_ticket_id = jQuery(this).attr('data-tt-id');

                    if (tc_ticket_type_id == tc_this_ticket_id) {
                        i++;
                    }

                }); //jQuery('.tc_seat_in_cart').each(function() {


                if (tc_min_tickets_per_order > i && i !== 0 && tc_min_tickets_per_order != '') {
                    $("#tc-ticket-requirements").dialog({
                        bgiframe: true,
                        closeOnEscape: false,
                        draggable: false,
                        resizable: false,
                        dialogClass: "no-close",
                        modal: true,
                        title: false,
                        closeText: "<i class='fa fa-times'></i>",
                        buttons: [
                            {
                                text: 'OK',
                                click: function () {
                                    $(this).dialog("close");

                                }
                            }
                        ]
                    });

                    event.preventDefault();

                    var tc_ticket_title = jQuery('.tt_' + tc_ticket_type_id).attr('data-tt-title');

                    jQuery('#tc-ticket-requirements').html(tc_seat_chart_ajax.tc_minimum_tickets_message + tc_ticket_title + tc_seat_chart_ajax.tc_minimum_tickets_message_is + tc_min_tickets_per_order + '!');

                } //if(tc_min_tickets_per_order > i)


                if (tc_max_tickets_per_order < i && i !== 0 && tc_max_tickets_per_order != '') {

                    $("#tc-ticket-requirements").dialog({
                        bgiframe: true,
                        closeOnEscape: false,
                        draggable: false,
                        resizable: false,
                        dialogClass: "no-close",
                        modal: true,
                        title: false,
                        closeText: "<i class='fa fa-times'></i>",
                        buttons: [
                            {
                                text: 'OK',
                                click: function () {
                                    $(this).dialog("close");

                                }
                            }
                        ]
                    });

                    event.preventDefault();

                    var tc_ticket_title = jQuery('.tt_' + tc_ticket_type_id).attr('data-tt-title');

                    jQuery('#tc-ticket-requirements').html(tc_seat_chart_ajax.tc_maximum_tickets_message + tc_ticket_title + tc_seat_chart_ajax.tc_minimum_tickets_message_is + tc_max_tickets_per_order + '!');

                } //if(tc_min_tickets_per_order > i)

            } //if (tc_min_tickets_per_order != 0)


        });
    }

    function tc_seat_chart_add_to_cart(button) {
        $('#tc-regular-modal button.tc_cart_button').html(tc_seat_chart_ajax.tc_adding_to_cart_title);
        button.prop("disabled", true);

        var tc_seat_cart_items = new Array();
        var tc_seat_cart_items_firebase = new Array();




        $.each($(".ui-selected"), function () {
            var chart_id = button.parent().find('.tc_regular_modal_seating_chart_id').val();
            var ticket_type_id = button.parent().find('.tc_regular_modal_ticket_type_id').val();
            var seat_id = button.parent().find('.tc_regular_modal_seat_id').val();
            var seat_label = $('#tc-regular-modal .tc_regular_modal_seat_label').html();

            $(this).addClass('tc_seat_in_cart');
            var seat = ticket_type_id + '-' + seat_id + '-' + seat_label + '-' + chart_id;
            tc_seat_cart_items.push(seat);

            var seat_firebase = chart_id + '-' + seat_id;
            tc_seat_cart_items_firebase.push(seat_firebase);
        });

        if (tc_seat_chart_ajax.tc_check_firebase == 1) {
            $.post(tc_seat_chart_ajax.ajaxUrl, {action: "tc_add_seat_to_firebase_cart", tc_seat_cart_items: tc_seat_cart_items_firebase}, function (data) {});
        }

        tc_mark_in_cart_seat();

        $.post(tc_seat_chart_ajax.ajaxUrl, {action: "tc_add_seat_to_cart", tc_seat_cart_items: tc_seat_cart_items}, function (data) {
            var response = jQuery.parseJSON(data);
            if (response) {
                $('#tc-regular-modal .tc_cart_button').prop("disabled", false);
                button.prop("disabled", false);
                $("#tc-regular-modal").dialog("close");
                $('.tc-seatchart-subtotal strong').html(response.total);//response.subtotal + 
                $('.tc-seatchart-in-cart-count').val(response.in_cart_count);
            }
            $('#tc-regular-modal .tc_cart_button').prop("disabled", false);
        });
    }

    function tc_mark_in_cart_seat() {
        $.each($(".tc_seat_in_cart"), function () {
            $(this).css('background-color', tc_seat_chart_ajax.tc_in_cart_seat_color);
            $(this).addClass('tc_seat_in_cart');
            $(this).removeClass('ui-selected ui-selectee');
        });
    }
    /**
     * Mark in-cart seats on the specified chart
     * @param {type} seat_chart_id
     * @returns {undefined}
     */
    function tc_mark_in_cart_seats(seat_chart_id) {
        for (var k in tc_in_cart_seats[seat_chart_id]) {
            if (tc_in_cart_seats[seat_chart_id].hasOwnProperty(k)) {

                $('.tc_seating_map_' + seat_chart_id + ' #' + k).css('background-color', tc_seat_chart_ajax.tc_in_cart_seat_color);
                $('.tc_seating_map_' + seat_chart_id + ' #' + k).addClass('tc_seat_in_cart');
                $('.tc_seating_map_' + seat_chart_id + ' #' + k).removeClass('ui-selected ui-selectee');
            }
        }
    }

    /**
     * Mark reserved seats on the chart
     * @param {type} seat_chart_id
     * @returns {undefined}
     */
    function tc_mark_reserved_seats(seat_chart_id) {
        for (var k in tc_reserved_seats[seat_chart_id]) {
            if (tc_reserved_seats[seat_chart_id].hasOwnProperty(k)) {
                $('.tc_seating_map_' + seat_chart_id + ' #' + k + ':not(.tc-object-selectable)').css('background-color', tc_seat_chart_ajax.tc_reserved_seat_color);
                $('.tc_seating_map_' + seat_chart_id + ' #' + k + ':not(.tc-object-selectable)').addClass('tc_seat_reserved');
                $('.tc_seating_map_' + seat_chart_id + ' #' + k + ':not(.tc-object-selectable)').removeClass('ui-selected ui-selectee');
            }
        }
    }

    function tc_mark_unavailable_seats(seat_chart_id) {
        $('.tc-seating-legend ul li.tc-ticket-listing').each(function () {

            var is_sales_available = $(this).attr('data-is-sales-available');
            var ticket_type_id = $(this).data('ticket-type-id');

            if (is_sales_available !== '1') {
                $('.tc_seat_unit, .tc-object-selectable, .tc-table-chair').each(function () {
                    var tc_this_ticket_id = $(this).attr('data-tt-id');
                    if (ticket_type_id == tc_this_ticket_id) {
                        $(this).css('background-color', tc_seat_chart_ajax.tc_unavailable_seat_color);
                        $(this).addClass('tc_seat_unavailable');
                        $(this).removeClass('ui-selected ui-selectee');
                    }
                });
            }
        });
    }

    //

    function tc_mark_reserved_standings(seat_chart_id) {
        $.each($(".tc-object-selectable"), function () {
            var ticket_type_id = $(this).data('tt-id');
            var qty_left = $('.tc-seating-legend .tt_' + ticket_type_id).data('qty-left');

            if (qty_left == 0) {
                $(this).css('background-color', tc_seat_chart_ajax.tc_reserved_seat_color);
                $(this).addClass('tc_seat_reserved');
                $(this).removeClass('ui-selected ui-selectee');
            }
        });
    }

    function tc_front_selectables() {
        $(".ui-selectable").selectable({
            filter: '.tc_set_seat',
            cancel: '.tc_seat_reserved, .tc_seat_in_others_cart, .tc_seat_unavailable',
            selected: function (e, ui) {
                var check_class = jQuery(ui.selected).attr('class');
                
                if( e.srcElement !== undefined) {
                    var checkElement = e.srcElement;
                } else {
                    var checkElement = e.target;
                    checkElement.id = 'mozila';
                }
                
                
                if (check_class.indexOf('tc_seat_unavailable ') == -1 && checkElement.id !== '' && checkElement.type !== 'mousemove' ) {

                    if ($(ui.selected).hasClass('tc_seat_in_others_cart')) {
                        $(ui.selected).removeClass('ui-selected');
                    }

                    var selected = $('.ui-selected').last();
                                        
                    if (!selected.hasClass('tc_seat_reserved')) {
                        
                        var ticket_type = selected.attr('data-tt-id');
                        var ticket_type_title = $('li.tt_' + ticket_type).attr('data-tt-title');
                        var ticket_type_price = $('li.tt_' + ticket_type).attr('data-tt-price');
                        var ticket_seat_number = selected.find('span p').html();

                        if (typeof ticket_seat_number === 'undefined' || !ticket_seat_number) {
                            ticket_seat_number = '';
                        }

                        if (!selected.hasClass('tc_seat_in_cart')) {
                            $('#tc-regular-modal button.tc_cart_button').html(tc_seat_chart_ajax.tc_add_to_cart_button_title);
                            $("#tc-regular-modal .tc_regular_modal_ticket_type").html(ticket_type_title);
                            $("#tc-regular-modal .tc_regular_modal_seat_label").html(ticket_seat_number);
                            $("#tc-regular-modal .tc_regular_modal_price").html(ticket_type_price);

                            var seating_chart_id = $(this).parents('.tc_seating_map').first().attr('data-seating-chart-id');

                            $('#tc-regular-modal .tc_regular_modal_seating_chart_id').val(seating_chart_id);

                            $('#tc-regular-modal .tc_regular_modal_ticket_type_id').val(ticket_type);

                            $('#tc-regular-modal .tc_regular_modal_seat_id').val(selected.attr('id'));

                            $("#tc-regular-modal").dialog({
                                dialogClass: 'tc-tickera-seating-modal',
                                resizable: false,
                                draggable: false,
                                height: "auto",
                                closeOnEscape: true,
                                width: 490,
                                modal: true,
                                close: function (event, ui) {
                                    $(".tc-group-wrap *").removeClass('ui-selected');
                                }
                            });
                        } else {
                            $('#tc-modal-added-to-cart button.tc_cart_button').html(tc_seat_chart_ajax.tc_remove_from_cart_button_title);
                            $("#tc-modal-added-to-cart .tc_regular_modal_ticket_type").html(ticket_type_title);
                            $("#tc-modal-added-to-cart .tc_regular_modal_seat_label").html(ticket_seat_number);
                            $("#tc-modal-added-to-cart .tc_regular_modal_price").html(ticket_type_price);

                            var seating_chart_id = $(this).parents('.tc_seating_map').first().attr('data-seating-chart-id');
                            $('#tc-modal-added-to-cart .tc_regular_modal_seating_chart_id').val(seating_chart_id);

                            $('#tc-modal-added-to-cart .tc_regular_modal_ticket_type_id').val(ticket_type);

                            $('#tc-modal-added-to-cart .tc_regular_modal_seat_id').val(selected.attr('id'));

                            $("#tc-modal-added-to-cart").dialog({
                                dialogClass: 'tc-tickera-seating-modal',
                                resizable: false,
                                draggable: false,
                                height: "auto",
                                width: 490,
                                closeOnEscape: true,
                                modal: true,
                                close: function (event, ui) {
                                    $(".tc-group-wrap *").removeClass('ui-selected');
                                }
                            });
                        }
                    }
                }
            },
            selecting: function (e, ui) {

                if ($(".ui-selected, .ui-selecting").length > 1) {
                    $('.ui-selecting').removeClass("ui-selecting");
                }

            },
        });
    }


});