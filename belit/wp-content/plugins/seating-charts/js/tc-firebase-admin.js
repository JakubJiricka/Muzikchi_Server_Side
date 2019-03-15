/**
 * TO DO (for future releases)
 */
jQuery(document).ready(function ($) {
    var config = {
        apiKey: tc_firebase_vars.apiKey,
        authDomain: tc_firebase_vars.authDomain,
        databaseURL: tc_firebase_vars.databaseURL,
    };

    firebase.initializeApp(config);

    function tc_get_checkedin_seats_results(chart_id) {

        var ref = firebase.database().ref('/check-ins/' + chart_id);

        ref.on("child_added", function (data) {
            tc_mark_checked_in_seat(chart_id, data.key);
        });

        ref.on('child_removed', function (data) {
            tc_mark_checked_in_seat_unchecked(chart_id, data.key);
        });
    }

    tc_get_checkedin_seats_results(tc_firebase_vars.tc_chart_id);

    function tc_mark_checked_in_seat(chart_id, seat_id) {
        $('.selectable_row #tc_seat_' + seat_id).find('.tc-add-font').css('color', tc_firebase_vars.tc_checkedin_seat_color);
        $('.selectable_row #tc_seat_' + seat_id).addClass('tc_seat_checked_in');
    }

    function tc_mark_checked_in_seat_unchecked(chart_id, seat_id) {
        tc_set_seat_attributes_firebase('.selectable_row #tc_seat_' + seat_id, tc_seats[seat_id]);
        $('.selectable_row #tc_seat_' + seat_id).removeClass('tc_seat_checked_in');
    }

    function tc_set_seat_attributes_firebase(element, values) {

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
    }

});