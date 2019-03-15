jQuery(document).ready(function ($) {

    function simplifyResponseHandler(status, response) {

        var hasExpirationMessage = false;
        if (status.error) {
            
            $('.tc_payment_confirm').removeAttr("disabled").show();

            for (var i = 0; i < status.error.fieldErrors.length; i++) {

                var fieldName = status.error.fieldErrors[i].field;
                var currentErrMessage = status.error.fieldErrors[i].message;



                switch (fieldName) {

                    case 'card.name':
                        $('#simplify_commerce_checkout_errors').append('<div class="tc_checkout_error"> ' + '<strong>Card Name:</strong>' + currentErrMessage + '</div>');
                        break;

                    case 'card.number':
                        $('#simplify_commerce_checkout_errors').append('<div class="tc_checkout_error"> ' + '<strong>Card Number:</strong>' + currentErrMessage + '</div>');
                        break;

                    case 'card.expYear':
                        $('#simplify_commerce_checkout_errors').append('<div class="tc_checkout_error"> ' + '<strong>Card Expiration Year:</strong>' + currentErrMessage + '</div>');
                        break;

                    case 'card.expMonth':

                        $('#simplify_commerce_checkout_errors').append('<div class="tc_checkout_error"> ' + '<strong>Card Expiration Month:</strong>' + currentErrMessage + '</div>');
                        break;

                    case 'card.cvv':
                        $('#simplify_commerce_checkout_errors').append('<div class="tc_checkout_error"> ' + '<strong>Card CVV:</strong>' + currentErrMessage + '</div>');
                        break;
                }
            }

            // re-enable the button and show it again. also delete the processing button
            $('#simplify_commerce #tc_payment_confirm').removeAttr('disabled', 'disabled').show();
            $('#simplify_commerce_processing').hide();


        } else {

            var token = status.id;             // insert the token into the form so it gets submitted to the server
            jQuery("#tc_payment_form").append("<input type='hidden' name='simplifyToken' value='" + token + "' />");
            // and submit
            jQuery("#tc_payment_form").get(0).submit();

        }

    }


    //when user click the 'continue checkout' button
    $("#tc_payment_form").on("submit", function (event) {


        // bail-out fast when no gateway can be choose by user
        if ($('input.tc_choose_gateway').length == false)
            return true;

        // bail-out if the selected gateway is not simplify
        if ($('input.tc_choose_gateway:checked').val() != "simplify_commerce")
            return true;

        //clear the element that handling errors
        $('#simplify_commerce_checkout_errors').empty();

        // Disable the submit button
        $('#simplify_commerce #tc_payment_confirm').attr('disabled', 'disabled').hide();
        $('#simplify_commerce_processing').show();

        // Generate a card token & handle the response
        SimplifyCommerce.generateToken({
            key: SimplifyGateway.public_key,
            card: {
                name: $("#sc_cc_name").val(),
                number: $("#sc_cc_number").val().replace(/ /g, ''),
                cvc: $("#sc_cc_cvc").val(),
                expMonth: $("#sc_cc_month").val(),
                expYear: $("#sc_cc_year").val()
            }
        }, simplifyResponseHandler);
        // Prevent the form from submitting
        return false;
    });
});