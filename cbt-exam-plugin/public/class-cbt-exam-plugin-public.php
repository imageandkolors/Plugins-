<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://example.com/
 * @since      1.0.0
 *
 * @package    Cbt_Exam_Plugin
 * @subpackage Cbt_Exam_Plugin/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Cbt_Exam_Plugin
 * @subpackage Cbt_Exam_Plugin/public
 * @author     Jules <you@example.com>
 */
class Cbt_Exam_Plugin_Public {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of the plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;

    }

    /**
     * Register the [cbt_exam] shortcode.
     *
     * @since    1.0.0
     */
    public function register_shortcodes() {
        add_shortcode( 'cbt_exam', array( $this, 'render_exam_shortcode' ) );
    }

    /**
     * Render the [cbt_exam] shortcode.
     *
     * @since    1.0.0
     * @param    array    $atts    Shortcode attributes.
     * @return   string           The shortcode output.
     */
    public function render_exam_shortcode( $atts ) {
        $atts = shortcode_atts( array(
            'id' => 0,
        ), $atts, 'cbt_exam' );

        $exam_id = intval( $atts['id'] );

        if ( ! $exam_id || get_post_type( $exam_id ) !== 'cbt_exam' ) {
            return '<p>' . __( 'Invalid exam ID.', 'cbt-exam-plugin' ) . '</p>';
        }

        // Enqueue scripts and styles for the exam interface
        $this->enqueue_exam_assets();

        $duration = get_post_meta( $exam_id, '_cbt_exam_duration', true );
        $question_ids = get_post_meta( $exam_id, '_cbt_exam_questions', true );

        if ( empty( $question_ids ) ) {
            return '<p>' . __( 'This exam has no questions.', 'cbt-exam-plugin' ) . '</p>';
        }

        $questions = get_posts( array(
            'post_type' => 'cbt_question',
            'post__in' => $question_ids,
            'orderby' => 'post__in',
            'numberposts' => -1,
        ) );

        ob_start();
        ?>
        <div id="cbt-exam-wrapper" class="cbt-exam-wrapper" data-exam-id="<?php echo esc_attr( $exam_id ); ?>" data-duration="<?php echo esc_attr( $duration ); ?>">
            <div class="cbt-exam-header">
                <h2><?php echo get_the_title( $exam_id ); ?></h2>
                <div class="cbt-timer">
                    <?php _e( 'Time Remaining:', 'cbt-exam-plugin' ); ?> <span id="cbt-time-display"></span>
                </div>
            </div>
            <div class="cbt-exam-body">
                <form id="cbt-exam-form">
                    <input type="hidden" name="exam_id" value="<?php echo esc_attr( $exam_id ); ?>">
                    <?php foreach ( $questions as $index => $question ) :
                        $question_type = get_post_meta( $question->ID, '_cbt_question_type', true );
                        $options = get_post_meta( $question->ID, '_cbt_question_options', true );
                        ?>
                        <div class="cbt-question" id="cbt-question-<?php echo $index; ?>" style="<?php echo $index > 0 ? 'display: none;' : ''; ?>">
                            <h3><?php echo $question->post_title; ?></h3>
                            <div class="cbt-question-content">
                                <?php echo apply_filters( 'the_content', $question->post_content ); ?>
                            </div>
                            <?php if ( $question_type === 'objective' && ! empty( $options ) ) : ?>
                                <ul class="cbt-options">
                                    <?php foreach ( $options as $opt_index => $option ) : ?>
                                        <li>
                                            <label>
                                                <input type="radio" name="answers[<?php echo $question->ID; ?>]" value="<?php echo $opt_index; ?>">
                                                <?php echo esc_html( $option ); ?>
                                            </label>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php elseif ( $question_type === 'theory' ) : ?>
                                <textarea name="answers[<?php echo $question->ID; ?>]" rows="8" cols="100"></textarea>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </form>
            </div>
            <div class="cbt-exam-footer">
                <button id="cbt-prev-btn" style="display: none;"><?php _e( 'Previous', 'cbt-exam-plugin' ); ?></button>
                <button id="cbt-next-btn"><?php _e( 'Next', 'cbt-exam-plugin' ); ?></button>
                <button id="cbt-submit-btn" style="display: none;"><?php _e( 'Submit Exam', 'cbt-exam-plugin' ); ?></button>
            </div>
            <div class="cbt-progress-bar">
                <div id="cbt-progress" class="cbt-progress"></div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Enqueue scripts and styles for the exam interface.
     *
     * @since    1.0.0
     */
    private function enqueue_exam_assets() {
        wp_enqueue_style(
            $this->plugin_name . '-exam',
            plugin_dir_url( __FILE__ ) . 'css/cbt-exam-public.css',
            array(),
            $this->version,
            'all'
        );

        wp_enqueue_script(
            $this->plugin_name . '-exam',
            plugin_dir_url( __FILE__ ) . 'js/cbt-exam-public.js',
            array( 'jquery' ),
            $this->version,
            true
        );

        // Pass data to the script
        $script_data = array(
            'totalQuestions' => count( get_post_meta( get_the_ID(), '_cbt_exam_questions', true ) ),
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'cbt_exam_nonce' ),
            'action' => 'cbt_submit_exam',
            'text' => array(
                'next' => __( 'Next', 'cbt-exam-plugin' ),
                'submit' => __( 'Submit Exam', 'cbt-exam-plugin' ),
                'results' => __( 'Exam Results', 'cbt-exam-plugin' ),
                'scored' => __( 'You scored:', 'cbt-exam-plugin' ),
                'percentage' => __( 'Percentage:', 'cbt-exam-plugin' ),
                'passed' => __( 'Congratulations, you passed!', 'cbt-exam-plugin' ),
                'failed' => __( 'Unfortunately, you did not pass.', 'cbt-exam-plugin' ),
            )
        );
        wp_localize_script( $this->plugin_name . '-exam', 'cbtExamData', $script_data );
    }

    /**
     * AJAX handler for submitting the exam.
     *
     * @since    1.0.0
     */
    public function submit_exam_ajax_handler() {
        check_ajax_referer( 'cbt_exam_nonce', 'nonce' );

        $exam_id = isset( $_POST['exam_id'] ) ? intval( $_POST['exam_id'] ) : 0;
        $answers = isset( $_POST['answers'] ) ? $_POST['answers'] : array();

        if ( ! $exam_id ) {
            wp_send_json_error( array( 'message' => 'Invalid exam.' ) );
        }

        $question_ids = get_post_meta( $exam_id, '_cbt_exam_questions', true );
        if ( empty( $question_ids ) ) {
            wp_send_json_error( array( 'message' => 'No questions found for this exam.' ) );
        }

        $score = 0;
        $total_objective = 0;

        foreach ( $question_ids as $question_id ) {
            $question_type = get_post_meta( $question_id, '_cbt_question_type', true );

            if ( $question_type === 'objective' ) {
                $total_objective++;
                $correct_answer = get_post_meta( $question_id, '_cbt_correct_answer', true );
                $student_answer = isset( $answers[ $question_id ] ) ? $answers[ $question_id ] : null;

                if ( $correct_answer !== '' && $student_answer == $correct_answer ) {
                    $score++;
                }
            }
        }

        $pass_mark = get_post_meta( $exam_id, '_cbt_exam_pass_mark', true );
        $percentage = ( $total_objective > 0 ) ? ( $score / $total_objective ) * 100 : 0;
        $passed = ( $pass_mark && $percentage >= $pass_mark ) ? true : false;

        // Here you would typically save the result to the database.
        // For now, we just return the result.

        wp_send_json_success( array(
            'score' => $score,
            'total' => $total_objective,
            'percentage' => round( $percentage, 2 ),
            'passed' => $passed,
            'pass_mark' => $pass_mark,
        ) );
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Cbt_Exam_Plugin_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Cbt_Exam_Plugin_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/cbt-exam-plugin-public.css', array(), $this->version, 'all' );

    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Cbt_Exam_Plugin_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Cbt_Exam_Plugin_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/cbt-exam-plugin-public.js', array( 'jquery' ), $this->version, false );

    }

}
