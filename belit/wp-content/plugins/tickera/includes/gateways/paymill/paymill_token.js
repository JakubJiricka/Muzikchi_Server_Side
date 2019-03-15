// CreateToken call below

var PAYMILL_PUBLIC_KEY = paymill_token.public_key;

jQuery( document ).ready( function( $ ) {
    var is_error = false;
    var current_payment_method = '';

    function PaymillResponseHandler( error, result ) {
        if ( error ) {
            
            $( '.tc_payment_confirm' ).removeAttr( "disabled" ).show();

            if ( error.apierror == 'field_invalid_card_cvc' ) {
                $( "#paymill_checkout_errors" ).text( paymill_token.invalid_cvc );
            } else if ( error.apierror == 'field_invalid_card_exp' ) {
                $( "#paymill_checkout_errors" ).text( paymill_token.expired_card );
            } else if ( error.apierror == 'field_invalid_card_holder' ) {
                $( "#paymill_checkout_errors" ).text( paymill_token.invalid_cardholder );
            } else {
                $( "#paymill_checkout_errors" ).text( error.apierror );
            }
        } else {
            $( "#paymill_checkout_errors" ).text( "" );
            var form = $( "#tc_payment_form" );
            // Token
            var token = result.token;
            // Insert Paymill token field into form in order to post it
            form.append( "<input type='hidden' name='paymillToken' value='" + token + "'/>" );

            $( "#tc_payment_form" ).get( 0 ).submit();
        }
        $( '.tc_payment_confirm' ).removeAttr( "disabled" ).show();
        $( '#tc_payment_confirm' ).show();
        $( "#tc_payment_confirm" ).removeAttr( "disabled" );
        $( '#paymill_processing' ).hide();
    }

    $( "#tc_payment_form" ).submit( function( event ) {

        //event.preventDefault();

        if ( $( 'input.tc_choose_gateway' ).length ) {
            // If the payment option selected is not Paymill then return and bypass input validations

            if ( $( 'input.tc_choose_gateway:checked' ).val() != "paymill" ) {
                return true;
                current_payment_method = $( 'input.tc_choose_gateway:checked' ).val();
            } else {
                current_payment_method = $( 'input.tc_choose_gateway:checked' ).val();
            }
        } else {
            if ( $( 'input[name="tc_choose_gateway"]' ).val() != "paymill" ) {
                return true;
                current_payment_method = $( 'input[name="tc_choose_gateway"]' ).val();
            } else {
                current_payment_method = $( 'input[name="tc_choose_gateway"]' ).val();
            }
        }

        //current_payment_method = $( 'input.tc_choose_gateway:checked' ).val();
        if ( current_payment_method == 'paymill' ) {

            $( '#paymill_processing' ).show();

            // Deactivate submit button on click
            $( '#tc_payment_confirm' ).attr( "disabled", "disabled" );

            if ( false == paymill.validateCardNumber( $( '.card-number' ).val() ) ) {
                $( "#paymill_checkout_errors" ).text( paymill_token.invalid_cc_number );
                $( '#tc_payment_confirm' ).show();
                $( "#tc_payment_confirm" ).removeAttr( "disabled" );
                $( '.tc_payment_confirm' ).removeAttr( "disabled" ).show();
                is_error = true;
                $( '#paymill_processing' ).hide();
                return false;
            }


            if ( false == paymill.validateExpiry( $( '.card-expiry-month' ).val(), $( '.card-expiry-year' ).val() ) ) {
                $( "#paymill_checkout_errors" ).text( paymill_token.invalid_expiration );
                $( '#tc_payment_confirm' ).show();
                $( "#tc_payment_confirm" ).removeAttr( "disabled" );
                $( '.tc_payment_confirm' ).removeAttr( "disabled" ).show();
                is_error = true;
                $( '#paymill_processing' ).hide();
                return false;
            }

            paymill.createToken( {
                number: $( '.card-number' ).val(),
                exp_month: $( '.card-expiry-month' ).val(),
                exp_year: $( '.card-expiry-year' ).val(),
                cvc: $( '.card-cvc' ).val(),
                cardholdername: $( '.card-holdername' ).val(),
                amount: $( '.amount' ).val(),
                currency: $( '.currency' ).val()
            }, PaymillResponseHandler );
            return false;
        }
    } );

    /* $("#tc_payment_form").submit(function(event) {
     
     // We need to only process if the payment 
     // type is Paymill or Paymill payment gateway is the only option
     
     // If we have the radio buttons allowing the user to select the payment method? ...
     // IF the length is zero then Paymill or some other payment gateway is the only one defined. 
     if ($('input.tc_choose_gateway').length) {
     
     // If the payment option selected is not Paymill then return and bypass input validations
     if ($('input.tc_choose_gateway:checked').val() != "paymill") {
     return true;
     }
     }
     
     //clear errors
     $("#paymill_checkout_errors").empty();
     
     if (is_error)
     return false;
     // disable the submit button to prevent repeated clicks
     $('#tc_payment_confirm').attr("disabled", "disabled").hide();
     $('#paymill_processing').show();
     });*/
} );