jQuery(document).ready(function ($) {

    //jQuery( '.tc_custom_forms_editable .field-checkbox' ).change( function () {
    $('body').on('change', '.tc_custom_forms_editable .field-checkbox', function (e) {
        var checkbox_values_field = jQuery(this).parent().parent().find('.checkbox_values');

        checkbox_values_field.val('');

        jQuery(this).parent().parent().find('input').each(function (key, value) {
            if (jQuery(this).attr('checked')) {
                checkbox_values_field.val(checkbox_values_field.val() + '' + jQuery(this).val( ) + ',');
            }
        });
        //checkbox_values_field.val( checkbox_values_field.val().substring( 0, checkbox_values_field.val().length - 2 ) );

    });

    function sticky_relocate() {
        if ($("#sticky-anchor").length) {
            var window_top = $(window).scrollTop();
            var div_top = $('#sticky-anchor').offset().top;
            if (window_top > div_top) {
                $('.tc-custom-forms-dragables-wrap').addClass('tc-custom-form-stick');
            } else {
                $('.tc-custom-forms-dragables-wrap').removeClass('tc-custom-form-stick');
            }
        }
    }

    $(function () {
        $(window).scroll(sticky_relocate);
        sticky_relocate();
    });


    $(".rows").sortable({
        items: 'ul',
        receive: function (template, ui) {
            update_rows();
        },
        stop: function (template, ui) {
            update_rows();
        }
    });

    var template_classes = new Array();
    var parent_id = 0;

    $(".draggable li").draggable({
        helper: "clone",
        connectToSortable: ".form-layout ul.sortables"
    });

    /*$( "#side-sortables ul.sortables" ).sortable( {
     connectWith: 'ul',
     forcePlaceholderSize: true,
     helper: "clone",
     //placeholder: "ui-state-highlight",
     receive: function( template, ui ) {
     },
     } );*/

    $(".form-layout ul.sortables").sortable({
        connectWith: 'ul',
        forcePlaceholderSize: true,
        //placeholder: "ui-state-highlight",
        receive: function (template, ui) {
            $(".rows ul li").last().addClass("last_child");

            var $this = $(this);

            /*if ( $this.children( 'li' ).length > tc_custom_fields_vars.max_elements ) {
             alert( tc_custom_fields_vars.max_elements_message );
             $( this ).data().uiSortable.currentItem.remove();
             }*/

            update_li();
        },
        stop: function (template, ui) {
            update_li();
            $(".rows ul li").last().addClass("last_child");
        }
    });

    //$( ".sortables" ).disableSelection();

    function update_rows() {
        $(".rows ul").each(function (index) {
            $(this).attr('id', 'row_' + (index + 1));
            $(this).find('.rows_classes').attr('name', 'rows_' + (index + 1) + '_post_meta');
            $(this).find('.field_row').val(index + 1);
        });
    }

    function update_li( ) {

        var children_num = 0;
        var current_child_num = 0;

        $(".rows ul").each(function () {

            var row_id = $(this).attr('id');
            if (typeof row_id !== 'undefined') {
                var row_num = row_id.replace("row_", "");
                $('#' + row_id + ' .field_row').val(row_num);
            }

            template_classes.length = 0; //empty the array

            children_num = $(this).children('li').length;

            $(this).children('li').removeClass();
            $(this).children('li').addClass("ui-state-default");
            $(this).children('li').addClass("cols cols_" + children_num);
            $(this).children('li').last().addClass("last_child");
            $(this).find('li').each(function (index, element) {

                $(this).find('.field_order').val(index);

                if ($.inArray($(this).attr('data-class'), template_classes) == -1) {
                    template_classes.push($(this).attr('data-class'));
                }

            });

            $(this).find('.rows_classes').val(template_classes.join());

        });

        tc_fix_template_elements_sizes()

        fix_chosen();

    }

    function fix_chosen() {
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
                $(this).height(maxHeight + 10);
            });
        });

        $("#side-sortables .sortables li").each(function () {
            $(this).height('auto');
        });
    }

    update_li();

    tc_fix_template_elements_sizes();

    $(window).resize(function () {
        tc_fix_template_elements_sizes();
    });


    /* Native WP media browser for file module (for instructors) */
    $('.file_url_button').live('click', function ()
    {
        var target_url_field = jQuery(this).prevAll(".file_url:first");
        wp.media.editor.send.attachment = function (props, attachment)
        {
            $(target_url_field).val(attachment.url);
        };
        wp.media.editor.open(this);
        return false;
    });

    $('.tc_forms_wrap #side-sortables input[type="text"]').val('');
    $('.tc_forms_wrap #side-sortables input[type="hidden"]').val('');
    $('.tc_forms_wrap #side-sortables textarea').val('');

    $('.tc-custom-field-delete').live('click', function () {
        var post_id = $(this).parent().parent().find('.field_post_id').val();
        $(this).parent().parent().remove();
        if (post_id !== '') {
            $('.tc_forms_wrap form').append('<input type="hidden" name="fields_to_remove[]" value="' + post_id + '" />');
        }
        update_li();
        tc_fix_template_elements_sizes();
    });

    $('.required_check').live('change', function () {
        if (this.checked) {
            $(this).parent().find('.field_required').val('1');
        } else {
            $(this).parent().find('.field_required').val('0');
        }
    });

    $('.order_column_check').live('change', function () {
        if (this.checked) {
            $(this).parent().find('.field_order_column').val('1');
        } else {
            $(this).parent().find('.field_order_column').val('0');
        }
    });

    $('.order_details_check').live('change', function () {
        if (this.checked) {
            $(this).parent().find('.field_order_details').val('1');
        } else {
            $(this).parent().find('.field_order_details').val('0');
        }
    });

    $('.export_check').live('change', function () {
        if (this.checked) {
            $(this).parent().find('.field_export').val('1');
        } else {
            $(this).parent().find('.field_export').val('0');
        }
    });

    $('.as_ticket_template_check').live('change', function () {
        if (this.checked) {
            $(this).parent().find('.field_as_ticket_template').val('1');
        } else {
            $(this).parent().find('.field_as_ticket_template').val('0');
        }
    });



});