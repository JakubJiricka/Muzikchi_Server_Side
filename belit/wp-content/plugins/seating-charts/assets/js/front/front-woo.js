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

        $.post(tc_common_vars.ajaxUrl, {action: "tc_load_seating_map", chart_id: seating_map_id, show_legend: show_legend, button_title: button_title, subtotal_title: subtotal_title, cart_title: cart_title}, function (data) {

            $('.tc_seating_map_' + seating_map_id).html(data);
            $('html').css('overflow', 'hidden');

            function tc_cart_hover() {
                var tc_ticket_cart_height = $('.tc-tickets-cart').height();
                $('.tc-tickets-cart').css('bottom', tc_ticket_cart_height * -1);
            }

            tc_mark_in_cart_seats(seating_map_id);
            tc_mark_reserved_seats(seating_map_id);
            tc_mark_unavailable_seats(seating_map_id);
            tc_mark_reserved_standings(seating_map_id);

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
            tc_controls.centerPoint(seating_map_id);
            tc_controls.reposition();
            //tc_controls.centerPoint(seating_map_id);

            //tc_cart_hover();
            $('.tc-chart-preloader').remove();

        });
        //}
    });

    $('body').on('click', '#tc-modal-woobridge .tc_cart_button', function (e) {
        if (!$(this).hasClass('disabled')) {
            e.preventDefault();
            tc_seat_chart_variation_add_to_cart($(this));
        }
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
        });

        var chart_id = $('#tc-modal-added-to-cart .tc_regular_modal_seating_chart_id').val();
        var ticket_type_id = $('#tc-modal-added-to-cart .tc_regular_modal_ticket_type_id').val();
        var seat_id = $('#tc-modal-added-to-cart .tc_regular_modal_seat_id').val();
        var seat_label = $('#tc-modal-added-to-cart .tc_regular_modal_seat_label').html();
        if (tc_seat_chart_ajax.tc_check_firebase == 1) {
            $.post(tc_seat_chart_ajax.ajaxUrl, {action: "tc_remove_seat_from_firebase_cart", seat_id: seat_id, chart_id: chart_id}, function (data) {});
        }
        $.post(tc_seat_chart_ajax.ajaxUrl, {action: "tc_remove_seat_from_cart", seat_ticket_type_id: ticket_type_id, seat_sign: seat_label, seat_id: seat_id, chart_id: chart_id}, function (data) {
            var response = jQuery.parseJSON(data);
            if (response) {
                $('#tc-modal-added-to-cart .tc_remove_from_cart_button').prop("disabled", false);
                $("#tc-modal-added-to-cart").dialog("close");
                $('.tc-seatchart-subtotal').html(response.subtotal + '<strong>' + response.total + '</strong>');/*, .tc-seatchart-subtotal .woocommerce-Price-amount*/
                $('.tc-seatchart-in-cart-count').val(response.in_cart_count);
                $('#tc-modal-added-to-cart button.tc_remove_from_cart_button').html(tc_seat_chart_ajax.tc_remove_from_cart_button_title);
                tc_mark_in_cart_seat();
            }
        });
    }

    function tc_seat_chart_variation_add_to_cart(button) {

        button.prop("disabled", true);
        //tc_add_seat_to_cart_woo_variation
        var serialized_values_original = $("#tc-modal-woobridge form.variations_form").serialize();

        $.post(tc_seat_chart_ajax.ajaxUrl, serialized_values_original, function (data) {
            //
            try {
                var response = jQuery.parseJSON(data);
                if (response) {

                    $('#tc-modal-woobridge .tc-modal-woobridge-inner').html('');
                    $("#tc-modal-woobridge").dialog("close");
                    $('.tc-seatchart-subtotal').html(response.subtotal + '<strong>' + response.total + '</strong>');
                    $('.tc-seatchart-in-cart-count').val(response.in_cart_count);
                }
            } catch (e) {//The error might occur due to the WooCommerce redirection - when WooCommerce > Settings > Products > Display > "Redirect to the basket page after successful addition" IS ENABLED
                serialized_values = serialized_values_original.replace("add-to-cart", "tc-wc-add-to-cart-action")

                $.post(tc_seat_chart_ajax.ajaxUrl, serialized_values, function (data) {
                    var response = jQuery.parseJSON(data);
                    if (response) {
                        $('#tc-modal-woobridge .tc-modal-woobridge-inner').html('');
                        $("#tc-modal-woobridge").dialog("close");
                        $('.tc-seatchart-subtotal').html(response.subtotal + '<strong>' + response.total + '</strong>');
                        $('.tc-seatchart-in-cart-count').val(response.in_cart_count);
                    }
                });
            }
        });


        $('#tc-modal-woobridge button.tc_cart_button').html(tc_seat_chart_ajax.tc_adding_to_cart_title);

        var tc_seat_cart_items = new Array();
        var tc_seat_cart_items_firebase = new Array();

        $.each($(".ui-selected"), function () {
            var chart_id = $('#tc-modal-woobridge .tc_regular_modal_seating_chart_id').val();
            var ticket_type_id = $('#tc-modal-woobridge .tc_regular_modal_ticket_type_id').val();
            var seat_id = $('#tc-modal-woobridge .tc_regular_modal_seat_id').val();
            var seat_label = $('tc-modal-woobridge .tc_regular_modal_seat_label').html();

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

        $.post(tc_seat_chart_ajax.ajaxUrl, {action: "tc_add_seat_to_cart_woo", tc_seat_cart_items: tc_seat_cart_items}, function (data) {
            var response = jQuery.parseJSON(data);
            if (response) {
                $("#tc-regular-modal").dialog("close");
                $('.tc-seatchart-subtotal').html(response.subtotal + '<strong>' + response.total + '</strong>');//response.subtotal + 
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
            //cancel: '.tc_sc_rfc, .tc_seat_in_others_cart, .tc_seat_in_cart, .tc_seat_reserved',
            selected: function (e, ui) {
                if( e.srcElement !== undefined) {
                    var checkElement = e.srcElement;
                    var originalElement = e.originalEvent;
                } else {
                    var checkElement = e.target;
                    var originalElement = e.target;
                    checkElement.id = 'mozila';
                }
                
                var check_class = jQuery(ui.selected).attr('class');

                if (check_class.indexOf('tc_seat_unavailable') == -1 && checkElement.id !== '' && originalElement.type !== 'mousemove' ) {

                    if ($(ui.selected).hasClass('tc_seat_in_others_cart')) {
                        $(ui.selected).removeClass('ui-selected');
                    }

                    var selected = $('.ui-selected').last();

                    if (!selected.hasClass('tc_seat_reserved')) {

                        var seating_chart_id = selected.parents('.tc_seating_map').first().attr('data-seating-chart-id');

                        var ticket_type = selected.attr('data-tt-id');
                        var ticket_type_title = $('li.tt_' + ticket_type).attr('data-tt-title');
                        var ticket_type_price = $('li.tt_' + ticket_type).attr('data-tt-price');
                        var is_variable = $('li.tt_' + ticket_type).attr('data-is-variable');

                        var ticket_seat_number = selected.find('span p').html();

                        if (typeof ticket_seat_number === 'undefined' || !ticket_seat_number) {
                            ticket_seat_number = '';
                        }

                        if (is_variable == '1') {//Variable product


                            if (!selected.hasClass('tc_seat_in_cart') || !selected.hasClass('tc_seat_reserved')) {//add to cart dialog
                                $("#tc-modal-woobridge .tc-modal-woobridge-inner").html(tc_seat_chart_ajax.tc_loading_options_message);

                                $.post(tc_seat_chart_ajax.ajaxUrl, {action: "tc_seat_chart_get_wc_variations", seat_ticket_type_id: ticket_type}, function (data) {

                                    $("#tc-modal-woobridge .tc-modal-woobridge-inner").html(data);
                                    $('#tc-modal-woobridge .single_add_to_cart_button').addClass('disabled wc-variation-selection-needed');
                                    $('#tc-modal-woobridge .input-text.qty.text').hide();

                                    $form = $("#tc-modal-woobridge").find('.variations_form');

                                    if ($form) {
                                        $('#tc-modal-woobridge .wc-variation-selection-needed').addClass('tc_cart_button');
                                        $form.wc_variation_form();
                                        $("#tc-modal-woobridge form.variations_form").append('<input type="hidden" name="action" value="tc_add_seat_to_cart_woo_variation" />');

                                        var seat_id = selected.attr('id');

                                        var seat = ticket_type + '-' + seat_id + '-' + ticket_seat_number + '-' + seating_chart_id;

                                        $("#tc-modal-woobridge form.variations_form").append('<input type="hidden" name="tc_seat_cart_items[]" value="' + seat + '" />');
                                        $("#tc-modal-woobridge form.variations_form").append('<span class="tc_seat_chart_woo_variable_message></span>"');
                                    }

                                });

                                $("#tc-modal-woobridge .tc_regular_modal_ticket_type").html(ticket_type_title);
                                $("#tc-modal-woobridge .tc_regular_modal_seat_label").html(ticket_seat_number);
                                $("#tc-modal-woobridge .tc_regular_modal_price").html(ticket_type_price);

                                var seating_chart_id = $(this).parents('.tc_seating_map').first().attr('data-seating-chart-id');

                                $('#tc-modal-woobridge .tc_regular_modal_seating_chart_id').val(seating_chart_id);

                                $('#tc-modal-woobridge .tc_regular_modal_ticket_type_id').val(ticket_type);

                                $('#tc-modal-woobridge .tc_regular_modal_seat_id').val(selected.attr('id'));

                                $("#tc-modal-woobridge").dialog({
                                    resizable: false,
                                    draggable: false,
                                    height: "auto",
                                    width: 490,
                                    modal: true,
                                    close: function (event, ui) {
                                        $(".tc-group-wrap *").removeClass('ui-selected');
                                    }
                                });
                            } else {//remove from cart dialog
                                $('#tc-modal-added-to-cart button.tc_cart_button').html(tc_seat_chart_ajax.tc_remove_from_cart_button_title);
                                $("#tc-modal-added-to-cart .tc_regular_modal_ticket_type").html(ticket_type_title);
                                $("#tc-modal-added-to-cart .tc_regular_modal_seat_label").html(ticket_seat_number);
                                $("#tc-modal-added-to-cart .tc_regular_modal_price").html(ticket_type_price);

                                var seating_chart_id = $(this).parents('.tc_seating_map').first().attr('data-seating-chart-id');
                                $('#tc-modal-added-to-cart .tc_regular_modal_seating_chart_id').val(seating_chart_id);

                                $('#tc-modal-added-to-cart .tc_regular_modal_ticket_type_id').val(ticket_type);

                                $('#tc-modal-added-to-cart .tc_regular_modal_seat_id').val(selected.attr('id'));

                                $("#tc-modal-added-to-cart").dialog({
                                    resizable: false,
                                    draggable: false,
                                    height: "auto",
                                    width: 490,
                                    modal: true,
                                    close: function (event, ui) {
                                        $(".tc-group-wrap *").removeClass('ui-selected');
                                    }
                                });
                            }
                        } else {//Standard Product
                            
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
                                    resizable: false,
                                    draggable: false,
                                    height: "auto",
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
                                    resizable: false,
                                    draggable: false,
                                    height: "auto",
                                    width: 490,
                                    modal: true,
                                    close: function (event, ui) {
                                        $(".tc-group-wrap *").removeClass('ui-selected');
                                    }
                                });
                            }
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