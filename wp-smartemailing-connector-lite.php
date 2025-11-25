<?php
/*
Plugin Name: SmartEmailing Connector Lite
Description: Vendor-neutral lead capture with shortcode/popup and AJAX to a configurable email API (e.g., SmartEmailing).
Version: 1.1.0
Author: Patrik Domjen
Text Domain: smartemailing-connector-lite
Domain Path: /languages
*/

if (!defined('ABSPATH')) { exit; }

define('SECL_VERSION', '1.1.0');
define('SECL_SLUG', 'smartemailing-connector-lite');
define('SECL_URL', plugin_dir_url(__FILE__));
define('SECL_PATH', plugin_dir_path(__FILE__));

// Autoload simple includes
require_once SECL_PATH . 'includes/Admin.php';
require_once SECL_PATH . 'includes/Frontend.php';
require_once SECL_PATH . 'includes/Ajax.php';
require_once SECL_PATH . 'includes/helpers.php';

add_action('plugins_loaded', function () {
    load_plugin_textdomain('smartemailing-connector-lite', false, dirname(plugin_basename(__FILE__)) . '/languages');
});

add_action('init', function () {
    \SmartEmailingConnector\Frontend::init();
    \SmartEmailingConnector\Ajax::init();
});

if (is_admin()) {
    add_action('init', function () { \SmartEmailingConnector\Admin::init(); });
}
