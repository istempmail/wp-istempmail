<?php
defined('ABSPATH') or die('Nope nope nope...');
?>

<div class="wrap">
    <h2><?php _e('IsTempMail Settings', 'istempmail') ?></h2>

    <?php if(!get_option('istempmail_token')) { ?>
        <div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">
            <p>
                <strong><?php _e('The plugin will work fine without an API token.', 'istempmail'); ?></strong>
                <?php printf(__('We\'ll use the <a href="%s">public API endpoint</a> which is free and limits <strong>10 email checks per minute</strong>.', 'istempmail'), 'https://www.istempmail.com/') ?>

            </p>
            <button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
        </div>
    <?php } ?>

    <form method="post" action="options.php">
        <?php settings_fields('istempmail-settings-group'); ?>

        <?php do_settings_sections('istempmail-settings-group'); ?>

        <table class="form-table">
            <tr valign="top">
                <th scope="row">
                    <label for="istempmail_token">
                        <?php _e('IsTempMail API token', 'istempmail') ?>
                    </label>
                </th>
                <td>
                    <input type="text" class="regular-text" id="istempmail_token" name="istempmail_token" value="<?php echo esc_attr(get_option('istempmail_token')); ?>" />
                    <p class="description">
                        <?php
                        printf(__('Get your private API token at %s.', 'istempmail'),
                            '<a href="https://www.istempmail.com/sign-in">IsTempMail</a>'
                        );
                        ?>
                    </p>
                </td>
            </tr>
        </table>
        
        <?php submit_button(); ?>
    </form>
</div>