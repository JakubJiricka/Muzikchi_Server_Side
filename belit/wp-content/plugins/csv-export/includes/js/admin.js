jQuery( document ).ready( function( $ ) {
//alert(tc_csv_vars.ajaxUrl);

    jQuery("#tc_select_all_csv").click(function(){
       var tc_select_all = jQuery(this).prop('checked'); 
       
       if(tc_select_all == true){
       jQuery("#tc_form_attendees_csv_export .form-table input[type=checkbox]").each(function() {
           jQuery(this).prop("checked", true);
        });
    } else {
              jQuery("#tc_form_attendees_csv_export .form-table input[type=checkbox]").each(function() {
           jQuery(this).prop("checked", false);
        }); 
    }
    });
    
    $( '#export_csv_event_data' ).click( function( e ) {
        e.preventDefault( );
        export_csv_attendees_post();
        //return false;
    } );

    function export_csv_attendees_post() {
        var progressLabel = $( ".progress-label" );

        $( "#csv_export_progressbar" ).show();

        $.post( tc_csv_vars.ajaxUrl, $( "#tc_form_attendees_csv_export" ).serialize() )
            .done( function( response ) {
                if ( typeof response.data.page !== 'undefined' ) {
                    $( '#page_num' ).val( response.data.page );
                    if ( response.data.done == false ) {
                        $( "#csv_export_progressbar" ).progressbar( {
                            value: response.data.exported,
                            change: function() {
                                progressLabel.text( $( "#csv_export_progressbar" ).progressbar( "value" ) + "%" );
                            },
                        } );
                        export_csv_attendees_post();
                    } else {
                        $( "#csv_export_progressbar" ).progressbar( {
                            value: 100,
                            change: function() {
                                $( "#csv_export_progressbar" ).text( '' );
                            },
                        } );
                        $( "#csv_export_progressbar" ).hide();
                        $( '#page_num' ).val( 1 );
                        tc_export_csv();
                    }
                }
            } );
    }

    function tc_export_csv() {
        jQuery.get( tc_csv_vars.ajaxUrl, {
            action: 'tc_export_csv_dummy'
        } )
            .done( function( response ) {
                window.location = tc_csv_vars.ajaxUrl + '?action=tc_export_csv&document_title=' + $( '#document_title' ).val();
            } );
    }

} );