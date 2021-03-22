<?php
/*
 * Plugin Name: Subscriber Login for YouTube
 * Plugin URI: https://wordpress.org/plugins/subscriber-login-for-youtube/
 * Description: Subscriber Login for YouTube enables user sign in with their YouTube account.
 * Version: 1.0.4
 *
 * Requires PHP: 7.0
 * Requires at least: 4.4
 *
 * Author: Luiz C. M. de Aquino
 * Author URI: https://www.professoraquino.com.br/
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Text Domain: subscriber-login-for-youtube
 * Domain Path: /languages
 *
 * @author     Luiz C. M. de Aquino <contato@professoraquino.com.br>
 * @since      1.0.0
 */

use Lcmaquino\SubscriberLoginForYouTube\Site\Plugin;

if (!defined('WPINC')) {
    echo 'Sorry! I\'m just a WordPress plugin. I don\'t work alone. ;)';
    exit;
}

if (!defined('SLYT_MAIN_FILE')) {
    define('SLYT_MAIN_FILE', __FILE__);
}

if (!defined('SLYT_BASEDIR')) {
    define('SLYT_BASEDIR', dirname(SLYT_MAIN_FILE));
}

if (!defined('SLYT_RESOURCES')) {
    define('SLYT_RESOURCES', SLYT_BASEDIR . '/resources');
}

if (!version_compare(PHP_VERSION, '7.0', '>=')) {
    add_action('admin_notices', 'slyt_fail_php_version');
} elseif (!version_compare(get_bloginfo('version'), '4.4', '>=')) {
    add_action('admin_notices', 'slyt_fail_wp_version');
} else {
    /** Composer generated auto loader */
    require __DIR__ . '/vendor/autoload.php';

    /** Run the plugin */
    $slyt_plugin = new Plugin();
    $slyt_plugin->run();
}

function slyt_fail_php_version()
{
    $message = sprintf(
        /* translators: %1$s: the plugin name; %2$s: PHP version */
        __('%1$s requires PHP version %2$s or later. This plugin is NOT RUNING.', 'subscriber-login-for-youtube'),
        'Subscriber Login for YouTube',
        '7.0'
    );

    slyt_print_fail_message($message);
}

function slyt_fail_wp_version()
{
    $message = sprintf(
        /* translators: %1$s: the plugin name; %2$s: WordPress version */
        __('%1$s requires WordPress version %2$s or later. This plugin is NOT RUNING.', 'subscriber-login-for-youtube'),
        'Subscriber Login for YouTube',
        '4.4'
    );
    
    slyt_print_fail_message($message);
}

function slyt_print_fail_message($message)
{
    $message = '<div class="notice notice-error">' . wpautop($message) . '</div>';
    echo wp_kses_post($message);
}

function slyt_load_plugin_textdomain()
{
    load_plugin_textdomain('subscriber-login-for-youtube', false, 'subscriber-login-for-youtube/languages');
}
