<?php
defined('ABSPATH') or die('Nope nope nope...');
?>

<div class="wrap">
    <h2><?php _e('IsTempMail Settings', 'block-temporary-email') ?></h2>

    <?php if(!get_option('istempmail_token')) { ?>
        <div id="setting-error-settings_updated" class="settings-error error">
            <p>
                <?php printf(__('Get your API token at <a href="%s">IsTempMail</a>.', 'block-temporary-email'), 'https://www.istempmail.com/'); ?>
            </p>
        </div>
    <?php } ?>

    <form method="post" action="options.php">
        <?php settings_fields('istempmail-settings-group'); ?>

        <?php do_settings_sections('istempmail-settings'); ?>

        <table class="form-table">
            <tr valign="top">
                <th scope="row">
                    <label for="istempmail_token">
                        <?php _e('IsTempMail API token', 'block-temporary-email') ?>
                    </label>
                </th>
                <td>
                    <input type="text" required="required" class="regular-text" id="istempmail_token" name="istempmail_token" value="<?php echo esc_attr(get_option('istempmail_token')); ?>" />
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label for="istempmail_whitelist">
                        <?php _e('Local white list', 'block-temporary-email') ?> (<?php echo count(array_filter(explode("\n", get_option('istempmail_whitelist')))); ?>)
                    </label>
                </th>
                <td>
                    <textarea rows="3" class="regular-text" id="istempmail_whitelist" name="istempmail_whitelist"><?php echo esc_attr(get_option('istempmail_whitelist')); ?></textarea>
                    <p class="description"><?php _e('One domain name per line.', 'block-temporary-email') ?></p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label for="istempmail_blacklist">
                        <?php _e('Local black list', 'block-temporary-email') ?> (<?php echo count(array_filter(explode("\n", get_option('istempmail_blacklist')))); ?>)
                    </label>
                </th>
                <td>
                    <textarea rows="3" class="regular-text" id="istempmail_blacklist" name="istempmail_blacklist"><?php echo esc_attr(get_option('istempmail_blacklist')); ?></textarea>
                    <p class="description"><?php _e('One domain name per line.', 'block-temporary-email') ?></p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <?php _e('Hook behavior', 'block-temporary-email') ?>
                </th>
                <td>
                    <p>
                        <label for="istempmail_check_all">
                            <input type="radio" id="istempmail_check_all" name="istempmail_check" value="0" <?php get_option('istempmail_check') or print('checked') ?> />
                            <?php _e('Check all emails', 'block-temporary-email') ?>
                        </label>
                        <br />

                        <label for="istempmail_check_submitted">
                            <input type="radio" id="istempmail_check_submitted" name="istempmail_check" value="1" <?php get_option('istempmail_check') and print('checked') ?> />
                            <?php _e('Check only emails submitted via browsers', 'block-temporary-email') ?>
                        </label>
                    </p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label for="istempmail_ignored_uris">
                        <?php _e('No check on URIs', 'block-temporary-email') ?> (<?php echo count(array_filter(explode("\n", get_option('istempmail_ignored_uris')))); ?>)
                    </label>
                </th>
                <td>
                    <textarea rows="3" class="regular-text" id="istempmail_ignored_uris" name="istempmail_ignored_uris"><?php echo esc_attr(get_option('istempmail_ignored_uris')); ?></textarea>
                    <p class="description"><?php _e('If the request URI contains these strings (one per line), emails will not be checked.', 'block-temporary-email') ?></p>
                </td>
            </tr>
        </table>

        <?php submit_button(); ?>
    </form>

    <p>
        <?php
        printf(__('Thank you for using <a href="%s">Block Temporary Email</a> plugin by <a href="%s">IsTempMail</a> .', 'block-temporary-email'),
            'https://wordpress.org/plugins/block-temporary-email/',
            'https://www.istempmail.com/'
        );
        ?>
    </p>
    <p><?php _e('Please help us spread the world!', 'block-temporary-email'); ?></p>
    <p>
        <a href="https://www.facebook.com/sharer/sharer.php?u=istempmail.com" target="_blank">
            <?php _e('Share on Facebook', 'block-temporary-email') ?>
        </a> &middot;
        <a href="https://twitter.com/intent/tweet?text=Block disposable, fake email addresses on WordPress by @istempmail https://wordpress.org/plugins/block-temporary-email/" target="_blank">
            <?php _e('Share on Twitter', 'block-temporary-email') ?>
        </a> &middot;
        <a href="https://www.facebook.com/istempmail" target="_blank">
            <?php _e('Like us on Facebook', 'block-temporary-email') ?>
        </a> &middot;
        <a href="https://twitter.com/istempmail" target="_blank">
            <?php _e('Follow us on Twitter', 'block-temporary-email') ?>
        </a>
    </p>
</div>