<?php
/*
  Plugin Name: Block Temporary Email
  Plugin URI: https://wordpress.org/plugins/block-temporary-email/
  Description: This plugin will <strong>detect and block disposable, temporary, fake email address</strong> every time an email is submitted. It checks email domain name using <a href="https://www.istempmail.com/">IsTempMail API</a>, and maintains its own local blacklist.
  Version: 1.3.1
  Author: Nguyen An Thuan
  Author URI: https://www.istempmail.com/
  License: GPLv2 or later
  Text Domain: block-temporary-email
 */

# NOPE #
defined('ABSPATH') or die('Nope nope nope...');

$isTempMailPlugin = new IsTempMailPlugin();

//End of main flow.

class IsTempMailPlugin
{
    const API_CHECK = 'https://www.istempmail.com/api/check/';

    static $deaFound = false;

    public function __construct()
    {
        add_action('plugins_loaded', array($this, 'loadTextDomain'));

        register_activation_hook(__FILE__, array($this, 'activate'));

        add_action('admin_menu', array($this, 'menu'));
        add_action('admin_init', array($this, 'settings'));

        add_filter('plugin_action_links', array($this, 'addActionLinks'), 10, 5);

        add_filter('is_email', array($this, 'deaEmailCheck'), 10, 2);

        add_filter('registration_errors', array($this, 'deaError'));
        add_filter('user_profile_update_errors', array($this, 'deaError'));
        add_filter('login_errors', array($this, 'deaError'));
    }

    public function loadTextDomain()
    {
        load_plugin_textdomain('block-temporary-email');
    }

    public function activate()
    {
        $token = get_option('istempmail_token');

        if (!$token || !$this->isValidToken($token)) {
            update_option('istempmail_token', '');
        }

        if (!get_option('istempmail_whitelist')) {
            $email = explode('@', wp_get_current_user()->user_email);
            update_option('istempmail_whitelist', end($email), false);
        }

        if (!get_option('istempmail_blacklist')) {
            update_option('istempmail_blacklist', '', false);
        }

        if (get_option('istempmail_check') === false) {
            update_option('istempmail_check', 1, false);
        }
    }

    public function menu()
    {
        add_options_page(
            __('IsTempMail Settings', 'block-temporary-email'), // page title,
            'IsTempMail', // menu item,
            'manage_options', // capability
            'istempmail-settings', // slug
            array($this, 'settingsPage') //function
        );
    }

    function addActionLinks($actions, $plugin_file)
    {
        static $plugin;

        if (!isset($plugin)) {
            $plugin = plugin_basename(__FILE__);
        }

        if ($plugin == $plugin_file) {
            $settings = '<a href="' . esc_url(get_admin_url(null, 'options-general.php?page=istempmail-settings')) . '">' . __('Settings', 'block-temporary-email') . '</a>';

            $actions = array_merge(array(
                'settings' => $settings,
            ), $actions);
        }

        return $actions;
    }

    public function settingsPage()
    {
        include plugin_dir_path(__FILE__) . '/settings.php';
    }

    public function settings()
    {
        register_setting('istempmail-settings-group', 'istempmail_token', array($this, 'validateToken'));
        register_setting('istempmail-settings-group', 'istempmail_whitelist', array($this, 'cleanList'));
        register_setting('istempmail-settings-group', 'istempmail_blacklist', array($this, 'cleanList'));
        register_setting('istempmail-settings-group', 'istempmail_check');
    }

    public function validateToken($token)
    {
        if (!$token || !$this->isValidToken($token)) {
            return '';
        }

        return $token;
    }

    public function isValidToken($token)
    {
        $url = self::API_CHECK . 'example.com?token=' . $token;

        $response = wp_remote_get($url, array('timeout' => 60));
        $responseBody = wp_remote_retrieve_body($response);

        $dataObj = @json_decode($responseBody);

        if (!$dataObj) {
            return false;
        }

        if ($dataObj->name == 'example.com') {
            return true;
        }

        $errorMessage = sprintf(__('Token error: %s', 'block-temporary-email'), $dataObj->error_description);

        add_settings_error('istempmail_token', $dataObj->error, $errorMessage);

        return false;
    }

    public function cleanList($list)
    {
        $cleanList=array_unique(array_filter(array_map('trim', explode("\n", $list))));
        natcasesort($cleanList);
        return implode("\n", $cleanList);
    }

    public function deaError($errors)
    {
        if (self::$deaFound) {
            $message = __('We will not spam or share your email. <strong>Please do not use disposable email address</strong>. Thank you!', 'block-temporary-email');

            if ($errors instanceof WP_Error) {
                $errors->add('disposable_email', $message);
            } else {
                $errors .= '<br>' . $message;
            }

            self::$deaFound = false;
        }

        return $errors;
    }

    public function deaEmailCheck($isEmail, $email)
    {
        if (!$isEmail) {
            return $isEmail;
        }

        $parts = explode('@', $email);
        $domain = end($parts);

        if (get_option('istempmail_check'))
        {
            // check if this email is submitted by user
            $isSubmitted = false;
            foreach ($_POST + $_GET as $value) {
                if (strpos($value, $domain)) {
                    $isSubmitted = true;
                }
            }

            if (!$isSubmitted) {
                return true;
            }
        }

        return !$this->isDea($domain);
    }

    protected function isDea($domain)
    {
        $blacklist = explode("\n", get_option('istempmail_blacklist'));
        $whitelist = explode("\n", get_option('istempmail_whitelist'));

        $nameArr = explode('.', $domain);
        for ($i = 2; $i <= count($nameArr); $i++) {
            $name = implode('.', array_slice($nameArr, -$i));

            if (in_array($name, $whitelist)) {
                return false;
            }

            if (in_array($name, $blacklist)) {
                self::$deaFound = true;
                return true;
            }
        }

        $token = get_option('istempmail_token');
        if (!$token) {
            return false;
        }

        $url = self::API_CHECK . $domain . '?token=' . $token;

        $response = wp_remote_get($url, array('timeout' => 60));
        $responseBody = wp_remote_retrieve_body($response);

        $dataObj = @json_decode($responseBody);

        if(!$dataObj) {
            return false;
        }

        if ($dataObj->blocked) {
            self::$deaFound = true;

            $blacklist[] = $dataObj->name;
            array_filter($blacklist);
            update_option('istempmail_blacklist', implode("\n", $blacklist));

            return true;
        }

        if(isset($dataObj->unresolvable)) {
            return true;
        }

        return false;
    }
}
