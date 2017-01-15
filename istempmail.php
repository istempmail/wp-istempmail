<?php
/*
  Plugin Name: Block Temporary Email
  Plugin URI: https://github.com/istempmail/wp-istempmail
  Description: This plugin will detect and block disposable, temporary, fake email address every time an email is submitted. It checks email domain name against IsTempMail service using its <a href="https://www.istempmail.com/">public API</a>. It will work immediately after activated. You do not need to register, pay or subscribe to IsTempMail service.
  Version: 1.0
  Author: Nguyen An Thuan
  Author URI: https://www.istempmail.com/
  License: GPLv2 or later
  Text Domain: istempmail
 */

# NOPE #
defined('ABSPATH') or die('Nope nope nope...');

$isTempMailPlugin=new IsTempMail_Plugin();
//End of main flow.

class IsTempMail_Plugin
{
    const API_ROOT = 'https://www.istempmail.com/api';

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
        load_plugin_textdomain('istempmail');
    }

    public function activate()
    {
        $token = get_option('istempmail_token');

        if (!$token || !$this->isValidToken($token)) {
            update_option('istempmail_token', '');
        }
    }

    public function menu()
    {
        add_options_page(
            __('IsTempMail Settings', 'istempmail'), // page title,
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
            $settings = '<a href="' . esc_url(get_admin_url(null, 'options-general.php?page=istempmail-settings')) . '">' . __('Settings', 'istempmail') . '</a>';

            $actions = array_merge(array(
                'settings' => $settings,
            ), $actions);
        }

        return $actions;
    }

    public function settingsPage()
    {
        include plugin_dir_path(__FILE__) . '/html/settings.php';
    }

    public function settings()
    {
        register_setting('istempmail-settings-group', 'istempmail_token', array($this, 'validateToken'));
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
        $url = self::API_ROOT . '/check/example.com?token=' . $token;

        $response = wp_remote_get($url, array('timeout' => 60));
        $responseBody = wp_remote_retrieve_body($response);

        $dataObj = @json_decode($responseBody);

        if (!$dataObj) {
            return false;
        }

        if ($dataObj->name == 'example.com') {
            return true;
        }

        $errorMessage = sprintf(__('Token error: %s', 'istempmail'), $dataObj->error_description);

        add_settings_error('istempmail_token', $dataObj->error, $errorMessage);

        return false;
    }

    public function deaError($errors)
    {
        if (self::$deaFound) {
            $message = __('We will not spam or share your email. <strong>Please do not use disposable email address</strong>. Thank you!', 'istempmail');

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

        return !$this->isDea($email);
    }

    protected function isDea($email)
    {
        $token = get_option('istempmail_token');
        $domain=end(explode('@', $email));

        if ($token) {
            $url = self::API_ROOT . '/check/' . $domain . '?token=' . $token;
        } else {
            $url = self::API_ROOT . '-public/check/' . $domain;
        }

        $response = wp_remote_get($url, array('timeout' => 60));
        $responseBody = wp_remote_retrieve_body($response);

        $dataObj = @json_decode($responseBody);

        if ($dataObj && $dataObj->blocked) {
            self::$deaFound = true;
            return true;
        }

        return false;
    }
}
