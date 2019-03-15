<?php
global $tc;

if (!defined('ABSPATH')) {
    exit;
}

if (isset($_POST['tc_delete_selected_data_permanently']) && current_user_can('manage_options')) {
    if (isset($_POST['tc_delete_plugin_data'])) {

        ini_set('max_input_time', 0);
        ini_set('max_execution_time', 0);
        set_time_limit(0);
        @ini_set('memory_limit', '1024M');

        do_action('tc_delete_plugins_data', $_POST['tc_delete_plugin_data']);
        $message = __('All selected data has been permanently deleted successfully.', 'tc');
    }
}

$tickera_plugins_and_addons = apply_filters('tc_delete_info_plugins_list', array('tickera' => $tc->title));
?>

<div class="wrap tc_wrap" id="tc_delete_info">
    <?php
    if (isset($message)) {
        ?>
        <div id="message" class="updated fade"><p><?php echo $message; ?></p></div>
        <?php
    }
    ?>
    <div id="poststuff" class="metabox-holder tc-settings">

        <form id="tc-delete-info" method='post' action='<?php echo esc_attr(admin_url('edit.php?post_type=tc_events&page=tc_settings&tab=tickera_delete_info')); ?>'>
            <div class="postbox">
                <h3 class='hndle'><span><?php _e('Delete Information stored by the plugin and its add-ons', 'tc'); ?></span></h3>
                <div class="inside">
                    <span class="description"></span>

                    <table class="form-table" cellspacing="0" id="status">
                        <tbody>
                            <tr>
                                <th><?php _e('Plugin', 'tc'); ?></th>
                                <th><?php _e('Confirm', 'tc'); ?></th>
                            </tr>
                            <?php
                            foreach ($tickera_plugins_and_addons as $plugin_name => $plugin_title) {
                                ?>
                                <tr>
                                    <td><?php echo $plugin_title; ?></td>
                                    <td><input type="checkbox" value="yes" name="tc_delete_plugin_data[<?php echo esc_attr($plugin_name); ?>]" /><?php _e('Delete', 'tc'); ?></td>
                                </tr>
                                <?php
                            }
                            ?>
                        </tbody>
                    </table>
                    <?php submit_button(__('Delete selected data permanently', 'tc'), 'primary', 'tc_delete_selected_data_permanently', true); ?>
                </div>
            </div>
        </form>
        <?php do_action('tc_after_delete_info');?>
    </div>
</div>

