<?php
/*
Plugin Name: Rytisluko
Version: 1.0.2
Description: Based on Boilerplate TypeRocket Plugin. It houses all custom code required for this website created by me.
Author: Rytis Luko
License: GPLv2 or later
*/

if ( !function_exists( 'add_action' ) ) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

if(!defined('TYPEROCKET_PLUGIN_RYTISLUKO_VIEWS_PATH')) {
    define('TYPEROCKET_PLUGIN_RYTISLUKO_VIEWS_PATH', __DIR__ . '/resources/views');
}

$__typerocket_plugin_rytisluko = null;

function typerocket_plugin_rytisluko() {
    global $__typerocket_plugin_rytisluko;

    if($__typerocket_plugin_rytisluko) {
        return;
    }

    if(file_exists(__DIR__ . '/vendor/autoload.php')) {
        require __DIR__ . '/vendor/autoload.php';
    } else {
        $map = [
            'prefix' => 'Rytisluko',
            'folder' => __DIR__ . '/app',
        ];

        typerocket_autoload_psr4($map);
    }

    $__typerocket_plugin_rytisluko = call_user_func('Rytisluko\RytislukoTypeRocketPlugin::new', __FILE__, __DIR__);
}

register_activation_hook( __FILE__, 'typerocket_plugin_rytisluko');
add_action('delete_plugin', 'typerocket_plugin_rytisluko');
add_action('typerocket_loaded', 'typerocket_plugin_rytisluko', 9);