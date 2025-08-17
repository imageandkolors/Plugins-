<?php
/**
 * Elementor integration for CBT Exam Plugin.
 *
 * @package    Cbt_Exam_Plugin
 * @subpackage Cbt_Exam_Plugin/elementor
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * The main class for Elementor integration.
 *
 * @since 1.1.0
 */
final class CBT_Elementor_Init {

    /**
     * Plugin Version
     *
     * @since 1.1.0
     * @var string The plugin version.
     */
    const VERSION = '1.1.0';

    /**
     * Minimum Elementor Version
     *
     * @since 1.1.0
     * @var string Minimum Elementor version required to run the plugin.
     */
    const MINIMUM_ELEMENTOR_VERSION = '3.0.0';

    /**
     * Instance
     *
     * @since 1.1.0
     * @access private
     * @static
     * @var \CBT_Elementor_Init The single instance of the class.
     */
    private static $_instance = null;

    /**
     * Instance
     *
     * Ensures only one instance of the class is loaded or can be loaded.
     *
     * @since 1.1.0
     * @access public
     * @static
     * @return \CBT_Elementor_Init An instance of the class.
     */
    public static function instance() {

        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;

    }

    /**
     * Constructor
     *
     * @since 1.1.0
     * @access public
     */
    public function __construct() {
        add_action( 'plugins_loaded', [ $this, 'init' ] );
    }

    /**
     * Initialize the plugin
     *
     * Load the plugin only after Elementor (and other plugins) are loaded.
     * Checks for basic plugin requirements, if on backend licenses, and registers the actions.
     *
     * @since 1.1.0
     * @access public
     */
    public function init() {

        // Check if Elementor installed and activated
        if ( ! did_action( 'elementor/loaded' ) ) {
            // add_action( 'admin_notices', [ $this, 'admin_notice_missing_main_plugin' ] );
            return;
        }

        // Check for required Elementor version
        if ( ! version_compare( ELEMENTOR_VERSION, self::MINIMUM_ELEMENTOR_VERSION, '>=' ) ) {
            // add_action( 'admin_notices', [ $this, 'admin_notice_minimum_elementor_version' ] );
            return;
        }

        // Add Plugin actions
        add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );
        add_action( 'elementor/elements/categories_registered', [ $this, 'add_widget_categories' ] );
    }

    /**
     * Register Widgets
     *
     * @since 1.1.0
     * @access public
     */
    public function register_widgets( $widgets_manager ) {
        require_once( __DIR__ . '/widgets/class-exam-card-widget.php' );
        require_once( __DIR__ . '/widgets/class-result-table-widget.php' );

        $widgets_manager->register( new \Elementor_Exam_Card_Widget() );
        $widgets_manager->register( new \Elementor_Result_Table_Widget() );
    }

    /**
     * Add Widget Categories
     *
     * @since 1.1.0
     * @access public
     */
    public function add_widget_categories( $elements_manager ) {
        $elements_manager->add_category(
            'cbt-exam',
            [
                'title' => __( 'CBT Exam', 'cbt-exam-plugin' ),
                'icon' => 'fa fa-graduation-cap',
            ]
        );
    }
}

CBT_Elementor_Init::instance();
