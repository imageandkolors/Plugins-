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
            'show_in_menu'       => true,
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
            'show_in_menu'       => 'edit.php?post_type=cbt_question',
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
            'show_in_menu'       => 'edit.php?post_type=cbt_question',
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
