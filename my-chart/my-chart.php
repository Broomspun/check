<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://example.com
 * @since             1.0.0
 * @package           Plugin_Name
 *
 * @wordpress-plugin
 * Plugin Name:       My Chart plugin
 * Plugin URI:        http://example.com/plugin-name-uri/
 * Description:       This plugin displays charts from mysql data.
 * Version:           1.0.0
 * Author:            Your Name or Your Company
 * Author URI:        http://example.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       mychart
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'PLUGIN_NAME_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-plugin-name-activator.php
 */
function activate_my_chart() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-mychart-activator.php';
	My_Chart_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-mychart-deactivator.php
 */
function deactivate_my_chart() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-mychart-deactivator.php';
	myChart_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_my_chart');
register_deactivation_hook( __FILE__, 'deactivate_my_chart');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-mychart.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_my_chart() {

	$plugin = new My_chart();

	$plugin->run();
    My_chart::set_instance($plugin);

    $GLOBALS['my_chart'] = $plugin;

    add_filter('widget_text', 'do_shortcode');
}

run_my_chart();
