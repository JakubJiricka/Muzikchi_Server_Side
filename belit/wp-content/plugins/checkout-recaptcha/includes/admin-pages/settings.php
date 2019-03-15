<?php
$error_message = '';

if (isset($_POST['save_checkout_recaptcha_settings_nonce']) && wp_verify_nonce($_POST['save_checkout_recaptcha_settings_nonce'], 'save_checkout_recaptcha_settings')) {
    update_option('tc_checkout_recaptcha_settings', $_POST['tc_checkout_recaptcha']);

    $settings = TC_Checkout_reCAPTCHA::get_settings();
}

$settings = TC_Checkout_reCAPTCHA::get_settings();
?>
<div class="wrap tc_wrap">
    <?php if (!empty($error_message)) {
        ?>
        <div class="error"><p><?php echo $error_message; ?></p></div>
    <?php }
    ?>

    <div id="poststuff">
        <form action="" method="post" enctype="multipart/form-data">
            <div class="postbox">
                <h3 class="hndle"><span><?php _e('Settings', 'tcrc'); ?></span></h3>
                <div class="inside">
                    <p class="description"><?php printf(__('If you do not have keys already then visit %shttps://www.google.com/recaptcha/admin%s', 'tcrc'), '<a href="https://www.google.com/recaptcha/admin" target="_blank">', '</a>'); ?></p>
                    <table class="form-table">
                        <tbody>

                            <tr>
                                <?php
                                $show_recaptcha = isset($settings['show_recaptcha']) ? $settings['show_recaptcha'] : '0';
                                ?>
                                <th scope="row"><label for="show_recaptcha"><?php _e('Show reCAPTCHA on the checkout page', 'tcrc') ?></label></th>
                                <td>
                                    <input type="radio" name="tc_checkout_recaptcha[show_recaptcha]" <?php checked($show_recaptcha, '1', true); ?> value="1"> <?php _e('Yes', 'tcrc'); ?>
                                    <input type="radio" name="tc_checkout_recaptcha[show_recaptcha]" <?php checked($show_recaptcha, '0', true); ?> value="0"> <?php _e('No', 'tcrc'); ?>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row"><label><?php _e('Language', 'tcrc') ?></label></th>
                                <td>
                                    <select name="tc_checkout_recaptcha[language]" id="language">
                                        <?php
                                        $languages = array();
                                        $languages['ar'] = __('Arabic', 'tcrc');
                                        $languages['af'] = __('Afrikaans', 'tcrc');
                                        $languages['am'] = __('Amharic', 'tcrc');
                                        $languages['hy'] = __('Armenian', 'tcrc');
                                        $languages['az'] = __('Azerbaijani', 'tcrc');
                                        $languages['eu'] = __('Basque', 'tcrc');
                                        $languages['bn'] = __('Bengali', 'tcrc');
                                        $languages['bg'] = __('Bulgarian', 'tcrc');
                                        $languages['ca'] = __('Catalan', 'tcrc');
                                        $languages['zh-HK'] = __('Chinese (Hong Kong)', 'tcrc');
                                        $languages['zh-CN'] = __('Chinese (Simplified)', 'tcrc');
                                        $languages['zh-TW'] = __('Chinese (Traditional)', 'tcrc');
                                        $languages['hr'] = __('Croatian', 'tcrc');
                                        $languages['cs'] = __('Czech', 'tcrc');
                                        $languages['da'] = __('Danish', 'tcrc');
                                        $languages['nl'] = __('Dutch', 'tcrc');
                                        $languages['en-GB'] = __('English (UK)', 'tcrc');
                                        $languages['en'] = __('English (US)', 'tcrc');
                                        $languages['et'] = __('Estonian', 'tcrc');
                                        $languages['fil'] = __('Filipino', 'tcrc');
                                        $languages['fi'] = __('Finnish', 'tcrc');
                                        $languages['fr'] = __('French', 'tcrc');
                                        $languages['fr-CA'] = __('French (Canadian)', 'tcrc');
                                        $languages['gl'] = __('Galician', 'tcrc');
                                        $languages['ka'] = __('Georgian', 'tcrc');
                                        $languages['de'] = __('German', 'tcrc');
                                        $languages['de-AT'] = __('German (Austria)', 'tcrc');
                                        $languages['de-CH'] = __('German (Switzerland)', 'tcrc');
                                        $languages['el'] = __('Greek', 'tcrc');
                                        $languages['gu'] = __('Gujarati', 'tcrc');
                                        $languages['iw'] = __('Hebrew', 'tcrc');
                                        $languages['hi'] = __('Hindi', 'tcrc');
                                        $languages['hu'] = __('Hungarain', 'tcrc');
                                        $languages['is'] = __('Icelandic', 'tcrc');
                                        $languages['id'] = __('Indonesian', 'tcrc');
                                        $languages['it'] = __('Italian', 'tcrc');
                                        $languages['ja'] = __('Japanese', 'tcrc');
                                        $languages['kn'] = __('Kannada', 'tcrc');
                                        $languages['ko'] = __('Korean', 'tcrc');
                                        $languages['lo'] = __('Laothian', 'tcrc');
                                        $languages['lv'] = __('Latvian', 'tcrc');
                                        $languages['lt'] = __('Lithuanian', 'tcrc');
                                        $languages['ms'] = __('Malay', 'tcrc');
                                        $languages['ml'] = __('Malayalam', 'tcrc');
                                        $languages['mr'] = __('Marathi', 'tcrc');
                                        $languages['mn'] = __('Mongolian', 'tcrc');
                                        $languages['no'] = __('Norwegian', 'tcrc');
                                        $languages['fa'] = __('Persian', 'tcrc');
                                        $languages['pl'] = __('Polish', 'tcrc');
                                        $languages['pt'] = __('Portuguese', 'tcrc');
                                        $languages['pt-BR'] = __('Portuguese (Brazil)', 'tcrc');
                                        $languages['pt-PT'] = __('Portuguese (Portugal)', 'tcrc');
                                        $languages['ro'] = __('Romanian', 'tcrc');
                                        $languages['ru'] = __('Russian', 'tcrc');
                                        $languages['sr'] = __('Serbian', 'tcrc');
                                        $languages['si'] = __('Sinhalese', 'tcrc');
                                        $languages['sk'] = __('Slovak', 'tcrc');
                                        $languages['sl'] = __('Slovenian', 'tcrc');
                                        $languages['es'] = __('Spanish', 'tcrc');
                                        $languages['es-419'] = __('Spanish (Latin America)', 'tcrc');
                                        $languages['sw'] = __('Swahili', 'tcrc');
                                        $languages['sv'] = __('Swedish', 'tcrc');
                                        $languages['ta'] = __('Tamil', 'tcrc');
                                        $languages['te'] = __('Telugu', 'tcrc');
                                        $languages['th'] = __('Thai', 'tcrc');
                                        $languages['tr'] = __('Turkish', 'tcrc');
                                        $languages['uk'] = __('Ukrainian', 'tcrc');
                                        $languages['ur'] = __('Urdu', 'tcrc');
                                        $languages['vi'] = __('Vietnamese', 'tcrc');
                                        $languages['zu'] = __('Zulu', 'tcrc');
                                        
                                        $selected_language = isset($settings['language']) ? $settings['language'] : 'en';
                                        
                                        foreach ($languages as $language_code => $language) {
                                            ?>
                                        <option value="<?php echo esc_attr($language_code); ?>" <?php selected($language_code, $selected_language, true)?>><?php echo $language; ?></option>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row"><label><?php _e('Incomplete reCaptcha error message', 'tcrc') ?></label></th>
                                <td>
                                    <input name="tc_checkout_recaptcha[error_message]" autocomplete="off" type="text" id="error_message" value="<?php echo isset($settings['error_message']) ? $settings['error_message'] : __('Please complete the reCAPTCHA', 'tcrc'); ?>" placeholder="<?php echo esc_attr(__('Your Site Key here', 'tcrc')); ?>" class="regular-text">
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row"><label><?php _e('Site key', 'tcrc') ?></label></th>
                                <td>
                                    <input name="tc_checkout_recaptcha[site_key]" autocomplete="off" type="text" id="site_key" value="<?php echo isset($settings['site_key']) ? $settings['site_key'] : ''; ?>" placeholder="<?php echo esc_attr(__('Your Site Key here', 'tcrc')); ?>" class="regular-text">
                                </td>
                            </tr>

                            <tr>
                                <th scope="row"><label><?php _e('Secret key', 'tcrc') ?></label></th>
                                <td>
                                    <input name="tc_checkout_recaptcha[secret_key]" autocomplete="off" type="text" id="secret_key" value="<?php echo isset($settings['secret_key']) ? $settings['secret_key'] : ''; ?>" placeholder="<?php echo esc_attr(__('Your Secret Key here', 'tcrc')); ?>" class="regular-text">
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </div>
            </div>
            <?php wp_nonce_field('save_checkout_recaptcha_settings', 'save_checkout_recaptcha_settings_nonce');
            ?>
            <?php submit_button(); ?>
        </form>
    </div>
</div>