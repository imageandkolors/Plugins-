<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://example.com/
 * @since      1.0.0
 *
 * @package    Cbt_Exam_Plugin
 * @subpackage Cbt_Exam_Plugin/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Cbt_Exam_Plugin
 * @subpackage Cbt_Exam_Plugin/includes
 * @author     Jules <you@example.com>
 */
class Cbt_Exam_Plugin_i18n {


    /**
     * Load the plugin text domain for translation.
     *
     * @since    1.0.0
     */
    public function load_plugin_textdomain() {

        load_plugin_textdomain(
            'cbt-exam-plugin',
            false,
            dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
        );

    }



}
