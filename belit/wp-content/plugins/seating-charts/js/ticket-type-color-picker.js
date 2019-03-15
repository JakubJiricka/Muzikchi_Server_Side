/**
 * Adds color pickers in the admin for seats colors
 * @param {type} param
 */
jQuery( document ).ready( function ( $ ) {
    var tc_color_picker_options = {
        defaultColor: true,
    };
    $( 'input[name=_seat_color_post_meta]' ).wpColorPicker( tc_color_picker_options );
    $( '#reserved_seat_color' ).wpColorPicker( tc_color_picker_options );
    $( '#unavailable_seat_color' ).wpColorPicker( tc_color_picker_options );
    $( '#in_cart_seat_color' ).wpColorPicker( tc_color_picker_options );
    $( '#in_others_cart_seat_color' ).wpColorPicker( tc_color_picker_options );
    $( '#checkedin_seat_color' ).wpColorPicker( tc_color_picker_options );
} );