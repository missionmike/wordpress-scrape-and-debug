<?php

/**
 * All things pertaining to enqueueing scripts
 */

defined('ABSPATH') or die('No script kiddies please!');

/**
 * Register styles/scripts
 */
function dts_dbggr_register_scripts()
{

    $version = '20200715';

    wp_register_style('dts-style', plugins_url('css/styles.css', dirname(__FILE__)), false, $version);
    wp_register_script('dts-scripts', plugins_url('js/dts-scripts.js', dirname(__FILE__)), false, $version);
}
add_action('admin_init', 'dts_dbggr_register_scripts');

/**
 * Enqueue styles/scripts
 */
function dts_dbggr_enqueue_scripts()
{

    wp_enqueue_style('dts-style');
    wp_enqueue_script('jquery');
    wp_enqueue_script('dts-scripts');
}
add_action('admin_enqueue_scripts', 'dts_dbggr_enqueue_scripts');
