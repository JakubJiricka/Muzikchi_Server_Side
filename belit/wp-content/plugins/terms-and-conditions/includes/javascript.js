jQuery( window ).ready( function() {

    //responsive thickbox
    var tc_maxwidth = 600;
    var tc_maxheight = 400;

    jQuery( window ).on( 'resize', function() {
        
        var tc_width = window.innerWidth - 40;
        if ( tc_width > tc_maxwidth ) {
            tc_width = tc_maxwidth;
        }


            var tc_height = window.innerHeight - 50;
            if ( tc_height > tc_maxheight ) {
                tc_height = tc_maxheight;
            }
            

            var tc_link = "#TB_inline?width=" + tc_width + "&height=" + tc_height + "&inlineId=tc_terms_content";
            jQuery( ".thickbox" ).attr( "href", tc_link );   
           
    } );
    
    
    jQuery('.thickbox').click(function()    {
        
        var tc_width = window.innerWidth;
        if ( tc_width > tc_maxwidth ) {
            tc_width = tc_maxwidth;
        }
        
        setTimeout(function() {
            jQuery('#TB_ajaxContent').css('padding', '20px 20px 15px 20px');
            jQuery('#TB_ajaxContent').css('width', '100%');
        }, 500);
    });

    jQuery( document ).ready( function() {
        jQuery( window ).trigger( 'resize' );
    } );


    //check the checkbox
    jQuery( "#proceed_to_checkout" ).click( function() {
        if ( jQuery( '#tc_terms_and_conditions' ).is( ':checked' ) ) {
            return true;
        } else {
            //display error
            jQuery( '.tc_term_error' ).show();
            return false;
        }
    } );


} );