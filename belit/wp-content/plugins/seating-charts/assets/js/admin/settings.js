jQuery(document).ready(function ($) {
    /**
     * Settings / General Events
     */


    $('body').on('click', '#tc-seat-labels-settings .tc-change-button', function (e) {
        e.preventDefault();
        tc_labels.assign();
    });

    $('body').on('click', '.tc_col_label_invert', function (e) {
        e.preventDefault();
        var col_sign_from = $('.tc_label_from_multi').val();
        var col_sign_to = $('.tc_label_to_multi').val();

        $('.tc_label_from_multi').val(col_sign_to);
        $('.tc_label_to_multi').val(col_sign_from);
    });

    $('body').on('change', '#tc-settings .tc-event-wrap select', function (e) {
        var selected_event_id = $("#tc-settings .tc-event-wrap option:selected").val();
        var selected_event_id_orig = $('#tc_init_event_id').val();

        if (selected_event_id !== selected_event_id_orig) {
            $('#tc-settings .tc-change-button').show();
        } else {
            $('#tc-settings .tc-change-button').hide();
        }
    });

    $('body').on('click', '#tc-settings .tc-change-button', function (e) {
        e.preventDefault();
        tc_controls.change_event_confirmation();
    });

    $('body').on('click', '#tc_ticket_type_widget .tc-change-button', function (e) {
        e.preventDefault();
        tc_controls.change_ticket_type();
    });

    $('body').on('click', '#tc_ticket_type_widget .tc-cancel-button', function (e) {
        e.preventDefault();
        tc_labels.unset(false);
        tc_controls.unset_ticket_type();
    });

    $('body').on('click', '#tc-seat-labels-settings .tc-cancel-button', function (e) {
        e.preventDefault();
        tc_labels.unset(true);
    });

    /**
     * Standing Events
     */
    $('body').on('click', '#tc_add_standing_button', function (e) {
        e.preventDefault();
        tc_standing.create();
    });

    /**
     * Element Events
     */

    $('body').on('click', '#tc_element_widget #tc_add_element_button', function (e) {
        e.preventDefault();
        tc_element.create();
    });

    $('body').on('change', '#tc_element_widget input[name=tc-element-selection]', function (e) {
        var title = $(this).next().attr('title');
        $('#tc_element_widget .tc_element_title').val(title);
    });

    /**
     * Text Events
     */

    $('body').on('click', '#tc_add_text_button', function (e) {
        e.preventDefault();
        tc_text.create();
    });

    /**
     * Table Events
     */


    $('body').on('change', '.tc_seat_table_type', function (e) {
        if ($('input:radio[name=tc_seat_table_type]:checked').val() == 'circle') {
            $('.tc_end_seats_holder').hide();
        } else {
            $('.tc_end_seats_holder').show();
        }
    });

    $('body').on('click', '.tc-table-wrap .tc-group-controls .tc-icon-trash', function (e) {
        e.preventDefault();
        tc_table.delete($(this));
    });



    $('body').on('click', '#tc_seating_group_widget #tc_add_seats_button, #tc_standing_widget #tc_add_standing_button, #tc-table .tc-change-button, #tc_element_widget #tc_add_element_button, #tc_text_widget #tc_add_text_button', function (e) {
        $('#tc_seating_group_title').val('');
        $('#tc_standing_group_title').val('');
        $('.tc_table_title').val('');
        $('.tc_element_title').val('');
        $('.tc_text_title').val('');
    });

});
    