<?php
/*
 * Plugin Name: Subscriber Login for YouTube
 * Plugin URI: https://github.com/lcmaquino/subscriber-login-for-youtube
 * Description: YouTube Subscriber Login displays login button for YouTube.
 * Version: 1.0.1
 * 
 * Requires PHP: 7.0
 * Requires at least: 4.9
 * 
 * Author: Luiz C. M. de Aquino
 * Author URI: https://www.github.com/lcmaquino
 * License: GPL v3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * 
 * Text Domain: subscriber-login-for-youtube
 * Domain Path: /languages
 * 
 * @author     Luiz C. M. de Aquino <contato@professoraquino.com.br>
 * @since      1.0.0
 */

if ( !defined( 'WPINC' ) ) {
	echo 'Sorry! I\'m just a WordPress plugin. I don\'t work alone. ;)';
	exit;
}

if ( !defined( 'SLYT_PATH_FILE' )) {
    define( 'SLYT_PATH_FILE', __FILE__ );
}

if ( !defined('SLYT_PATH' ) ) {
    define( 'SLYT_PATH', dirname(SLYT_PATH_FILE) );
}

if ( !defined( 'SLYT_INCLUDES_PATH' ) ) {
    define( 'SLYT_INCLUDES_PATH', SLYT_PATH . '/includes' );
}

if ( !defined( 'SLYT_ADMIN_PATH' ) ) {
    define( 'SLYT_ADMIN_PATH', SLYT_PATH . '/admin' );
}

require_once( SLYT_INCLUDES_PATH . '/SlytPlugin.php' );

$slyt_plugin = new SlytPlugin();
$slyt_plugin->run();
