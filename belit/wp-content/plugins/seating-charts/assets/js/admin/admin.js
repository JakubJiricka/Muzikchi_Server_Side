jQuery(window).ready(function() {

    jQuery('.tc_events_page_tc_settings .button-primary').click(function(e) {

        if (jQuery('#tc-seatings-firebase-checked').is(":checked")) {

            var authDomain = jQuery('#authDomain').val();
            var databaseURL = jQuery('#databaseURL').val();
            var apiKey = jQuery('#apiKey').val();
            var secret = jQuery('#secret').val();
            if (!authDomain || !databaseURL || !apiKey || !secret) {
                alert('You need to fill in all Firebase fields in order to use it.');
                return false;
            }

        }


    });

});