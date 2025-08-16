<?php
/**
 * Plugin Name:       CBT Exam Plugin
 * Plugin URI:        https://example.com/
 * Description:       A modular and scalable CBT exam plugin for WordPress, compatible with Elementor.
 * Version:           1.0.0
 * Author:            Jules
 * Author URI:        https://example.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       cbt-exam-plugin
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-cbt-exam-plugin.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-cbt-exam-plugin-activator.php
 */
function activate_cbt_exam_plugin() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-cbt-exam-plugin-activator.php';
    Cbt_Exam_Plugin_Activator::activate();
}

register_activation_hook( __FILE__, 'activate_cbt_exam_plugin' );


function run_cbt_exam_plugin() {

    $plugin = new Cbt_Exam_Plugin();
    $plugin->run();

}
run_cbt_exam_plugin();
