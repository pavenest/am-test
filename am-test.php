<?php
/**
 * Plugin Name: Awesomemotive test plugin
 * Description: Awesomemotive test plugin
 * Author: Md. Atiqur Rahman
 * Version: 1.0.0
 *
 * Text Domain: am-test
 * Domain Path: /languages
 *
 */


const AMT_VERSION = '1.0.0';

define('AMT_PLG_PATH', plugin_dir_path(__FILE__));
define('AMT_PLG_URL', plugin_dir_url(__FILE__));

defined('ABSPATH') || exit;

require __DIR__ . '/Autoloader.php';

register_activation_hook(__FILE__, function () {
    \AMT\Handler\Installer::activate();
});

register_deactivation_hook(__FILE__, function () {
    \AMT\Handler\Installer::deactivate();
});

new \AMT\System\Boot(__FILE__);


