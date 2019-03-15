jQuery(document).ready(function ($) {

    //$form.find( '#publish, #save-post,.save-bulk-form, [type="submit"]' ).click( function( e ) {


    jQuery(".post-type-tc_tickets #post, .post-type-tc_events #post").validate({ ignore: '[id^="acf"]' });


    jQuery("#tc-general-settings, #tc_ticket_type_form, #tc_discount_code_form, .tc_form_validation_required").validate({
        rules: {
            /* field: {
             required: true,
             number: true
             },*/
            field: {
                number: true
            },
            field: {
                required: true
            }
        }
    });



    if ($('#discount_type').length && $('#discount_value').length) {
        tc_check_discount_code_type();

        $('#discount_type').change(function () {
            tc_check_discount_code_type();
        });

    }

    function tc_check_discount_code_type() {
        if ($('#discount_type').length && $('#discount_value').length) {
            if ($("#discount_type option:selected").val() == '3') {
                $('tr.discount_availability').hide();
            } else {
                $('tr.discount_availability').show();
            }
        }
    }

    //jQuery( '#tc-general-settings' ).validate();

    $('.has_conditional').live('change', function ( ) {
        tc_conditionals_init( );
    });

    function tc_conditionals_init( ) {
        $('.tc_conditional').each(function (i, obj) {
            tc_conditional($(this));
        });
    }

    function tc_conditional(obj) {

        var field_name = $(obj).attr('data-condition-field_name');
        if (!$('.' + field_name).hasClass('has_conditional')) {
            $('.' + field_name).addClass('has_conditional');
        }

        var field_type = $(obj).attr('data-condition-field_type');
        var value = $(obj).attr('data-condition-value');
        var action = $(obj).attr('data-condition-action');
        if (field_type == 'radio') {
            var selected_value = $('.' + field_name + ':checked').val( );
            //alert(selected_value);
        }

        if (field_type == 'text' || field_type == 'textarea' || field_type == 'select') {
            var selected_value = $('.' + field_name).val( );
        }

        if (value == selected_value) {
            if (action == 'hide') {
                $(obj).hide();
            }
            if (action == 'show') {
                $(obj).show(200);
            }
        } else {
            if (action == 'hide') {
                $(obj).show(200);
            }
            if (action == 'show') {
                $(obj).hide();
            }
        }

        fix_chosen();
    }

    tc_conditionals_init( );

    $('.tc_tooltip').tooltip({
        content: function () {
            return $(this).prop('title');
        },
        show: null,
        close: function (event, ui) {
            ui.tooltip.hover(
                    function () {
                        $(this).stop(true).fadeTo(100, 1);
                    },
                    function () {
                        $(this).fadeOut("100", function () {
                            $(this).remove();
                        })
                    });
        }
    });

    /* Toggle Controls */
    //tickera_page_tc_ticket_types
    var tc_event_id = 0;
    var tc_ticket_id = 0;
    var tc_event_status = 'publish';
    var tc_ticket_status = 'publish';

    var tc_toggle = {
        init: function () {
            $('body').addClass('tctgl');
            this.attachHandlers('.tctgl .tc-control');
        },
        tc_controls: {
            $tc_toggle_init: function (selector)
            {
                $(selector).click(function ()
                {
                    tc_event_id = $(this).attr('event_id');
                    tc_ticket_id = $(this).attr('ticket_id');

                    if ($(this).hasClass('tc-on')) {
                        $(this).removeClass('tc-on');
                        tc_event_status = 'private';
                        tc_ticket_status = 'private';
                    } else {
                        $(this).addClass('tc-on');
                        tc_event_status = 'publish';
                        tc_ticket_status = 'publish';
                    }

                    var attr = $(this).attr('event_id');
                    if (typeof attr !== typeof undefined && attr !== false) {//Event toggle
                        $.post(
                                tc_vars.ajaxUrl, {
                                    action: 'change_event_status',
                                    event_status: tc_event_status,
                                    event_id: tc_event_id,
                                }
                        );
                    } else {
                        $.post(
                                tc_vars.ajaxUrl, {
                                    action: 'change_ticket_status',
                                    ticket_status: tc_ticket_status,
                                    ticket_id: tc_ticket_id,
                                }
                        );
                    }


                });

            }
        },
        attachHandlers: function (selector) {
            this.tc_controls.$tc_toggle_init(selector);
        }
    };

    tc_toggle.init();


    $("input.tc_active_gateways").change(function () {
        //alert($(this).val());
        var currently_selected_gateway_name = $(this).val();
        var checked = $(this).attr('checked');
        if (checked == 'checked') {
            $('#' + currently_selected_gateway_name).show(200);
        } else {
            $('#' + currently_selected_gateway_name).hide(200);
        }
    });


    if (tc_vars.animated_transitions) {
        $(".tc_wrap").fadeTo(250, 1);
        $(".tc_wrap #message").delay(2000).slideUp(250);
    } else {
        $(".tc_wrap").fadeTo(0, 1);
    }


    $('.tc_delete_link').click(function (event)
    {
        tc_delete(event);
    });

    function tc_delete_confirmed() {
        return confirm(tc_vars.delete_confirmation_message);
    }

    function tc_delete(event) {
        if (tc_delete_confirmed()) {
            return true;
        } else {
            event.preventDefault()
            return false;
        }
    }


    $('.file_url_button').click(function ()
    {
        var target_url_field = $(this).prevAll(".file_url:first");
        wp.media.editor.send.attachment = function (props, attachment)
        {
            $(target_url_field).val(attachment.url);
        };
        wp.media.editor.open(this);
        return false;
    });


    /* Ticket Tempaltes */

    var ticket_classes = new Array();
    var parent_id = 0;

    $('.tc-color-picker').wpColorPicker();
    $("ul.sortables").sortable({
        connectWith: 'ul',
        forcePlaceholderSize: true,
        //placeholder: "ui-state-highlight",
        receive: function (template, ui) {
            update_li();
            $(".rows ul li").last().addClass("last_child");
        },
        stop: function (template, ui) {
            update_li();
        }
    })/*.disableSelection()*/;

    //$( ".sortables" ).disableSelection();

    function update_li() {

        var children_num = 0;
        var current_child_num = 0;

        $(".rows ul").each(function () {

            ticket_classes.length = 0; //empty the array

            children_num = $(this).children('li').length;
            $(this).children('li').removeClass();
            $(this).children('li').addClass("ui-state-default");
            $(this).children('li').addClass("cols cols_" + children_num);
            $(this).children('li').last().addClass("last_child");
            $(this).find('li').each(function (index, element) {
                if ($.inArray($(this).attr('data-class'), ticket_classes) == -1) {
                    ticket_classes.push($(this).attr('data-class'));
                }
            });
            $(this).find('.rows_classes').val(ticket_classes.join());
        });
        tc_fix_template_elements_sizes();

        $(".rows ul li").last().addClass("last_child");
        $(".tc_wrap select").css('width', '25em');
        $(".tc_wrap select").css('display', 'block');

        $(".tc_wrap select").chosen({disable_search_threshold: 5});
        $(".tc_wrap select").css('display', 'none');
        $(".tc_wrap .chosen-container").css('width', '100%');
        $(".tc_wrap .chosen-container").css('max-width', '25em');
        $(".tc_wrap .chosen-container").css('min-width', '1em');
    }

    function tc_fix_template_elements_sizes() {
        $(".rows ul").each(function () {
            var maxHeight = -1;

            $(this).find('li').each(function () {
                $(this).removeAttr("style");
                maxHeight = maxHeight > $(this).height() ? maxHeight : $(this).height();
            });

            $(this).find('li').each(function () {
                $(this).height(maxHeight);
            });
        });
    }


    if ($('#ticket_elements').length) {
        update_li();
        tc_fix_template_elements_sizes();
    }


    jQuery('.close-this').click(function (event) {
        event.preventDefault();
        jQuery(this).closest('.ui-state-default').appendTo('#ticket_elements');
        update_li();
        tc_fix_template_elements_sizes();
    });



    function fix_chosen() {
        $(".tc_wrap select").css('width', '25em');
        $(".tc_wrap select").css('display', 'block');
        $(".tc_wrap select").chosen({disable_search_threshold: 5, allow_single_deselect: false});
        $(".tc_wrap select").css('display', 'none');
        $(".tc_wrap .chosen-container").css('width', '100%');
        $(".tc_wrap .chosen-container").css('max-width', '25em');
        $(".tc_wrap .chosen-container").css('min-width', '1em');
    }

    /*$( '.order_status_change' ).on( 'change', function () {
     var new_status = $( this ).val();
     var order_id = $( '#order_id' ).val();
     
     $.post( tc_vars.ajaxUrl, { action: "change_order_status", order_id: order_id, new_status: new_status }, function ( data ) {
     if ( data != 'error' ) {
     $( '.tc_wrap .message_placeholder' ).html( '' );
     $( '.tc_wrap .message_placeholder' ).append( '<div id="message" class="updated fade"><p>' + tc_vars.order_status_changed_message + '</p></div>' );
     $( ".tc_wrap .message_placeholder" ).show( 250 );
     $( ".tc_wrap .message_placeholder" ).delay( 2000 ).slideUp( 250 );
     } else {
     //current_form.html(data);//Show error message
     }
     $( this ).fadeTo( "fast", 1 );
     } );
     } );*/

    $('#tc_order_resend_condirmation_email').on('click', function (event) {
        event.preventDefault();
        var new_status = $('.order_status_change').val();
        var order_id = $('#order_id').val();

        $(this).hide();
        $(this).after('<span id="tc_resending">' + tc_vars.order_confirmation_email_resending_message + '</a>');

        $.post(tc_vars.ajaxUrl, {action: "change_order_status", order_id: order_id, new_status: new_status}, function (data) {
            if (data != 'error') {

                $('.tc_wrap .message_placeholder').html('');
                $('.tc_wrap .message_placeholder').append('<div id="message" class="updated fade"><p>' + tc_vars.order_confirmation_email_resent_message + '</p></div>');
                $(".tc_wrap .message_placeholder").show(250);
                $(".tc_wrap .message_placeholder").delay(2000).slideUp(250);
                $('#tc_order_resend_condirmation_email').show();
                $('#tc_resending').remove();
            } else {
                //current_form.html(data);//Show error message
            }
            $(this).fadeTo("fast", 1);
        });

    });



    /* PAYMENT GATEWAY IMAGE SWITCH */

    jQuery(".tc_active_gateways").each(function () {

        if (this.checked) {
            jQuery(this).closest('.image-check-wrap').toggleClass('active-gateway');
        }

        jQuery(this).change(function () {
            fix_chosen();

            if (this.checked) {
                jQuery(this).closest('.image-check-wrap').toggleClass('active-gateway');
            } else {
                jQuery(this).closest('.image-check-wrap').toggleClass('active-gateway');
            }
        });
    })

    if (jQuery('#tickets_limit_type').val() == 'event_level') {
        jQuery('#event_ticket_limit').parent().parent().show();
    } else {
        jQuery('#event_ticket_limit').parent().parent().hide();
    }

    jQuery('#tickets_limit_type').on('change', function () {
        if (jQuery('#tickets_limit_type').val() == 'event_level') {
            jQuery('#event_ticket_limit').parent().parent().show();
        } else {
            jQuery('#event_ticket_limit').parent().parent().hide();
        }
    });

    $(".tc_wrap select").chosen({disable_search_threshold: 5, allow_single_deselect: false});

    /* INLINE EDIT */
    $.fn.inlineEdit = function (replaceWith, connectWith) {

        $(this).hover(function ( ) {
            $(this).addClass('inline_hover');
        }, function () {
            $(this).removeClass('inline_hover');
        }
        );
        $(this).click(function () {

            var orig_val = $(this).html();
            $(replaceWith).val($.trim(orig_val));

            var elem = $(this);

            elem.hide();
            elem.after(replaceWith);
            replaceWith.focus();

            replaceWith.blur(function () {

                if ($(this).val() != "") {
                    connectWith.val($(this).val()).change();
                    elem.text($(this).val());
                }

                elem.text($(this).val( ));

                var ticket_id = $(this).parent('tr').find('.ID');
                ticket_id = ticket_id.attr('data-id');

                save_attendee_info(ticket_id, $(this).prev().attr('class'), $(this).val());

                $(this).remove();
                elem.show();

            });
        });
    };

    $(".tc_temp_value").live('keyup', function (e) {
        if (e.keyCode == 13) {
            $(this).blur( );
        }
        e.preventDefault( );
    });

    function save_attendee_info(ticket_id, meta_name, meta_value) {
        var data = {
            action: 'save_attendee_info',
            post_id: ticket_id,
            meta_name: meta_name,
            meta_value: meta_value
        }
        $.post(tc_vars.ajaxUrl, data);
    }


    /*jQuery('.tc_wrap input .post-type-tc_events input').iCheck({
     labelHover: false,
     cursor: true
     });*/


});

