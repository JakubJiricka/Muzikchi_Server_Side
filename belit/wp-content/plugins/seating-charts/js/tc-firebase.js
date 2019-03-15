jQuery(document).ready(function ($) {

    var config = {
        apiKey: tc_firebase_vars.apiKey,
        authDomain: tc_firebase_vars.authDomain,
        databaseURL: tc_firebase_vars.databaseURL,
    };
    firebase.initializeApp(config);
    window.tc_firebase = {
        init: function (chart_id) {
            tc_firebase.get_in_cart_seats_results(chart_id);
            tc_firebase.get_reserved_seats_results(chart_id);

            setInterval(function () {
                tc_firebase.get_in_cart_seats_results(chart_id);
                tc_firebase.get_reserved_seats_results(chart_id);
            }, 2 * 60 * 1000);

            setTimeout(function () {
                tc_firebase.send_seats_to_firebase_cart(chart_id);
            }, 1000);

            setInterval(function () {
                tc_firebase.send_seats_to_firebase_cart(chart_id);
            }, 60 * 1000);
        },
        send_seats_to_firebase_cart: function (chart_id) {
            var tc_seat_cart_items = new Array();
            $.each($(".tc_seat_in_cart:not(.tc-object-selectable)"), function () {
                var seat_id = $(this).attr('id');
                var seat = chart_id + '-' + seat_id;
                tc_seat_cart_items.push(seat);
            });
            $.post(tc_seat_chart_ajax.ajaxUrl, {action: "tc_add_seat_to_firebase_cart", tc_seat_cart_items: tc_seat_cart_items}, function (data) {});
        },
        get_reserved_seats_results: function (chart_id) {
            var ref = firebase.database().ref('/reserved/' + chart_id);
            var two_minutes_ago = ServerDate.now() - (2 * 60 * 1000);
            ref.orderByChild("timestamp").startAt(two_minutes_ago).on("child_added", function (data) {
                $('.tc_seating_map_' + chart_id + ' #' + data.key + ':not(.tc-object-selectable)').css('background-color', tc_seat_chart_ajax.tc_reserved_seat_color);
                $('.tc_seating_map_' + chart_id + ' #' + data.key + ':not(.tc-object-selectable)').removeClass('ui-selected ui-selectee');
                $('.tc_seating_map_' + chart_id + ' #' + data.key + ':not(.tc-object-selectable)').addClass('tc_seat_reserved');
            });
        },
        get_in_cart_seats_results: function (chart_id) {
            var ref = firebase.database().ref('/in-cart/' + chart_id);
            var two_minutes_ago = ServerDate.now() - (2 * 60 * 1000);
            ref.orderByChild("timestamp").startAt(two_minutes_ago).on("child_added", function (data) {
                if (data.val().session_id != tc_firebase_vars.session_id) {
                    tc_firebase.mark_in_others_cart_seat(chart_id, data.key);
                }
            });
            ref.on('child_removed', function (data) {
                tc_firebase.mark_available_seat(chart_id, data.key);
            });
        },
        mark_in_others_cart_seat: function (chart_id, seat_id) {
            $('.tc_seating_map_' + chart_id + ' #' + seat_id + ':not(.tc-object-selectable)').css('background-color', tc_firebase_vars.tc_in_others_cart_seat_color);
            $('.tc_seating_map_' + chart_id + ' #' + seat_id + ':not(.tc-object-selectable)').addClass('tc_seat_in_others_cart');
            $('.tc_seating_map_' + chart_id + ' #' + seat_id + ':not(.tc-object-selectable)').removeClass('ui-selected ui-selectee');
        },
        mark_available_seat: function (chart_id, seat_id) {
            ticket_type = $('.tc_seating_map_' + chart_id + ' #' + seat_id).attr('data-tt-id');
            var color = $('li.tt_' + ticket_type).css('color');
            $('.tc_seating_map_' + chart_id + ' #' + seat_id + ':not(.tc-object-selectable)').css({'background-color': color});
            $('.tc_seating_map_' + chart_id + ' #' + seat_id + ':not(.tc-object-selectable)').removeClass('tc_seat_in_cart tc_seat_in_others_cart');
            $('.tc_seating_map_' + chart_id + ' #' + seat_id + ':not(.tc-object-selectable)').addClass('ui-selectee');
        }
    }
});

