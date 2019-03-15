/* 
 
 Based on: http://wordpress.stackexchange.com/questions/42652/#answer-42729
 
 These functions provide a simple way to interact with TinyMCE (wp_editor) visual editor.
 This is the same thing that WordPress does, but a tad more intuitive.
 Additionally, this works for any editor - not just the "content" editor.
 
 Usage:
 
 0) If you are not using the default visual editor, make your own in PHP with a defined editor ID:
 wp_editor( $content, 'tab-editor' );
 
 1) Get contents of your editor in JavaScript:
 tmce_getContent( 'tab-editor' )
 
 2) Set content of the editor:
 tmce_setContent( content, 'tab-editor' )
 
 Note: If you just want to use the default editor, you can leave the ID blank:
 tmce_getContent()
 tmce_setContent( content )
 
 Note: If using a custom textarea ID, different than the editor id, add an extra argument:
 tmce_getContent( 'visual-id', 'textarea-id' )
 tmce_getContent( content, 'visual-id', 'textarea-id')
 
 Note: An additional function to provide "focus" to the displayed editor:
 tmce_focus( 'tab-editor' )
 
 =========================================================
 */
function tmce_getContent(editor_id, textarea_id) {
    if (typeof editor_id == 'undefined')
        editor_id = wpActiveEditor;
    if (typeof textarea_id == 'undefined')
        textarea_id = editor_id;

    if (jQuery('#wp-' + editor_id + '-wrap').hasClass('tmce-active') && tinyMCE.get(editor_id)) {
        return tinyMCE.get(editor_id).getContent();
    } else {
        return jQuery('#' + textarea_id).val();
    }
}

function tmce_setContent(content, editor_id, textarea_id) {

    jQuery('#content-html').click();

    if (typeof editor_id == 'undefined')
        editor_id = wpActiveEditor;
    if (typeof textarea_id == 'undefined')
        textarea_id = editor_id;

    if (jQuery('#wp-' + editor_id + '-wrap').hasClass('tmce-active') && tinyMCE.get(editor_id)) {
        return tinyMCE.get(editor_id).setContent(content);
    } else {
        return jQuery('#' + textarea_id).val(content);
    }
}

function tmce_focus(editor_id, textarea_id) {
    if (typeof editor_id == 'undefined')
        editor_id = wpActiveEditor;
    if (typeof textarea_id == 'undefined')
        textarea_id = editor_id;

    if (jQuery('#wp-' + editor_id + '-wrap').hasClass('tmce-active') && tinyMCE.get(editor_id)) {
        return tinyMCE.get(editor_id).focus();
    } else {
        return jQuery('#' + textarea_id).focus();
    }
}