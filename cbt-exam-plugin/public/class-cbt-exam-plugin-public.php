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

        add_action( 'init', array( $this, 'download_certificate_handler' ) );
    }

    /**
     * Handle the certificate download request.
     *
     * @since    1.5.0
     */
    public function download_certificate_handler() {
        if ( ! isset( $_GET['action'] ) || 'download_certificate' !== $_GET['action'] ) {
            return;
        }

        $result_id = isset( $_GET['result_id'] ) ? intval( $_GET['result_id'] ) : 0;
        $nonce = isset( $_GET['nonce'] ) ? $_GET['nonce'] : '';

        if ( ! $result_id || ! $nonce || ! wp_verify_nonce( $nonce, 'cbt_download_cert_' . $result_id ) ) {
            wp_die( 'Invalid request.' );
        }

        $result = get_post( $result_id );
        $user_id = get_current_user_id();

        // Security check: ensure user has permission to view this certificate
        if ( $user_id != $result->post_author && ! current_user_can( 'manage_options' ) ) {
            // A more robust check would involve checking parent/teacher roles
            wp_die( 'You do not have permission to view this certificate.' );
        }

        $exam_id = get_post_meta( $result_id, '_cbt_result_exam_id', true );

        // Assume Mpdf is in vendor directory. This is a simplification.
        // In a real plugin, you'd use Composer's autoloader.
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'vendor/mpdf/mpdf/src/Mpdf.php';

        $mpdf = new \Mpdf\Mpdf();

        $title = get_post_meta( $exam_id, '_cbt_certificate_title', true );
        $body = get_post_meta( $exam_id, '_cbt_certificate_body', true );
        $student = get_userdata( $result->post_author );
        $score = get_post_meta( $result_id, '_cbt_result_score', true );

        // Replace placeholders
        $body = str_replace( '[student_name]', $student->display_name, $body );
        $body = str_replace( '[exam_name]', get_the_title( $exam_id ), $body );
        $body = str_replace( '[completion_date]', get_the_date( '', $result_id ), $body );
        $body = str_replace( '[score]', $score, $body );

        $html = "<h1>{$title}</h1><p>{$body}</p>";

        $mpdf->WriteHTML( $html );
        $mpdf->Output( 'certificate.pdf', 'D' ); // D for download
        die();
    }

    /**
     * Register the [cbt_exam] shortcode.
     *
     * @since    1.0.0
     */
    public function register_shortcodes() {
        add_shortcode( 'cbt_exam', array( $this, 'render_exam_shortcode' ) );
        add_shortcode( 'cbt_dashboard', array( $this, 'render_dashboard_shortcode' ) );
    }

    /**
     * Render the [cbt_dashboard] shortcode.
     *
     * @since    1.2.0
     * @return   string   The shortcode output.
     */
    public function render_dashboard_shortcode() {
        if ( ! is_user_logged_in() ) {
            return '<p>' . __( 'You must be logged in to view your dashboard.', 'cbt-exam-plugin' ) . '</p>';
        }

        $current_user = wp_get_current_user();

        ob_start();

        if ( in_array( 'cbt_parent', (array) $current_user->roles ) ) {
            // Parent view
            $linked_children = get_user_meta( $current_user->ID, '_cbt_linked_children', true );
            if ( empty( $linked_children ) ) {
                echo '<p>' . __( 'You do not have any children linked to your account.', 'cbt-exam-plugin' ) . '</p>';
                return ob_get_clean();
            }

            $selected_child_id = isset( $_GET['student_id'] ) ? intval( $_GET['student_id'] ) : $linked_children[0];

            echo '<form method="get">';
            echo '<select name="student_id" onchange="this.form.submit()">';
            foreach( $linked_children as $child_id ) {
                $child = get_userdata( $child_id );
                echo '<option value="' . esc_attr( $child_id ) . '" ' . selected( $selected_child_id, $child_id, false ) . '>' . esc_html( $child->display_name ) . '</option>';
            }
            echo '</select>';
            echo '</form>';

            $this->display_student_dashboard( $selected_child_id );

        } else {
            // Student view
            $this->display_student_dashboard( $current_user->ID );
        }

        return ob_get_clean();
    }

    /**
     * Display the dashboard for a given student ID.
     *
     * @since    1.4.0
     * @param    int    $user_id    The ID of the student.
     */
    private function display_student_dashboard( $user_id ) {
        // Get completed exams
        $completed_results_query = new \WP_Query( array(
            'post_type' => 'cbt_result',
            'author' => $user_id,
            'posts_per_page' => -1,
        ) );

        $completed_exams = [];
        $completed_exam_ids = [];
        if ( $completed_results_query->have_posts() ) {
            while ( $completed_results_query->have_posts() ) {
                $completed_results_query->the_post();
                $exam_id = get_post_meta( get_the_ID(), '_cbt_result_exam_id', true );
                $completed_exams[] = [
                    'result_id' => get_the_ID(),
                    'exam_id' => $exam_id,
                    'exam_title' => get_the_title( $exam_id ),
                    'score' => get_post_meta( get_the_ID(), '_cbt_result_score', true ),
                    'total' => get_post_meta( get_the_ID(), '_cbt_result_total_objective', true ),
                    'percentage' => get_post_meta( get_the_ID(), '_cbt_result_percentage', true ),
                    'passed' => get_post_meta( get_the_ID(), '_cbt_result_passed', true ),
                    'date' => get_the_date(),
                ];
                $completed_exam_ids[] = $exam_id;
            }
        }
        wp_reset_postdata();

        // Get upcoming exams
        $all_exams_query = new \WP_Query( array(
            'post_type' => 'cbt_exam',
            'posts_per_page' => -1,
            'post__not_in' => $completed_exam_ids,
        ) );
        ?>
        <div class="cbt-dashboard">
            <h3><?php _e( 'Upcoming Exams', 'cbt-exam-plugin' ); ?></h3>
            <?php if ( $all_exams_query->have_posts() ) : ?>
                <ul>
                    <?php while ( $all_exams_query->have_posts() ) : $all_exams_query->the_post(); ?>
                        <li>
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else : ?>
                <p><?php _e( 'You have no upcoming exams.', 'cbt-exam-plugin' ); ?></p>
            <?php endif; ?>
            <?php wp_reset_postdata(); ?>

            <h3><?php _e( 'Completed Exams', 'cbt-exam-plugin' ); ?></h3>
            <?php if ( ! empty( $completed_exams ) ) : ?>
                <table>
                    <thead>
                        <tr>
                            <th><?php _e( 'Exam', 'cbt-exam-plugin' ); ?></th>
                            <th><?php _e( 'Score', 'cbt-exam-plugin' ); ?></th>
                            <th><?php _e( 'Percentage', 'cbt-exam-plugin' ); ?></th>
                            <th><?php _e( 'Date', 'cbt-exam-plugin' ); ?></th>
                            <th><?php _e( 'Certificate', 'cbt-exam-plugin' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $completed_exams as $exam ) : ?>
                            <tr>
                                <td><?php echo esc_html( $exam['exam_title'] ); ?></td>
                                <td><?php echo esc_html( $exam['score'] . ' / ' . $exam['total'] ); ?></td>
                                <td><?php echo esc_html( $exam['percentage'] ); ?>%</td>
                                <td><?php echo esc_html( $exam['date'] ); ?></td>
                                <td>
                                    <?php
                                    $enable_cert = get_post_meta( $exam['exam_id'], '_cbt_enable_certificate', true );
                                    if ( $enable_cert && $exam['passed'] ) {
                                        $cert_url = add_query_arg( [
                                            'action' => 'download_certificate',
                                            'result_id' => $exam['result_id'],
                                            'nonce' => wp_create_nonce( 'cbt_download_cert_' . $exam['result_id'] )
                                        ], site_url() );
                                        echo '<a href="' . esc_url( $cert_url ) . '" class="button">' . __( 'Download', 'cbt-exam-plugin' ) . '</a>';
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p><?php _e( 'You have not completed any exams yet.', 'cbt-exam-plugin' ); ?></p>
            <?php endif; ?>
        </div>
        <?php
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
        $randomize = get_post_meta( $exam_id, '_cbt_randomize_questions', true );
        $proctoring_enabled = get_post_meta( $exam_id, '_cbt_enable_proctoring', true );

        if ( $randomize ) {
            shuffle( $question_ids );
        }

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
        <div id="cbt-exam-container">

            <?php if ( $proctoring_enabled ) : ?>
                <div id="cbt-proctoring-consent">
                    <h2><?php _e( 'Exam Proctoring Enabled', 'cbt-exam-plugin' ); ?></h2>
                    <p><?php _e( 'This exam is proctored. You must grant access to your webcam and microphone to continue.', 'cbt-exam-plugin' ); ?></p>
                    <button id="cbt-grant-access-btn" class="button button-primary"><?php _e( 'Grant Access', 'cbt-exam-plugin' ); ?></button>
                </div>
                <div id="cbt-proctoring-video-wrapper" style="display: none; position: fixed; bottom: 10px; right: 10px; border: 2px solid #ccc; z-index: 9999;">
                    <video id="cbt-proctoring-video" width="200" autoplay muted></video>
                </div>
            <?php endif; ?>

            <div id="cbt-exam-wrapper" class="cbt-exam-wrapper" data-exam-id="<?php echo esc_attr( $exam_id ); ?>" style="<?php echo $proctoring_enabled ? 'display: none;' : ''; ?>">
                <div class="cbt-exam-header">
                    <h2><?php echo get_the_title( $exam_id ); ?></h2>
                </div>
                <div class="cbt-exam-body">
                <form id="cbt-exam-form">
                    <input type="hidden" name="exam_id" value="<?php echo esc_attr( $exam_id ); ?>">
                    <?php foreach ( $questions as $index => $question ) :
                        $question_type = get_post_meta( $question->ID, '_cbt_question_type', true );
                        $options = get_post_meta( $question->ID, '_cbt_question_options', true );
                        $time_limit = get_post_meta( $question->ID, '_cbt_question_time_limit', true );
                        ?>
                        <div class="cbt-question" id="cbt-question-<?php echo $index; ?>" style="<?php echo $index > 0 ? 'display: none;' : ''; ?>" data-time-limit="<?php echo esc_attr( $time_limit ); ?>">
                            <h3><?php echo $question->post_title; ?></h3>
                            <?php if ( $time_limit ) : ?>
                                <div class="cbt-question-timer">
                                    <?php _e( 'Time for this question:', 'cbt-exam-plugin' ); ?> <span class="cbt-question-time-display"></span>
                                </div>
                            <?php endif; ?>
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
                'pending_review' => __( 'Your objective questions have been graded. Your final score will be available after your theory questions have been reviewed.', 'cbt-exam-plugin' ),
                'objective_score' => __( 'Objective Score:', 'cbt-exam-plugin' ),
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
        $has_theory = false;

        foreach ( $question_ids as $question_id ) {
            $question_type = get_post_meta( $question_id, '_cbt_question_type', true );

            if ( $question_type === 'objective' ) {
                $total_objective++;
                $correct_answer = get_post_meta( $question_id, '_cbt_correct_answer', true );
                $student_answer = isset( $answers[ $question_id ] ) ? $answers[ $question_id ] : null;

                if ( $correct_answer !== '' && $student_answer == $correct_answer ) {
                    $score++;
                }
            } else {
                $has_theory = true;
            }
        }

        $pass_mark = get_post_meta( $exam_id, '_cbt_exam_pass_mark', true );
        $percentage = ( $total_objective > 0 ) ? ( $score / $total_objective ) * 100 : 0;
        $grading_status = $has_theory ? 'pending' : 'graded';
        $passed = ( ! $has_theory && $pass_mark && $percentage >= $pass_mark ) ? true : false;

        // Save the result to the database
        $user_id = get_current_user_id();
        if ( $user_id ) {
            $result_post = array(
                'post_title'   => 'Result for ' . get_the_title( $exam_id ) . ' by user ' . $user_id,
                'post_content' => '',
                'post_status'  => 'publish',
                'post_author'  => $user_id,
                'post_type'    => 'cbt_result',
            );

            $result_id = wp_insert_post( $result_post );

            if ( ! is_wp_error( $result_id ) ) {
                update_post_meta( $result_id, '_cbt_result_exam_id', $exam_id );
                update_post_meta( $result_id, '_cbt_result_user_id', $user_id );
                update_post_meta( $result_id, '_cbt_result_objective_score', $score ); // Store objective score
                update_post_meta( $result_id, '_cbt_result_total_objective', $total_objective );
                update_post_meta( $result_id, '_cbt_result_answers', $answers );
                update_post_meta( $result_id, '_cbt_grading_status', $grading_status );

                // Only set final score if fully graded
                if ( ! $has_theory ) {
                    update_post_meta( $result_id, '_cbt_result_score', $score );
                    update_post_meta( $result_id, '_cbt_result_percentage', round( $percentage, 2 ) );
                    update_post_meta( $result_id, '_cbt_result_passed', $passed );
                }
            }
        }

        $response_data = array(
            'score' => $score,
            'total' => $total_objective,
            'percentage' => round( $percentage, 2 ),
            'passed' => $passed,
            'pass_mark' => $pass_mark,
            'status' => $grading_status,
        );

        wp_send_json_success( $response_data );
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
