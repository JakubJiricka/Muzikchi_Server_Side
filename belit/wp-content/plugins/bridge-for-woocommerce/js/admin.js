jQuery( function ( $ ) {

    /**
     * is ticket control
     */
    $( 'input#_tc_is_ticket' ).change( function () {
        tc_show_and_hide_panels();
    } );

    function tc_show_and_hide_panels() {

        var is_ticket = $( 'input#_tc_is_ticket:checked' ).size();

        if ( is_ticket ) {
            $( '.show_if_tc_ticket' ).show();
        } else {
            $( '.show_if_tc_ticket' ).hide();
        }

    }

    /**
     * _ticket_checkin_availability check
     */

    $( 'input[name="_ticket_checkin_availability"]' ).change( function () {
        tc_show_and_hide_ticket_checkin_availability_dates();
        tc_show_and_hide_ticket_checkin_availability_after_order_time();
    } );

    function tc_show_and_hide_ticket_checkin_availability_dates() {
        var _ticket_checkin_availability_element_exist = $( 'input[name="_ticket_checkin_availability"]' ).size();
        if ( _ticket_checkin_availability_element_exist ) {
            var _ticket_availability = $( 'input[name="_ticket_checkin_availability"]:checked' ).val();

            if ( _ticket_availability == 'range' ) {
                $( '#_ticket_checkin_availability_dates' ).show();
            } else {
                $( '#_ticket_checkin_availability_dates' ).hide();
            }
        }
    }
    
    function tc_show_and_hide_ticket_checkin_availability_after_order_time() {
        var _ticket_checkin_availability_element_exist = $( 'input[name="_ticket_checkin_availability"]' ).size();
        if ( _ticket_checkin_availability_element_exist ) {
            var _ticket_availability = $( 'input[name="_ticket_checkin_availability"]:checked' ).val();

            if ( _ticket_availability == 'time_after_order' ) {
                $( '#_ticket_checkin_availability_after_order_time' ).show();
            } else {
                $( '#_ticket_checkin_availability_after_order_time' ).hide();
            }
        }
    }
    
    /**
     * Selling availability
     */
    $( 'input[name="_ticket_availability"]' ).change( function () {
        tc_show_and_hide_ticket_availability_dates();
    } );

    function tc_show_and_hide_ticket_availability_dates() {
        var _ticket_availability_element_exist = $( 'input[name="_ticket_availability"]' ).size();
        if ( _ticket_availability_element_exist ) {
            var _ticket_availability = $( 'input[name="_ticket_availability"]:checked' ).val();

            if ( _ticket_availability == 'range' ) {
                $( '#_ticket_availability_dates' ).show();
            } else {
                $( '#_ticket_availability_dates' ).hide();
            }
        }
    }
    

    /**
     * General
     */
    $( window ).load( function () {
        var is_ticket = $( 'input#_tc_is_ticket:checked' ).size();

        if ( is_ticket ) {
            $( 'input#_tc_is_ticket' ).prop( 'checked', true );
            tc_show_and_hide_panels();
        }
        tc_show_and_hide_ticket_checkin_availability_dates();
        tc_show_and_hide_ticket_availability_dates();
        tc_show_and_hide_ticket_checkin_availability_after_order_time();
    } );

} );