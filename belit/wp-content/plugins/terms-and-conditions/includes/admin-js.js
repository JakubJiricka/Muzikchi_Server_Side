jQuery( window ).ready( function() {
    
    if(jQuery('#tc_term_page').is(':checked')) {
        jQuery('#tc_term_editor').hide();
        jQuery('#tc_term_select_page').show();
    } else {
        jQuery('#tc_term_select_page').hide();
        jQuery('#tc_term_editor').show();
    }
    
    jQuery('#tc_term_popup').click(function(){
        jQuery('#tc_term_select_page').hide();
        jQuery('#tc_term_editor').show();
    });
    
    jQuery('#tc_term_page').click(function(){
        jQuery('#tc_term_editor').hide();
        jQuery('#tc_term_select_page').show();
    })
    
});