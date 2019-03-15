jQuery( document ).ready( function ( $ ) {


jQuery('.tc-skip-button').click(function(e){
   event.preventDefault(e); 
});

    $( '.tc_show_tax_rate' ).change( function () {
        var selected_value = $( '.tc_show_tax_rate:checked' ).val();
        if ( selected_value == 'yes' ) {
            $( '.tc-taxes-fields-wrap' ).show();
        } else {
            $( '.tc-taxes-fields-wrap' ).hide();
        }
    } );

    $( '.tc-continue-button' ).click( function ( e ) {
        e.preventDefault();

        $( '.tc-wiz-screen-content' ).fadeTo( "slow", 0.5 );
        $( '.tc-wiz-screen-footer' ).fadeTo( "slow", 0.5 );
        $( '.tc-continue-button, .tc-skip-button' ).attr( 'disabled', true );

        var tc_step = $( '.tc_step' ).val();

        var input_data = { };

        switch ( tc_step ) {
            case 'start':
                input_data = {
                    step: tc_step,
                    mode: $( 'input[name=mode]:checked' ).val()
                };
                break;
            case 'license-key':
                input_data = {
                    step: tc_step,
                    license_key: $( '#tc-license-key' ).val()
                };
                break;
            case 'settings':
                input_data = {
                    step: tc_step,
                    currencies: $( '.tc_select_currency' ).val(),
                    currency_symbol: $( '.tc_currency_symbol' ).val(),
                    currency_position: $( '.tc_currency_position' ).val(),
                    price_format: $( '.tc_price_format' ).val(),
                    show_tax_rate: $( '.tc_show_tax_rate:checked' ).val(),
                    tax_rate: $( '.tc_tax_rate' ).val(),
                    tax_inclusive: $( '.tc_tax_inclusive:checked' ).val(),
                    tax_label: $( '.tc_tax_label' ).val(),
                };
                break;
            case 'pages-setup':
                input_data = {
                    step: tc_step
                };
                break;
        }

        $.post( tc_ajax.ajaxUrl, { action: "tc_installation_wizard_save_step_data", data: input_data }, function ( data ) {
            var tc_step = $( '.tc_step' ).val();
            if ( tc_step == 'start' ) {
                $( '.tc-continue-button, .tc-skip-button' ).attr( 'disabled', false );
                $( '#tc_wizard_start_form' ).submit();
            } else {
                window.location = $( '.tc-continue-button' ).attr( 'data-href' );
            }
        } );

    } );
   
    $( ".tc-wiz-wrapper select" ).chosen( { disable_search_threshold: 5, allow_single_deselect: false } );
   
} );