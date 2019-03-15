// CreateToken call below

jQuery( document ).ready( function( $ ) {
    var current_payment_method = '';

    $( "#tc_payment_form" ).submit( function( event ) {

        //event.preventDefault();

        if ( $( 'input.tc_choose_gateway' ).length ) {
            // If the payment option selected is not Paymill then return and bypass input validations

            if ( $( 'input.tc_choose_gateway:checked' ).val() != "paypal_pro" ) {
                current_payment_method = $( 'input.tc_choose_gateway:checked' ).val();
            } else {
                current_payment_method = $( 'input.tc_choose_gateway:checked' ).val();
            }
        } else {
            if ( $( 'input[name="tc_choose_gateway"]' ).val() != "paypal_pro" ) {
                current_payment_method = $( 'input[name="tc_choose_gateway"]' ).val();
            } else {
                current_payment_method = $( 'input[name="tc_choose_gateway"]' ).val();
            }
        }

        //current_payment_method = $( 'input.tc_choose_gateway:checked' ).val();
        if ( current_payment_method == 'paypal_pro' ) {

            $( '#paypal_processing' ).show();

            // Deactivate submit button on click
            $( '#tc_payment_confirm' ).attr( "disabled", "disabled" );

        }
        return true;
    } );

} );