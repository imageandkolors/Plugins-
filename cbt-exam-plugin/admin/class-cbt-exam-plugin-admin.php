<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://example.com/
 * @since      1.0.0
 *
 * @package    Cbt_Exam_Plugin
 * @subpackage Cbt_Exam_Plugin/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Cbt_Exam_Plugin
 * @subpackage Cbt_Exam_Plugin/admin
 * @author     Jules <you@example.com>
 */
class Cbt_Exam_Plugin_Admin {

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
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;

        add_action( 'admin_menu', array( $this, 'add_admin_pages' ) );
        add_action( 'admin_init', array( $this, 'handle_admin_actions' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'show_user_profile', array( $this, 'add_user_profile_fields' ) );
        add_action( 'edit_user_profile', array( $this, 'add_user_profile_fields' ) );
        add_action( 'personal_options_update', array( $this, 'save_user_profile_fields' ) );
        add_action( 'edit_user_profile_update', array( $this, 'save_user_profile_fields' ) );
    }

    /**
     * Add custom fields to the user profile page.
     *
     * @since    1.4.0
     * @param    WP_User    $user    The user object.
     */
    public function add_user_profile_fields( $user ) {
        if ( ! in_array( 'cbt_parent', (array) $user->roles ) ) {
            return;
        }

        $children = get_users( ['role' => 'subscriber'] ); // Assuming students are subscribers
        $linked_children = get_user_meta( $user->ID, '_cbt_linked_children', true );
        if ( ! is_array( $linked_children ) ) {
            $linked_children = [];
        }

        ?>
        <h3><?php _e( 'Parent/Child Linking', 'cbt-exam-plugin' ); ?></h3>
        <table class="form-table">
            <tr>
                <th><label for="linked_children"><?php _e( 'Linked Children', 'cbt-exam-plugin' ); ?></label></th>
                <td>
                    <select name="linked_children[]" id="linked_children" multiple="multiple" style="min-width: 200px;">
                        <?php foreach ( $children as $child ) : ?>
                            <option value="<?php echo esc_attr( $child->ID ); ?>" <?php selected( in_array( $child->ID, $linked_children ) ); ?>>
                                <?php echo esc_html( $child->display_name ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description"><?php _e( 'Select the student accounts linked to this parent.', 'cbt-exam-plugin' ); ?></p>
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Save the custom user profile fields.
     *
     * @since    1.4.0
     * @param    int    $user_id    The user ID.
     */
    public function save_user_profile_fields( $user_id ) {
        if ( ! current_user_can( 'edit_user', $user_id ) ) {
            return false;
        }

        if ( isset( $_POST['linked_children'] ) ) {
            $children_ids = array_map( 'intval', $_POST['linked_children'] );
            update_user_meta( $user_id, '_cbt_linked_children', $children_ids );
        } else {
            delete_user_meta( $user_id, '_cbt_linked_children' );
        }
    }

    /**
     * Register the settings for the plugin.
     *
     * @since    1.4.0
     */
    public function register_settings() {
        register_setting(
            'cbt_exam_settings_group',
            'cbt_exam_options',
            array( 'sanitize_callback' => array( $this, 'sanitize_settings' ) )
        );

        add_settings_section(
            'cbt_exam_general_section',
            __( 'General Settings', 'cbt-exam-plugin' ),
            array( $this, 'render_general_section' ),
            'cbt-exam-plugin'
        );

        add_settings_field(
            'default_randomization',
            __( 'Default Randomization', 'cbt-exam-plugin' ),
            array( $this, 'render_default_randomization_field' ),
            'cbt-exam-plugin',
            'cbt_exam_general_section'
        );
    }

    /**
     * Sanitize the settings.
     *
     * @since    1.4.0
     * @param    array    $input    The input from the settings form.
     * @return   array              The sanitized input.
     */
    public function sanitize_settings( $input ) {
        $new_input = array();
        if ( isset( $input['default_randomization'] ) ) {
            $new_input['default_randomization'] = absint( $input['default_randomization'] );
        }
        return $new_input;
    }

    /**
     * Render the general section description.
     *
     * @since    1.4.0
     */
    public function render_general_section() {
        echo '<p>' . __( 'General settings for the CBT Exam plugin.', 'cbt-exam-plugin' ) . '</p>';
    }

    /**
     * Render the default randomization field.
     *
     * @since    1.4.0
     */
    public function render_default_randomization_field() {
        $options = get_option( 'cbt_exam_options' );
        $checked = isset( $options['default_randomization'] ) ? $options['default_randomization'] : 0;
        ?>
        <input type="checkbox" name="cbt_exam_options[default_randomization]" value="1" <?php checked( $checked, 1 ); ?> />
        <label><?php _e( 'Enable question randomization for all new exams by default.', 'cbt-exam-plugin' ); ?></label>
        <?php
    }

    /**
     * Add the import page to the admin menu.
     *
     * @since    1.3.0
     */
    public function add_admin_pages() {
        // Add top-level menu page
        add_menu_page(
            __( 'CBT Exam', 'cbt-exam-plugin' ),
            __( 'CBT Exam', 'cbt-exam-plugin' ),
            'manage_options',
            'cbt-exam-plugin',
            array( $this, 'render_settings_page' ),
            'dashicons-welcome-learn-more',
            20
        );

        // Import Questions page
        add_submenu_page(
            'cbt-exam-plugin',
            __( 'Import Questions', 'cbt-exam-plugin' ),
            __( 'Import Questions', 'cbt-exam-plugin' ),
            'manage_exams',
            'cbt-question-import',
            array( $this, 'render_import_page' )
        );

        // Manual Grading page
        add_submenu_page(
            'cbt-exam-plugin',
            __( 'Manual Grading', 'cbt-exam-plugin' ),
            __( 'Manual Grading', 'cbt-exam-plugin' ),
            'grade_exams',
            'cbt-manual-grading',
            array( $this, 'render_grading_page' )
        );

        // Exam Reports page
        add_submenu_page(
            'cbt-exam-plugin',
            __( 'Exam Reports', 'cbt-exam-plugin' ),
            __( 'Exam Reports', 'cbt-exam-plugin' ),
            'view_exam_reports',
            'cbt-exam-reports',
            array( $this, 'render_reports_page' )
        );
    }

    /**
     * Render the reports page.
     *
     * @since    1.4.0
     */
    public function render_reports_page() {
        require_once plugin_dir_path( __FILE__ ) . 'class-cbt-exam-reports-list-table.php';
        require_once plugin_dir_path( __FILE__ ) . 'class-cbt-exam-results-list-table.php';

        echo '<div class="wrap">';
        echo '<h1>' . esc_html__( 'Exam Reports', 'cbt-exam-plugin' ) . '</h1>';

        $action = isset( $_GET['action'] ) ? $_GET['action'] : 'list';
        $exam_id = isset( $_GET['exam_id'] ) ? intval( $_GET['exam_id'] ) : 0;
        $result_id = isset( $_GET['result_id'] ) ? intval( $_GET['result_id'] ) : 0;

        if ( 'view_submission' === $action && $result_id ) {
            $this->render_single_submission_view( $result_id );
        }
        else if ( 'view_exam_report' === $action && $exam_id ) {
            $this->render_single_exam_report( $exam_id );
        } else {
            $list_table = new CBT_Exam_Reports_List_Table();
            $list_table->prepare_items();
            $list_table->display();
        }

        echo '</div>';
    }

    /**
     * Render the report for a single exam.
     *
     * @since    1.4.0
     * @param    int    $exam_id    The ID of the exam.
     */
    private function render_single_exam_report( $exam_id ) {
        echo '<h2>' . sprintf( __( 'Report for: %s', 'cbt-exam-plugin' ), get_the_title( $exam_id ) ) . '</h2>';
        $list_table = new CBT_Exam_Results_List_Table( $exam_id );
        $list_table->prepare_items();
        $list_table->display();
    }

    /**
     * Render the view for a single submission.
     *
     * @since    1.4.0
     * @param    int    $result_id    The ID of the result post.
     */
    private function render_single_submission_view( $result_id ) {
        $result = get_post( $result_id );
        $exam_id = get_post_meta( $result_id, '_cbt_result_exam_id', true );
        $student = get_userdata( $result->post_author );
        $answers = get_post_meta( $result_id, '_cbt_result_answers', true );
        $question_ids = get_post_meta( $exam_id, '_cbt_exam_questions', true );

        echo '<h2>' . sprintf( __( 'Submission by %s for %s', 'cbt-exam-plugin' ), $student->display_name, get_the_title( $exam_id ) ) . '</h2>';

        foreach( $question_ids as $question_id ) {
            $question = get_post( $question_id );
            $question_type = get_post_meta( $question_id, '_cbt_question_type', true );
            $student_answer = isset( $answers[ $question_id ] ) ? $answers[ $question_id ] : 'N/A';

            echo '<h4>' . $question->post_title . '</h4>';
            echo '<p><strong>' . __( 'Student Answer:', 'cbt-exam-plugin' ) . '</strong> ';
            if ( $question_type === 'objective' ) {
                $options = get_post_meta( $question_id, '_cbt_question_options', true );
                $correct_answer_index = get_post_meta( $question_id, '_cbt_correct_answer', true );
                echo esc_html( $options[ $student_answer ] );
                echo ' (' . ( $student_answer == $correct_answer_index ? 'Correct' : 'Incorrect' ) . ')';
            } else {
                echo nl2br( esc_textarea( $student_answer ) );
            }
            echo '</p>';
        }
    }

    /**
     * Render the settings page.
     *
     * @since    1.4.0
     */
    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php _e( 'CBT Exam Settings', 'cbt-exam-plugin' ); ?></h1>
            <form method="post" action="options.php">
                <?php
                    settings_fields( 'cbt_exam_settings_group' );
                    do_settings_sections( 'cbt-exam-plugin' );
                    submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Render the manual grading page.
     *
     * @since    1.3.0
     */
    public function render_grading_page() {
        require_once plugin_dir_path( __FILE__ ) . 'class-cbt-grading-list-table.php';

        echo '<div class="wrap">';
        echo '<h1>' . esc_html__( 'Manual Grading', 'cbt-exam-plugin' ) . '</h1>';

        $action = isset( $_GET['action'] ) ? $_GET['action'] : 'list';
        $result_id = isset( $_GET['result_id'] ) ? intval( $_GET['result_id'] ) : 0;

        if ( 'grade' === $action && $result_id ) {
            $this->render_single_grading_interface( $result_id );
        } else {
            echo '<p>' . esc_html__( 'Here you can grade exams that have theory questions and are pending review.', 'cbt-exam-plugin' ) . '</p>';
            $list_table = new CBT_Grading_List_Table();
            $list_table->prepare_items();
            $list_table->display();
        }

        echo '</div>';
    }

    /**
     * Render the interface for grading a single result.
     *
     * @since    1.3.0
     * @param    int    $result_id    The ID of the result post.
     */
    private function render_single_grading_interface( $result_id ) {
        // Placeholder for the single grading interface
        echo '<h2>' . esc_html__( 'Grade Exam', 'cbt-exam-plugin' ) . '</h2>';
        echo '<p>' . sprintf( __( 'Now grading result ID: %d', 'cbt-exam-plugin' ), $result_id ) . '</p>';
    }

    /**
     * Render the import page.
     *
     * @since    1.3.0
     */
    public function render_import_page() {
        ?>
        <div class="wrap">
            <h1><?php _e( 'Import Questions from CSV', 'cbt-exam-plugin' ); ?></h1>
            <p><?php _e( 'Upload a CSV file to import questions into the question bank.', 'cbt-exam-plugin' ); ?></p>

            <form method="post" enctype="multipart/form-data">
                <?php wp_nonce_field( 'cbt_import_questions_nonce', 'cbt_import_nonce' ); ?>
                <p>
                    <label for="csv_file"><?php _e( 'Choose a CSV file:', 'cbt-exam-plugin' ); ?></label>
                    <input type="file" id="csv_file" name="csv_file" accept=".csv" required>
                </p>
                <p>
                    <input type="submit" name="cbt_import_submit" class="button button-primary" value="<?php _e( 'Import Questions', 'cbt-exam-plugin' ); ?>" />
                </p>
            </form>

            <h3><?php _e( 'CSV File Format Instructions', 'cbt-exam-plugin' ); ?></h3>
            <p><?php _e( 'The CSV file must have the following columns in this specific order:', 'cbt-exam-plugin' ); ?></p>
            <ol>
                <li><strong>title</strong>: The question title.</li>
                <li><strong>content</strong>: The main content/body of the question.</li>
                <li><strong>type</strong>: The question type. Must be either <code>objective</code> or <code>theory</code>.</li>
                <li><strong>options</strong>: For objective questions, a pipe-separated list of options (e.g., <code>Option A|Option B|Option C</code>). Leave empty for theory questions.</li>
                <li><strong>correct_answer</strong>: For objective questions, the index of the correct answer (starting from 0). Leave empty for theory questions.</li>
                <li><strong>time_limit</strong>: The time limit for the question in seconds. Leave empty for no limit.</li>
                <li><strong>subject</strong>: The subject taxonomy term.</li>
                <li><strong>topic</strong>: The topic taxonomy term.</li>
                <li><strong>class_level</strong>: The class level taxonomy term.</li>
            </ol>
        </div>
        <?php
    }

    /**
     * Handle admin actions like import and grading.
     *
     * @since    1.3.0
     */
    public function handle_admin_actions() {
        // Handle Question Import
        if ( isset( $_POST['cbt_import_submit'] ) && isset( $_POST['cbt_import_nonce'] ) ) {
            if ( ! wp_verify_nonce( $_POST['cbt_import_nonce'], 'cbt_import_questions_nonce' ) ) {
                wp_die( 'Security check failed.' );
            }
            $this->process_question_import();
        }

        // Handle Grade Submission
        if ( isset( $_POST['cbt_grade_submit'] ) && isset( $_POST['cbt_grade_nonce'] ) ) {
            if ( ! wp_verify_nonce( $_POST['cbt_grade_nonce'], 'cbt_grade_submission_nonce' ) ) {
                wp_die( 'Security check failed.' );
            }
            $this->process_grade_submission();
        }
    }

    /**
     * Process the grade submission.
     *
     * @since    1.3.0
     */
    private function process_grade_submission() {
        $result_id = isset( $_POST['result_id'] ) ? intval( $_POST['result_id'] ) : 0;
        $theory_scores = isset( $_POST['theory_scores'] ) ? $_POST['theory_scores'] : array();

        if ( ! $result_id || ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $objective_score = (int) get_post_meta( $result_id, '_cbt_result_objective_score', true );
        $theory_score = 0;
        foreach( $theory_scores as $score ) {
            $theory_score += (int) $score;
        }

        $total_score = $objective_score + $theory_score;

        // Here you would also need to know the total possible score for theory questions
        // to calculate a percentage. For now, we'll just save the raw score.

        update_post_meta( $result_id, '_cbt_result_theory_score', $theory_score );
        update_post_meta( $result_id, '_cbt_result_score', $total_score );
        update_post_meta( $result_id, '_cbt_grading_status', 'graded' );

        // Redirect back to the grading page
        wp_redirect( admin_url( 'edit.php?post_type=cbt_question&page=cbt-manual-grading' ) );
        exit;
    }

    /**
     * Process the question import from CSV.
     *
     * @since    1.3.0
     */
    private function process_question_import() {
        if ( ! isset( $_FILES['csv_file'] ) || $_FILES['csv_file']['error'] != 0 ) {
            return;
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'You do not have permission to import questions.' );
        }

        $file = $_FILES['csv_file']['tmp_name'];
        $handle = fopen( $file, "r" );
        $imported = 0;
        $failed = 0;

        // Skip header row
        fgetcsv( $handle, 1000, "," );

        while ( ( $data = fgetcsv( $handle, 1000, "," ) ) !== FALSE ) {
            // Create post object
            $new_question = array(
                'post_title'    => wp_strip_all_tags( $data[0] ),
                'post_content'  => $data[1],
                'post_type'     => 'cbt_question',
                'post_status'   => 'publish',
            );

            // Insert the post into the database
            $post_id = wp_insert_post( $new_question );

            if ( $post_id ) {
                // Set post meta
                update_post_meta( $post_id, '_cbt_question_type', sanitize_text_field( $data[2] ) );
                if ( $data[2] === 'objective' ) {
                    $options = explode( '|', $data[3] );
                    update_post_meta( $post_id, '_cbt_question_options', $options );
                    update_post_meta( $post_id, '_cbt_correct_answer', sanitize_text_field( $data[4] ) );
                }
                update_post_meta( $post_id, '_cbt_question_time_limit', sanitize_text_field( $data[5] ) );

                // Set taxonomies
                wp_set_object_terms( $post_id, sanitize_text_field( $data[6] ), 'cbt_subject' );
                wp_set_object_terms( $post_id, sanitize_text_field( $data[7] ), 'cbt_topic' );
                wp_set_object_terms( $post_id, sanitize_text_field( $data[8] ), 'cbt_class_level' );

                $imported++;
            } else {
                $failed++;
            }
        }
        fclose( $handle );

        // Add admin notice
        add_action( 'admin_notices', function() use ( $imported, $failed ) {
            ?>
            <div class="notice notice-success is-dismissible">
                <p><?php printf( __( 'Import complete. Successfully imported %d questions. Failed to import %d rows.', 'cbt-exam-plugin' ), $imported, $failed ); ?></p>
            </div>
            <?php
        });
    }

    /**
     * Render the interface for grading a single result.
     *
     * @since    1.3.0
     * @param    int    $result_id    The ID of the result post.
     */
    private function render_single_grading_interface( $result_id ) {
        $result = get_post( $result_id );
        $exam_id = get_post_meta( $result_id, '_cbt_result_exam_id', true );
        $student_id = $result->post_author;
        $answers = get_post_meta( $result_id, '_cbt_result_answers', true );
        $objective_score = get_post_meta( $result_id, '_cbt_result_objective_score', true );
        $question_ids = get_post_meta( $exam_id, '_cbt_exam_questions', true );

        $student = get_userdata( $student_id );
        ?>
        <h2><?php printf( __( 'Grading: %s', 'cbt-exam-plugin' ), get_the_title( $exam_id ) ); ?></h2>
        <p><?php printf( __( 'Student: %s', 'cbt-exam-plugin' ), $student->display_name ); ?></p>
        <p><strong><?php printf( __( 'Objective Score: %s', 'cbt-exam-plugin' ), $objective_score ); ?></strong></p>

        <form method="post">
            <input type="hidden" name="result_id" value="<?php echo esc_attr( $result_id ); ?>" />
            <?php wp_nonce_field( 'cbt_grade_submission_nonce', 'cbt_grade_nonce' ); ?>

            <table class="form-table">
                <tbody>
                <?php foreach ( $question_ids as $question_id ) :
                    $question = get_post( $question_id );
                    $question_type = get_post_meta( $question_id, '_cbt_question_type', true );

                    if ( 'theory' !== $question_type ) {
                        continue;
                    }

                    $student_answer = isset( $answers[ $question_id ] ) ? $answers[ $question_id ] : __( 'No answer submitted.', 'cbt-exam-plugin' );
                    ?>
                    <tr valign="top">
                        <th scope="row">
                            <strong><?php echo esc_html( $question->post_title ); ?></strong>
                            <p><?php echo esc_html( $question->post_content ); ?></p>
                        </th>
                        <td>
                            <strong><?php _e( 'Student Answer:', 'cbt-exam-plugin' ); ?></strong>
                            <p><?php echo nl2br( esc_textarea( $student_answer ) ); ?></p>
                            <label for="theory_score_<?php echo esc_attr( $question_id ); ?>"><?php _e( 'Score:', 'cbt-exam-plugin' ); ?></label>
                            <input type="number" id="theory_score_<?php echo esc_attr( $question_id ); ?>" name="theory_scores[<?php echo esc_attr( $question_id ); ?>]" value="0" min="0" />
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <?php submit_button( __( 'Save Grade', 'cbt-exam-plugin' ), 'primary', 'cbt_grade_submit' ); ?>
        </form>
        <?php
    }

    /**
     * Handle the question import from CSV.
     *
     * @since    1.3.0
     */
    public function handle_question_import() {
        if ( ! isset( $_POST['cbt_import_submit'] ) || ! isset( $_POST['cbt_import_nonce'] ) ) {
            return;
        }

        if ( ! wp_verify_nonce( $_POST['cbt_import_nonce'], 'cbt_import_questions_nonce' ) ) {
            wp_die( 'Security check failed.' );
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'You do not have permission to import questions.' );
        }

        if ( isset( $_FILES['csv_file'] ) && $_FILES['csv_file']['error'] == 0 ) {
            $file = $_FILES['csv_file']['tmp_name'];
            $handle = fopen( $file, "r" );
            $imported = 0;
            $failed = 0;

            // Skip header row
            fgetcsv( $handle, 1000, "," );

            while ( ( $data = fgetcsv( $handle, 1000, "," ) ) !== FALSE ) {
                // Create post object
                $new_question = array(
                    'post_title'    => wp_strip_all_tags( $data[0] ),
                    'post_content'  => $data[1],
                    'post_type'     => 'cbt_question',
                    'post_status'   => 'publish',
                );

                // Insert the post into the database
                $post_id = wp_insert_post( $new_question );

                if ( $post_id ) {
                    // Set post meta
                    update_post_meta( $post_id, '_cbt_question_type', sanitize_text_field( $data[2] ) );
                    if ( $data[2] === 'objective' ) {
                        $options = explode( '|', $data[3] );
                        update_post_meta( $post_id, '_cbt_question_options', $options );
                        update_post_meta( $post_id, '_cbt_correct_answer', sanitize_text_field( $data[4] ) );
                    }
                    update_post_meta( $post_id, '_cbt_question_time_limit', sanitize_text_field( $data[5] ) );

                    // Set taxonomies
                    wp_set_object_terms( $post_id, sanitize_text_field( $data[6] ), 'cbt_subject' );
                    wp_set_object_terms( $post_id, sanitize_text_field( $data[7] ), 'cbt_topic' );
                    wp_set_object_terms( $post_id, sanitize_text_field( $data[8] ), 'cbt_class_level' );

                    $imported++;
                } else {
                    $failed++;
                }
            }
            fclose( $handle );

            // Add admin notice
            add_action( 'admin_notices', function() use ( $imported, $failed ) {
                ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php printf( __( 'Import complete. Successfully imported %d questions. Failed to import %d rows.', 'cbt-exam-plugin' ), $imported, $failed ); ?></p>
                </div>
                <?php
            });

        }
    }

    /**
     * Add meta boxes for the "Question" and "Exam" post types.
     *
     * @since    1.0.0
     */
    public function add_meta_boxes() {
        // Question Meta Box
        add_meta_box(
            'cbt_question_details',
            __( 'Question Details', 'cbt-exam-plugin' ),
            array( $this, 'render_question_meta_box' ),
            'cbt_question',
            'normal',
            'high'
        );

        // Exam Meta Boxes
        add_meta_box(
            'cbt_exam_settings',
            __( 'Exam Settings', 'cbt-exam-plugin' ),
            array( $this, 'render_exam_settings_meta_box' ),
            'cbt_exam',
            'normal',
            'high'
        );
        add_meta_box(
            'cbt_exam_questions',
            __( 'Exam Questions', 'cbt-exam-plugin' ),
            array( $this, 'render_exam_questions_meta_box' ),
            'cbt_exam',
            'normal',
            'high'
        );
    }

    /**
     * Render the meta box for "Exam Settings".
     *
     * @since    1.0.0
     * @param    WP_Post    $post    The post object.
     */
    public function render_exam_settings_meta_box( $post ) {
        wp_nonce_field( 'cbt_exam_settings_meta_box', 'cbt_exam_settings_meta_box_nonce' );

        $duration = get_post_meta( $post->ID, '_cbt_exam_duration', true );
        $pass_mark = get_post_meta( $post->ID, '_cbt_exam_pass_mark', true );
        $randomize = get_post_meta( $post->ID, '_cbt_randomize_questions', true );

        if ( '' === $randomize ) {
            $options = get_option( 'cbt_exam_options' );
            $randomize = isset( $options['default_randomization'] ) ? $options['default_randomization'] : 0;
        }
        ?>
        <p>
            <label for="cbt_exam_duration"><?php _e( 'Duration (in minutes)', 'cbt-exam-plugin' ); ?></label>
            <input type="number" id="cbt_exam_duration" name="cbt_exam_duration" value="<?php echo esc_attr( $duration ); ?>" />
        </p>
        <p>
            <label for="cbt_exam_pass_mark"><?php _e( 'Pass Mark (%)', 'cbt-exam-plugin' ); ?></label>
            <input type="number" id="cbt_exam_pass_mark" name="cbt_exam_pass_mark" value="<?php echo esc_attr( $pass_mark ); ?>" min="0" max="100" />
        </p>
        <p>
            <label for="cbt_randomize_questions">
                <input type="checkbox" id="cbt_randomize_questions" name="cbt_randomize_questions" value="1" <?php checked( $randomize, '1' ); ?> />
                <?php _e( 'Randomize Questions', 'cbt-exam-plugin' ); ?>
            </label>
        </p>
        <?php
    }

    /**
     * Render the meta box for "Exam Questions".
     *
     * @since    1.0.0
     * @param    WP_Post    $post    The post object.
     */
    public function render_exam_questions_meta_box( $post ) {
        wp_nonce_field( 'cbt_exam_questions_meta_box', 'cbt_exam_questions_meta_box_nonce' );

        $selected_questions = get_post_meta( $post->ID, '_cbt_exam_questions', true );
        if ( ! is_array( $selected_questions ) ) {
            $selected_questions = array();
        }

        $all_questions = get_posts( array(
            'post_type' => 'cbt_question',
            'numberposts' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        ) );
        ?>
        <div class="cbt-exam-questions-wrapper" style="max-height: 300px; overflow-y: auto; border: 1px solid #ccd0d4; padding: 10px;">
            <?php if ( ! empty( $all_questions ) ) : ?>
                <ul>
                    <?php foreach ( $all_questions as $question ) : ?>
                        <li>
                            <label>
                                <input type="checkbox" name="cbt_exam_questions[]" value="<?php echo $question->ID; ?>" <?php checked( in_array( $question->ID, $selected_questions ) ); ?> />
                                <?php echo esc_html( $question->post_title ); ?>
                            </label>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else : ?>
                <p><?php _e( 'No questions found. Please add some questions first.', 'cbt-exam-plugin' ); ?></p>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Save the meta box data for "Exam" post type.
     *
     * @since    1.0.0
     * @param    int    $post_id    The post ID.
     */
    public function save_exam_meta_data( $post_id ) {
        // Save Settings
        if ( isset( $_POST['cbt_exam_settings_meta_box_nonce'] ) && wp_verify_nonce( $_POST['cbt_exam_settings_meta_box_nonce'], 'cbt_exam_settings_meta_box' ) ) {
            if ( isset( $_POST['cbt_exam_duration'] ) ) {
                update_post_meta( $post_id, '_cbt_exam_duration', sanitize_text_field( $_POST['cbt_exam_duration'] ) );
            }
            if ( isset( $_POST['cbt_exam_pass_mark'] ) ) {
                update_post_meta( $post_id, '_cbt_exam_pass_mark', sanitize_text_field( $_POST['cbt_exam_pass_mark'] ) );
            }
            if ( isset( $_POST['cbt_randomize_questions'] ) ) {
                update_post_meta( $post_id, '_cbt_randomize_questions', '1' );
            } else {
                update_post_meta( $post_id, '_cbt_randomize_questions', '0' );
            }
        }

        // Save Questions
        if ( isset( $_POST['cbt_exam_questions_meta_box_nonce'] ) && wp_verify_nonce( $_POST['cbt_exam_questions_meta_box_nonce'], 'cbt_exam_questions_meta_box' ) ) {
            if ( isset( $_POST['cbt_exam_questions'] ) && is_array( $_POST['cbt_exam_questions'] ) ) {
                $question_ids = array_map( 'intval', $_POST['cbt_exam_questions'] );
                update_post_meta( $post_id, '_cbt_exam_questions', $question_ids );
            } else {
                delete_post_meta( $post_id, '_cbt_exam_questions' );
            }
        }
    }

    /**
     * Render the meta box for "Question" post type.
     *
     * @since    1.0.0
     * @param    WP_Post    $post    The post object.
     */
    public function render_question_meta_box( $post ) {
        // Add a nonce field so we can check for it later.
        wp_nonce_field( 'cbt_question_meta_box', 'cbt_question_meta_box_nonce' );

        // Use get_post_meta to retrieve an existing value from the database.
        $question_type = get_post_meta( $post->ID, '_cbt_question_type', true );
        $options = get_post_meta( $post->ID, '_cbt_question_options', true );
        $correct_answer = get_post_meta( $post->ID, '_cbt_correct_answer', true );
        $time_limit = get_post_meta( $post->ID, '_cbt_question_time_limit', true );

        ?>
        <div class="cbt-meta-box">
            <p>
                <label for="cbt_question_time_limit"><?php _e( 'Time Limit (in seconds)', 'cbt-exam-plugin' ); ?></label>
                <input type="number" id="cbt_question_time_limit" name="cbt_question_time_limit" value="<?php echo esc_attr( $time_limit ); ?>" />
                <br>
                <small><?php _e( 'Leave blank for no time limit.', 'cbt-exam-plugin' ); ?></small>
            </p>
            <p>
                <label for="cbt_question_type"><?php _e( 'Question Type', 'cbt-exam-plugin' ); ?></label>
                <select name="cbt_question_type" id="cbt_question_type">
                    <option value="objective" <?php selected( $question_type, 'objective' ); ?>><?php _e( 'Objective', 'cbt-exam-plugin' ); ?></option>
                    <option value="theory" <?php selected( $question_type, 'theory' ); ?>><?php _e( 'Theory', 'cbt-exam-plugin' ); ?></option>
                </select>
            </p>
            <div id="cbt_objective_fields" style="<?php echo ( $question_type === 'theory' ) ? 'display:none;' : ''; ?>">
                <h4><?php _e( 'Objective Options', 'cbt-exam-plugin' ); ?></h4>
                <div id="cbt_options_wrapper">
                    <?php
                    if ( ! empty( $options ) && is_array( $options ) ) {
                        foreach ( $options as $index => $option ) {
                            ?>
                            <p>
                                <input type="text" name="cbt_question_options[]" value="<?php echo esc_attr( $option ); ?>" placeholder="Option <?php echo $index + 1; ?>" />
                                <input type="radio" name="cbt_correct_answer" value="<?php echo $index; ?>" <?php checked( $correct_answer, $index ); ?> />
                                <label><?php _e( 'Correct Answer', 'cbt-exam-plugin' ); ?></label>
                            </p>
                            <?php
                        }
                    } else {
                        // Show 4 empty fields by default
                        for ( $i = 0; $i < 4; $i++ ) {
                            ?>
                            <p>
                                <input type="text" name="cbt_question_options[]" value="" placeholder="Option <?php echo $i + 1; ?>" />
                                <input type="radio" name="cbt_correct_answer" value="<?php echo $i; ?>" />
                                <label><?php _e( 'Correct Answer', 'cbt-exam-plugin' ); ?></label>
                            </p>
                            <?php
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
        <script>
            jQuery(document).ready(function($) {
                $('#cbt_question_type').on('change', function() {
                    if ( this.value === 'objective' ) {
                        $('#cbt_objective_fields').show();
                    } else {
                        $('#cbt_objective_fields').hide();
                    }
                });
            });
        </script>
        <?php
    }

    /**
     * Save the meta box data for "Question" post type.
     *
     * @since    1.0.0
     * @param    int    $post_id    The post ID.
     */
    public function save_question_meta_data( $post_id ) {
        // Check if our nonce is set.
        if ( ! isset( $_POST['cbt_question_meta_box_nonce'] ) ) {
            return;
        }
        // Verify that the nonce is valid.
        if ( ! wp_verify_nonce( $_POST['cbt_question_meta_box_nonce'], 'cbt_question_meta_box' ) ) {
            return;
        }
        // If this is an autosave, our form has not been submitted, so we don't want to do anything.
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        // Check the user's permissions.
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // Sanitize and save the data
        if ( isset( $_POST['cbt_question_type'] ) ) {
            update_post_meta( $post_id, '_cbt_question_type', sanitize_text_field( $_POST['cbt_question_type'] ) );
        }

        if ( isset( $_POST['cbt_question_time_limit'] ) ) {
            update_post_meta( $post_id, '_cbt_question_time_limit', sanitize_text_field( $_POST['cbt_question_time_limit'] ) );
        }

        if ( isset( $_POST['cbt_question_options'] ) ) {
            $options = array_map( 'sanitize_text_field', $_POST['cbt_question_options'] );
            update_post_meta( $post_id, '_cbt_question_options', $options );
        }

        if ( isset( $_POST['cbt_correct_answer'] ) ) {
            update_post_meta( $post_id, '_cbt_correct_answer', sanitize_text_field( $_POST['cbt_correct_answer'] ) );
        } else {
            delete_post_meta( $post_id, '_cbt_correct_answer' );
        }
    }

    /**
     * Register the custom post types for the plugin.
     *
     * @since    1.0.0
     */
    public function setup_post_types() {
        $this->register_question_post_type();
        $this->register_exam_post_type();
        $this->register_result_post_type();
        $this->register_taxonomies();
    }

    /**
     * Register the "Question" custom post type.
     *
     * @since    1.0.0
     */
    private function register_question_post_type() {
        $labels = array(
            'name'               => _x( 'Questions', 'post type general name', 'cbt-exam-plugin' ),
            'singular_name'      => _x( 'Question', 'post type singular name', 'cbt-exam-plugin' ),
            'menu_name'          => _x( 'Questions', 'admin menu', 'cbt-exam-plugin' ),
            'name_admin_bar'     => _x( 'Question', 'add new on admin bar', 'cbt-exam-plugin' ),
            'add_new'            => _x( 'Add New', 'question', 'cbt-exam-plugin' ),
            'add_new_item'       => __( 'Add New Question', 'cbt-exam-plugin' ),
            'new_item'           => __( 'New Question', 'cbt-exam-plugin' ),
            'edit_item'          => __( 'Edit Question', 'cbt-exam-plugin' ),
            'view_item'          => __( 'View Question', 'cbt-exam-plugin' ),
            'all_items'          => __( 'All Questions', 'cbt-exam-plugin' ),
            'search_items'       => __( 'Search Questions', 'cbt-exam-plugin' ),
            'parent_item_colon'  => __( 'Parent Questions:', 'cbt-exam-plugin' ),
            'not_found'          => __( 'No questions found.', 'cbt-exam-plugin' ),
            'not_found_in_trash' => __( 'No questions found in Trash.', 'cbt-exam-plugin' )
        );

        $args = array(
            'labels'             => $labels,
            'description'        => __( 'Description.', 'cbt-exam-plugin' ),
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => 'cbt-exam-plugin',
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'question' ),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => array( 'title', 'editor', 'author' ),
            'menu_icon'          => 'dashicons-editor-help',
        );

        register_post_type( 'cbt_question', $args );
    }

    /**
     * Register the "Exam" custom post type.
     *
     * @since    1.0.0
     */
    private function register_exam_post_type() {
        $labels = array(
            'name'               => _x( 'Exams', 'post type general name', 'cbt-exam-plugin' ),
            'singular_name'      => _x( 'Exam', 'post type singular name', 'cbt-exam-plugin' ),
            'menu_name'          => _x( 'Exams', 'admin menu', 'cbt-exam-plugin' ),
            'name_admin_bar'     => _x( 'Exam', 'add new on admin bar', 'cbt-exam-plugin' ),
            'add_new'            => _x( 'Add New', 'exam', 'cbt-exam-plugin' ),
            'add_new_item'       => __( 'Add New Exam', 'cbt-exam-plugin' ),
            'new_item'           => __( 'New Exam', 'cbt-exam-plugin' ),
            'edit_item'          => __( 'Edit Exam', 'cbt-exam-plugin' ),
            'view_item'          => __( 'View Exam', 'cbt-exam-plugin' ),
            'all_items'          => __( 'All Exams', 'cbt-exam-plugin' ),
            'search_items'       => __( 'Search Exams', 'cbt-exam-plugin' ),
            'parent_item_colon'  => __( 'Parent Exams:', 'cbt-exam-plugin' ),
            'not_found'          => __( 'No exams found.', 'cbt-exam-plugin' ),
            'not_found_in_trash' => __( 'No exams found in Trash.', 'cbt-exam-plugin' )
        );

        $args = array(
            'labels'             => $labels,
            'description'        => __( 'Description.', 'cbt-exam-plugin' ),
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => 'cbt-exam-plugin',
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'exam' ),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => array( 'title', 'editor', 'author' ),
        );

        register_post_type( 'cbt_exam', $args );
    }

    /**
     * Register the "Result" custom post type.
     *
     * @since    1.2.0
     */
    private function register_result_post_type() {
        $labels = array(
            'name'               => _x( 'Results', 'post type general name', 'cbt-exam-plugin' ),
            'singular_name'      => _x( 'Result', 'post type singular name', 'cbt-exam-plugin' ),
            'menu_name'          => _x( 'Results', 'admin menu', 'cbt-exam-plugin' ),
            'name_admin_bar'     => _x( 'Result', 'add new on admin bar', 'cbt-exam-plugin' ),
            'add_new'            => _x( 'Add New', 'result', 'cbt-exam-plugin' ),
            'add_new_item'       => __( 'Add New Result', 'cbt-exam-plugin' ),
            'new_item'           => __( 'New Result', 'cbt-exam-plugin' ),
            'edit_item'          => __( 'Edit Result', 'cbt-exam-plugin' ),
            'view_item'          => __( 'View Result', 'cbt-exam-plugin' ),
            'all_items'          => __( 'All Results', 'cbt-exam-plugin' ),
            'search_items'       => __( 'Search Results', 'cbt-exam-plugin' ),
            'parent_item_colon'  => __( 'Parent Results:', 'cbt-exam-plugin' ),
            'not_found'          => __( 'No results found.', 'cbt-exam-plugin' ),
            'not_found_in_trash' => __( 'No results found in Trash.', 'cbt-exam-plugin' )
        );

        $args = array(
            'labels'             => $labels,
            'description'        => __( 'Stores exam results for students.', 'cbt-exam-plugin' ),
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'            => true, // Show in admin for debugging
            'show_in_menu'       => 'cbt-exam-plugin',
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'supports'           => array( 'title', 'author' ),
        );

        register_post_type( 'cbt_result', $args );
    }

    /**
     * Register the taxonomies for the plugin.
     *
     * @since    1.0.0
     */
    private function register_taxonomies() {
        // Subject Taxonomy
        $subject_labels = array(
            'name'              => _x( 'Subjects', 'taxonomy general name', 'cbt-exam-plugin' ),
            'singular_name'     => _x( 'Subject', 'taxonomy singular name', 'cbt-exam-plugin' ),
            'search_items'      => __( 'Search Subjects', 'cbt-exam-plugin' ),
            'all_items'         => __( 'All Subjects', 'cbt-exam-plugin' ),
            'parent_item'       => __( 'Parent Subject', 'cbt-exam-plugin' ),
            'parent_item_colon' => __( 'Parent Subject:', 'cbt-exam-plugin' ),
            'edit_item'         => __( 'Edit Subject', 'cbt-exam-plugin' ),
            'update_item'       => __( 'Update Subject', 'cbt-exam-plugin' ),
            'add_new_item'      => __( 'Add New Subject', 'cbt-exam-plugin' ),
            'new_item_name'     => __( 'New Subject Name', 'cbt-exam-plugin' ),
            'menu_name'         => __( 'Subjects', 'cbt-exam-plugin' ),
        );
        $subject_args = array(
            'hierarchical'      => true,
            'labels'            => $subject_labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array( 'slug' => 'subject' ),
        );
        register_taxonomy( 'cbt_subject', array( 'cbt_question' ), $subject_args );

        // Topic Taxonomy
        $topic_labels = array(
            'name'              => _x( 'Topics', 'taxonomy general name', 'cbt-exam-plugin' ),
            'singular_name'     => _x( 'Topic', 'taxonomy singular name', 'cbt-exam-plugin' ),
            'search_items'      => __( 'Search Topics', 'cbt-exam-plugin' ),
            'all_items'         => __( 'All Topics', 'cbt-exam-plugin' ),
            'parent_item'       => __( 'Parent Topic', 'cbt-exam-plugin' ),
            'parent_item_colon' => __( 'Parent Topic:', 'cbt-exam-plugin' ),
            'edit_item'         => __( 'Edit Topic', 'cbt-exam-plugin' ),
            'update_item'       => __( 'Update Topic', 'cbt-exam-plugin' ),
            'add_new_item'      => __( 'Add New Topic', 'cbt-exam-plugin' ),
            'new_item_name'     => __( 'New Topic Name', 'cbt-exam-plugin' ),
            'menu_name'         => __( 'Topics', 'cbt-exam-plugin' ),
        );
        $topic_args = array(
            'hierarchical'      => true,
            'labels'            => $topic_labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array( 'slug' => 'topic' ),
        );
        register_taxonomy( 'cbt_topic', array( 'cbt_question' ), $topic_args );

        // Class Level Taxonomy
        $class_level_labels = array(
            'name'              => _x( 'Class Levels', 'taxonomy general name', 'cbt-exam-plugin' ),
            'singular_name'     => _x( 'Class Level', 'taxonomy singular name', 'cbt-exam-plugin' ),
            'search_items'      => __( 'Search Class Levels', 'cbt-exam-plugin' ),
            'all_items'         => __( 'All Class Levels', 'cbt-exam-plugin' ),
            'parent_item'       => __( 'Parent Class Level', 'cbt-exam-plugin' ),
            'parent_item_colon' => __( 'Parent Class Level:', 'cbt-exam-plugin' ),
            'edit_item'         => __( 'Edit Class Level', 'cbt-exam-plugin' ),
            'update_item'       => __( 'Update Class Level', 'cbt-exam-plugin' ),
            'add_new_item'      => __( 'Add New Class Level', 'cbt-exam-plugin' ),
            'new_item_name'     => __( 'New Class Level Name', 'cbt-exam-plugin' ),
            'menu_name'         => __( 'Class Levels', 'cbt-exam-plugin' ),
        );
        $class_level_args = array(
            'hierarchical'      => true,
            'labels'            => $class_level_labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array( 'slug' => 'class-level' ),
        );
        register_taxonomy( 'cbt_class_level', array( 'cbt_question', 'cbt_exam' ), $class_level_args );
    }

    /**
     * Register the stylesheets for the admin area.
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

        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/cbt-exam-plugin-admin.css', array(), $this->version, 'all' );

    }

    /**
     * Register the JavaScript for the admin area.
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

        wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/cbt-exam-plugin-admin.js', array( 'jquery' ), $this->version, false );

    }

}
