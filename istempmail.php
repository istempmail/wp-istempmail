<?php
/*
  Plugin Name: Block Temporary Email
  Plugin URI: https://wordpress.org/plugins/block-temporary-email/
  Description: This plugin will <strong>detect and block disposable, temporary, fake email address</strong> every time an email is submitted. It checks email domain name using <a href="https://www.istempmail.com/?ref=wp">IsTempMail API</a>, and maintains its own local blacklist.
  Version: 1.7
  Author: istempmail.com
  Author URI: https://www.istempmail.com/
  License: GPLv2 or later
  Text Domain: block-temporary-email
 */

# NOPE #
defined('ABSPATH') or die('Nope nope nope...');

$isTempMailPlugin = new istempmail();

//End of main flow.

class istempmail
{
    const API_CHECK = 'https://www.istempmail.com/api/check/';

    private $deaFound = false;
    private $isKadenceFormValidation = false;

    public function __construct() {
        add_action( 'plugins_loaded', array( $this, 'loadTextDomain' ) );

        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_uninstall_hook( __FILE__, array( 'istempmail', 'uninstall' ) );

        add_action( 'admin_menu', array( $this, 'menu' ) );
        add_action( 'admin_init', array( $this, 'settings' ) );

        add_filter( 'plugin_action_links', array( $this, 'addActionLinks' ), 10, 5 );

        add_filter( 'is_email', array( $this, 'isEmail' ), 10, 2 );

        add_filter( 'registration_errors', array( $this, 'deaError' ) );
        add_filter( 'user_profile_update_errors', array( $this, 'deaError' ) );
        add_filter( 'login_errors', array( $this, 'deaError' ) );

        // For Kadence Blocks Forms
        add_filter( 'kadence_blocks_form_submission_success', array( $this, 'kadenceSuccess' ), 10, 5 );
        add_filter( 'kadence_blocks_form_submission_messages', array( $this, 'kadenceMessages' ) );
    }

    public function kadenceSuccess( $success, $form_args, $fields, $form_id, $post_id ) {
        $this->isKadenceFormValidation = true;
        foreach ( $fields as $field ) {
            if ( $field['type'] == 'email' ) {
                $this->isEmail( true, $field['value'] );
            }
        }
        $rtn = $success;
        if ( $this->deaFound ) {
            $rtn = false;
        }

        return $rtn;
    }

    public function kadenceMessages( $messages ) {
        $this->isKadenceFormValidation = true;
        $rtn                           = $messages;
        if ( $this->deaFound ) {
            $rtn[0]['error'] = __( 'We will not spam or share your email. <strong>Please do not use a disposable email address</strong>. Thank you!', 'block-temporary-email' );
        }

        return $rtn;
    }

    public function loadTextDomain() {
        load_plugin_textdomain( 'block-temporary-email' );
    }

    public function activate() {
        $token = get_option( 'istempmail_token' );

        if ( ! $token || ! $this->isValidToken( $token ) ) {
            update_option( 'istempmail_token', '' );
        }

        if ( ! get_option( 'istempmail_blocked_list' ) ) {
            update_option( 'istempmail_blocked_list', '', false );
        }

        if (!get_option('istempmail_whitelist')) {
            $email = explode('@', wp_get_current_user()->user_email);
            update_option('istempmail_whitelist', end($email) ."\nmyapp.email", false);
        }

        if (!get_option('istempmail_blacklist')) {
            update_option('istempmail_blacklist', '', false);
        }

        if (get_option('istempmail_check') === false) {
            update_option('istempmail_check', 1, false);
        }

        if (get_option('istempmail_check_scope') === false) {
            update_option('istempmail_check_scope', 0, false);
        }

        if (get_option('istempmail_ignored_uris') === false) {
            update_option('istempmail_ignored_uris', '/wp-admin/admin.php?page=mailpoet-', false);
        }

        if (get_option('istempmail_ignored_payload') === false) {
            update_option('istempmail_ignored_payload', '_xoo_el_form=login', false);
        }
    }

