jQuery(function ($) {

    $("#tc_tickets_additional_tabs").tabs();

    function tc_get_event_new_ticket_types() {

        $('#ticket_type_new_holder #ticket_type_new').remove();

        var event_id = $('.event_new').val();

        var select_content = '<select name="ticket_type_new" id="ticket_type_new"><option value=""></option>';

        if (typeof ticket_types_list[event_id] !== "undefined") {
            for (var key in ticket_types_list[event_id]) {
                select_content += '<option value="' + key + '">' + ticket_types_list[event_id][key] + '</option>';
            }
        }

        select_content += '</select>';

        $('#ticket_type_new_holder').html(select_content);

        maybe_get_ticket_type_custom_form();
    }

    function maybe_get_ticket_type_custom_form() {
        if ($('.custom_fields_col').length) {
            var selected_ticket_type = $('#ticket_type_new').val();
            if (selected_ticket_type !== null) {
                //Show preloader
                $('.custom_fields').html('');

                //Load custom form if exists
                var custom_forms_html = ticket_types_custom_forms[selected_ticket_type];

                var result = '';

                if (custom_forms_html !== '' && typeof custom_forms_html !== 'undefined') {
                    result = custom_forms_html.slice(1, -1);
                }

                $('.custom_fields').html(result);
            } else {
                $('.custom_fields').html('-');
            }
        }
    }

    $('body').on('change', '.event_new', function (e) {
        tc_get_event_new_ticket_types();
    });

    $('body').on('change', '#ticket_type_new', function (e) {
        maybe_get_ticket_type_custom_form();
    });

    tc_get_event_new_ticket_types();
});