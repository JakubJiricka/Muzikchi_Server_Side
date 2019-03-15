<?php
if (isset($_POST['tc_terms'])) {
    
    $tc_terms = $_POST['tc_terms'];
    $tc_terms = stripslashes_deep_terms_conditions($tc_terms);
    if (wp_verify_nonce($_POST['save_terms_settings_nonce'], 'save_terms_settings')) {
        update_option('tc_terms_settings', $tc_terms);
    }
}

//function for stripping slashes on fields
function stripslashes_deep_terms_conditions($value) {
    $value = is_array($value) ?
                array_map('stripslashes_deep_terms_conditions', $value) :
                stripslashes($value);
    return $value;
}

$tc_terms_settings = get_option('tc_terms_settings');
$tc_list_pages = get_pages();
?>
<div class="wrap tc_wrap" style="opacity: 1;">
    <div id="poststuff" class="metabox-holder tc-settings">
        <form action="" method="post" name="save_terms_options" enctype="multipart/form-data">
            <div id="store_settings" class="postbox">
                <h3 class="hndle">
                    <span>
                        <?php _e('Terms And Conditions Options', 'tc'); ?>
                    </span>
                </h3>
                <div class="inside">
                    <table class="form-table">
                        <tbody>

                            <tr>
                                <th scope="row"><label for="list_id"><?php _e('Disable Terms And Conditions', 'tc') ?></label></th>
                                <td>
                                    <input name="tc_terms[disable_terms]" type="checkbox" id="disable_terms" value="1" <?php
                                    if (isset($tc_terms_settings['disable_terms'])) {
                                        echo "checked";
                                    }
                                    ?>>
                                    <p class="description"><?php _e('Check to disable Terms and Conditions.', 'tc'); ?></p>
                                </td>
                            </tr>

                            <tr valign="top">
                                <th scope="row"><label for="tc_term_popup"><?php _e('Terms And Conditions display', 'tc'); ?></label></th>
                                <td>
                                    <label>
                                        <input type="radio" id="tc_term_popup" name="tc_terms[term_display]" value="p" 
                                        <?php
                                        if (!isset($tc_terms_settings['term_display']) || $tc_terms_settings['term_display'] == 'p') {
                                            echo 'checked="checked"';
                                        }
                                        ?> ><?php _e('Show in a popup.', 'tc') ?></label>
                                    <label>
                                        <input type="radio" id="tc_term_page" name="tc_terms[term_display]"
                                               <?php
                                               if ($tc_terms_settings['term_display'] == 'l') {
                                                   echo 'checked="checked"';
                                               }
                                               ?>  value="l"><?php _e('Link to a page', 'tc') ?>	</label>
                                </td>
                            </tr>

                            <tr id="tc_term_select_page">
                                <th scope="row"><label for="terms"><?php _e('Select Terms And Conditions Page', 'tc') ?></label></th>
                                <td>
                                    <select name="tc_terms[terms_page]">
                                        <?php foreach ($tc_list_pages as $tc_page) { ?>
                                            <option <?php if ($tc_page->ID == $tc_terms_settings['terms_page']) {
                                            echo 'selected';
                                        } ?> value="<?php echo $tc_page->ID; ?>">
                                        <?php echo $tc_page->post_title; ?>
                                            </option>
                                        <?php } ?>
                                    </select>

                                    <p class="description"><?php _e('Set the Terms And Conditions text that will be shown to customers when they click the link.', 'tc'); ?></p>
                                </td>
                            </tr>

                            <tr id="tc_term_editor">
                                <th scope="row"><label for="terms"><?php _e('Terms And Conditions Text', 'tc') ?></label></th>
                                <td>
                                    <?php wp_editor($tc_terms_settings['terms'], 'terms', $settings = array('textarea_name' => 'tc_terms[terms]')); ?> 
                                    <p class="description"><?php _e('Set the Terms And Conditions text that will be shown to customers when they click the link.', 'tc'); ?></p>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row"><label for="link_title"><?php _e('Link Title', 'tc') ?></label></th>
                                <td>
                                    <input name="tc_terms[link_title]" type="text" id="link_title" value="<?php echo isset($tc_terms_settings['link_title']) ? $tc_terms_settings['link_title'] : 'I agree to the Terms and Conditions'; ?>" class="regular-text">
                                    <p class="description"><?php _e('Set the link title for Terms And Conditions', 'tc'); ?></p>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row"><label for="error_text"><?php _e('Error Text', 'tc') ?></label></th>
                                <td>
                                    <input name="tc_terms[error_text]" type="text" id="error_text" value="<?php echo isset($tc_terms_settings['error_text']) ? $tc_terms_settings['error_text'] : 'You must agree to the terms and conditions before proceeding to the checkout.'; ?>" class="regular-text">
                                    <p class="description"><?php _e("Set the error text that will be shown if users don't agree to Terms And Conditions", 'tc'); ?></p>
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </div><!-- inside -->
            </div><!-- store-settings -->
            <?php wp_nonce_field('save_terms_settings', 'save_terms_settings_nonce'); ?>
            <?php submit_button('', 'primary', 'save_terms_options'); ?>
        </form>
    </div><!-- poststuff -->
</div><!-- wrap -->