    public static function uninstall()
    {
        delete_option('istempmail_token');
        delete_option('istempmail_blocked_list');
        delete_option('istempmail_whitelist');
        delete_option('istempmail_blacklist');
        delete_option('istempmail_ignored_uris');
        delete_option('istempmail_ignored_payload');
        delete_option('istempmail_check');
        delete_option('istempmail_check_scope');
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

    public function addActionLinks($actions, $plugin_file)
    {
        static $plugin;

        if (!isset($plugin)) {
            $plugin = plugin_basename(__FILE__);
        }

        if ($plugin === $plugin_file) {
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
        register_setting('istempmail-settings-group', 'istempmail_ignored_uris', array($this, 'cleanList'));
        register_setting('istempmail-settings-group', 'istempmail_ignored_payload', array($this, 'cleanList'));
        register_setting('istempmail-settings-group', 'istempmail_check');
        register_setting('istempmail-settings-group', 'istempmail_check_scope');
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
        $url = self::API_CHECK . $token. '/example.com';

        $response = wp_remote_get($url, array('timeout' => 30));
        $responseBody = wp_remote_retrieve_body($response);

        $dataObj = @json_decode($responseBody);

        if (!$dataObj) {
            return false;
        }

        if ($dataObj->name === 'example.com') {
            return true;
        }

        $errorMessage = sprintf(__('Token error: %s', 'block-temporary-email'), $dataObj->error_description);

        add_settings_error('istempmail_token', $dataObj->error, $errorMessage);

        return false;
    }

    private function isFeatureEnabled($featureName){
        $enabled = false;
        if($featureName === 'SETINGS_SCOPE_CHOOSER'){
            $token = get_option('istempmail_token');
            $tokenHash = md5($token);
            $enabledForTokenHashes = array('14b4ea327acd9644b0ea508b28a92f73', 'a06a8527389ff6b783a3c350edc3636c');
            $enabled = in_array($tokenHash, $enabledForTokenHashes);
        }
        return $enabled;
    }

    public function cleanList($list)
    {
        $cleanList = array_unique(array_filter(array_map('trim', explode("\n", $list))));
        natcasesort($cleanList);
        return implode("\n", $cleanList);
    }

    public function deaError($errors)
    {
        if ($this->deaFound) {
            $message = __('We will not spam or share your email. <strong>Please do not use a disposable email address</strong>. Thank you!', 'block-temporary-email');

            if ($errors instanceof WP_Error) {
                $errors->add('disposable_email', $message);
            } elseif(is_string($errors)) {
                $errors .= '<br>' . $message;
            }

            $this->deaFound = false;
        }

        return $errors;
    }

    protected function flatten($array)
    {
        $result = '';

        foreach ($array as $value) {
            if (is_array($value)) {
                $result .= $this->flatten($value);
            } elseif (is_scalar($value)) {
                $result .= $value;
            }
        }

        return $result;
    }

    private $requestContents;

    protected function getRequestContents()
    {
        if ($this->requestContents === null) {
            $this->requestContents = '';
            if (!empty($_POST)) {
                $this->requestContents .= $this->flatten($_POST);
            }

            if (!empty($_GET)) {
                $this->requestContents .= $this->flatten($_GET);
            }
        }

        return $this->requestContents;
    }

    private $results = [];

    public static function isLoginRequest()
    {
        global $pagenow;
        return $pagenow === 'wp-login.php';
    }

    /**
     * Check if email is valid
     *
     * @param bool $isEmail
     * @param string $email the email to check
     * @return bool TRUE when the email is valid, FALSE otherwise
     */
    public function isEmail($isEmail, $email)
    {
        if (!$isEmail) {
            return false;
        }

        // premium feature: is email check disabled for login?
        $checkScopeNoLogin = get_option( 'istempmail_check_scope' );
        $isLoginRequest    = self::isLoginRequest();
        if ( $checkScopeNoLogin && $isLoginRequest ) {
            return true;
        }

        $parts  = explode( '@', $email );
        $domain = end( $parts );

        if ( isset( $this->results[ $domain ] ) ) {
            return $this->results[ $domain ];
        }

        // check if this email was submitted by user
        // or submitted by Kadence Blocks form which removes email from global request data
        if ( get_option( 'istempmail_check' ) && ! stripos( $this->getRequestContents(), $domain ) && ! $this->isKadenceFormValidation ) {
            return true;
        } else {
            return $this->results[ $domain ] = $this->shouldBeBlocked( $domain );
        }
    }

    /**
     * Note: This returns true if domain should not be blocked.
     **/
    public function shouldBeBlocked( $domain ) {
        $ignoredURIs = explode( "\n", get_option( 'istempmail_ignored_uris' ) );
        if ( $ignoredURIs ) {
            $requestUri = $_SERVER['REQUEST_URI'];
            if ( isset( $_SERVER['HTTP_REFERER'] ) && strpos( $requestUri, 'admin-ajax.php' ) ) {
                $requestUri = $_SERVER['HTTP_REFERER'];
            }

            foreach ( $ignoredURIs as $uri ) {
                if ( stripos( $requestUri, $uri ) !== false ) {
                    return true;
                }
            }
        }

        $ignoredPayload = explode("\n", get_option('istempmail_ignored_payload'));
        if($ignoredPayload) {
            $queries = http_build_query( $_POST );

            foreach ($ignoredPayload as $payload) {
                if ( stripos( $payload, $queries ) !== false ) {
                    return true;
                }
            }
        }

        $blockList = explode( "\n", get_option( 'istempmail_blocked_list' ) );
        $blacklist = explode( "\n", get_option( 'istempmail_blacklist' ) );
        $whitelist = explode( "\n", get_option( 'istempmail_whitelist' ) );

        $nameArr      = explode( '.', $domain );
        $nameArrCount = count( $nameArr );
        for ( $i = 2; $i <= $nameArrCount; $i ++ ) {
            $name = implode( '.', array_slice( $nameArr, - $i ) );

            if ( in_array( $name, $whitelist, true ) ) {
                return true;
            }

            if ( in_array( $name, $blockList, true ) || in_array( $name, $blacklist, true ) ) {
                $this->deaFound = true;
                return false;
            }
        }

        $token = get_option('istempmail_token');
        if (!$token) {
            return true;
        }

        $url = self::API_CHECK . $token . '/' . $domain;

        $response     = wp_remote_get( $url, array( 'timeout' => 4 ) );
        $responseBody = wp_remote_retrieve_body( $response );

        $dataObj = @json_decode($responseBody);

        if (!$dataObj) {
            return true;
        }

        if ($dataObj->blocked) {
            $this->deaFound = true;

            $blockList[] = $dataObj->name;
            array_filter($blockList);
            update_option('istempmail_blocked_list', implode("\n", $blockList));

            return false;
        }

        if (isset($dataObj->unresolvable)) {
            return false;
        }

        return true;
    }
}
