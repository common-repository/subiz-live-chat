<?php

/**
 * @link              https://subiz.com.vn
 * @package           Subiz
 *
 * @wordpress-plugin
 * Plugin Name:       Subiz
 * Plugin URI:        http://subiz.com.vn/wordpress-plugin
 * Description:       Subiz live chat plugin offers an excellent customer interaction platform where sales and customer service team can communicate directly with visitors, fulfil any enquiry in real-time, and actively receive feedback
 * Version:           4.5
 * Author:            Subiz
 * Author URI:        https://subiz.com.vn
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort.
if (! defined('WPINC')) {
    die;
}

define('SUBIZ_VERSION', '4.5');

function activate_subiz()
{
    $settings = array(
        'subiz_account_id' => '',
        'subiz_account_name' => '',
        'subiz_cf7' => '',
    );
    add_option('subiz_settings', $settings, '', 'yes');
}

function deactivate_subiz()
{
    delete_option('subiz_settings');
}

/**
 * Registers a new settings page under Settings.
 */
function admin_menu()
{
    add_menu_page(
        __('Subiz Settings', 'textdomain'),
        __('Subiz', 'textdomain'),
        'administrator',
        'subiz-plugin',
        'subiz_settings_page',
        plugin_dir_url(dirname(__FILE__)) . 'subiz-live-chat/images/subiz_channel.svg',
    );
    subiz_add_admin_css();
}

function admin_setting_form_init()
{
    register_setting('subiz_options', 'subiz_settings');
}

/**
 * Settings page display callback.
 */
function subiz_settings_page()
{
    include sprintf('%s/page.php', dirname(__FILE__));
}

function print_embed_code()
{
    include sprintf('%s/widget.php', dirname(__FILE__));
}

function subiz_add_admin_css()
{
    $plugin_url = plugin_dir_url(__FILE__);
    wp_enqueue_style('style', $plugin_url . "style.css");
    // wp_enqueue_script('my-custom-script', $plugin_url . 'script.js', array(), true);
}

add_action('wpcf7_before_send_mail', 'subiz_wpcf7_before_send_mail', 10, 3);

function subiz_wpcf7_before_send_mail($contact_form, &$abort, $object)
{
    $submission = WPCF7_Submission::get_instance();
    // error_log(print_r(wp_json_encode($contact_form->id), true));

    $sbzsetting = get_option('subiz_settings');

    // not enabled
    if ($sbzsetting['subiz_cf7'] != 1) {
        return $contact_form;
    }

    // not enabled for this form
    if ($sbzsetting['subiz_wpcf7_' . $contact_form->id . '_enabled'] != 1) {
        return $contact_form;
    }

    // no account id
    $accid = $sbzsetting['subiz_account_id'];
    if (empty($accid)) {
        return $contact_form;
    }

    if (empty($submission)) {
        return $contact_form;
    }

    $posted_data = $submission->get_posted_data();
    $formateddata = [];
    $codes = $contact_form->form_scan_shortcode();
    foreach ($posted_data as $key => $value) {
        $found = false;
        foreach ($codes as $fd) { // field definition
            if ($fd->raw_name == $key) {
                // error_log(print_r( "BBBBBBBBBBBBB: " . $fd->raw_name . "-". in_array("class:subiz-desc", $fd->options ) , true));
                $sbzf = null;
                if (in_array("class:subiz-desc", $fd->options)) {
                    $sbzf = "description";
                }

                if (in_array("class:subiz-title", $fd->options)) {
                    $sbzf = "title";
                }

                $found = true;
                $formateddata[] = array("key" => $key, "value" => $value, "option" => $sbzf, "type" => $fd->basetype );
                break;
            }
        }
        if (!$found) {
            $formateddata[] = array("key" => $key, "value" => $value, "type" => "text" );
        }
    }

    $body = wp_json_encode(array(
        "data" => $formateddata,
        "form_id" => (string)$contact_form->id,
        "form_title" => $contact_form->title,
    ));
    $options = [
        'body'        => $body,
        'headers'     => [
            'Content-Type' => 'application/json',
            'Referer' => $_SERVER['HTTP_REFERER'],
        ],
        'timeout'     => 30,
        'redirection' => 5,
        'blocking'    => true,
        'data_format' => 'body',
        'user-agent' => $_SERVER['HTTP_USER_AGENT'],
    ];
    $userref = $_COOKIE['__sbref'];

    $host = 'api.subiz.com.vn';
    // $host = 'eo442kcw8mkje7l.m.pipedream.net';
    $response = wp_remote_post('https://'.$host.'/4.0/accounts/'. $accid .'/wpcf7-submissions?x-user-ref=' . $userref, $options);
    update_option('subiz_wpcf7_' . $contact_form->id .'_last_submit_at', floor(microtime(true) * 1000));
    $out = wp_remote_retrieve_body($response);
    // error_log(print_r($out, true));
    $abort = false;
    return $contact_form;
}

register_activation_hook(__FILE__, 'activate_subiz');
register_deactivation_hook(__FILE__, 'deactivate_subiz');

add_action('wp_footer', 'print_embed_code');
add_action('admin_menu', 'admin_menu');
add_action('admin_init', 'admin_setting_form_init');
